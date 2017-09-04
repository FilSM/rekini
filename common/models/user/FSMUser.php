<?php

namespace common\models\user;

use Yii;
use yii\base\Exception;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

use kartik\password\StrengthValidator;

use dektrium\user\helpers\Password;

use common\models\client\Client;

/**
 * User ActiveRecord model.
 *
 * Database fields:
 * @property integer $id
 * @property string  $username
 * @property string  $email
 * @property string  $unconfirmed_email
 * @property string  $password_hash
 * @property string  $auth_key
 * @property integer $superuser
 * @property integer $registration_ip
 * @property integer $confirmed_at
 * @property integer $blocked_at
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $flags
 *
 * Defined relations:
 * @property Account[] $accounts
 * @property Profile   $profile
 */
class FSMUser extends \dektrium\user\models\User {

    const USER_ROLE_SUPERUSER   = 'superuser';
    const USER_ROLE_PORTALADMIN = 'portal_admin';
    const USER_ROLE_ADMIN       = 'admin';
    const USER_ROLE_BOSS        = 'boss';
    const USER_ROLE_MANAGER     = 'manager';
    const USER_ROLE_BOOKER      = 'booker';
    const USER_ROLE_OPERATOR    = 'operator';
    //const USER_ROLE_DRIVER      = 'driver';
    const USER_ROLE_USER        = 'user';
    
    public $role;
    public $client_id;
    public $clientItIs;
    
    /** @inheritdoc */
    public function scenarios() {
        $scenarios = parent::scenarios();
        return ArrayHelper::merge($scenarios, [
            'from-client' => ['username', 'email', 'password'],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        $rules = parent::rules();
        $rules = ArrayHelper::merge(
            $rules,
            [
                [['superuser'], 'integer'],
                [['password'], StrengthValidator::className(), 'preset' => 'normal', 'userAttribute' => 'username'],
                [['email'], 'required', 'on' => ['from-client']],
            ]
        );
        return $rules;          
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge(
            $labels, 
            [
                'client_id' => Yii::t('user', 'User company'),
                'superuser' => Yii::t('user', 'Superuser'),
                'role' => Yii::t('fsmuser', 'Role'),
                'name' => Yii::t('client', 'Full name'),
                'phone' => Yii::t('user', 'Phone'),
                'clientItIs' => Yii::t('user', 'Profile type'),
            ]
        );
        return $labels;
    }

    public function behaviors() {
        return [
            'modelBehavior' => array(
                'class' => \common\behaviors\UserModelBehavior::className(),
            ),
        ];
    }

    public static function modelTitle($n = 1, $translate = true) {
        return self::label('user', 'User|Users', $n, $translate);
    }

    public static function label($category, $message, $n = 1, $translate = true) {
        if (strpos($message, '|') !== false) {
            $chunks = explode('|', $message);
            $message = $chunks[$n - 1];
        }
        return $translate ? Yii::t($category, $message) : $message;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient() {
        $profile = $this->profile;
        return $profile ? $profile->hasOne(Client::className(), ['id' => 'client_id']) : null;
    }
    
    /**
     * @inheritdoc
     */
    public function register() {
        $result = parent::register();
        if (!$result) {
            $message = Yii::t('user', $this->modelTitle() . ' not inserted due to validation error.');
            $message = $this->getErrorMessage($message);
            Yii::$app->getSession()->setFlash('error', Yii::t('user', $message));
            return false;
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert) {
        if(parent::beforeSave($insert)) {
            if (empty($this->role)) {
                $this->role = [FSMUser::USER_ROLE_USER];
            }
            if($insert){
                $this->setAttribute('created_at', time());
            }
            $this->setAttribute('updated_at', time());
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes) {
        if ($insert) {
            if($this->id == 1){
                $this->updateAttributes([
                    'superuser' => 1,
                    //'role' => 'superuser',
                ]);
                $auth = Yii::$app->authManager;
                $authorRole = $auth->getRole(FSMUser::USER_ROLE_SUPERUSER);
                if(isset($authorRole)){
                    $auth->assign($authorRole, $this->id);
                }else{
                    throw new Exception('User role '.FSMUser::USER_ROLE_SUPERUSER.' not exist!');
                }
            }
            
            $userId = Yii::$app->getUser()->getId();
            $postData = Yii::$app->request->post();
            $profile = Yii::createObject([
                'class' => FSMProfile::className(),
                'version' => 0,
                'user_id' => $this->id,
                'client_id' => ($postData && isset($postData['FSMUser']) && !empty($postData['FSMUser']['client_id'])) ? $postData['FSMUser']['client_id'] : null,
                'name' => $this->username,
                'phone' => '',
                'gravatar_email' => $this->email,
                'timezone' => '',
                'deleted' => false,
                'create_user_id' => $userId,
                'create_time' => new Expression('NOW()'),
                'update_user_id' => $userId,
                'update_time' => new Expression('NOW()')
            ]);

            if (!$profile->save(false)) {
                $message = Yii::t('user', $profile->modelTitle() . ' not inserted due to non-specific error.');
                $message = $this->getErrorMessage($message);
                Yii::$app->getSession()->setFlash('error', Yii::t('user', $message));
            }
        }
        Yii\db\ActiveRecord::afterSave($insert, $changedAttributes);
    }

    public function getErrorMessage($defaultMessage = 'Undefined error!') {
        if ($this->hasErrors()) {
            $message = [];
            foreach ($this->getErrors() as $key => $attribute) {
                foreach ($attribute as $error) {
                    if(!in_array($error, $message)){
                        $message[] = $error;
                    }
                }
            }
            $message = implode("<br/>", $message);
        } else {
            $message = $defaultMessage;
        }
        return $message;
    }

    public function getMyRoleList() {
        static $result = null;
        static $oldUserId = null;
        
        $userId = $this->id;
        if(!isset($result) || ($oldUserId != $userId)){
            $oldUserId = $userId;
            $result = Yii::$app->authManager->getArrRolesByUser($userId);
        }
        return $result;
    }
    
    static public function getUserRoleList($userId = null) {
        static $result = null;
        static $oldUserId = null;
        
        if(!isset($result) || ($oldUserId != $userId)){
            if(!$userId){
                $currentUser = Yii::$app->user->identity;
                $userId = $currentUser ? $currentUser->id : null;
            }
            $oldUserId = $userId;
            $result = Yii::$app->authManager->getRolesByUser($userId);
        }
        return $result;
    }
    
    static public function getIsPortalAdmin($userId = null) {
        static $result = null;
        static $oldUserId = null;
        
        if(!isset($result) || ($oldUserId != $userId)){
            if(!$userId){
                $currentUser = Yii::$app->user->identity;
                $userId = $currentUser ? $currentUser->id : null;
            }else{
                $currentUser = self::findOne($userId);
            }
            $oldUserId = $userId;
            if(!$currentUser){
                $result = false;
                return $result;
            }
            $isAdmin = $currentUser->getIsAdmin();
            if($isAdmin){
                $result = true;
                return $result;
            }  else {
                $userRoles = Yii::$app->authManager->getRolesByUser($currentUser->id);
                foreach ($userRoles as $role) {
                    if(in_array($role->name, [
                            FSMUser::USER_ROLE_SUPERUSER, 
                            FSMUser::USER_ROLE_PORTALADMIN,
                        ])){
                        $result = true;
                        return $result;
                    }
                }
                $result = false;
                return $result;
            }
        }else{
            return $result;
        }
    }

    static public function getIsSystemAdmin($userId = null) {
        static $result = null;
        static $oldUserId = null;
        
        if(!isset($result) || ($oldUserId != $userId)){
            if(!$userId){
                $currentUser = Yii::$app->user->identity;
                $userId = $currentUser ? $currentUser->id : null;
            }else{
                $currentUser = self::findOne($userId);
            }
            $oldUserId = $userId;
            if(!$currentUser){
                $result = false;
                return $result;
            }
            $isAdmin = $currentUser->getIsAdmin();
            if($isAdmin){
                $result = true;
                return $result;
            }  else {
                $userRoles = Yii::$app->authManager->getRolesByUser($currentUser->id);
                foreach ($userRoles as $role) {
                    if(in_array($role->name, [
                            FSMUser::USER_ROLE_SUPERUSER, 
                            FSMUser::USER_ROLE_PORTALADMIN,
                            FSMUser::USER_ROLE_ADMIN,
                        ])){
                        $result = true;
                        return $result;
                    }
                }
                $result = false;
                return $result;
            }
        }else{
            return $result;
        }
    }

    static public function getMyClientType($userId = null) {
        static $result = null;
        static $oldUserId = null;
        
        if(!isset($result) || ($oldUserId != $userId)){
            if(!$userId){
                $currentUser = Yii::$app->user->identity;
                $userId = $currentUser ? $currentUser->id : null;
            }else{
                $currentUser = self::findOne($userId);
            }
            $oldUserId = $userId;
            if(!$currentUser){
                $result = false;
                return $result;
            }
            $client = !empty($currentUser->profile->client_id) ? $currentUser->profile->client : null;
            $result = $client ? $client->it_is : false;
        }
        return $result;
    }
    
    static public function getIamOwner($userId = null) {
        static $result = null;
        static $oldUserId = null;
        
        if(!isset($result) || ($oldUserId != $userId)){
            if(!$userId){
                $currentUser = Yii::$app->user->identity;
                $userId = $currentUser ? $currentUser->id : null;
            }else{
                $currentUser = self::findOne($userId);
            }
            $oldUserId = $userId;
            if(!$currentUser){
                $result = false;
                return $result;
            }
            $client = !empty($currentUser->profile->client_id) ? $currentUser->profile->client : null;
            $result = $client && ($client->it_is == Client::CLIENT_IT_IS_OWNER);
            return $result;
        }else{
            return $result;
        }
    }
    
    public function hasRole($userId = null, $roleList = null) {
        static $result = null;
        static $oldUserId = null;
        
        if(!isset($result) || ($oldUserId != $userId)){
            if(!$userId){
                $currentUser = Yii::$app->user->identity;
                $userId = $currentUser ? $currentUser->id : null;
            }            
            $oldUserId = $userId;
            $userRoles = $this->getUserRoleList($userId);
            $result = false;
            foreach ($userRoles as $role) {
                if($result = in_array($role->name, $roleList)){
                    break;
                }
            }
        }
        return $result;
    }
    
    public static function getRoleList() {
        static $result = null;
        
        if(!isset($result)){
            $authManager = Yii::$app->authManager;
            $result = [];
            foreach ($authManager->getRoles() as $name => $role) {
                $result[$name] = Yii::t('user', $role->description);
            }      
        }
        return $result;
    }
    
}
