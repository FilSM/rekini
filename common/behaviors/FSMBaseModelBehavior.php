<?php

namespace common\behaviors;

use yii;
use yii\base\Exception;
use yii\base\Behavior;
use yii\db\ActiveRecord;

use common\models\mainclass\FSMBaseModel;

/**
 * 
 */
class FSMBaseModelBehavior extends Behavior {

    public function events() {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            FSMBaseModel::EVENT_BEFORE_MARK_AS_DELETED => 'beforeMarkAsDeleted',
        ];
    }

    public function beforeValidate($event) {
        
    }

    public function beforeSave($event) {
        if ($this->owner->hasAttribute('deleted')) {
            $this->owner->deleted = empty($this->owner->deleted) ? 0 : 1;
        }
    }

    public function beforeDelete($event) {
        if(!empty($this->owner->cascadeDeleting)){
            // need to delete all related rows from other models
            $relatedModels = $this->owner->relations;
            $ignoredFieldList = $this->owner->ignoredFieldsForDelete;
            $result = true;
            try {
                foreach ($relatedModels as $key => $rm) {
                    if(empty($rm) || in_array($rm['field'], $ignoredFieldList)){
                        continue;
                    }

                    $method = new \ReflectionMethod($this->owner, 'get' . $key);
                    $key = lcfirst(substr($method->getName(), 3));                
                    if(!$rm['hasMany']){
                        $model = $this->owner->{$key};
                        if(!empty($model)){
                            if(!$model->hasAttribute('deleted')){
                                $event->isValid = $this->owner->updateAttributes([$rm['field'] => null]);
                            }
                            $result = $model->delete();
                            $event->isValid = $event->isValid && ($result !== false);
                        }
                    }else{
                        $models = $this->owner->{$key};
                        if(!is_array($models)){
                            if(!empty($models)){
                                $result = $models->delete();
                                $event->isValid = $event->isValid && ($result !== false);
                            }
                        }else{
                            foreach ($models as $model) {
                                $result = $model->delete();
                                $event->isValid = $event->isValid && ($result !== false);
                            }
                        }
                    }
                    if(!$event->isValid){
                        break;
                    }
                }                
            } catch (Exception $e) {
                //echo $exc->getTraceAsString();
                $message = $e->getMessage();
                Yii::$app->getSession()->setFlash('error', $message);
                $event->isValid = false;
            } finally {
                
            }
        }
    }    

    public function beforeMarkAsDeleted($event) {
        if (!$this->owner->hasAttribute('deleted')) {
            throw new Exception('The model does not have the field "Deleted".');
        }
        $this->beforeDelete($event);
    }
    
}
