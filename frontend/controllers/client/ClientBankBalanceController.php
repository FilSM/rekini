<?php

namespace frontend\controllers\client;

use Yii;
use yii\web\Response;

use common\controllers\FilSMController;
use common\assets\ButtonDeleteAsset;
use common\models\mainclass\FSMBaseModel;
use common\models\user\FSMUser;
use common\models\Bank;
use common\models\Valuta;
use common\models\FileXML;
use common\models\FilePDF;
use common\models\client\Client;
use common\models\client\ClientBank;
use common\models\client\ClientBankBalance;

/**
 * ClientBankBalanceController implements the CRUD actions for ClientBankBalance model.
 */
class ClientBankBalanceController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\client\ClientBankBalance';
        $this->defaultSearchModel = 'common\models\client\search\ClientBankBalanceSearch';
    }
    
    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($project_id = null) {
        $model = new $this->defaultModel;
        $filesXMLModel = new FileXML();
        $filesPDFModel = new FilePDF();

        $modelArr = [
            'ClientBankBalance' => $model,
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
                if($file && !$filesXMLModel->save()){
                    throw new Exception(Yii::t('bill', 'The XML file was saved with error!'));
                }
                $model->start_date = date('Y-m-d', strtotime($model->start_date));
                $model->end_date = date('Y-m-d', strtotime($model->end_date));                
                $model->uploaded_file_id = $filesXMLModel->id;
                $model->uploaded_pdf_file_id = $filesPDFModel->id;
                $model->currency = !empty($_POST['valuta_id']) ? Valuta::findOne($_POST['valuta_id'])->name : '';
                if (!$model->save()) {
                    throw new Exception(Yii::t('bill', 'The XML file data was saved with error!'));
                }
                $account = $model->account->account;
                $clientBankAccount = ClientBank::findOne([
                    'account' => $account,
                    'deleted' => false,
                ]);
                if(!$clientBankAccount->updateAttributes([
                        'uploaded_file_id' => $model->uploaded_file_id,
                        'uploaded_pdf_file_id' => $model->uploaded_pdf_file_id,
                        'start_date' => $model->start_date, 
                        'end_date' => $model->end_date,
                        'balance' => $model->balance,
                        'currency' => $model->currency,
                    ])){
                    throw new Exception(Yii::t('bill', 'The bank account data was saved with error!'));
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $filesXMLModel->delete();
                $filesPDFModel->delete();
                $transaction->rollBack();
                $message = $e->getMessage();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                if ($isPjax) {
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return 'reload';
                }else{
                    return $this->redirect('index');
                }                 
            }                
        } else {
            $isAdmin = FSMUser::getIsPortalAdmin();
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            $bankList = Bank::getNameArr();
            $valutaList = Valuta::getNameArr();
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('create', [
                    'model' => $model,
                    'filesXMLModel' => $filesXMLModel,
                    'filesPDFModel' => $filesPDFModel,
                    'clientList' => $clientList,
                    'bankList' => $bankList,
                    'valutaList' => $valutaList,
                    'isAdmin' => $isAdmin,
                    'isModal' => true,
                ]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                    'filesXMLModel' => $filesXMLModel,
                    'filesPDFModel' => $filesPDFModel,
                    'clientList' => $clientList,
                    'bankList' => $bankList,
                    'valutaList' => $valutaList,
                    'isAdmin' => $isAdmin,
                    'isModal' => false,
                ]);
            }            
        }
    }
    
    /**
     * Lists all models.
     * @return mixed
     */
    public function actionReport($account_id = null) {
        ButtonDeleteAsset::register(Yii::$app->getView());
        
        $searchModel = new $this->defaultSearchModel;
        $params = Yii::$app->request->getQueryParams();
        $dataProvider = $searchModel->search($params);
        
        $isAdmin = FSMUser::getIsPortalAdmin();
        return $this->render('report-bank-statement', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'isAdmin' => $isAdmin,
        ]);
    } 
}