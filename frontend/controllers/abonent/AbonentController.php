<?php

namespace frontend\controllers\abonent;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;

use common\controllers\FilSMController;
use common\models\mainclass\FSMBaseModel;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;
use common\models\Bank;
use common\models\Valuta;
use common\models\Language;
use common\models\client\Client;
use common\models\address\Country;
use common\models\client\ClientBank;
use common\models\Files;

use frontend\assets\ClientUIAsset;
use common\assets\ButtonDeleteAsset;

/**
 * AbonentController implements the CRUD actions for Abonent model.
 */
class AbonentController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\abonent\Abonent';
        $this->defaultSearchModel = 'common\models\abonent\search\AbonentSearch';
        $this->pjaxIndex = true;
    }
        
    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex() {
        ButtonDeleteAsset::register(Yii::$app->getView());
        
        $searchModel = new $this->defaultSearchModel;
        if(!$searchModel){
            $className = $this->className();
            throw new Exception("For the {$className} defaultSearchModel not defined.");
        }        
        $params = Yii::$app->request->getQueryParams();
        $params['deleted'] = (empty($params) || empty($params['AbonentSearch'])) ?
            0 :
            (isset($params['AbonentSearch']['deleted']) && ($params['AbonentSearch']['deleted'] !== '') ?
                    $params['AbonentSearch']['deleted'] :
                    0
            );

        $dataProvider = $searchModel->search($params);

        $isAdmin = FSMUser::getIsPortalAdmin();
        $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
        $profile = Yii::$app->user->identity->profile;
        $profileId = isset($profile) ? $profile->id : null;        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'managerList' => $managerList,
            'isAdmin' => $isAdmin,
            'profileId' => $profileId,
        ]);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new $this->defaultModel;
        $clientModel = new Client();
        $filesModel = new Files();

        $modelArr = [
            'Abonent' => $model,
            'Client' => $clientModel,
            'Files' => $filesModel,
        ];

        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                $clientModel->it_is = Client::CLIENT_IT_IS_ABONENT;
                if(!empty($_POST['use_legal_address'])){
                    $clientModel->office_address = $clientModel->legal_address;
                    $clientModel->office_country_id = $clientModel->legal_country_id;
                }
                if (!$clientModel->save()) {
                    $transaction->rollBack();
                    throw new Exception('Cannot to save data! '.$clientModel->errorMessage);
                } else {
                    $model->main_client_id = $clientModel->id;
                    $model->manager_id = $clientModel->manager_id;
                    $model->subscription_end_date = !empty($model->subscription_end_date) ? date('Y-m-d', strtotime($model->subscription_end_date)) : null;
                    
                    if (!$model->save()) {
                        $transaction->rollBack();
                        throw new Exception('Cannot to save data! '.$model->errorMessage);
                    } else {
                        $clientBankModel = FSMBaseModel::createMultiple(ClientBank::classname());
                        FSMBaseModel::loadMultiple($clientBankModel, Yii::$app->request->post());            
                        foreach ($clientBankModel as $index => $clientBank) {
                            if(empty($clientBank->bank_id) && empty($clientBank->account) && empty($clientBank->name)){
                                unset($clientBankModel[$index]);
                                continue;
                            }
                            $clientBank->client_id = $clientModel->id;
                        }

                        $flag = true;
                        if(!empty($clientBankModel)){
                            // ajax validation
                            if (Yii::$app->request->isAjax) {
                                Yii::$app->response->format = Response::FORMAT_JSON;
                                return ArrayHelper::merge(
                                    ActiveForm::validateMultiple($clientBankModel),
                                    []
                                );
                            }

                            if ($flag = FSMBaseModel::validateMultiple($clientBankModel)) {
                                foreach ($clientBankModel as $clientBank) {
                                    if (($flag = $clientBank->save(false)) === false) {
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
                    }
                }
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
            ClientUIAsset::register(Yii::$app->getView());

            $clientModel->status = Client::CLIENT_STATUS_ACTIVE;
            $clientModel->it_is = Client::CLIENT_IT_IS_ABONENT;
            $clientModel->tax = Client::CLIENT_DEFAULT_VAT_TAX;
            $clientModel->debit_valuta_id = Valuta::VALUTA_DEFAULT;

            $clientBankModel = [new ClientBank()];
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            $isOwner = $isAdmin || FSMUser::getIamOwner();
            $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
            $profileId = Yii::$app->user->identity->profile->id;
            if (array_key_exists($profileId, $managerList)) {
                $model->manager_id = $profileId;
            }

            $countryList = Country::getNameArr();
            $bankList = Bank::getNameArr(['enabled' => true]);
            $valutaList = Valuta::getNameArr();
            $languageList = Language::getEnabledLanguageList();
            return $this->render('create', [
                'model' => $model,
                'clientModel' => $clientModel,
                'clientBankModel' => $clientBankModel,
                'filesModel' => $filesModel,
                'countryList' => $countryList,
                'languageList' => $languageList,
                'bankList' => $bankList,
                'valutaList' => $valutaList,
                'managerList' => $managerList,
                'isAdmin' => $isAdmin,
                'isOwner' => $isOwner,
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
        $clientModel = $model->mainClient;
        $clientModel = (!empty($clientModel) ? $clientModel : new Client());
        $clientBankModel = $clientModel->clientBanks;        
        $clientBankModel = !empty($clientBankModel) ? $clientBankModel : [new ClientBank()];
        $filesModel = $oldFileModel = $clientModel->logo;
        $filesModel = (!empty($filesModel) ? $filesModel : new Files());
        
        $modelArr = [
            'Abonent' => $model,
            'Client' => $clientModel,
            'Files' => $filesModel,
        ];
        
        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if(!empty($_POST['use_legal_address'])){
                    $clientModel->office_address = $clientModel->legal_address;
                    $clientModel->office_country_id = $clientModel->legal_country_id;
                }
                if (!$clientModel->save()) {
                    $transaction->rollBack();
                    throw new Exception('Cannot to save data! '.$clientModel->errorMessage);
                } else {
                    $model->manager_id = $clientModel->manager_id;
                    $model->deleted = $clientModel->deleted;
                    $model->subscription_end_date = !empty($model->subscription_end_date) ? date('Y-m-d', strtotime($model->subscription_end_date)) : null;
                    if (!$model->save()) {
                        $transaction->rollBack();
                        throw new Exception('Cannot to save data! '.$model->errorMessage);
                    } else {
                        $oldBankIDs = isset($clientBankModel[0]) && !empty($clientBankModel[0]->id) ? ArrayHelper::map($clientBankModel, 'id', 'id') : [];

                        $clientBankModel = FSMBaseModel::createMultiple(ClientBank::classname(), $clientBankModel);
                        FSMBaseModel::loadMultiple($clientBankModel, Yii::$app->request->post());  
                        $deletedBankIDs = array_diff($oldBankIDs, array_filter(ArrayHelper::map($clientBankModel, 'id', 'id')));

                        foreach ($clientBankModel as $index => $clientBank) {
                            if(empty($clientBank->bank_id) && empty($clientBank->account) && empty($clientBank->name)){
                                unset($clientBankModel[$index]);
                                continue;
                            }
                            $clientBank->client_id = $clientModel->id;
                        }
                        $flag = true;
                        if(!empty($clientBankModel)){
                            // ajax validation
                            if (Yii::$app->request->isAjax) {
                                Yii::$app->response->format = Response::FORMAT_JSON;
                                return ArrayHelper::merge(
                                    ActiveForm::validateMultiple($clientBankModel),
                                    []
                                );
                            }

                            if ($flag = FSMBaseModel::validateMultiple($clientBankModel)) {
                                if (!empty($deletedBankIDs)) {
                                    $flag = ClientBank::deleteByIDs($deletedBankIDs);
                                }     
                                if ($flag) {
                                    foreach ($clientBankModel as $clientBank) {
                                        if (($flag = $clientBank->save(false)) === false) {
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
                    }
                }
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
            
            ClientUIAsset::register(Yii::$app->getView());

            $model->subscription_end_date = !empty($model->subscription_end_date) ? date('d-M-Y', strtotime($model->subscription_end_date)) : null;
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            $isOwner = $isAdmin || FSMUser::getIamOwner();
            $countryList = Country::getNameArr();
            $bankList = Bank::getNameArr(['enabled' => true]);
            $valutaList = Valuta::getNameArr();
            $languageList = Language::getEnabledLanguageList();
            $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
            return $this->render('update', [
                'model' => $model,
                'clientModel' => $clientModel,
                'clientBankModel' => $clientBankModel,
                'filesModel' => $filesModel, 
                'countryList' => $countryList,
                'languageList' => $languageList,
                'bankList' => $bankList,
                'valutaList' => $valutaList,
                'managerList' => $managerList,
                'isAdmin' => $isAdmin,
                'isOwner' => $isOwner,                        
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
        
        return $this->render('view', [
            'model' => $this->findModel($id),
            'isAdmin' => FSMUser::getIsPortalAdmin(),
        ]);
    }    
}