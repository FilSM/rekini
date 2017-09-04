<?php

namespace common\models\user;

use Yii;
use yii\helpers\ArrayHelper;
use dektrium\user\Mailer;

use common\models\user\FSMUser;

class FSMSettingsForm extends \dektrium\user\models\SettingsForm {

    /** @var string */
    public $role;
    private $_user;

    /** @return User */
    public function getUser() {
        if ($this->_user == null) {
            $this->_user = \Yii::$app->user->identity;
        }

        return $this->_user;
    }

    /** @inheritdoc */
    public function __construct(Mailer $mailer, $config = []) {
        if (isset($config['user'])) {
            $this->user = $config['user'];
        }
        parent::__construct($mailer, $config);
        $this->setAttributes(['role' => $this->user->role,], false);
    }

    /** @inheritdoc */
    public function scenarios() {
        $scenarios = parent::scenarios();
        $scenarios = ArrayHelper::merge($scenarios, ['admin_update' => ['role', 'username', 'email', 'password', 'new_password'],]);
        return $scenarios;        
    }

    public function setUser($user) {
        $this->_user = $user;
    }

    /** @inheritdoc */
    public function rules() {
        $rules = parent::rules();
        $rules = ArrayHelper::merge(
            $rules, 
            [
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
     * Saves new account settings.
     *
     * @return bool
     */
    public function save() {
        $this->user->role = $this->role;
        return parent::save();
    }

}
