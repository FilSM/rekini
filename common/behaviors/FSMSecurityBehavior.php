<?php
namespace common\behaviors;

use yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

use dektrium\user\controllers\SecurityController;

use common\models\Cargo;

/**
 * 
 */
class FSMSecurityBehavior extends Behavior {
    
    public function events() {
        return [
            SecurityController::EVENT_AFTER_LOGIN => 'afterLogin',
        ];        
    }
    
    public function afterLogin($event) {
        $user = Yii::$app->user->identity;
        $profile = $user->profile;
        $client = isset($user->profile->client) ? $user->profile->client : null;
        Yii::$app->getSession()['user_client_id'] = ($client ? $client->id : null);
        Yii::$app->getSession()['user_client_it_is'] = ($client ? $client->it_is : null);
    }    
}
