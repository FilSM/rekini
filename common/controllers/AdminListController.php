<?php

namespace common\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

use common\controllers\FilSMController;

/**
 * Controller implements the CRUD actions for model.
 */
class AdminListController extends FilSMController {
    
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge(
            $behaviors,
                [
                'access' => [
                    'class' => AccessControl::className(),
                    'rules' => [
                        [
                            'controllers' => [
                                'language', 
                                'bank', 
                                'valuta', 
                                'measure', 
                                'product', 
                                'person-position', 
                                'action',
                                'reg-doc-type'],
                            'allow' => Yii::$app->user->can('showBackend'),
                            //'roles' => ['superuser', 'portal_admin', 'admin', 'boss', 'manager'],
                        ],
                    ],
                ],
            ]
        );
        return $behaviors;
    }
    
}
