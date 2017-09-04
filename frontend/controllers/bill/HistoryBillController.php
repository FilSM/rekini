<?php

namespace frontend\controllers\bill;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\Response;

use common\controllers\FilSMController;
use common\models\Action;
use common\models\user\FSMProfile;
use common\models\bill\Bill;
use common\models\bill\HistoryBill;
use common\models\mainclass\FSMBaseModel;

/**
 * HistoryBillController implements the CRUD actions for HistoryBill model.
 */
class HistoryBillController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\bill\HistoryBill';
        $this->defaultSearchModel = 'common\models\bill\search\HistoryBillSearch';
        $this->pjaxIndex = true;
    }

    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new $this->defaultSearchModel;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        $userList = FSMProfile::getNameArr(['deleted' => false]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'userList' => $userList,
        ]);
    }

    protected function changeBillStatus($id, $action_id, $bill_status_id)
    {
        $model = new $this->defaultModel;
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            if ($model->saveHistory($id, $action_id, $bill_status_id)) {
                $billModel = Bill::findOne($id);
                $billModel->changeStatus($bill_status_id);
            }else{    
                throw new Exception('Cannot to save data! '.$model->errorMessage);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('error', $message);
            Yii::error($message, __METHOD__);
            return $this->refresh();
        } finally {
            return $this->redirect(FSMBaseModel::getBackUrl());
        }
    }

    protected function changeBillStatusForm($id, $action_id, $bill_status_id)
    {
        $model = new $this->defaultModel;
        $model->bill_id = !empty($id) ? $id : null;
        $model->action_id = $action_id;
        $model->create_time = date('d-M-Y H:i:s');
        $model->create_user_id = Yii::$app->user->id;

        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxValidation($model);
        }

        if ($model->load(Yii::$app->request->post())) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if ($model->saveHistory($id, $action_id, $bill_status_id)) {
                    $billModel = Bill::findOne($id);
                    $billModel->changeStatus($bill_status_id);
                } else {
                    $transaction->rollBack();
                    throw new Exception('Cannot to save data! '.$model->errorMessage);
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return 'reload';
            }
        } else {
            return $this->renderAjax('create', [
                    'model' => $model,
                    'isModal' => true,
            ]);
        }
    }

    public function actionBillRegister($id = null)
    {
        return $this->changeBillStatus($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_NEW'], Bill::BILL_STATUS_NEW);
    }

    public function actionBillSendSignature($id = null)
    {
        return $this->changeBillStatus($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_READY'], Bill::BILL_STATUS_READY);
    }

    public function actionBillSign($id = null)
    {
        return $this->changeBillStatus($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_SIGNED'], Bill::BILL_STATUS_SIGNED);
    }

    public function actionBillPrepPayment($id = null)
    {
        return $this->changeBillStatus($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_PREP_PAYMENT'], Bill::BILL_STATUS_PREP_PAYMENT);
    }

    public function actionBillPayment($id = null)
    {
        return $this->changeBillStatus($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_PAYMENT'], Bill::BILL_STATUS_PAYMENT);
    }

    public function actionBillPay($id = null)
    {
        return $this->changeBillStatus($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_PAID'], Bill::BILL_STATUS_PAID);
    }

    public function actionBillComplete($id = null)
    {
        return $this->changeBillStatus($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_COMPLETE'], Bill::BILL_STATUS_COMPLETE);
    }

    public function actionBillCancel($id = null)
    {
        return $this->changeBillStatus($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_CANCELED'], Bill::BILL_STATUS_CANCELED);
    }

    public function actionBillRollbackPrepar($id = null)
    {
        return $this->changeBillStatusForm($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_PREPAR'], Bill::BILL_STATUS_PREPAR);
    }

    public function actionBillRollbackNew($id = null)
    {
        return $this->changeBillStatusForm($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_NEW'], Bill::BILL_STATUS_NEW);
    }

    public function actionBillRollbackReady($id = null)
    {
        return $this->changeBillStatusForm($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_READY'], Bill::BILL_STATUS_READY);
    }

    public function actionBillRollbackSigned($id = null)
    {
        return $this->changeBillStatusForm($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_SIGNED'], Bill::BILL_STATUS_SIGNED);
    }

    public function actionBillRollbackPrepPayment($id = null)
    {
        return $this->changeBillStatusForm($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_PREP_PAYMENT'], Bill::BILL_STATUS_PREP_PAYMENT);
    }

    public function actionBillRollbackPayment($id = null)
    {
        return $this->changeBillStatusForm($id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_PAYMENT'], Bill::BILL_STATUS_PAYMENT);
    }

}
