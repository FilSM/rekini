<?php
namespace common\behaviors;

use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

use common\models\mainclass\FSMBaseModel;
use common\models\address\Address;

/**
 * 
 */
class FSMProfileModelBehavior extends FSMBaseModelBehavior {

    public function events() {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
            FSMBaseModel::EVENT_BEFORE_MARK_AS_DELETED => 'beforeDelete',
        ];
    }
    
    public function beforeDelete($event) {
        // need to delete all related rows from other models
        $relatedModels = [];
        foreach ($relatedModels as $rm) {
            if(empty($rm)){
                continue;
            }
            foreach ($rm as $m) {
                $m->delete();
            }
        }
    }
    
}
