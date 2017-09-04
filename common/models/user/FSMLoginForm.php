<?php

namespace common\models\user;

use Yii;
use yii\helpers\ArrayHelper;

class FSMLoginForm extends \dektrium\user\models\LoginForm {

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge(
            $labels,
            ['login' => \Yii::t('user', 'Username'),] 
            );
        return $labels;
    }
    
}
