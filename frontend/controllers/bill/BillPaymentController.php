<?php

namespace frontend\controllers\bill;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\Response;

use common\controllers\FilSMController;
use common\models\bill\HistoryBill;
use common\models\bill\Bill;
use common\models\bill\BillPayment;
use common\models\bill\PaymentOrder;
use common\models\mainclass\FSMBaseModel;
use common\models\user\FSMUser;

/**
 * BillPaymentController implements the CRUD actions for BillPayment model.
 */
class BillPaymentController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\bill\BillPayment';
        $this->defaultSearchModel = 'common\models\bill\search\BillPaymentSearch';
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAjaxCreate($id) {
        $model = new $this->defaultModel;
        $historyModel = new HistoryBill();
        $billModel = !empty($id) ? Bill::findOne($id) : new Bill();

        $model->bill_id = !empty($id) ? $id : null;
        $model->summa = number_format($billModel->total - $billModel->paymentsSumma, 2);

        $historyModel->bill_id = !empty($id) ? $id : null;
        $historyModel->action_id = HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_PAYMENT'];
        $historyModel->create_time = date('d-M-Y H:i:s');
        $historyModel->create_user_id = Yii::$app->user->id;

        $modelArr = [
            'BillPayment' => $model,
            'HistoryBill' => $historyModel,
        ];
        
        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxMultipleValidation($modelArr);
        }

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                $historyModel->create_time = date('Y-m-d H:i:s', strtotime($historyModel->create_time));
                $historyModel->comment .= (!empty($historyModel->comment) ? "\n" : '').'Summa: '.number_format($model->summa, 2);
                if (!$historyModel->save()) {
                    $transaction->rollBack();
                    throw new Exception('Cannot to save data! '.$historyModel->errorMessage);
                }
                $model->history_bill_id = $historyModel->id;
                //$model->pay_date = date('Y-m-d', strtotime($model->pay_date));
                if ($model->save()) {
                    $billModel = Bill::findOne($id);
                    $billModel->changeStatus(Bill::BILL_STATUS_PAYMENT);
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
            $paymentOrderList = PaymentOrder::getNameArr(['status' => PaymentOrder::EXPORT_STATE_PREPARE]);
            return $this->renderAjax('create', [
                'model' => $model,
                'historyModel' => $historyModel,
                'paymentOrderList' => $paymentOrderList,
                'isModal' => true,
            ]);
        }
    }
   
    public function actionAjaxCreateMany($ids) {
        if(empty($ids)){
            Yii::$app->response->format = Response::FORMAT_JSON;
            return 'reload';
        }
        $model = new $this->defaultModel;
        $historyModel = new HistoryBill();
        $historyModel->action_id = HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_PAYMENT'];
        $historyModel->create_time = date('d-M-Y H:i:s');
        $historyModel->create_user_id = Yii::$app->user->id;

        $modelArr = [
            'BillPayment' => $model,
            'HistoryBill' => $historyModel,
        ];
        
        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxMultipleValidation($modelArr);
        }

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                $paymentOrderId = $model->payment_order_id;
                $comment = $historyModel->comment;
                $billPaymentList = Yii::$app->request->post('BillPayment');
                foreach ($billPaymentList as $billPayment) {
                    if(!is_array($billPayment)){
                        continue;
                    }
                    $historyModel->id = null;
                    $historyModel->setIsNewRecord(true);
                    $historyModel->bill_id = $billPayment['bill_id'];
                    $historyModel->create_time = date('Y-m-d H:i:s', strtotime($historyModel->create_time));
                    $historyModel->comment = (!empty($comment) ? $comment."\n" : '').'Summa: '.number_format($billPayment['summa'], 2);
                    if (!$historyModel->save()) {
                        $transaction->rollBack();
                        throw new Exception('Cannot to save data! '.$historyModel->errorMessage);
                    }
                    
                    $payment = new $this->defaultModel;
                    $payment->history_bill_id = $historyModel->id;
                    $payment->payment_order_id = $paymentOrderId;
                    $payment->bill_id = $billPayment['bill_id'];
                    $payment->summa = $billPayment['summa'];
                    if ($payment->save()) {
                        $billModel = Bill::findOne($payment->bill_id);
                        $billModel->changeStatus(Bill::BILL_STATUS_PAYMENT);
                    } else {
                        $transaction->rollBack();
                        throw new Exception('Cannot to save data! '.$payment->errorMessage);
                    }
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
            $model = [];
            $idsArr = explode(',', $ids);
            foreach ($idsArr as $id) {
                $bill = Bill::findOne($id);
                $payment = new $this->defaultModel;
                $payment->bill_id = $id;
                $payment->bill_number = $bill->doc_number;
                $payment->summa = number_format($bill->total - $bill->paymentsSumma, 2);
                $model[] =  $payment;
            }

            $paymentOrderList = PaymentOrder::getNameArr(['status' => PaymentOrder::EXPORT_STATE_PREPARE]);
            return $this->renderAjax('create_many', [
            //return $this->render('create_many', [
                'model' => $model,
                'historyModel' => $historyModel,
                'paymentOrderList' => $paymentOrderList,
                'isModal' => true,
            ]);
        }
    }

    /**
     * Updates an existing model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $payment_order_id = !empty($_GET['payment_order_id']) ? $_GET['payment_order_id'] : null;
        $model = $this->findModel($id);
        $billModel = $model->bill;
        $historyModel = $model->historyBill;
        $historyModel->create_time = date('d-M-Y H:i:s');
        $historyModel->create_user_id = Yii::$app->user->id;

        $modelArr = [
            'BillPayment' => $model,
            'HistoryBill' => $historyModel,
        ];
        
        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxMultipleValidation($modelArr);
        }

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if(strpos($historyModel->comment, 'Summa: ') === false){
                    $historyModel->comment .= (!empty($historyModel->comment) ? "\n" : '').'Summa: '.number_format($model->summa, 2);
                }
                $historyModel->create_time = date('Y-m-d H:i:s', strtotime($historyModel->create_time));
                if (!$historyModel->save()) {
                    $transaction->rollBack();
                    throw new Exception('Cannot to save data! '.$historyModel->errorMessage);
                }
                $model->history_bill_id = $historyModel->id;
                //$model->pay_date = date('Y-m-d', strtotime($model->pay_date));
                if ($model->save()) {
                    $billModel = Bill::findOne($id);
                    $billModel->changeStatus(Bill::BILL_STATUS_PAYMENT);
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
            $isAdmin = FSMUser::getIsPortalAdmin();
            $paymentOrderList = PaymentOrder::getNameArr(['status' => PaymentOrder::EXPORT_STATE_PREPARE]);
            return $this->renderAjax('create', [
                'model' => $model,
                'historyModel' => $historyModel,
                'paymentOrderList' => $paymentOrderList,
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
            $historyModel->delete();
            $billModel->changeStatus(Bill::BILL_STATUS_PREP_PAYMENT);       
        }
        return $this->redirect(FSMBaseModel::getBackUrl());
    }    
}