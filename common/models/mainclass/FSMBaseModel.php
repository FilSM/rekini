<?php

namespace common\models\mainclass;

use Yii;
use yii\helpers\Url;
use yii\base\Exception;
use yii\base\ModelEvent;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;

use kartik\helpers\Html;

class FSMBaseModel extends \yii\db\ActiveRecord
{

    const EVENT_BEFORE_MARK_AS_DELETED = 'beforeMarkAsDeleted';

    private $_cascadeDeleting = false;
    public $_initDefaultValues = true;
    public $db = 'db';
    public $clone = false;
    protected $_externalFields = [];
    public static $keyField = 'id';
    public static $nameField = 'name';

    public function init()
    {
        if ($this->_initDefaultValues) {
            $tableName = static::getDb()->getSchema()->getRawTableName($this->tableName());
            if (!in_array($tableName, ['fsmbase_model', 'fsmcreate_model', 'fsmcreate_update_model', 'fsmversion_model'])) {
                $this->loadDefaultValues();
            }
        }
        parent::init();
    }

    public static function modelTitle($n = 1, $translate = true)
    {
        $class = get_called_class();
        $method = __FUNCTION__;
        throw new Exception("Method \"{$class}::{$method}()\" is not implemented");
    }

    public static function label($category, $message, $n = 1, $translate = true)
    {
        if (strpos($message, '|') !== false) {
            $chunks = explode('|', $message);
            $message = $chunks[$n - 1];
        }
        return $translate ? Yii::t($category, $message) : $message;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge(
                        $behaviors, [
                    'baseModelBehavior' => array(
                        'class' => \common\behaviors\FSMBaseModelBehavior::className(),
                    ),
                        ]
        );
        return $behaviors;
    }

    public function canCreate()
    {
        return !Yii::$app->user->isGuest;
    }

    public function canView()
    {
        return !Yii::$app->user->isGuest;
    }

    public function canUpdate()
    {
        return !Yii::$app->user->isGuest;
    }

    public function canDelete()
    {
        return !Yii::$app->user->isGuest;
    }

    public function getErrorMessage($defaultMessage = 'Undefined error!')
    {
        if ($this->hasErrors()) {
            $message = [];
            foreach ($this->getErrors() as $attribute) {
                foreach ($attribute as $error) {
                    $message[] = $error;
                }
            }
            $message = implode(PHP_EOL, $message);
        } else {
            $message = $defaultMessage;
        }
        return $message;
    }

    public function clearDefaultValues()
    {
        foreach ($this->getAttributes() as $attribute => $value) {
            if (isset($value)) {
                $this->__unset($attribute);
            }
        }
    }

    public function attributes($withExternal = true)
    {
        // add related fields to searchable attributes 
        if ($withExternal) {
            return ArrayHelper::merge(parent::attributes(), $this->getExternalFields());
        } else {
            return parent::attributes();
        }
    }

    public function getDirtyAttributes($names = null)
    {
        $attributes = parent::getDirtyAttributes($names);
        if (!empty($attributes)) {
            $table = $this->getTableSchema();

            foreach ($attributes as $name => $value) {
                if (!isset($table->columns[$name])) {
                    unset($attributes[$name]);
                    continue;
                }
                if ($table->columns[$name]->allowNull && (is_null($value) OR ( $value === ''))) {
                    if (empty($this->oldAttributes[$name])) {
                        unset($attributes[$name]);
                    } else {
                        $attributes[$name] = null;
                    }
                } elseif (in_array($table->columns[$name]->type, ['smallint', 'integer', 'decimal', 'number', 'double', 'float', 'time', 'datetime']) && is_string($value)) {
                    if (isset($this->oldAttributes[$name]) && ($value == $this->oldAttributes[$name])) {
                        unset($attributes[$name]);
                        continue;
                    }
                    switch ($table->columns[$name]->type) {
                        case 'smallint':
                        case 'integer':
                            $this->$name = intval($value);
                            break;
                        case 'decimal':
                        case 'number':
                            $this->$name = floatval(number_format(floatval($value), $table->columns[$name]->scale, '.', ''));
                            break;
                        case 'double':
                            $this->$name = doubleval($value);
                            break;
                        case 'float':
                            $this->$name = floatval($value);
                            break;
                        case 'time':
                            if ((strlen($value) == 5) &&
                                    (
                                    !isset($this->oldAttributes[$name]) ||
                                    (isset($this->oldAttributes[$name]) && ($value == substr($this->oldAttributes[$name], 0, 5)))
                                    )
                            ) {
                                $this->$name = $value . ':00';
                            }
                            break;
                        case 'datetime':
                            if ((strlen($value) == 16) &&
                                    (
                                    !isset($this->oldAttributes[$name]) ||
                                    (isset($this->oldAttributes[$name]) && ($value == substr($this->oldAttributes[$name], 0, 16)))
                                    )
                            ) {
                                $this->$name = $value . ':00';
                            }
                            break;
                        default:
                            break;
                    }
                } elseif (in_array($table->columns[$name]->type, ['decimal'])) {
                    $oldValue = isset($this->oldAttributes[$name]) ? number_format($this->oldAttributes[$name], $table->columns[$name]->scale) : null;
                    if (number_format($value, $table->columns[$name]->scale) == $oldValue) {
                        unset($attributes[$name]);
                    }
                } elseif (in_array($table->columns[$name]->type, ['double', 'float'])) {
                    $oldValue = isset($this->oldAttributes[$name]) ? strval($this->oldAttributes[$name]) : null;
                    if (strval($value) == $oldValue) {
                        unset($attributes[$name]);
                    }
                }
            }
        }
        return $attributes;
    }

    public function cloneSelf($count = 1)
    {
        if ($count == 0) {
            return true;
        }

        for ($index = 1; $index <= $count; $index++) {
            $className = get_class($this);
            $clone = new $className;
            $clone->attributes = $this->attributes;
            $clone->id = null;
            $clone->clone = true;
            $clone->save();
        }
    }

    /**
     * Creates and populates a set of models.
     *
     * @param string $modelClass
     * @param array $multipleModels
     * @return array
     */
    public static function createMultiple($modelClass, $multipleModels = [])
    {
        $model = new $modelClass;
        $formName = $model->formName();
        $post = Yii::$app->request->post($formName);
        $models = [];

        if (!empty($multipleModels)) {
            $keys = array_keys(ArrayHelper::map($multipleModels, self::$keyField, self::$keyField));
            $multipleModels = array_combine($keys, $multipleModels);
        }

        if ($post && is_array($post)) {
            foreach ($post as $i => $item) {
                if (isset($item[self::$keyField]) && !empty($item[self::$keyField]) && isset($multipleModels[$item[self::$keyField]])) {
                    $models[] = $multipleModels[$item[self::$keyField]];
                } else {
                    $models[] = new $modelClass;
                }
            }
        }

        unset($model, $formName, $post);

        return $models;
    }

    public function cloneModel($model, $count = 1)
    {
        if ($count == 0) {
            return true;
        }

        for ($index = 1; $index <= $count; $index++) {
            $className = get_class($model);
            $clone = new $className;
            $clone->attributes = $model->attributes;
            $clone->id = null;
            $clone->clone = true;
            $clone->save();
        }
    }

    public function load($data, $formName = null)
    {
        if (!$formName) {
            $formName = $this->formName();
        }
        $scope = null;
        if (!empty($data)) {
            if (!empty($formName)) {
                if (!isset($data[$formName])) {
                    $scope = '';
                } else {
                    $scope = $formName;
                    $addArr = [];
                    foreach ($data as $key => $item) {
                        if (!is_array($data[$key])) {
                            $addArr[$key] = $item;
                            unset($data[$key]);
                        }
                    }
                    if (!empty($addArr)) {
                        $data[$formName] = ArrayHelper::merge($data[$formName], $addArr);
                    }
                }
            }
        }

        return parent::load($data, $scope);
    }

    public static function loadMultiple($models, $data, $formName = null)
    {
        if ($formName === null) {
            /* @var $first Model */
            $first = reset($models);
            if ($first === false) {
                return false;
            }
            $formName = $first->formName();
        }

        $success = false;
        foreach ($models as $i => $model) {
            $modelClass = get_class($model);
            if ($formName == '') {
                if (!empty($data[$i])) {
                    $model->load($data[$i], '');
                    $success = true;
                } elseif (!empty($data[$modelClass])) {
                    $model->load($data[$modelClass], '');
                    $success = true;
                }
            } elseif (!empty($data[$formName][$i])) {
                $model->load($data[$formName][$i], '');
                $success = true;
            }
        }

        return $success;
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        $result = parent::validate($attributeNames, $clearErrors);
        if (!$result && !\Yii::$app->request->isAjax) {
            $message = $this->modelTitle() . \Yii::t('common', ' not updated due to validation error.');
            $message = $this->getErrorMessage($message);
            Yii::error($message, __METHOD__);
            Yii::$app->getSession()->setFlash('error', $message);
        }
        return $result;
    }

    static public function findWithTranslation($category = 'database', $idField = '', $nameField = '')
    {
        $idField = !empty($idField) ? $idField : self::$keyField;
        $nameField = !empty($nameField) ? $nameField : self::$nameField;
        $data = ArrayHelper::map(self::find()->orderBy($idField)->asArray()->all(), $idField, $nameField);
        $result = [];
        if (!empty($data)) {
            foreach ($data as $key => $item) {
                $result[$key] = Yii::t($category, $item);
            }
        }
        asort($result, SORT_STRING);
        return $result;
    }

    static public function getNameArr($where = null, $orderBy = '', $idField = '', $nameField = '')
    {
        $idField = !empty($idField) ? $idField : self::$keyField;
        $nameField = !empty($nameField) ? $nameField : self::$nameField;
        $orderBy = !empty($orderBy) ? $orderBy : self::$nameField;
        if(isset($where)){
            return ArrayHelper::map(self::findByCondition($where)->orderBy($orderBy)->asArray()->all(), $idField, $nameField);
        }else{
            return ArrayHelper::map(self::find()->orderBy($orderBy)->asArray()->all(), $idField, $nameField);
        }
    }

    static public function getNameList($search, array $args = null)
    {
        $query = self::find()->where(['LIKE', self::$nameField, $search]);
        if (!empty($args)) {
            $query->andWhere($args);
        }
        $data = $query->orderBy(self::$nameField)->asArray()->all();
        $result = ArrayHelper::map($data, self::$keyField, self::$nameField);
        return $result;
    }

    static public function getBackURL($defaultUrl = null)
    {
        $http = Yii::$app->request->getIsSecureConnection() ? 'https://' : 'http://';
        $serverName = Yii::$app->request->getServerName();
        $baseUrl = Yii::$app->request->getBaseUrl();
        $referrerPath = Yii::$app->request->getReferrer();
        $referrerPath = str_replace($http, '', $referrerPath);
        $referrerPath = str_replace($serverName, '', $referrerPath);
        $referrerPath = [str_replace($baseUrl, '', $referrerPath)];

        return !empty($referrerPath) ? $referrerPath : (!empty($defaultUrl) ? $defaultUrl : '');
    }

    static public function getCancelButton()
    {
        $referrerPath = self::getBackURL();
        return Html::a(Html::icon('ban-circle', ['class' => 'bootstrap-dialog-button-icon']).Yii::t('common', 'Cancel'), 
            $referrerPath, [
                'class' => 'btn btn-lg btn-default btn-cancel',
                'data-dismiss' => 'modal',
        ]);
    }

    static public function getResetButton()
    {
        return Html::resetButton(Yii::t('common', 'Cancel'), ['class' => 'btn btn-default']);
    }

    static public function getSaveButton()
    {
        return Html::submitButton(Yii::t('common', 'Save'), ['class' => 'btn btn-lg btn-success']);
    }

    public function getSubmitButton()
    {
        return Html::submitButton(Html::icon('ok', ['class' => 'bootstrap-dialog-button-icon']).
            ($this->isNewRecord ? Yii::t('common', 'Create') : Yii::t('common', 'Update')), [
            'class' => $this->isNewRecord ? 'btn btn-lg btn-success btn-submit' : 'btn btn-lg btn-primary btn-submit',
        ]);
    }

    static public function getBackButton($btnSize = '', $backPath = null)
    {
        $btnSize = !empty($btnSize) ? 'btn-'.$btnSize : '';
        $backPath = !empty($backPath) ? $backPath : self::getBackURL();
        return Html::a(Html::icon('arrow-left', ['class' => 'bootstrap-dialog-button-icon']).'&nbsp;'.Yii::t('common', 'Back'), 
            $backPath, 
            [
                'class' => "btn {$btnSize} btn-default btn-back",
            ]
        );
    }
    
    static public function getModalButton($btnOptions = [])
    {
        $btnOptions = ArrayHelper::merge([
            'formId' => '',
            'prefix' => '',
            'controller' => '',
            'isModal' => true,
            'options' => [],
        ], $btnOptions);
        extract($btnOptions);
        
        $addBtnId = isset($options['id']) ? $options['id'] : null;
        $refreshBtnId = isset($options['id']) ? $options['id'].'-refresh' : null;
        unset($options['id']);
        
        return 
            Html::button(Html::icon('plus'), ArrayHelper::merge(
                    [
                        'id' => $addBtnId, 
                        'class' => 'btn btn-primary '.($isModal ? 'show-new-tab-button' : 'show-modal-button'),
                        'modal-tag' => "btn-add-{$prefix}{$controller}-{$formId}",
                        'value' => (isset($parent) ?
                            Url::to(["/{$controller}/create", $parent['field_name'] => $parent['id']]) :
                            Url::to(["/{$controller}/create"])
                        ),
                        'title' => Yii::t('common', 'Add new'),
                    ],
                    $options
                )
            ).
            ($isModal ?
                Html::button(Html::icon('refresh'), ArrayHelper::merge(
                    $options,
                    [
                        'id' => $refreshBtnId, 
                        'class'=>'btn btn-success refresh-list-button',
                        'modal-tag' => "btn-refresh-{$prefix}{$controller}-{$formId}",
                        'value' => Url::to(["/{$controller}/ajax-modal-name-list"]),
                        'title' => Yii::t('common', 'Refresh'),
                        'style' => 'display: none;',
                    ])
                ) : ''
            );
    }
    
    static function getModalButtonContent($btnOptions = [])
    {
        return [
            //'content' => $this->getModalButton($btnOptions),
            'content' => self::getModalButton($btnOptions),
            'asButton' => true, 
        ];
    }

    protected function getCascadeDeleting()
    {
        return $this->_cascadeDeleting;
    }

    protected function setCascadeDeleting($value = 1)
    {
        $this->_cascadeDeleting = $value;
    }

    private function markAsDeleted()
    {
        $result = true;
        if ((!$this->deleted) && ($result = $this->beforeMarkAsDeleted())) {
            $result = $this->updateAttributes(['deleted' => true]);
        }
        return $result;
    }

    public function beforeMarkAsDeleted()
    {
        $event = new ModelEvent;
        $this->trigger(self::EVENT_BEFORE_MARK_AS_DELETED, $event);

        return $event->isValid;
    }

    public static function deleteByIDs(array $ids)
    {
        $result = true;
        foreach ($ids as $id) {
            $model = self::findOne($id);
            $result = $result && $model->delete();
        }
        return $result !== false;
    }

    public function delete()
    {
        if (!$this->hasAttribute('deleted')) {
            $result = parent::delete();
        } else {
            $result = $this->markAsDeleted();
        }
        return $result !== false;
    }

    protected function getIgnoredFieldsForDelete()
    {
        return [];
    }

    public function getExternalFields()
    {
        return $this->_externalFields;
    }

    public function setExternalFields(array $fields)
    {
        $fields = ArrayHelper::merge(
                        $fields, (array) $this->_externalFields
        );
        $this->_externalFields = $fields;
    }

    /**
     * @return array of relations to other model
     */
    protected function getRelations()
    {
        $db = Yii::$app->get($this->db, false);
        $baseTableName = $this->tableName();
        $baseClassName = $this->generateClassName($baseTableName);

        if (($pos = strpos($baseTableName, '.')) !== false) {
            $schemaName = substr($baseTableName, 0, $pos);
        } else {
            $schemaName = '';
        }

        $relations = [];
        foreach ($db->getSchema()->getTableSchemas($schemaName) as $table) {
            $tableName = $table->name;
            $className = $this->generateClassName($tableName);
            foreach ($table->foreignKeys as $refs) {
                $refTable = $refs[0];
                unset($refs[0]);
                $fks = array_keys($refs);
                $refClassName = $this->generateClassName($refTable);

                // Add relation for this table
                $relationName = $this->generateRelationName($relations, $className, $table, $fks[0], false);
                $relations[$className][$relationName] = [
                    'class' => $refClassName,
                    'field' => $fks[0],
                    'hasMany' => false,
                ];

                // Add relation for the referenced table
                $hasMany = false;
                if (count($table->primaryKey) > count($fks)) {
                    $hasMany = true;
                } else {
                    foreach ($fks as $key) {
                        if (!in_array($key, $table->primaryKey, true)) {
                            $hasMany = true;
                            break;
                        }
                    }
                }
                $relationName = $this->generateRelationName($relations, $refClassName, $refTable, $className, $hasMany);
                $relations[$refClassName][$relationName] = [
                    'class' => $className,
                    'field' => $fks[0],
                    'hasMany' => $hasMany,
                ];
            }

            if (($fks = $this->checkPivotTable($table)) === false) {
                continue;
            }
            $table0 = $fks[$table->primaryKey[0]][0];
            $table1 = $fks[$table->primaryKey[1]][0];
            $className0 = $this->generateClassName($table0);
            $className1 = $this->generateClassName($table1);

            $relationName = $this->generateRelationName($relations, $className0, $db->getTableSchema($table0), $table->primaryKey[1], true);
            $relations[$className0][$relationName] = [
                'class' => $className1,
                'field' => $table->primaryKey[1],
                'hasMany' => true,
            ];

            $relationName = $this->generateRelationName($relations, $className1, $db->getTableSchema($table1), $table->primaryKey[0], true);
            $relations[$className1][$relationName] = [
                'class' => $className0,
                'field' => $table->primaryKey[0],
                'hasMany' => true,
            ];
        }

        return isset($relations[$baseClassName]) ? $relations[$baseClassName] : [];
    }

    /**
     * Generates a class name from the specified table name.
     * @param string $tableName the table name (which may contain schema prefix)
     * @return string the generated class name
     */
    protected function generateClassName($tableName)
    {
        static $_classNames = [];

        if (isset($_classNames[$tableName])) {
            return $_classNames[$tableName];
        }

        if (($pos = strrpos($tableName, '.')) !== false) {
            $tableName = substr($tableName, $pos + 1);
        }

        $db = Yii::$app->get($this->db, false);
        $patterns = [];
        $patterns[] = "/^{$db->tablePrefix}(.*?)$/";
        $patterns[] = "/^(.*?){$db->tablePrefix}$/";
        $baseTableName = $this->tableName();
        if (strpos($baseTableName, '*') !== false) {
            $pattern = $baseTableName;
            if (($pos = strrpos($pattern, '.')) !== false) {
                $pattern = substr($pattern, $pos + 1);
            }
            $patterns[] = '/^' . str_replace('*', '(\w+)', $pattern) . '$/';
        }
        $className = $tableName;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $tableName, $matches)) {
                $className = $matches[1];
                break;
            }
        }

        return $_classNames[$tableName] = Inflector::id2camel($className, '_');
    }

    /**
     * Generate a relation name for the specified table and a base name.
     * @param array $relations the relations being generated currently.
     * @param string $className the class name that will contain the relation declarations
     * @param \yii\db\TableSchema $table the table schema
     * @param string $key a base name that the relation name may be generated from
     * @param boolean $multiple whether this is a has-many relation
     * @return string the relation name
     */
    protected function generateRelationName($relations, $className, $table, $key, $multiple)
    {
        if (strcasecmp(substr($key, -2), self::$keyField) === 0 && strcasecmp($key, self::$keyField)) {
            $key = rtrim(substr($key, 0, -2), '_');
        }
        if ($multiple) {
            $key = Inflector::pluralize($key);
        }
        $name = $rawName = Inflector::id2camel($key, '_');
        $i = 0;
        while (isset($table->columns[lcfirst($name)])) {
            $name = $rawName . ($i++);
        }
        while (isset($relations[$className][lcfirst($name)])) {
            $name = $rawName . ($i++);
        }

        return $name;
    }

    /**
     * Checks if the given table is a pivot table.
     * For simplicity, this method only deals with the case where the pivot contains two PK columns,
     * each referencing a column in a different table.
     * @param \yii\db\TableSchema the table being checked
     * @return array|boolean the relevant foreign key constraint information if the table is a pivot table,
     * or false if the table is not a pivot table.
     */
    protected function checkPivotTable($table)
    {
        $pk = $table->primaryKey;
        if (count($pk) !== 2) {
            return false;
        }
        $fks = [];
        foreach ($table->foreignKeys as $refs) {
            if (count($refs) === 2) {
                if (isset($refs[$pk[0]])) {
                    $fks[$pk[0]] = [$refs[0], $refs[$pk[0]]];
                } elseif (isset($refs[$pk[1]])) {
                    $fks[$pk[1]] = [$refs[0], $refs[$pk[1]]];
                }
            }
        }
        if (count($fks) === 2 && $fks[$pk[0]][0] !== $fks[$pk[1]][0]) {
            return $fks;
        } else {
            return false;
        }
    }

    static public function toFloat($num, $decimals = null)
    {
        $dotPos = strrpos($num, '.');
        $commaPos = strrpos($num, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
                ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) {
            return floatval(preg_replace("/[^0-9]/", "", $num));
        }

        $result = floatval(
                preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
                preg_replace("/[^0-9]/", "", substr($num, $sep + 1, strlen($num)))
        );

        return !$decimals ? $result : floatval(number_format($result, $decimals));
    }

    public function fieldToFloat($field, $decimals = null)
    {
        if (!isset($this->$field)) {
            return null;
        }
        return $this->toFloat($this->$field, $decimals);
    }
    
    static public function facetedSearch(array $param)
    {
        return null;
    }
}
