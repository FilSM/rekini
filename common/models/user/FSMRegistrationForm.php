<?php

namespace common\models\user;

use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

use common\models\user\FSMUser;

class FSMRegistrationForm extends \dektrium\user\models\RegistrationForm {
    
    /** @var string */
    public $role;
    
    /** @inheritdoc */
    public function rules() {
        $rules = parent::rules();
        $rules = ArrayHelper::merge(
            $rules, 
            [
                [['role'], 'default', 'value' => FSMUser::USER_ROLE_USER],
                [['role'], 'required'],
            ]        
        );
        return $rules;
    }
 
    /** @inheritdoc */
    public function attributeLabels() {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge($labels, ['role' => Yii::t('fsmuser', 'Role'),]);
        return $labels;
    }
    
    /**
     * Registers a new user account.
     * @return bool
     */
    public function register() {
        if (!$this->validate()) {
            return false;
        }
        
        /** @var User $user */
        $user = Yii::createObject(FSMUser::className());
        $user->setScenario('register');
        $this->loadAttributes($user);
        
        $transaction = Yii::$app->getDb()->beginTransaction();        
        try {
            if(!$user->register()){
                $transaction->rollBack();
                return false;
            }
            
            $transaction->commit();

            Yii::$app->session->setFlash(
                'info',
                Yii::t('user', 'Your account has been created and a message with further instructions has been sent to your email')
            );
            return true;
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        
    }

}
