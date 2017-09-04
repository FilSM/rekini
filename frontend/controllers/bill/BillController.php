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
use common\models\user\FSMProfile;
use common\models\Valuta;
use common\models\client\Project;
use common\models\client\Agreement;
use common\models\client\Client;
use common\models\client\ClientRole;
use common\models\bill\Bill;
use common\models\bill\HistoryBill;
use common\models\bill\BillProduct;
use common\models\Product;
use common\models\Measure;
use common\models\Language;
use common\assets\ButtonDeleteAsset;
use common\assets\ButtonMultiActionAsset;
use common\printDocs\PrintModule;
use frontend\assets\BillUIAsset;

/**
 * BillController implements the CRUD actions for Bill model.
 */
class BillController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\bill\Bill';
        $this->defaultSearchModel = 'common\models\bill\search\BillSearch';
        $this->pjaxIndex = true;
        
        \lajax\translatemanager\helpers\Language::registerAssets();
    }
        
    public function actionAjaxGetLastNumber($id, $doc_type)
    {
        if(!empty($id)){
            $model = $this->findModel($id);
        }else{
            $model = new $this->defaultModel;
            $model->doc_type = $doc_type;
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        switch ($doc_type){
            case 'avans':
                $dType = 'A';
                break;
            case 'bill':
                $dType = 'R';
                break;
            case 'invoice':
                $dType = 'P';
                break;
        }
        $out = $dType.'-EC'.date('dmY').'/'.$model->lastNumber;
        return $out;        
    }
    
    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex() {
        if (YII_ENV_DEV) {
            $this->actionCheckDelayed(true);
        }
        
        BillUIAsset::register(Yii::$app->getView());
        ButtonDeleteAsset::register(Yii::$app->getView());
        ButtonMultiActionAsset::register(Yii::$app->getView());
        
        $searchModel = new $this->defaultSearchModel;
        $params = Yii::$app->request->getQueryParams();
        $params['deleted'] = (empty($params) || empty($params['BillSearch'])) ?
            0 :
            (isset($params['BillSearch']['deleted']) && ($params['BillSearch']['deleted'] !== '') ?
                    $params['BillSearch']['deleted'] :
                    0
            );

        $dataProvider = $searchModel->search($params);

        $filter = isset($params['BillSearch']) ? $params['BillSearch'] : [];
        $isAdmin = FSMUser::getIsPortalAdmin();
        $projectList = Project::getNameArr(['deleted' => false]);
        $agreementFilter = [
            'deleted' => false,
        ];
        if(!empty($filter['project_id'])){
            $agreementFilter['project_id'] = $filter['project_id'];
        }
        $agreementList = Agreement::getNameArr($agreementFilter);
        $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
        $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'projectList' => $projectList,
            'agreementList' => $agreementList,
            'clientList' => $clientList,
            'managerList' => $managerList,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function actionReportDetailsEbitda($project_id)
    {
        BillUIAsset::register(Yii::$app->getView());
        ButtonDeleteAsset::register(Yii::$app->getView());
        ButtonMultiActionAsset::register(Yii::$app->getView());
        
        $searchModel = new $this->defaultSearchModel;
        $reportTitle = $searchModel->modelTitle(2).'. '.Yii::t('report', 'EBITDA report details: ').Project::findOne($project_id)->name;
        
        $params = Yii::$app->request->getQueryParams();
        $params['doc_type'] = ['bill', 'cr_bill', 'invoice', 'debt', 'cession'];
        $params['status'] = ['signed', 'prepar_payment', 'payment', 'paid', 'complete'];
        $params['deleted'] = (empty($params) || empty($params['BillSearch'])) ?
            0 :
            (isset($params['BillSearch']['deleted']) && ($params['BillSearch']['deleted'] !== '') ?
                    $params['BillSearch']['deleted'] :
                    0
            );
        $dataProvider = $searchModel->search($params);

        $searchExpenseModel = new \common\models\bill\search\ExpenseSearch();
        $dataExpenseProvider = $searchExpenseModel->search($params);
        
        $isAdmin = FSMUser::getIsPortalAdmin();
        $projectList = Project::getNameArr(['deleted' => false]);
        $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
        $expenseTypeList = \common\models\bill\ExpenseType::getNameArr();
        $valutaList = Valuta::getNameArr();
        
        return $this->render('ebitda-details', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'dataExpenseProvider' => $dataExpenseProvider,
            'searchExpenseModel' => $searchExpenseModel,
            'projectList' => $projectList,
            'clientList' => $clientList,
            'expenseTypeList' => $expenseTypeList,
            'valutaList' => $valutaList,
            'isAdmin' => $isAdmin,
            'reportTitle' => $reportTitle,
            'reportDetails' => 'ebitda',
            'direction' => !empty($params['direction']) ? $params['direction'] : null,
        ]);       
    }
    
    public function actionReportDetailsDebitorCreditor()
    {
        BillUIAsset::register(Yii::$app->getView());
        ButtonDeleteAsset::register(Yii::$app->getView());
        ButtonMultiActionAsset::register(Yii::$app->getView());
        
        $searchModel = new $this->defaultSearchModel;
        $params = Yii::$app->request->getQueryParams();

        $reportTitle = $searchModel->modelTitle(2).'. '.Yii::t('report', 'Debtors/Creditors report details: ').Client::findOne($params['client_id'])->name;
        
        $params['doc_type'] = ['bill', 'cr_bill', 'invoice', 'debt', 'cession'];
        $params['status'] = ['signed', 'prepar_payment', 'payment', 'paid', 'complete'];
        $params['pay_status'] = ['not', 'part'];
        if($params['direction'] == 'in'){
            $params['BillSearch']['first_client_id'] = $params['client_id'];
        }else{
            $params['BillSearch']['second_client_id'] = $params['client_id'];
        }
        unset($params['client_id'], $params['direction']);
        $params['deleted'] = (empty($params) || empty($params['BillSearch'])) ?
            0 :
            (isset($params['BillSearch']['deleted']) && ($params['BillSearch']['deleted'] !== '') ?
                    $params['BillSearch']['deleted'] :
                    0
            );
        $dataProvider = $searchModel->search($params);

        $filter = isset($params['BillSearch']) ? $params['BillSearch'] : [];
        $isAdmin = FSMUser::getIsPortalAdmin();
        $projectList = Project::getNameArr(['deleted' => false]);
        $agreementFilter = [
            'deleted' => false,
        ];
        if(!empty($filter['project_id'])){
            $agreementFilter['project_id'] = $filter['project_id'];
        }
        $agreementList = Agreement::getNameArr($agreementFilter);
        $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
        $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
        $valutaList = Valuta::getNameArr();
        return $this->render('debitor-creditor-details', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'projectList' => $projectList,
            'agreementList' => $agreementList,
            'clientList' => $clientList,
            'managerList' => $managerList,
            'valutaList' => $valutaList,
            'isAdmin' => $isAdmin,
            'reportTitle' => $reportTitle,
        ]);       
    }
    
    public function actionReportDetailsVat()
    {
        BillUIAsset::register(Yii::$app->getView());
        ButtonDeleteAsset::register(Yii::$app->getView());
        ButtonMultiActionAsset::register(Yii::$app->getView());
        
        $searchModel = new $this->defaultSearchModel;
        $params = Yii::$app->request->getQueryParams();

        $reportTitle = $searchModel->modelTitle(2).'. '.Yii::t('report', 'VAT report details: ').Client::findOne($params['client_id'])->name;
        
        $params['doc_type'] = ['bill', 'cr_bill', 'invoice', 'debt', 'cession'];
        $params['status'] = ['signed', 'prepar_payment', 'payment', 'paid', 'complete'];
        //$params['BillSearch']['pay_status'] = ['not', 'part'];        
        if($params['direction'] == 'in'){
            $params['BillSearch']['first_client_id'] = $params['client_id'];
        }else{
            $params['BillSearch']['second_client_id'] = $params['client_id'];
        }
        unset($params['client_id'], $params['direction']);
        $params['deleted'] = (empty($params) || empty($params['BillSearch'])) ?
            0 :
            (isset($params['BillSearch']['deleted']) && ($params['BillSearch']['deleted'] !== '') ?
                    $params['BillSearch']['deleted'] :
                    0
            );
        $dataProvider = $searchModel->search($params);

        $filter = isset($params['BillSearch']) ? $params['BillSearch'] : [];
        $isAdmin = FSMUser::getIsPortalAdmin();
        $projectList = Project::getNameArr(['deleted' => false]);
        $agreementFilter = [
            'deleted' => false,
        ];
        if(!empty($filter['project_id'])){
            $agreementFilter['project_id'] = $filter['project_id'];
        }
        $agreementList = Agreement::getNameArr($agreementFilter);
        $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
        $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
        $valutaList = Valuta::getNameArr();
        return $this->render('vat-details', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'projectList' => $projectList,
            'agreementList' => $agreementList,
            'clientList' => $clientList,
            'managerList' => $managerList,
            'valutaList' => $valutaList,
            'isAdmin' => $isAdmin,
            'reportTitle' => $reportTitle,
        ]);       
    }
    
    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new $this->defaultModel;
        $agreementModel = new Agreement();
        $modelArr = [
            'Bill' => $model,
            'Agreement' => $agreementModel,
        ];
        
        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if(empty($model->agreement_id)){
                    throw new Exception('Agreement was not defined!');
                }
                $model->abonent_id = $model->agreement->abonent_id;
                $model->doc_date = date('Y-m-d', strtotime($model->doc_date));
                $model->pay_date = date('Y-m-d', strtotime($model->pay_date));
                //if(isset($model->abonent_id)) {
                    if (!$model->save()) {
                        throw new Exception('Cannot to save data! '.$model->errorMessage);
                    }

                    $flag = true;
                    $billProductModel = FSMBaseModel::createMultiple(BillProduct::classname());
                    FSMBaseModel::loadMultiple($billProductModel, Yii::$app->request->post());            
                    foreach ($billProductModel as $index => $billProduct) {
                        if(empty($billProduct->product_id) && empty($billProduct->amount) && empty($billProduct->price)){
                            unset($billProductModel[$index]);
                            continue;
                        }
                        $billProduct->bill_id = $model->id;
                        if($model->according_contract){
                            $billProduct->product_id = null;
                        }
                    }

                    if(!empty($billProductModel)){
                        if ($flag = FSMBaseModel::validateMultiple($billProductModel)) {
                            foreach ($billProductModel as $billProduct) {
                                if (($flag = $billProduct->save(false)) === false) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                    }  

                    if (!$flag) {
                        $transaction->rollBack();
                        throw new Exception('Cannot to save data!');
                    }else{
                        $transaction->commit();
                    } 
                    /*
                }else{
                    throw new Exception('Abonent is not defined!');
                }
                     * 
                     */
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->redirect('index');
            }                
        } else {
            BillUIAsset::register(Yii::$app->getView());

            $model->doc_number = 'R-EC'.date('dmY').'/'.$model->lastNumber;
            $model->doc_date = date('d-M-Y');
            $model->pay_date = date('d-M-Y', strtotime("+".Bill::BILL_DEFAULT_PAYMENT_DAYS." days", strtotime($model->doc_date)));
            /*
            $model->doc_date = date('Y-m-d');
            $model->pay_date = date('Y-m-d', strtotime("+".Bill::BILL_DEFAULT_PAYMENT_DAYS." days", strtotime($model->doc_date)));
             * 
             */
            $model->summa = '0.00';
            $model->valuta_id = Valuta::VALUTA_DEFAULT;
            
            $billProductModel = [new BillProduct()];

            $isAdmin = FSMUser::getIsPortalAdmin();
            $projectList = Project::getNameArr(['deleted' => false]);
            $clientRoleList = ClientRole::getNameArr();
            $valutaList = Valuta::getNameArr();
            $productList = Product::getNameArr();
            $measureList = Measure::getNameArr();
            $languageList = Language::getEnabledLanguageList();
            $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
            if(!Yii::$app->user->isGuest){
                $profile = Yii::$app->user->identity->profile;
                if(array_key_exists($profile->id, $managerList)){
                    $model->manager_id = $profile->id;
                }                
            }
            return $this->render('create', [
                'model' => $model,
                'agreementModel' => $agreementModel,
                'firstClientModel' => new Client(),
                'secondClientModel' => new Client(),
                'projectList' => $projectList,
                'clientRoleList' => $clientRoleList,
                'valutaList' => $valutaList,
                'managerList' => $managerList,
                'billProductModel' => $billProductModel,
                'productList' => $productList,
                'measureList' => $measureList,
                'languageList' => $languageList,
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
        $agreementModel = $model->agreement;
        $agreementModel = (!empty($agreementModel) ? $agreementModel : new Agreement());
        $billProductModel = $model->billProducts;        
        $billProductModel = !empty($billProductModel) ? $billProductModel : [new BillProduct()];
        
        $modelArr = [
            'Bill' => $model,
            'Agreement' => $agreementModel,
        ];
        
        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if(empty($model->agreement_id)){
                    throw new Exception('Agreement was not defined!');
                }
                $model->abonent_id = $model->agreement->abonent_id;
                $model->doc_date = date('Y-m-d', strtotime($model->doc_date));
                $model->pay_date = date('Y-m-d', strtotime($model->pay_date));
                
                $paymentsSumma = $model->paymentsSumma;
                if($paymentsSumma == 0){
                    $model->pay_status = Bill::BILL_PAY_STATUS_NOT;
                }elseif($paymentsSumma < $model->total){
                    $model->pay_status = Bill::BILL_PAY_STATUS_PART;
                }elseif($paymentsSumma > $model->total){
                    $model->pay_status = Bill::BILL_PAY_STATUS_OVER;
                }else{
                    $model->pay_status = Bill::BILL_PAY_STATUS_FULL;
                }
                $model->delayed = (int)(($model->pay_date < date('Y-m-d')) && $model->paymentsSummaIsLess);
                
                if($model->doc_type == Bill::BILL_DOC_TYPE_CRBILL){
                    $model->summa = $model->summa * -1;
                    $model->vat = $model->vat * -1;
                    $model->total = $model->total * -1;
                }
                    
                //if(isset($model->abonent_id)) {
                    //$model->pay_date = date('Y-m-d', strtotime("+".Bill::BILL_DEFAULT_PAYMENT_DAYS." days", strtotime($model->doc_date)));
                    if (!$model->save()) {
                        throw new Exception('Cannot to save data! '.$model->errorMessage);
                    }else{
                        if($model->doc_type == Bill::BILL_DOC_TYPE_CRBILL){
                            $parentInvoice = $model->parent;
                            $parentInvoice->updateAttributes([
                                'pay_status' => (($parentInvoice->total == ($model->total * -1)) ? Bill::BILL_PAY_STATUS_FULL : Bill::BILL_PAY_STATUS_PART), 
                            ]);
                        }
                    }
                    
                    $flag = true;
                    $oldProductIDs = isset($billProductModel[0]) && !empty($billProductModel[0]->id) ? ArrayHelper::map($billProductModel, 'id', 'id') : [];

                    $billProductModel = FSMBaseModel::createMultiple(BillProduct::classname(), $billProductModel);
                    FSMBaseModel::loadMultiple($billProductModel, Yii::$app->request->post());  
                    $deletedIDs = array_diff($oldProductIDs, array_filter(ArrayHelper::map($billProductModel, 'id', 'id')));

                    foreach ($billProductModel as $index => $billProduct) {
                        if(empty($billProduct->productName) && empty($billProduct->amount) && empty($billProduct->price)){
                            unset($billProductModel[$index]);
                            continue;
                        }
                        $billProduct->bill_id = $model->id;
                        if($model->according_contract){
                            $billProduct->product_id = null;
                        }
                    }
                    if(!empty($billProductModel)){
                        // ajax validation
                        if (Yii::$app->request->isAjax) {
                            Yii::$app->response->format = Response::FORMAT_JSON;
                            return ArrayHelper::merge(
                                ActiveForm::validateMultiple($billProductModel),
                                []
                            );
                        }

                        if ($flag = FSMBaseModel::validateMultiple($billProductModel)) {
                            if (!empty($deletedIDs)) {
                                $flag = BillProduct::deleteByIDs($deletedIDs);
                            }     
                            if ($flag) {
                                foreach ($billProductModel as $billProduct) {
                                    if (($flag = $billProduct->save(false)) === false) {
                                        $transaction->rollBack();
                                        break;
                                    }
                                }
                            }
                        }
                    }
                    
                    if (!$flag) {
                        $transaction->rollBack();
                        throw new Exception('Cannot to save data!');
                    }else{
                        $transaction->commit();
                    }          
                    /*
                }else{
                    throw new Exception('Abonent is not defined!');
                }           
                     * 
                     */ 
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->redirectToPreviousUrl($model->id);              
            }                
        } else {
            $this->rememberUrl($model->backURL, $id);            
            
            BillUIAsset::register(Yii::$app->getView());

            $model->doc_date = date('d-M-Y', strtotime($model->doc_date));
            $model->pay_date = date('d-M-Y', strtotime($model->pay_date));
            
            if($model->doc_type == Bill::BILL_DOC_TYPE_CRBILL){
                $model->summa = $model->summa * -1;
                $model->vat = $model->vat * -1;
                $model->total = $model->total * -1;
            }
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            $projectList = Project::getNameArr();
            $clientRoleList = ClientRole::getNameArr();
            $productList = Product::getNameArr();
            $measureList = Measure::getNameArr();
            $valutaList = Valuta::getNameArr();
            $languageList = Language::getEnabledLanguageList();
            $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
            if(!Yii::$app->user->isGuest && empty($model->manager_id)){
                $profile = Yii::$app->user->identity->profile;
                if(array_key_exists($profile->id, $managerList)){
                    $model->manager_id = $profile->id;
                }                
            }
            $hideDocType = !empty($model->child) || !empty($model->parent) || !empty($model->parents);
            
            $firstClientModel = ($model->doc_type != Bill::BILL_DOC_TYPE_CESSION) ? $agreementModel->firstClient : ($model->cession_direction == 'D' ? $agreementModel->firstClient : $agreementModel->thirdClient);
            $secondClientModel = ($model->doc_type != Bill::BILL_DOC_TYPE_CESSION) ? $agreementModel->secondClient : ($model->cession_direction == 'D' ? $agreementModel->thirdClient : $agreementModel->secondClient);
            
            return $this->render('update', [
                'model' => $model,
                'agreementModel' => $agreementModel,
                'firstClientModel' => $firstClientModel,
                'secondClientModel' => $secondClientModel,
                'projectList' => $projectList,
                'clientRoleList' => $clientRoleList,
                'valutaList' => $valutaList,
                'managerList' => $managerList,
                'billProductModel' => $billProductModel,
                'productList' => $productList,
                'measureList' => $measureList,
                'languageList' => $languageList,
                'isAdmin' => $isAdmin,
                'hideDocType' => $hideDocType,
            ]);
        }
    }    
    
    /**
     * Displays a single model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        /*
        if (Yii::$app->request->isAjax) {
            return;
        }
         * 
         */
        ButtonDeleteAsset::register(Yii::$app->getView());
        
        $model = $this->findModel($id);
        $agreementModel = $model->agreement;
        $agreementModel = (!empty($agreementModel) ? $agreementModel : new Agreement());
        $firstClientModel = ($model->doc_type != Bill::BILL_DOC_TYPE_CESSION) ? $agreementModel->firstClient : ($model->cession_direction == 'D' ? $agreementModel->firstClient : $agreementModel->thirdClient);
        $firstClientModel = (!empty($firstClientModel) ? $firstClientModel : new Client());
        $secondClientModel = ($model->doc_type != Bill::BILL_DOC_TYPE_CESSION) ? $agreementModel->secondClient : ($model->cession_direction == 'D' ? $agreementModel->thirdClient : $agreementModel->secondClient);
        $secondClientModel = (!empty($secondClientModel) ? $secondClientModel : new Client());
        $billProductModel = $model->billProducts;        
        $billProductModel = !empty($billProductModel) ? $billProductModel : [new BillProduct()];
        
        $historyModel = new \common\models\bill\search\HistoryBillSearch();
        $params = Yii::$app->request->getQueryParams();
        unset($params['id']);
        $params['bill_id'] = $id;
        $historyDataProvider = $historyModel->search($params);
        
        $isAdmin = FSMUser::getIsPortalAdmin();
        return $this->render('view', [
            'model' => $model,
            'agreementModel' => $agreementModel,
            'firstClientModel' => $firstClientModel,
            'secondClientModel' => $secondClientModel,
            'billProductModel' => $billProductModel,
            'historyModel' => $historyModel,
            'historyDataProvider' => $historyDataProvider,
            'isAdmin' => $isAdmin,
            'time' => date('H:i:s'),
        ]);
    }    
    
    public function actionViewPdf($id, $recursive = false) {
        $model = $this->findModel($id);

        if(!$model->doc_key){
            $model->createPdf();
        }elseif(YII_ENV_DEV){
            $filename = "invoice-{$model->doc_key}.pdf";
            $storagePath = Yii::getAlias(PrintModule::INVOICES_DIR.'/'.$model->agreement->first_client_id);
            $filepath = "$storagePath/$filename";
            if (file_exists($filepath)) {
                unlink($filepath);
            }            
            $model->createPdf();
        }
        $agreementModel = $model->agreement;
        $firstClient = ($model->doc_type != Bill::BILL_DOC_TYPE_CESSION) ? $agreementModel->firstClient : ($model->cession_direction == 'D' ? $agreementModel->firstClient : $agreementModel->thirdClient);
        $filename = "invoice-{$model->doc_key}.pdf";
        $storagePath = Yii::getAlias(PrintModule::INVOICES_DIR.'/'.$firstClient->id);
        $filepath = "$storagePath/$filename";
        
        if (file_exists($filepath)) {
            // Set up PDF headers
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($filepath));
            header('Accept-Ranges: bytes');

            // Render the file
            readfile($filepath);
        } else {
            // PDF doesn't exist so throw an error or something
            if(!$recursive){
                if(!$model->createPdf()){
                    Yii::$app->getSession()->setFlash('error', Yii::t('bill', 'Cannot to create PDF file')); 
                    return $this->redirect(FSMBaseModel::getBackUrl());
                }
                $this->actionViewPdf($id, true);
            }else{
                return $this->redirect(FSMBaseModel::getBackUrl());
            }            
        }
    }    

    private function changeStatus($id, $status) {
        $model = $this->findModel($id);
        $model->changeStatus($status);
    }
    
    public function actionBillWriteOnBasis($id) {
        $model = $this->findModel($id);
        $parentInvoice = $this->findModel($id);

        $agreementModel = $model->agreement;
        $agreementModel = (!empty($agreementModel) ? $agreementModel : new Agreement());
        $billProductModel = $model->billProducts;        
        $billProductModel = !empty($billProductModel) ? $billProductModel : [new BillProduct()];
        
        $model->id = null;
        $model->setIsNewRecord(true);
        
        $modelArr = [
            'Bill' => $model,
            'Agreement' => $agreementModel,
        ];
        
        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if(empty($model->agreement_id)){
                    throw new Exception('Agreement was not defined!');
                }
                $model->abonent_id = $model->agreement->abonent_id;
                //if(isset($model->abonent_id)) {
                    //$model->pay_date = date('Y-m-d', strtotime("+".Bill::BILL_DEFAULT_PAYMENT_DAYS." days", strtotime($model->doc_date)));
                    if (!$model->save()) {
                        throw new Exception('Cannot to save data! '.$model->errorMessage);
                    }else{
                        $parentInvoice->updateAttributes(['child_id' => $model->id]);
                    }
                    
                    $paymentsSumma = $parentInvoice->paymentsSumma;
                    if($paymentsSumma == 0){
                        $arrForUpdate['pay_status'] = Bill::BILL_PAY_STATUS_NOT;
                    }elseif($paymentsSumma < $model->total){
                        $arrForUpdate['pay_status'] = Bill::BILL_PAY_STATUS_PART;
                    }elseif($paymentsSumma > $model->total){
                        $arrForUpdate['pay_status'] = Bill::BILL_PAY_STATUS_OVER;
                    }else{
                        $arrForUpdate['pay_status'] = Bill::BILL_PAY_STATUS_FULL;
                    }
                    $arrForUpdate['delayed'] = (int)(($model->pay_date < date('Y-m-d')) && $model->paymentsSummaIsLess);
                    $model->updateAttributes($arrForUpdate);
                    
                    $billProductModel = FSMBaseModel::createMultiple(BillProduct::classname());
                    FSMBaseModel::loadMultiple($billProductModel, Yii::$app->request->post());            
                    foreach ($billProductModel as $index => $billProduct) {
                        if(empty($billProduct->product_id) && empty($billProduct->amount) && empty($billProduct->price)){
                            unset($billProductModel[$index]);
                            continue;
                        }
                        $billProduct->id = null;
                        $billProduct->setIsNewRecord(true);
                        $billProduct->bill_id = $model->id;
                        if($model->according_contract){
                            $billProduct->product_id = null;
                        }
                    }

                    if(!empty($billProductModel)){
                        if ($flag = FSMBaseModel::validateMultiple($billProductModel)) {
                            foreach ($billProductModel as $billProduct) {
                                if (($flag = $billProduct->save(false)) === false) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                    }  
                    
                    if (!$flag) {
                        $transaction->rollBack();
                        throw new Exception('Cannot to save data!');
                    }else{
                        $transaction->commit();
                    }     
                    /*
                }else{
                    throw new Exception('Abonent is not defined!');
                }           
                     * 
                     */ 
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->redirect('index');              
            }                
        } else {
            BillUIAsset::register(Yii::$app->getView());
            
            //$model->parent_id = $id;
            $model->status = Bill::BILL_STATUS_PREPAR;
            $model->pay_status = Bill::BILL_PAY_STATUS_NOT;
            $model->doc_type = Bill::BILL_DOC_TYPE_BILL;
            $model->doc_number = 'R-EC'.date('dmY').'/'.$model->lastNumber;
            $model->doc_date = date('Y-m-d');
            $model->pay_date = date('Y-m-d', strtotime("+".Bill::BILL_DEFAULT_PAYMENT_DAYS." days", strtotime($model->doc_date)));
            //$model->summa = '0.00';
            //$model->vat = '0.00';
            //$model->total = '0.00';
            $model->delayed = 0;
            $model->doc_key = '';

            $isAdmin = FSMUser::getIsPortalAdmin();
            $projectList = Project::getNameArr();
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            $clientRoleList = ClientRole::getNameArr();
            $productList = Product::getNameArr();
            $measureList = Measure::getNameArr();
            $valutaList = Valuta::getNameArr();
            $languageList = Language::getEnabledLanguageList();
            $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
            if(!Yii::$app->user->isGuest){
                $profile = Yii::$app->user->identity->profile;
                if(array_key_exists($profile->id, $managerList)){
                    $model->manager_id = $profile->id;
                }                
            }
            return $this->render('create', [
                'model' => $model,
                'agreementModel' => $agreementModel,
                'firstClientModel' => $agreementModel->firstClient,
                'secondClientModel' => $agreementModel->secondClient,
                'projectList' => $projectList,
                'clientList' => $clientList,
                'clientRoleList' => $clientRoleList,
                'valutaList' => $valutaList,
                'managerList' => $managerList,
                'billProductModel' => $billProductModel,
                'productList' => $productList,
                'measureList' => $measureList,
                'languageList' => $languageList,
                'isAdmin' => $isAdmin,
                'hideDocType' => true,
            ]);
        }
    }    
    
    public function actionBillWriteOnBasisMany($ids) {
        $ids = explode(',', $ids);
        $model = $this->findModel($ids[0]);
        $parentInvoice = $this->findModel($ids[0]);

        $agreementModel = $model->agreement;
        $agreementModel = (!empty($agreementModel) ? $agreementModel : new Agreement());
        $billProductModel = $model->billProducts;        
        $billProductModel = !empty($billProductModel) ? $billProductModel : [new BillProduct()];
        
        $model->id = null;
        $model->setIsNewRecord(true);
        
        $modelArr = [
            'Bill' => $model,
            'Agreement' => $agreementModel,
        ];
        
        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if(empty($model->agreement_id)){
                    throw new Exception('Agreement was not defined!');
                }
                $model->abonent_id = $model->agreement->abonent_id;
                //if(isset($model->abonent_id)) {
                    //$model->pay_date = date('Y-m-d', strtotime("+".Bill::BILL_DEFAULT_PAYMENT_DAYS." days", strtotime($model->doc_date)));
                    $paymentsSumma = 0;
                    if (!$model->save()) {
                        throw new Exception('Cannot to save data! '.$model->errorMessage);
                    }else{
                        for($i=0; $i < count($ids); $i++){
                            $parentInvoice = $this->findModel($ids[$i]);
                            $parentInvoice->updateAttributes(['child_id' => $model->id]);
                            $paymentsSumma += $parentInvoice->paymentsSumma;
                        }
                    }
                    
                    if($paymentsSumma == 0){
                        $arrForUpdate['pay_status'] = Bill::BILL_PAY_STATUS_NOT;
                    }elseif($paymentsSumma < $model->total){
                        $arrForUpdate['pay_status'] = Bill::BILL_PAY_STATUS_PART;
                    }elseif($paymentsSumma > $model->total){
                        $arrForUpdate['pay_status'] = Bill::BILL_PAY_STATUS_OVER;
                    }else{
                        $arrForUpdate['pay_status'] = Bill::BILL_PAY_STATUS_FULL;
                    }
                    $arrForUpdate['delayed'] = (int)(($model->pay_date < date('Y-m-d')) && $model->paymentsSummaIsLess);
                    $model->updateAttributes($arrForUpdate);
                                        
                    $billProductModel = FSMBaseModel::createMultiple(BillProduct::classname());
                    FSMBaseModel::loadMultiple($billProductModel, Yii::$app->request->post());            
                    foreach ($billProductModel as $index => $billProduct) {
                        if(empty($billProduct->product_id) && empty($billProduct->amount) && empty($billProduct->price)){
                            unset($billProductModel[$index]);
                            continue;
                        }
                        $billProduct->id = null;
                        $billProduct->setIsNewRecord(true);
                        $billProduct->bill_id = $model->id;
                        if($model->according_contract){
                            $billProduct->product_id = null;
                        }
                    }

                    if(!empty($billProductModel)){
                        if ($flag = FSMBaseModel::validateMultiple($billProductModel)) {
                            foreach ($billProductModel as $billProduct) {
                                if (($flag = $billProduct->save(false)) === false) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                    }  
                    
                    if (!$flag) {
                        $transaction->rollBack();
                        throw new Exception('Cannot to save data!');
                    }else{
                        $transaction->commit();
                    }     
                    /*
                }else{
                    throw new Exception('Abonent is not defined!');
                }           
                     * 
                     */ 
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->redirect('index');              
            }                
        } else {
            BillUIAsset::register(Yii::$app->getView());
            
            //$model->parent_id = $id;
            $model->status = Bill::BILL_STATUS_PREPAR;
            $model->pay_status = Bill::BILL_PAY_STATUS_NOT;
            $model->doc_type = Bill::BILL_DOC_TYPE_BILL;
            $model->doc_number = 'R-EC'.date('dmY').'/'.$model->lastNumber;
            $model->doc_date = date('Y-m-d');
            $model->pay_date = date('Y-m-d', strtotime("+".Bill::BILL_DEFAULT_PAYMENT_DAYS." days", strtotime($model->doc_date)));
            //$model->summa = '0.00';
            //$model->vat = '0.00';
            //$model->total = '0.00';
            $model->delayed = 0;
            $model->doc_key = '';
            
            for($i=1; $i < count($ids); $i++){
                $nextModel = $this->findModel($ids[$i]);
                $model->summa += $nextModel->summa;
                $model->vat += $nextModel->vat;
                $model->total += $nextModel->total;
                $nextBillProductModel = $nextModel->billProducts;
                $billProductModel = ArrayHelper::merge($billProductModel, $nextBillProductModel);
            }

            $isAdmin = FSMUser::getIsPortalAdmin();
            $projectList = Project::getNameArr();
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            $clientRoleList = ClientRole::getNameArr();
            $productList = Product::getNameArr();
            $measureList = Measure::getNameArr();
            $valutaList = Valuta::getNameArr();
            $languageList = Language::getEnabledLanguageList();
            $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
            if(!Yii::$app->user->isGuest){
                $profile = Yii::$app->user->identity->profile;
                if(array_key_exists($profile->id, $managerList)){
                    $model->manager_id = $profile->id;
                }                
            }
            return $this->render('create', [
                'model' => $model,
                'agreementModel' => $agreementModel,
                'firstClientModel' => $agreementModel->firstClient,
                'secondClientModel' => $agreementModel->secondClient,
                'projectList' => $projectList,
                'clientList' => $clientList,
                'clientRoleList' => $clientRoleList,
                'valutaList' => $valutaList,
                'managerList' => $managerList,
                'billProductModel' => $billProductModel,
                'productList' => $productList,
                'measureList' => $measureList,
                'languageList' => $languageList,
                'isAdmin' => $isAdmin,
                'hideDocType' => true,
            ]);
        }
    }    
    
    public function actionBillCreditInvoice($id) {
        $model = $this->findModel($id);
        $parentInvoice = $this->findModel($id);
        
        $agreementModel = $model->agreement;
        $agreementModel = (!empty($agreementModel) ? $agreementModel : new Agreement());
        $billProductModel = $model->billProducts;        
        $billProductModel = !empty($billProductModel) ? $billProductModel : [new BillProduct()];
        
        $model->id = null;
        $model->setIsNewRecord(true);
        
        $modelArr = [
            'Bill' => $model,
            'Agreement' => $agreementModel,
        ];
        
        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if(empty($model->agreement_id)){
                    throw new Exception('Agreement was not defined!');
                }
                $model->abonent_id = $model->agreement->abonent_id;
                //if(isset($model->abonent_id)) {
                    //$model->pay_date = date('Y-m-d', strtotime("+".Bill::BILL_DEFAULT_PAYMENT_DAYS." days", strtotime($model->doc_date)));
                    $model->parent_id = $id;
                    $model->summa = $model->summa * -1;
                    $model->vat = $model->vat * -1;
                    $model->total = $model->total * -1;
                    $model->complete_date = date('Y-m-d');
                    if (!$model->save()) {
                        throw new Exception('Cannot to save data! '.$model->errorMessage);
                    }else{
                        $historyModule = new HistoryBill();
                        $historyModule->saveHistory($model->id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_COMPLETE'], Bill::BILL_STATUS_COMPLETE);
                        unset($historyModule);                        
                        
                        $parentInvoice->updateAttributes([
                            'status' => Bill::BILL_STATUS_COMPLETE, 
                            'pay_status' => (($parentInvoice->total == ($model->total * -1)) ? Bill::BILL_PAY_STATUS_FULL : Bill::BILL_PAY_STATUS_PART), 
                            'pay_date' => date('Y-m-d'),
                            'complete_date' => date('Y-m-d'),

                        ]);
                        $historyModule = new HistoryBill();
                        $historyModule->saveHistory($parentInvoice->id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_COMPLETE'], Bill::BILL_STATUS_COMPLETE);
                        unset($historyModule);                        
                    }
                    
                    $billProductModel = FSMBaseModel::createMultiple(BillProduct::classname());
                    FSMBaseModel::loadMultiple($billProductModel, Yii::$app->request->post());            
                    foreach ($billProductModel as $index => $billProduct) {
                        if(empty($billProduct->product_id) && empty($billProduct->amount) && empty($billProduct->price)){
                            unset($billProductModel[$index]);
                            continue;
                        }
                        $billProduct->id = null;
                        $billProduct->setIsNewRecord(true);
                        $billProduct->bill_id = $model->id;
                        if($model->according_contract){
                            $billProduct->product_id = null;
                        }
                    }

                    if(!empty($billProductModel)){
                        if ($flag = FSMBaseModel::validateMultiple($billProductModel)) {
                            foreach ($billProductModel as $billProduct) {
                                if (($flag = $billProduct->save(false)) === false) {
                                    $transaction->rollBack();
                                    break;
                                }
                            }
                        }
                    }  
                    
                    if (!$flag) {
                        $transaction->rollBack();
                        throw new Exception('Cannot to save data!');
                    }else{
                        $transaction->commit();
                    }                
                    /*
                }else{
                    throw new Exception('Abonent is not defined!');
                }            
                     * 
                     */
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->redirect('index');              
            }                
        } else {
            BillUIAsset::register(Yii::$app->getView());
            
            $model->status = Bill::BILL_STATUS_COMPLETE;
            $model->pay_status = Bill::BILL_PAY_STATUS_FULL;
            $model->doc_type = Bill::BILL_DOC_TYPE_CRBILL;
            $model->doc_number = 'K-EC'.date('dmY').'/'.$model->lastNumber;
            $model->doc_date = date('Y-m-d');
            $model->pay_date = date('Y-m-d');
            //$model->summa = $model->summa * -1;
            //$model->vat = $model->vat * -1;
            //$model->total = $model->total * -1;
            $model->delayed = 0;
            $model->doc_key = '';

            $isAdmin = FSMUser::getIsPortalAdmin();
            $projectList = Project::getNameArr();
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            $clientRoleList = ClientRole::getNameArr();
            $productList = Product::getNameArr();
            $measureList = Measure::getNameArr();
            $valutaList = Valuta::getNameArr();
            $languageList = Language::getEnabledLanguageList();
            $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
            if(!Yii::$app->user->isGuest){
                $profile = Yii::$app->user->identity->profile;
                if(array_key_exists($profile->id, $managerList)){
                    $model->manager_id = $profile->id;
                }                
            }
            return $this->render('create', [
                'model' => $model,
                'agreementModel' => $agreementModel,
                'firstClientModel' => $agreementModel->firstClient,
                'secondClientModel' => $agreementModel->secondClient,
                'projectList' => $projectList,
                'clientList' => $clientList,
                'clientRoleList' => $clientRoleList,
                'valutaList' => $valutaList,
                'managerList' => $managerList,
                'billProductModel' => $billProductModel,
                'productList' => $productList,
                'measureList' => $measureList,
                'languageList' => $languageList,
                'isAdmin' => $isAdmin,
            ]);
        }
    }    
    
    public function actionBillCession($id) {
        $model = $this->findModel($id);
        $parentInvoice = $this->findModel($id);
        
        $agreementModel = $model->agreement;
        $agreementModel = (!empty($agreementModel) ? $agreementModel : new Agreement());
        
        $debt = $parentInvoice->total - $parentInvoice->paymentsSumma;
        
        $model->id = null;
        $model->setIsNewRecord(true);
        
        $modelArr = [
            'Bill' => $model,
            'Agreement' => $agreementModel,
        ];
        
        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if(empty($model->agreement_id)){
                    throw new Exception('Agreement was not defined!');
                }
                $model->abonent_id = $model->agreement->abonent_id;
                //if(isset($model->abonent_id)) {
                    //$model->pay_date = date('Y-m-d', strtotime("+".Bill::BILL_DEFAULT_PAYMENT_DAYS." days", strtotime($model->doc_date)));
                    $model->parent_id = $id;
                    $model->summa = $model->summa * -1;
                    $model->vat = $model->vat * -1;
                    $model->total = $model->total * -1;
                    $model->complete_date = date('Y-m-d');
                    if (!$model->save()) {
                        throw new Exception('Cannot to save data! '.$model->errorMessage);
                    }else{
                        $historyModule = new HistoryBill();
                        $historyModule->saveHistory($model->id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_COMPLETE'], Bill::BILL_STATUS_COMPLETE);
                        unset($historyModule);                        
                        
                        $parentInvoice->updateAttributes([
                            'status' => Bill::BILL_STATUS_COMPLETE, 
                            'pay_status' => Bill::BILL_PAY_STATUS_FULL, 
                            'pay_date' => date('Y-m-d'),
                        ]);
                        $historyModule = new HistoryBill();
                        $historyModule->saveHistory($parentInvoice->id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_COMPLETE'], Bill::BILL_STATUS_COMPLETE);
                        unset($historyModule);                        
                    }
                    
                    $billProductModel = FSMBaseModel::createMultiple(BillProduct::classname());
                    FSMBaseModel::loadMultiple($billProductModel, Yii::$app->request->post());            
                    foreach ($billProductModel as $index => $billProduct) {
                        if(empty($billProduct->product_id) && empty($billProduct->amount) && empty($billProduct->price)){
                            unset($billProductModel[$index]);
                            continue;
                        }
                        $billProduct->id = null;
                        $billProduct->setIsNewRecord(true);
                        $billProduct->bill_id = $model->id;
                        if($model->according_contract){
                            $billProduct->product_id = null;
                        }
                    }

                    if(!empty($billProductModel)){
                        if (FSMBaseModel::validateMultiple($billProductModel)) {
                            foreach ($billProductModel as $billProduct) {
                                if (!$billProduct->save(false)) {
                                    throw new Exception('Cannot to save data! '.$billProduct->errorMessage);
                                    break;
                                }
                            }
                        }
                    }  
                    
                    $cessionDebet = new $this->defaultModel;
                    $cessionKredit = new $this->defaultModel;
                    $agreementList = Agreement::findAll([
                        'first_client_id' => $model->firstClient->id, 
                        'second_client_id' => $model->secondClient->id, 
                        'agreement_type' => Agreement::AGREEMENT_TYPE_CESSION,
                        'status' => Agreement::AGREEMENT_STATUS_SIGNED,
                        'deleted' => 0]);
                    if(empty($agreementList)){
                        throw new Exception('Can not find an appropriate agreement!');
                    }                    
                    $agreement = $agreementList[0];
                    $thirdClient = $agreement->thirdClient;
                    $thirdClientBankList = $thirdClient->clientBanks;
                    $thirdClientBank = !empty($thirdClientBankList) ? $thirdClientBankList[0] : null;
                    
                    $model->doc_type = Bill::BILL_DOC_TYPE_CESSION;
                    $cessionDebet->abonent_id = $agreement->abonent_id;
                    $cessionDebet->project_id = $agreement->project_id;
                    $cessionDebet->agreement_id = $agreement->id;
                    $cessionDebet->first_client_bank_id = $model->first_client_bank_id;
                    $cessionDebet->second_client_bank_id = $thirdClientBank ? $thirdClientBank->id : null;
                    $cessionDebet->parent_id = $id;
                    $cessionDebet->doc_type = Bill::BILL_DOC_TYPE_CESSION;
                    $cessionDebet->cession_direction = 'D';
                    $cessionDebet->doc_number = 'C-EC'.date('dmY').'/'.$model->lastNumber;
                    $cessionDebet->doc_date = date('Y-m-d');
                    $cessionDebet->pay_date = date('Y-m-d', strtotime("+".(!empty($agreement->deferment_payment) ? $agreement->deferment_payment : Bill::BILL_DEFAULT_PAYMENT_DAYS)." days", strtotime($cessionDebet->doc_date)));
                    $cessionDebet->according_contract = true;
                    $cessionDebet->summa = abs($agreement->summa);
                    $cessionDebet->vat = 0;
                    $cessionDebet->total = abs($agreement->summa);
                    $cessionDebet->valuta_id = $agreement->valuta_id;
                    $cessionDebet->manager_id = $model->manager_id;
                    $cessionDebet->language_id = $model->language_id;
                    $cessionDebet->services_period = $model->services_period;
                    if (!$cessionDebet->save()) {
                        throw new Exception('Cannot to save data! '.$cessionDebet->errorMessage);
                    }
                                        
                    $cessionKredit->abonent_id = $agreement->abonent_id;
                    $cessionKredit->project_id = $agreement->project_id;
                    $cessionKredit->agreement_id = $agreement->id;
                    $cessionKredit->first_client_bank_id = $thirdClientBank ? $thirdClientBank->id : null;
                    $cessionKredit->second_client_bank_id = $model->second_client_bank_id;
                    $cessionKredit->parent_id = $id;
                    $cessionKredit->doc_type = Bill::BILL_DOC_TYPE_CESSION;
                    $cessionDebet->cession_direction = 'K';
                    $cessionKredit->doc_number = 'C-EC'.date('dmY').'/'.$cessionDebet->lastNumber;
                    $cessionKredit->doc_date = date('Y-m-d');
                    $cessionKredit->pay_date = date('Y-m-d', strtotime("+".(!empty($agreement->deferment_payment) ? $agreement->deferment_payment : Bill::BILL_DEFAULT_PAYMENT_DAYS)." days", strtotime($cessionKredit->doc_date)));
                    $cessionKredit->according_contract = true;
                    $cessionKredit->summa = abs($model->total);
                    $cessionKredit->vat = 0;
                    $cessionKredit->total = abs($model->total);
                    $cessionKredit->valuta_id = $model->valuta_id;
                    $cessionKredit->manager_id = $model->manager_id;
                    $cessionKredit->language_id = $model->language_id;
                    $cessionKredit->services_period = $model->services_period;
                    if (!$cessionKredit->save()) {
                        throw new Exception('Cannot to save data! '.$cessionKredit->errorMessage);
                    }
                                        
                    if(!empty($billProductModel)){
                        foreach ($billProductModel as $billProduct) {
                            $billProduct->id = null;
                            $billProduct->setIsNewRecord(true);
                            $billProduct->bill_id = $cessionDebet->id;
                            if (!$billProduct->save(false)) {
                                throw new Exception('Cannot to save data! '.$billProduct->errorMessage);
                                break;
                            }
                        }
                        
                        foreach ($billProductModel as $billProduct) {
                            $billProduct->id = null;
                            $billProduct->setIsNewRecord(true);
                            $billProduct->bill_id = $cessionKredit->id;
                            if (!$billProduct->save(false)) {
                                throw new Exception('Cannot to save data! '.$billProduct->errorMessage);
                                break;
                            }
                        }
                    }  
                    $transaction->commit();
                    /*
                }else{
                    throw new Exception('Abonent is not defined!');
                }            
                     * 
                     */
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->redirect('index');              
            }                
        } else {
            BillUIAsset::register(Yii::$app->getView());
            
            $billProductModel = [new BillProduct()];  
            $billProductModel[0]->product_name = $parentInvoice->doc_number .' '.Yii::t('bill', 'Debt').': '.$debt.' '.$model->valuta->name;
            $billProductModel[0]->amount = 1;
            $billProductModel[0]->price = $debt;
            $billProductModel[0]->vat = 0;
            
            $model->status = Bill::BILL_STATUS_COMPLETE;
            $model->pay_status = Bill::BILL_PAY_STATUS_FULL;
            $model->doc_type = Bill::BILL_DOC_TYPE_DEBT;
            $model->doc_number = 'D-EC'.date('dmY').'/'.$model->lastNumber;
            $model->doc_date = date('Y-m-d');
            $model->pay_date = date('Y-m-d');
            $model->delayed = 0;
            $model->doc_key = '';
            $model->according_contract = true;

            $isAdmin = FSMUser::getIsPortalAdmin();
            $projectList = Project::getNameArr();
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            $clientRoleList = ClientRole::getNameArr();
            $productList = Product::getNameArr();
            $measureList = Measure::getNameArr();
            $valutaList = Valuta::getNameArr();
            $languageList = Language::getEnabledLanguageList();
            $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
            if(!Yii::$app->user->isGuest){
                $profile = Yii::$app->user->identity->profile;
                if(array_key_exists($profile->id, $managerList)){
                    $model->manager_id = $profile->id;
                }                
            }
            return $this->render('create', [
                'model' => $model,
                'agreementModel' => $agreementModel,
                'firstClientModel' => $agreementModel->firstClient,
                'secondClientModel' => $agreementModel->secondClient,
                'projectList' => $projectList,
                'clientList' => $clientList,
                'clientRoleList' => $clientRoleList,
                'valutaList' => $valutaList,
                'managerList' => $managerList,
                'billProductModel' => $billProductModel,
                'productList' => $productList,
                'measureList' => $measureList,
                'languageList' => $languageList,
                'isAdmin' => $isAdmin,
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
        $parentInvoice = $model->parent;
        $parentInvoice->updateAttributes([
            'status' => Bill::BILL_STATUS_SIGNED, 
            'pay_status' => Bill::BILL_PAY_STATUS_NOT, 
            'pay_date' => date('Y-m-d', strtotime("+".Bill::BILL_DEFAULT_PAYMENT_DAYS." days", strtotime($model->doc_date))),
        ]);
        
        return parent::actionDelete($id);
    }    
    
    public function actionCheckDelayed($isCron = true)
    {
        $searchModel = new $this->defaultSearchModel;
        $billList = $searchModel->searchDelayed();
        foreach ($billList as $bill) {
            $bill->updateAttributes(['delayed' => true]);
        }
        if($isCron){
            return true;
        }else{
            return $this->render('check-delayed', [
                'billCount' => count($billList),
            ]);        
        }
    }
}