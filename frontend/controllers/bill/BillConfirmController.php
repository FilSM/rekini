<?php

namespace frontend\controllers\bill;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\Response;

use common\controllers\FilSMController;
use common\models\mainclass\FSMBaseModel;
use common\models\user\FSMUser;
use common\models\bill\Bill;
/**
 * BillConfirmController implements the CRUD actions for BillConfirm model.
 */
class BillConfirmController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\bill\BillConfirm';
        $this->defaultSearchModel = 'common\models\bill\search\BillConfirmSearch';
    }

    /**
     * Updates an existing model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $payment_confirm_id = !empty($_GET['payment_confirm_id']) ? $_GET['payment_confirm_id'] : null;
        $model = $this->findModel($id);

        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxValidation($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return 'reload';
        } else {
            $isAdmin = FSMUser::getIsPortalAdmin();
            $billModelList = Bill::getNameArr(['status' => Bill::BILL_STATUS_PAYMENT, 'deleted' => false], 'doc_number', '', 'doc_number');
            return $this->renderAjax('create', [
                'model' => $model,
                'billModelList' => $billModelList,
                'isAdmin' => $isAdmin,
                'isModal' => true,
            ]);
        }
    }  
    
    /**
     * Deletes an existing single model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $model = $this->findModel($id);
        $billModel = $model->bill;
        $historyModel = $model->historyBill;
        if($model->delete()){
            if($historyModel){
                $historyModel->delete();
            }
            if($billModel->status == Bill::BILL_STATUS_PAID){
                $billModel->changeStatus(Bill::BILL_STATUS_PAYMENT);       
            }
        }
        return $this->redirect(FSMBaseModel::getBackUrl());
    }      
}