<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Json; 
use yii\web\Response;

use common\controllers\FilSMController;
use common\models\client\ClientRole;

/**
 * ClientRoleController implements the CRUD actions for ClientRole model.
 */
class ClientRoleController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\client\ClientRole';
        $this->defaultSearchModel = 'common\models\client\search\ClientRoleSearch';
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new $this->defaultModel;

        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxValidation($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($isPjax) {
                return $this->actionAjaxModalNameList(['selected_id' => $model->id]);
            } else {
                return $this->redirect('index');
            }            
        } else {
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('create', [
                    'model' => $model,
                    'isModal' => true,
                ]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                    'isModal' => false,
                ]);
            }
        }
    }
}