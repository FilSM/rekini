<?php

namespace frontend\controllers\bill;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\Response;

use common\controllers\FilSMController;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;
use common\models\client\Client;
use common\models\client\ClientBank;
use common\models\client\ClientBankBalance;
use common\models\bill\Bill;
use common\models\bill\HistoryBill;
use common\models\bill\BillPayment;
use common\models\bill\BillConfirm;
use common\models\bill\PaymentConfirm;
use common\models\Bank;
use common\models\FileXML;
use common\models\FilePDF;
use common\models\mainclass\FSMBaseModel;

use common\assets\ButtonDeleteAsset;

/**
 * PaymentConfirmController implements the CRUD actions for PaymentConfirm model.
 */
class PaymentConfirmController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\bill\PaymentConfirm';
        $this->defaultSearchModel = 'common\models\bill\search\PaymentConfirmSearch';
    }

    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex()
    {
        ButtonDeleteAsset::register(Yii::$app->getView());

        $searchModel = new $this->defaultSearchModel;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        $bankList = Bank::getNameArr(['enabled' => true]);
        $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
        $userList = FSMProfile::getNameArr(['deleted' => false]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'bankList' => $bankList,
            'clientList' => $clientList,
            'userList' => $userList,
        ]);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new $this->defaultModel;
        $filesXMLModel = new FileXML();
        $filesPDFModel = new FilePDF();

        $modelArr = [
            'PaymentConfirm' => $model,
            'FilesXML' => $filesXMLModel,
            'FilesPDF' => $filesPDFModel,
        ];
        
        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxMultipleValidation($modelArr);
        }

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                $file = $filesPDFModel->uploadFile('payment/payment-confirm');
                if($file && !$filesPDFModel->save()){
                    throw new Exception(Yii::t('bill', 'The PDF file was saved with error!'));
                }
                $file = $filesXMLModel->uploadFile('payment/payment-confirm');
                if(!empty($file)){
                    if($filesXMLModel->save()){
                        if($result = $model->parseImportXML($filesXMLModel)){
                            $model->uploaded_file_id = $filesXMLModel->id;
                            $model->uploaded_pdf_file_id = $filesPDFModel->id;
                            if (!$model->save()) {
                                throw new Exception(Yii::t('bill', 'The XML file data was saved with error!'));
                            }else{
                                foreach ($result as $item) {
                                    if(!empty($item['PmtExtId'])){
                                        $billPayment = BillPayment::findOne($item['PmtExtId']);
                                    }
                                    $billConfirm = new BillConfirm();
                                    $billConfirm->payment_confirm_id = $model->id;
                                    $billConfirm->bill_payment_id = isset($billPayment) ? $billPayment->id : null;
                                    $billConfirm->bill_id = isset($billPayment) ? $billPayment->bill_id : null;
                                    $billConfirm->first_client_account = $item['BeneficiaryAccount'];
                                    $billConfirm->second_client_name = $item['PayeeName'];
                                    $billConfirm->second_client_reg_number = $item['PayeeLegalID'];
                                    $billConfirm->second_client_account = $item['PayeeAccount'];
                                    $billConfirm->second_client_id = !empty($item['PayeeExtId']) ? $item['PayeeExtId'] : null;
                                    $billConfirm->doc_date = $item['PmtDate'];
                                    $billConfirm->doc_number = !empty($item['PmtDocNo']) ? $item['PmtDocNo'] : null;
                                    $billConfirm->bank_ref = $item['PmtBankRef'];
                                    $billConfirm->direction = $item['CorD'];
                                    $billConfirm->summa = $item['PmtAmount'];
                                    $billConfirm->currency = $item['PmtCurrency'];
                                    $billConfirm->comment = $item['PmtInfo'];
                                    
                                    if(!$billConfirm->save()){
                                        throw new Exception(Yii::t('bill', 'Cannot save XML file data!'));
                                    }
                                    
                                    $clientBankAccount = ClientBank::findOne([
                                        'client_id' => $model->client_id, 
                                        'bank_id' => $model->bank_id, 
                                        'account' => $item['BeneficiaryAccount'],
                                        'deleted' => false,
                                    ]);
                                    if(!$clientBankAccount){
                                        $clientBankAccount = new ClientBank();
                                        $clientBankAccount->client_id = $model->client_id;
                                        $clientBankAccount->bank_id = $model->bank_id;
                                        $clientBankAccount->account = $item['BeneficiaryAccount'];
                                        if(!$clientBankAccount->save()){
                                            throw new Exception(Yii::t('bill', 'Cannot save new bank account!'));
                                        }
                                    }
                                    $clientBankAccount->updateAttributes([
                                        'uploaded_file_id' => $model->uploaded_file_id,
                                        'uploaded_pdf_file_id' => $model->uploaded_pdf_file_id,
                                        'start_date' => $model->start_date, 
                                        'end_date' => $model->end_date,
                                        'balance' => $item['closeBal'],
                                        'currency' => $item['currencyName'],
                                    ]);
                                    
                                    $clientBankBalance = ClientBankBalance::findOne([
                                        'account_id' => $clientBankAccount->id, 
                                        'start_date' => $model->start_date, 
                                        'end_date' => $model->end_date,
                                    ]);
                                    if(!$clientBankBalance){
                                        $clientBankBalance = new ClientBankBalance();
                                        $clientBankBalance->payment_confirm_id = $model->id;
                                        $clientBankBalance->account_id = $clientBankAccount->id;
                                        $clientBankBalance->start_date = $model->start_date;
                                        $clientBankBalance->end_date = $model->end_date;
                                        $clientBankBalance->uploaded_file_id = $model->uploaded_file_id;
                                        $clientBankBalance->uploaded_pdf_file_id = $model->uploaded_pdf_file_id;
                                        $clientBankBalance->balance = $item['closeBal'];
                                        $clientBankBalance->currency = $item['currencyName'];
                                        if(!$clientBankBalance->save()){
                                            throw new Exception(Yii::t('bill', 'Cannot save XML file data!'));
                                        }
                                    }
                                    unset($billConfirm, $clientBankAccount, $clientBankBalance);
                                }
                                Yii::$app->getSession()->setFlash('success', Yii::t('bill', 'The XML file data saving is complete!'));
                                $transaction->commit();
                                $result = true;
                            }
                        }else{
                            throw new Exception(Yii::t('bill', 'Cannot parse XML file!'));
                        }
                    }else{
                        throw new Exception(Yii::t('bill', 'The XML file data was saved with error!'));
                    }
                }else{
                    throw new Exception(Yii::t('bill', 'The XML file was not uploaded!'));
                }
                return $this->redirect('index');
            } catch (\Exception $e) {
                $filesXMLModel->delete();
                $filesPDFModel->delete();
                $transaction->rollBack();
                $message = $e->getMessage();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->redirect('index');
            }         
        } else {
            $isAdmin = FSMUser::getIsPortalAdmin();
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('create', [
                    'model' => $model,
                    'filesXMLModel' => $filesXMLModel,
                    'filesPDFModel' => $filesPDFModel,
                    'isAdmin' => $isAdmin,
                    'isModal' => true,
                ]);
            }else{
                return $this->render('create', [
                    'model' => $model,
                    'filesXMLModel' => $filesXMLModel,
                    'filesPDFModel' => $filesPDFModel,
                    'isAdmin' => $isAdmin,
                    'isModal' => false,
                ]);
            }
        }
    }      
    
    /**
     * Updates an existing model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        
        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirectToPreviousUrl($model->id);         
        } else {
            $this->rememberUrl($model->backURL, $id);            
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            return $this->render('update', [
                'model' => $model,
                'filesXMLModel' => null,
                'filesPDFModel' => null,
                'isAdmin' => $isAdmin,
                'isModal' => false,
            ]);
        }
    }
    
    /**
     * Displays a single model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        ButtonDeleteAsset::register(Yii::$app->getView());
        
        $model = $this->findModel($id);
        $billModel = $model->bills;        
        $billModel = !empty($billModel) ? $billModel : [new Bill()];
        
        $billConfirmModel = new \common\models\bill\search\BillConfirmSearch;
        $params = Yii::$app->request->getQueryParams();
        unset($params['id']);
        $params['payment_order_id'] = $id;
        $billConfirmDataProvider = $billConfirmModel->search($params);
        
        $isAdmin = FSMUser::getIsPortalAdmin();
        return $this->render('view', [
            'model' => $model,
            'billConfirmModel' => $billConfirmModel,
            'billConfirmDataProvider' => $billConfirmDataProvider,
            'isAdmin' => $isAdmin,
        ]);
    }      
    
    public function actionImport($id)
    {
        $model = $this->findModel($id);
        
        $billConfirmModel = new \common\models\bill\search\BillConfirmSearch;
        $params = Yii::$app->request->getQueryParams();
        unset($params['id']);
        $params['payment_confirm_id'] = $id;
        $billConfirmDataProvider = $billConfirmModel->search($params);
        $data = $billConfirmDataProvider->query->all();
        
        foreach ($data as $key => $item) {
            if(empty($item->bill_id)){
                unset($data[$key]);
            }
        }
        if(empty($data)){
            Yii::$app->getSession()->setFlash('error', Yii::t('bill', 'Cannot import data without invoices'));
            return $this->redirect(FSMBaseModel::getBackUrl());
        }

        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $result = true;
            foreach ($data as $confirmation) {
                $historyModel = new HistoryBill();
                $historyModel->bill_id = $confirmation->bill_id;
                $historyModel->action_id = HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_PAID'];
                $historyModel->create_time = date('Y-m-d H:i:s');
                $historyModel->create_user_id = Yii::$app->user->id;
                $historyModel->comment = (!empty($confirmation->comment) ? $confirmation->comment."\n" : '').
                    'Summa: '.number_format($confirmation->summa, 2).(!empty($confirmation->currency) ? ' '.$confirmation->currency : '');
                if (!$historyModel->save()) {
                    throw new Exception('Cannot to save data! '.$historyModel->errorMessage);
                }
                $confirmation->history_bill_id = $historyModel->id;
                
                $billModel = $confirmation->bill;
                
                $billPaymentModel = $confirmation->billPayment;
                if(empty($billPaymentModel)){
                    $billPaymentList = $billModel->billPayments;
                    if(!empty($billPaymentList)){
                        $billPaymentModel = end($billPaymentList);
                    }
                }
                if(!empty($billPaymentModel)){
                    $billPaymentModel->updateAttributes(['confirmed' => true]);
                    $confirmation->bill_payment_id = $billPaymentModel->id;
                    $billModel->changeStatus(Bill::BILL_STATUS_PAID, ['paid_date' => $confirmation->doc_date]);
                }else{
                    $billModel->changeStatus(Bill::BILL_STATUS_COMPLETE, ['paid_date' => $confirmation->doc_date]);
                    $historyModule = new HistoryBill();
                    $historyModule->saveHistory($billModel->id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_COMPLETE'], Bill::BILL_STATUS_COMPLETE);
                    unset($historyModule);                        
                }
                $result = $result && $confirmation->save();
            }
            if($result){
                $model->updateAttributes([
                    'status' => PaymentConfirm::IMPORT_STATE_COMPLETE, 
                    'action_time' => date('Y-m-d H:i'),
                    'action_user_id' => Yii::$app->user->identity->getId(),
                ]);
                Yii::$app->getSession()->setFlash('success', Yii::t('bill', 'The import from the XML file is complete!'));
                $transaction->commit();
            }else{
                throw new Exception(Yii::t('bill', 'The import was completed with error!'));
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            $message = $e->getMessage();
            Yii::$app->getSession()->setFlash('error', $message);
            Yii::error($message, __METHOD__);
        } finally {
            return $this->redirect(FSMBaseModel::getBackUrl());
        }        
    }    
}