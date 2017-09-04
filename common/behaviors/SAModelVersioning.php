<?php
namespace common\behaviors;

use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * 
 */
class SAModelVersioning extends FSMBaseModelBehavior {

    /**
     * Params that can be setted when declaring the
     * behavior.
     * There's an additional param that can be used to 
     * specify the version table name: 'VersionTable'.
     * If not setted it's value is {tableName}_version
     */
    protected $_createdAt = "";
    protected $_createdBy = "";
    protected $_operation = "";
    protected $_lastVersion;
    protected $_versionTable;
    protected $ownerPrimaryKey = 'id';

    public $createdByField = "log_user_id";
    public $createdAtField = "log_time";
    public $operationField = "log_operation";
    public $versionField = "version";
    public $deletedField = "deleted";
    public $removeVersioningOnDelete = true;
    public $markModelAsDeleted = false;
    
    public function events() {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }
    
    public function beforeValidate($event) {
        parent::beforeValidate($event);
        if($this->owner->scenario == 'search'){
            return false;
        }
        if(empty($this->getVersion())){
            $this->setVersion(0);
        }
        $key = $this->owner->tableSchema->primaryKey;
        if($this->ownerPrimaryKey != $key[0]){
            $this->ownerPrimaryKey = $key[0];
        }
    }
    
    public function beforeSave($event) {
        parent::beforeSave($event);
        $this->_createdBy = Yii::$app->getUser()->getId();
        $this->_operation = $event->name == 'beforeUpdate' ? 'update' : 'insert';
        $needNewVersion = ($this->_operation == 'insert');
        if($event->name == 'beforeUpdate'){
            $attributeList = $this->owner->getDirtyAttributes();
            if(isset($attributeList['create_time'])){
                unset($attributeList['create_time']);
            }
            if(isset($attributeList['create_user_id'])){
                unset($attributeList['create_user_id']);
            }
            if(isset($attributeList['update_time'])){
                unset($attributeList['update_time']);
            }
            if(isset($attributeList['update_user_id'])){
                unset($attributeList['update_user_id']);
            }
            $needNewVersion = !empty($attributeList);
        }
        if(!$needNewVersion){
            return true;
        }
        
        $command = Yii::$app->db->createCommand()->insert($this->versionTable, $this->versionedAttributes);
        if ($command->execute()) {
            $this->_lastVersion = Yii::$app->db->getLastInsertID();
            $this->owner->setAttribute($this->versionField, $this->_lastVersion);
        }
    }

    public function afterInsert($event) {
        $command = Yii::$app->db->createCommand()->update(
            $this->versionTable, 
            [
                $this->ownerPrimaryKey => $this->owner[$this->ownerPrimaryKey],
            ], 
            $this->ownerPrimaryKey.' = 0 and '.$this->versionField.' = :version', 
            [
                ':version' => $this->owner->getAttribute($this->versionField),
            ]
        );
        $command->execute();
    }
    
    public function beforeDelete($event) {
        parent::beforeDelete($event);
        if ($this->removeVersioningOnDelete) {
            $this->deleteVersioning(false);
        } else {
            $this->_createdBy = Yii::$app->getUser()->getId();
            $this->_operation = 'delete';
            $command = Yii::$app->db->createCommand()->insert($this->versionTable, $this->versionedAttributes);
            if ($command->execute()) {
                $this->_lastVersion = Yii::$app->db->getLastInsertID();
                $this->owner->setAttribute($this->versionField, $this->_lastVersion);
                if($this->markModelAsDeleted){
                    /*
                    $command = Yii::$app->db->createCommand()->update(
                        $this->owner->tableName(), 
                        array(
                            $this->deletedField => true,
                            $this->versionField => $version,
                        ), 
                        'id=:id', array(':id' => $this->owner->id)
                    );
                    if ($command->execute()) {
                     * 
                     */
                    $this->owner->setAttribute($this->deletedField, true);
                    /*
                    }
                     * 
                     */
                }
            }
        }
    }

    /**
     * Return the name of the version table for the model
     * Default to {tableName}_version
     * @return String return the name of the version table for the model
     */
    public function getVersionTable() {
        if ($this->_versionTable !== null) {
            return $this->_versionTable;
        } else {
            return "log_". preg_replace('/[^(\w)|(\x7F-\xFF)|(\s)]/', '', $this->owner->tableName());            
        }
    }

    public function setVersionTable($table) {
        $this->_versionTable = $this->setAttribute($table);
    }

    public function setVersionCreatedBy($createdBy) {
        $this->_createdBy = $this->setAttribute($createdBy);
    }

    public function getVersionCreatedBy() {
        return $this->_createdBy;
    }

    public function setVersionOperation($versionOperation) {
        $this->_operation = $this->setAttribute($versionOperation);
    }

    public function getVersionOperation() {
        return $this->_operation;
    }

    public function getVersionCreatedAt() {
        if ($this->_createdAt !== null) {
            return $this->_createdAt;
        } else {
            return time();
        }
    }

    public function setVersionCreatedAt($versionCreatedAt) {
        $this->_createdAt = $versionCreatedAt;
    }

    /**
     * @return Return if the model is at its last version
     */
    public function isLastVersion() {
        return $this->owner->getAttribute($this->versionField) === $this->getLastVersionNumber();
    }

    /**
     * @return int Return the version number of the model
     */
    public function getVersion() {
        if ($this->owner[$this->versionField] == null) {
            return 0;
        } else {
            return $this->owner[$this->versionField];
        }
    }

    public function setVersion($value) {
        $this->owner[$this->versionField] = $value;
    }
    
    /**
     * @return int Return the last version number of the model
     */
    public function getLastVersionNumber() {
        if ($this->_lastVersion !== null) {
            return $this->_lastVersion;
        } else {
            $lastVersion = Yii::$app->db->createCommand()
                    ->select("MAX($this->versionField) as version_number")
                    ->from($this->versionTable)
                    ->where($this->ownerPrimaryKey.' =: id', array(':id' => $this->owner->primaryKey))
                    ->queryRow();
            $this->_lastVersion = $lastVersion['version_number'];
            return $this->_lastVersion;
        }
    }

    /**
     * Remove all the versioned data from the version table
     * @param  boolean $updateVersion Wheither or not the original model version must be resetted
     */
    public function deleteVersioning($updateVersion = true) {
        Yii::$app->db->createCommand()->delete($this->versionTable, $this->ownerPrimaryKey.' = :id', array(
            ':id' => $this->owner->primaryKey
            )
        )->execute();
        if ($updateVersion) {
            Yii::$app->db->createCommand()->update($this->owner->tableName(), array(
                $this->versionField => 1,
                ), $this->ownerPrimaryKey.' = :id', array(':id' => $this->owner->primaryKey)
            )->execute();
        }
    }

    /**
     * Return all the versions of the Active Record Object
     * @return array List of the different versions of the object
     */
    public function getAllVersions() {
        $allVersionsArray = Yii::$app->db->createCommand()
                ->select('*')
                ->from($this->versionTable)
                ->where($this->ownerPrimaryKey.' = :id', array(':id' => $this->owner->primaryKey))
                ->order('version ASC')
                ->queryAll();
        if (!empty($allVersionsArray)) {
            return $this->populateActiveRecords($allVersionsArray);
        } else {
            return array();
        }
    }

    /**
     * Return the n last versions of the model.
     * @param int $number Number of the last versions to return. Default to 1.
     * @return array return an array containing the last versions or 
     * an empty array if no versions are available
     */
    public function getLastVersions($number = 1) {
        $lastVersionsArray = Yii::$app->db->createCommand()
                ->select('*')
                ->from($this->versionTable)
                ->where($this->ownerPrimaryKey.' = :id', array(':id' => $this->owner->primaryKey))
                ->order('version DESC')
                ->limit($number)
                ->queryAll();
        if (!empty($lastVersionsArray)) {
            return $this->populateActiveRecords($lastVersionsArray);
        } else {
            return array();
        }
    }

    /**
     * Return a version of the model or false if the version number doesn't exist
     * @param int $versionNumber Number of the version to return
     * @return mixed return an active record corresponding to the version number
     * or false if it doesn't exist in the db
     */
    public function getOneVersion($versionNumber) {
        $versionArray = Yii::$app->db->createCommand()
                ->select('*')
                ->from($this->versionTable)
                ->where(
                    $this->ownerPrimaryKey.' = :id AND '.$this->versionField.' = :version', array(
                    ':id' => $this->owner->primaryKey,
                    ':version' => $versionNumber,
                    )
                )
                ->queryRow();
        if ($versionArray) {
            return $this->populateNewRecord($versionArray, get_class($this->owner));
        } else {
            return false;
        }
    }

    /**
     * Convert the model to the given version
     * @param int $versionNumber The version to convert to
     * @return bool true if everything went fine, false otherwise
     */
    public function toVersion($versionNumber) {
        $versionArray = Yii::$app->db->createCommand()
                ->select('*')
                ->from($this->versionTable)
                ->where(
                    $this->ownerPrimaryKey.' = :id AND '.$this->versionField.' = :version', array(
                    ':id' => $this->owner->primaryKey,
                    ':version' => $versionNumber,
                    )
                )
                ->queryRow();
        if ($versionArray) {
            $this->populateActiveRecord($versionArray, $this->owner);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Compare 2 versions of the model
     * the number of the 2 version
     * @return mixed return an array containing the differences or false
     * if a version hasn't been found in the db
     */
    public function compareVersions($version1, $version2) {
        $versionsArray = Yii::$app->db->createCommand()
                ->select('*')
                ->from($this->versionTable)
                ->where(
                    array('and', $this->ownerPrimaryKey.' = :id', 
                        array(
                            'or', 
                            $this->versionField . ' = :version1', 
                            $this->versionField . ' = :version2'
                        )
                    ), 
                    array(
                        ':id' => $this->owner->primaryKey,
                        ':version1' => $version1,
                        ':version2' => $version2,
                    )
                )
                ->order("$this->versionField ASC")
                ->queryAll();
        if (!empty($versionsArray) && count($versionsArray) == 2) {
            //Watch attributes changing from one version to the other and put them in the array
            //penser � unset les attributs de version (version, operation, created by, created at)
            $differences = array();
            foreach ($versionsArray[0] as $index => $value) {
                if (isset($versionsArray[1][$index]) && $value !== $versionsArray[1][$index]) {
                    $differences[$index] = array($versionsArray[0]['version'] => $value, $versionsArray[1]['version'] => $versionsArray[1][$index]);
                }
            }
            $differences = $this->unsetVersionedAttributes($differences);
            return $differences;
        } else {
            return false;
        }
    }

    /**
     * Compare the actual model to the given version
     * @param int $versionNumber Version number to compare to
     * @return mixed An array containing the differences or false if the version number
     * doesn't exist in the db
     */
    public function compareTo($versionNumber) {
        $thisVersion = $this->owner->getAttributes(false);
        $versionArray = Yii::$app->db->createCommand()
                ->select('*')
                ->from($this->versionTable)
                ->where(
                    $this->ownerPrimaryKey.' = :id AND '.$this->versionField.' = :version', array(
                    ':id' => $this->owner->primaryKey,
                    ':version' => $versionNumber,
                    )
                )
                ->queryRow();
        if ($versionArray) {
            $differences = array();
            $thisVersion = $this->unsetVersionedAttributes($this->owner->getAttributes(false));
            foreach ($thisVersion as $index => $value) {
                if (isset($versionArray[$index]) && $value !== $versionArray[$index]) {
                    $differences[$index] = array('actual' => $value, $versionArray[$this->versionField] => $versionArray[$index]);
                }
            }
            return $differences;
        } else {
            return false;
        }
    }

    /**
     * Get the attributes to add to the version table:
     * - the attributes from the model (also the not safe ones)
     * - The version attributes (createdBy, ...)
     * @return array an array containing the attributes
     */
    protected function getVersionedAttributes() {
        $this->versionCreatedAt = date('Y-m-d H:i:s', time());

        //we don't save the actual version number in the version table since it'll be automatically incremented
        $withoutAttributes = ArrayHelper::merge(
                (array)$this->versionField,
                (array)$this->owner->getNonVersionFields()
        );        
        $versionedAttributes = $this->owner->getAttributes(null, $withoutAttributes);
        if($this->_operation == 'insert'){
            $versionedAttributes[$this->ownerPrimaryKey] = 0;
        }
        $versionedAttributes[$this->createdByField] = $this->versionCreatedBy;
        $versionedAttributes[$this->createdAtField] = $this->versionCreatedAt;
        $versionedAttributes[$this->operationField] = $this->versionOperation;
        //unset($versionedAttributes[$this->versionField]);
        return $versionedAttributes;
    }

    /**
     * Unset the versionned attributes that could be returned from sql requests
     * @param array $array the array to unset
     */
    protected function unsetVersionedAttributes($array) {
        unset($array[$this->versionField]);
        unset($array[$this->createdAtField]);
        unset($array[$this->createdByField]);
        unset($array[$this->operationField]);
        return $array;
    }

    protected function setAttribute($value) {
        if ($value == null) {
            return "";
        } else {
            return $value;
        }
    }

    /**
     * Create some Active Records from an array of their values
     * @param array $values Array of the values returned from the db
     * @return array return an array containing the CactiveRecords models
     */
    protected function populateActiveRecords($values) {
        $className = get_class($this->owner);
        $activeRecords = array();
        foreach ($values as $version) {
            $activeRecords[] = $this->populateNewRecord($version, $className);
        }
        return $activeRecords;
    }

    /**
     * Create a new active record object and fill it with the given value
     * @param array $values the values that the active record object need to be filled with
     * @param string $className Name of the class object to create
     * @return CActiveRecord Return the newly created object populated
     */
    protected function populateNewRecord($values, $className) {
        return $this->populateActiveRecord($values, new $className());
    }

    /**
     * Populate the given Active Record with the given values.
     * @param array $values The values to put in the Active record
     * @param CActiveRecord $model the object to populate
     * @return CActiveRecord return the populate active record
     */
    protected function populateActiveRecord($values, $model) {
        $model->versionOperation = $values[$this->operationField];
        $model->versionCreatedBy = $values[$this->createdByField];
        $model->versionCreatedAt = $values[$this->createdAtField];
        $model->setAttributes($values, false);
        return $model;
    }

}
