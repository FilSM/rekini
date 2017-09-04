<?php

namespace backend\controllers\user;

use Yii;
use yii\helpers\ArrayHelper;

use dektrium\user\controllers\SecurityController as BaseSecurityController;

class SecurityController extends BaseSecurityController {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge(
            $behaviors,
            [
                'FSMSecurityBehavior' => array(
                    'class' => \common\behaviors\FSMSecurityBehavior::className(),
                ),
            ]
        );
        return $behaviors;        
    }
}
