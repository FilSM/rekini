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
use common\models\bill\Bill;
use common\models\bill\PaymentOrder;
use common\models\Bank;
use common\models\Files;
use common\models\mainclass\FSMBaseModel;
use common\components\FSMArray2XML;

use common\assets\ButtonDeleteAsset;

/**
 * PaymentOrderController implements the CRUD actions for PaymentOrder model.
 */
class PaymentOrderController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\bill\PaymentOrder';
        $this->defaultSearchModel = 'common\models\bill\search\PaymentOrderSearch';
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
        $userList = FSMProfile::getNameArr(['deleted' => false]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'bankList' => $bankList,
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

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post())) {
            $model->pay_date = date('Y-m-d', strtotime($model->pay_date));
            if(!$model->save()){
                $this->refresh();
            }
            return $this->redirect('index');
        } else {
            $model->number = 'PO-EC'.date('dmY').'/'.$model->lastNumber;
            $model->pay_date = date('d-M-Y');
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            $bankList = Bank::getNameArr(['enabled' => true]);
            return $this->render('create', [
                'model' => $model,
                'bankList' => $bankList,
                'isAdmin' => $isAdmin,
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
        $model = $this->findModel($id);
        
        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post())) {
            $model->pay_date = date('Y-m-d', strtotime($model->pay_date));
            if(!$model->save()){
                $this->refresh();
            }
            
            return $this->redirectToPreviousUrl($model->id);              
        } else {
            $this->rememberUrl($model->backURL, $id);            
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            $model->pay_date = date('d-M-Y', strtotime($model->pay_date));
            $bankList = Bank::getNameArr(['enabled' => true]);
            return $this->render('update', [
                'model' => $model,
                'bankList' => $bankList,
                'isAdmin' => $isAdmin,
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
        
        $billPaymentModel = new \common\models\bill\search\BillPaymentSearch;
        $params = Yii::$app->request->getQueryParams();
        unset($params['id']);
        $params['payment_order_id'] = $id;
        $billPaymentDataProvider = $billPaymentModel->search($params);
        
        $isAdmin = FSMUser::getIsPortalAdmin();
        return $this->render('view', [
            'model' => $model,
            'billPaymentModel' => $billPaymentModel,
            'billPaymentDataProvider' => $billPaymentDataProvider,
            'isAdmin' => $isAdmin,
        ]);
    }     
    
    public function actionSend($id)
    {
        $model = $this->findModel($id);
        
        $billPaymentModel = new \common\models\bill\search\BillPaymentSearch;
        $params = Yii::$app->request->getQueryParams();
        unset($params['id']);
        $params['payment_order_id'] = $id;
        $billPaymentDataProvider = $billPaymentModel->search($params);
        $data = $billPaymentDataProvider->query->all();
        
        if(empty($data)){
            Yii::$app->getSession()->setFlash('error', Yii::t('bill', 'Cannot to export XML file without invoices'));
            return $this->redirect(FSMBaseModel::getBackUrl());
        }

        $fidavistaArr['Header'] = [
            'Timestamp' => date('YmdU'),
            'From' => Yii::$app->params['brandLabel'],
        ];
        foreach ($data as $payment) {
            $bill = $payment->bill;
            $agreement = $bill->agreement;
            $project = $agreement->project;
            $currency = $bill->valuta;
            $firstClient = $agreement->firstClient;
            $secondClient = $agreement->secondClient;
            $firstClientBank = $bill->firstClientBank;
            $secondClientBank = $bill->secondClientBank;
            $paymentArr = [
                'Payment' => [
                    'ExtId' => $payment->id,
                    'DocNo' => $bill->doc_number,
                    'RegDate' => $bill->doc_date,
                    'TaxPmtFlg' => ($project->vat_taxable ? 'Y' : 'N') ,
                    'Ccy' => $currency->name,
                    'PmtInfo' => $model->name,
                    'PayLegalId' => $firstClient->reg_number,
                    'PayAccNo' => $firstClientBank->account,
                    'DebitCcy' => $currency->name,
                    'BenSet' => [
                        'BenExtId' => $firstClient->id,
                        'Priority' => 'N',
                        'Comm' => 'OUR',
                        'Amt' => number_format($payment->summa, 2),
                        'BenAccNo' => $secondClientBank->account,
                        'BenName' => $secondClient->name,
                        'BenLegalId' => $secondClient->reg_number,
                        'BenAddress' => $secondClient->legal_address,
                        'BenCountry' => $secondClient->legalCountry->short_name,
                        'BBName' => $secondClientBank->bank->name,
                        'BBAddress' => $secondClientBank->bank->address,
                        'BBSwift' => $secondClientBank->bank->swift,
                    ]
                ]
            ];
            array_push($fidavistaArr, $paymentArr);
        }
         
        $xmlArr = $fidavistaArr;
        //$xmlArr = ['FIDAVISTA' => $fidavistaArr];
        
        $converter = new FSMArray2XML();
        $converter->setRootName(['FIDAVISTA' => ['xmlns' => 'http://bankasoc.lv/fidavista/fidavista_1-2.xsd']]);
        $xmlStr = $converter->convert($xmlArr);
        
        $filesModel = new Files();
        $filename = date('Ymd-H-i').'-'.Yii::$app->security->generateRandomString().".xml";
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $result = $filesModel->saveDataToFile($filename, $xmlStr, 'payment/payment-order');
            if($result){
                $model->updateAttributes([
                    'status' => PaymentOrder::EXPORT_STATE_SENT, 
                    'file_id' => $filesModel->id,
                    'action_time' => date('Y-m-d H:i'),
                    'action_user_id' => Yii::$app->user->identity->getId(),
                ]);
                //Yii::$app->getSession()->setFlash('success', Yii::t('bill', 'The export of the XML file is complete! Email sent to '.$recipientMail));
                Yii::$app->getSession()->setFlash('success', Yii::t('bill', 'The XML file has generated!'));
                $transaction->commit();
            }else{
                throw new Exception(Yii::t('bill', 'The export of the XML file was completed with error!'));
            }
        } catch (\Exception $e) {
            $filesModel->delete();
            $transaction->rollBack();
            $message = $e->getMessage();
            Yii::$app->getSession()->setFlash('error', $message);
            Yii::error($message, __METHOD__);
        } finally {
            return $this->redirect(FSMBaseModel::getBackUrl());
        }         
    }
}