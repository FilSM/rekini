<?php
namespace common\behaviors;

use yii;
use yii\base\Exception;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * 
 */
class UserModelBehavior extends Behavior {

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }
    
    
    public function beforeValidate($event) {
        if($this->owner->role){
            $this->owner->role = (array)$this->owner->role;
        }else{
            $this->owner->role = [\common\models\user\FSMUser::USER_ROLE_USER];
        }
    }    
    
    public function afterInsert($event) {
        // the following three lines were added:
        $auth = Yii::$app->authManager;
        $auth->revokeAll($this->owner->id);
        $roleArr = $this->owner->role;
        foreach ($roleArr as $role) {
            $authorRole = $auth->getRole($role);
            if(isset($authorRole)){
                $auth->assign($authorRole, $this->owner->id);
            }else{
                throw new Exception('User role '.$role.' not exist!');
            }
        }
    }
    
    public function beforeDelete($event) {
        $result = true;
        if($profile = $this->owner->profile){
            $result = $profile->delete();
        }
        $event->isValid = $result;
    }

}
