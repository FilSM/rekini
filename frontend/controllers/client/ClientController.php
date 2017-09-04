<?php

namespace frontend\controllers\client;

use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\Response;
use yii\widgets\ActiveForm;
use yii\base\Model;
use kartik\helpers\Html;
use common\controllers\FilSMController;
use common\models\mainclass\FSMBaseModel;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;
use common\models\Bank;
use common\models\Valuta;
use common\models\Language;
use common\models\Files;
use common\models\client\Client;
use common\models\client\ClientGroup;
use common\models\address\Country;
use common\models\client\ClientBank;
use common\models\client\search\ClientBankSearch;
use common\models\client\ClientContact;
use common\components\FSMLursoft;
use common\components\FSMVIES;
use Naucon\Iban\Iban;
use frontend\assets\ClientUIAsset;
use common\assets\ButtonDeleteAsset;

/**
 * ClientController implements the CRUD actions for Client model.
 */
class ClientController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\client\Client';
        $this->defaultSearchModel = 'common\models\client\search\ClientSearch';
        $this->pjaxIndex = true;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge(
            $behaviors, [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'ajax-get-model' => ['get'],
                ],
            ],
        ]
        );
        return $behaviors;
    }

    public function actionAjaxGetModel($id)
    {
        if (empty($id)) {
            return [];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out['client'] = Client::findOne($id);
        $out['client_address_text'] = $out['client']->legal_address;
        return $out;
    }

    public function actionAjaxGetLursoftData()
    {
        $params = Yii::$app->request->get();
        $service = new FSMLursoft();
        $data = $service->search($params);
        unset($service);
        if (!empty($data['result']) && !empty($data['answer'])) {
            $answer = (array) $data['answer'];
            $person = (array) $answer['person'];
            $result = [];
            foreach ($person as $key => $value) {
                if (!is_string($value)) {
                    continue;
                }
                $result[$key] = $value;
            }
            $result['address'] = (array) $person['address'];
            $data = $result;
        } else {
            $data = [];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }

    public function actionAjaxGetViesData()
    {
        $params = Yii::$app->request->get();
        $service = new FSMVIES();
        $data = $service->search($params);
        unset($service);
        if (!empty($data['result'])) {
            $data = (array) $data['answer'];
        } else {
            $data = [];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $data;
    }

    public function actionAjaxCheckIban()
    {
        $params = Yii::$app->request->get();
        $result = false;
        if (!empty($params['iban'])) {
            $iban = new Iban($params['iban']);
            $result = $iban->isValid();
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $result;
    }

    /*
      public function actionAjaxClientList($q = null, $id = null) {
      $out = ['results' => ['id' => '', 'text' => '']];
      $data = [];
      if (!is_null($q)) {
      $data = Client::getClientList($q);
      } elseif ($id > 0) {
      $data = Client::findOne($id);
      }
      if (!empty($data)) {
      $out = [];
      foreach ($data as $key => $value) {
      $out['results'][] = ['id' => $key, 'text' => $value]; // !!! 'text' is needed for Select2 templateResult & templateSelection functions
      }
      }
      Yii::$app->response->format = Response::FORMAT_JSON;
      return $out;
      }
     * 
     */

    /**
     * Displays the registration page.
     * After successful registration if enableConfirmation is enabled shows info message otherwise redirects to home page.
     *
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionRegister()
    {
        $userModule = Yii::createObject(\dektrium\user\Module::className(), ['dektrium-user-module']);
        if (!$userModule->enableRegistration) {
            throw new Exception();
        }

        $model = new $this->defaultModel;
        $userModel = new FSMUser();
        $userModel->setScenario('from-client');

        $modelArr = [
            'Client' => $model,
            'FSMUser' => $userModel,
        ];

        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {

                if (!$model->save()) {
                    $transaction->rollBack();
                    throw new Exception('Cannot to save data! ' . $model->errorMessage);
                }

                if (empty($userModel->username)) {
                    $userModel->username = $userModel->generateUsername();
                }
                if (!$userModel->register/* create */()) {
                    $transaction->rollBack();
                    throw new Exception('Cannot to save data! ' . $userModel->errorMessage);
                } else {
                    if ($profile = $userModel->profile) {
                        $profile->updateAttributes([
                            'client_id' => $model->id,
                            'language_id' => $model->language_id,
                        ]);
                        if ($model->client_type == Client::CLIENT_TYPE_PHYSICAL) {
                            $profile->updateAttributes([
                                'name' => $model->name,
                            ]);
                        }
                    }
                    Yii::$app->session->setFlash(
                            'info', Yii::t('user', 'Your account has been created and a message with further instructions has been sent to your email')
                    );
                }

                $transaction->commit();
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->goHome();
            }
        } else {
            ClientUIAsset::register(Yii::$app->getView());

            $model['status'] = Client::CLIENT_STATUS_ACTIVE;
            $model['it_is'] = Client::CLIENT_IT_IS_CLIENT;

            $countryList = Country::getNameArr();
            $bankList = Bank::getNameArr(['enabled' => true]);
            $languageList = \common\models\Language::getEnabledLanguageList();
            return $this->render('create', [
                        'model' => $model,
                        'userModel' => $userModel,
                        'countryList' => $countryList,
                        'languageList' => $languageList,
                        'bankList' => $bankList,
                        'managerList' => [],
                        'isAdmin' => false,
                        'isOwner' => false,
                        'itIs' => null,
                        'isModal' => false,
                        'registerAction' => true,
            ]);
        }
    }

    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex()
    {
        ButtonDeleteAsset::register(Yii::$app->getView());

        $searchModel = new $this->defaultSearchModel;
        $params = Yii::$app->request->getQueryParams();
        $params['deleted'] = (empty($params) || empty($params['ClientSearch'])) ?
                0 :
                (isset($params['ClientSearch']['deleted']) && ($params['ClientSearch']['deleted'] !== '') ?
                $params['ClientSearch']['deleted'] :
                0
                );

        $dataProvider = $searchModel->search($params);

        $withoutTypes = [];
        if (!FSMUser::getIsPortalAdmin()) {
            $withoutTypes[] = Client::CLIENT_IT_IS_OWNER;
        }
        $itIsList = $searchModel->clientItIsList;
        foreach ($withoutTypes as $type) {
            unset($itIsList[$type]);
        }

        $isAdmin = FSMUser::getIsPortalAdmin();
        $bankList = Bank::getNameArr(['enabled' => true]);
        $languageList = Language::getEnabledLanguageList();
        $countryList = Country::getNameArr();
        $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
        $profile = Yii::$app->user->identity->profile;
        $profileId = isset($profile) ? $profile->id : null;
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'itIsList' => $itIsList,
            'languageList' => $languageList,
            'bankList' => $bankList,
            'countryList' => $countryList,
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
    public function actionCreate()
    {
        $model = new $this->defaultModel;
        $filesModel = new Files();

        $modelArr = [
            'Client' => $model,
            'Files' => $filesModel,
        ];

        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxMultipleValidation($modelArr);
        }

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {

            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if (!empty($model->parent_id)) {
                    $parent = $model->parent;
                    $model->abonent_id = (!empty($parent->abonent_id) ? $parent->abonent_id : null);
                    $model->it_is = (!empty($parent->abonent_id) ? Client::CLIENT_IT_IS_ABONENT : $model->it_is);
                }

                $file = $filesModel->uploadFile('logo');
                $result = true;
                if (!empty($file)) {
                    $result = $filesModel->save();
                }
                $model->uploaded_file_id = $filesModel->id;

                if (!empty($_POST['use_legal_address'])) {
                    $model->office_address = $model->legal_address;
                    $model->office_country_id = $model->legal_country_id;
                }

                if (!$model->save()) {
                    $transaction->rollBack();
                    throw new Exception('Cannot to save data! ' . $model->errorMessage);
                } else {
                    $clientBankModel = FSMBaseModel::createMultiple(ClientBank::classname());
                    FSMBaseModel::loadMultiple($clientBankModel, Yii::$app->request->post());
                    foreach ($clientBankModel as $index => $clientBank) {
                        if (empty($clientBank->bank_id) && empty($clientBank->account) && empty($clientBank->name)) {
                            unset($clientBankModel[$index]);
                            continue;
                        }
                        $clientBank->client_id = $model->id;
                    }

                    $flag = true;
                    if (!empty($clientBankModel)) {
                        // ajax validation
                        if (Yii::$app->request->isAjax && !$isPjax) {
                            Yii::$app->response->format = Response::FORMAT_JSON;
                            return ArrayHelper::merge(
                                            ActiveForm::validateMultiple($clientBankModel), []
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
                    } else {
                        $transaction->commit();
                    }
                }
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                if ($isPjax) {
                    return $this->actionAjaxModalNameList(['selected_id' => $model->id]);
                } else {
                    return $this->redirect('index');
                }
            }
        } else {
            ClientUIAsset::register(Yii::$app->getView());

            $model->status = Client::CLIENT_STATUS_ACTIVE;
            $model->it_is = Client::CLIENT_IT_IS_CLIENT;
            $model->tax = Client::CLIENT_DEFAULT_VAT_TAX;
            $model->debit_valuta_id = Valuta::VALUTA_DEFAULT;

            $clientBankModel = [new ClientBank()];

            $isAdmin = FSMUser::getIsPortalAdmin();
            $isOwner = $isAdmin || FSMUser::getIamOwner();
            $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
            $user = Yii::$app->user->identity;
            $profileId = $user ? $user->profile->id : null;
            if (array_key_exists($profileId, $managerList)) {
                $model->manager_id = $profileId;
            }

            if ($model->manager_id && class_exists('Post', false)) {
                $manager = FSMProfile::findOne($model->manager_id);
                if ($manager) {
                    $post = $manager->post;
                    $managerList = $post->getProfileListForPost();
                    $managerList = ArrayHelper::map($managerList, 'id', 'name');
                }
            }

            $countryList = Country::getNameArr();
            $clientGroupList = ClientGroup::getNameArr(['enabled' => true]);
            $bankList = Bank::getNameArr(['enabled' => true]);
            $valutaList = Valuta::getNameArr();
            $languageList = Language::getEnabledLanguageList();
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('create', [
                            'model' => $model,
                            'clientBankModel' => $clientBankModel,
                            'filesModel' => $filesModel,
                            'countryList' => $countryList,
                            'languageList' => $languageList,
                            'bankList' => $bankList,
                            'valutaList' => $valutaList,
                            'managerList' => $managerList,
                            'clientGroupList' => $clientGroupList,
                            'isAdmin' => $isAdmin,
                            'isOwner' => $isOwner,
                            'isModal' => true,
                ]);
            } else {
                return $this->render('create', [
                            'model' => $model,
                            'clientBankModel' => $clientBankModel,
                            'filesModel' => $filesModel,
                            'countryList' => $countryList,
                            'languageList' => $languageList,
                            'bankList' => $bankList,
                            'valutaList' => $valutaList,
                            'managerList' => $managerList,
                            'clientGroupList' => $clientGroupList,
                            'isAdmin' => $isAdmin,
                            'isOwner' => $isOwner,
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
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $clientBankModel = $model->clientBanks;
        $clientBankModel = !empty($clientBankModel) ? $clientBankModel : [new ClientBank()];
        $filesModel = $model->logo;
        $filesModel = (!empty($filesModel) ? $filesModel : new Files());

        $modelArr = [
            'Client' => $model,
            'Files' => $filesModel,
        ];

        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                if (!empty($model->parent_id)) {
                    $parent = $model->parent;
                    $model->abonent_id = (!empty($parent->abonent_id) ? $parent->abonent_id : null);
                    $model->it_is = (!empty($parent->abonent_id) ? Client::CLIENT_IT_IS_ABONENT : $model->it_is);
                }

                $oldFileName = $filesModel->filepath;
                $file = $filesModel->uploadFile('logo');
                $result = true;
                if (!empty($file)) {
                    $filesModel->oldFileName = $oldFileName;
                    $result = $filesModel->save();
                    $model->uploaded_file_id = $filesModel->id;
                } else {
                    $model->uploaded_file_id = null;
                }

                if (!empty($_POST['use_legal_address'])) {
                    $model->office_address = $model->legal_address;
                    $model->office_country_id = $model->legal_country_id;
                }

                if (!$model->save()) {
                    $transaction->rollBack();
                    throw new Exception('Cannot to save data! ' . $model->errorMessage);
                } else {
                    if (empty($file) && !empty($oldFileName)) {
                        $filesModel->delete();
                    }

                    $oldBankIDs = isset($clientBankModel[0]) && !empty($clientBankModel[0]->id) ? ArrayHelper::map($clientBankModel, 'id', 'id') : [];

                    $clientBankModel = FSMBaseModel::createMultiple(ClientBank::classname(), $clientBankModel);
                    FSMBaseModel::loadMultiple($clientBankModel, Yii::$app->request->post());
                    $deletedBankIDs = array_diff($oldBankIDs, array_filter(ArrayHelper::map($clientBankModel, 'id', 'id')));

                    foreach ($clientBankModel as $index => $clientBank) {
                        if (empty($clientBank->bank_id) && empty($clientBank->account) && empty($clientBank->name)) {
                            unset($clientBankModel[$index]);
                            continue;
                        }
                        $clientBank->client_id = $model->id;
                    }
                    $flag = true;
                    if (!empty($clientBankModel)) {
                        // ajax validation
                        if (Yii::$app->request->isAjax) {
                            Yii::$app->response->format = Response::FORMAT_JSON;
                            return ArrayHelper::merge(
                                            ActiveForm::validateMultiple($clientBankModel), []
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
                    } else {
                        $transaction->commit();
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

            $isAdmin = FSMUser::getIsPortalAdmin();
            $isOwner = $isAdmin || FSMUser::getIamOwner();
            $clientGroupList = ClientGroup::getNameArr(['enabled' => true]);
            $countryList = Country::getNameArr();
            $bankList = Bank::getNameArr(['enabled' => true]);
            $valutaList = Valuta::getNameArr();
            $languageList = Language::getEnabledLanguageList();
            $manager = $model->manager;
            $managerList = [];
            if ($manager && class_exists('Post', false)) {
                $post = $manager->post;
                $managerList = $post->getProfileListForPost();
                $managerList = ArrayHelper::map($managerList, 'id', 'name');
            } else {
                $managerList = FSMProfile::getProfileListByRole([FSMUser::USER_ROLE_OPERATOR, FSMUser::USER_ROLE_BOOKER]);
            }
            return $this->render('update', [
                        'model' => $model,
                        'clientBankModel' => $clientBankModel,
                        'filesModel' => $filesModel,
                        'countryList' => $countryList,
                        'languageList' => $languageList,
                        'bankList' => $bankList,
                        'valutaList' => $valutaList,
                        'managerList' => $managerList,
                        'clientGroupList' => $clientGroupList,
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
    public function actionView($id)
    {
        ButtonDeleteAsset::register(Yii::$app->getView());

        $model = $this->findModel($id);
        $logoModel = $model->logo;
        $logoPath = !empty($logoModel) ? $logoModel->uploadedFileUrl : null;

        $clientBankModel = $model->clientBanks;
        $clientBankModel = !empty($clientBankModel) ? $clientBankModel : [new ClientBank()];

        $isAdmin = FSMUser::getIsPortalAdmin();
        $isOwner = $isAdmin || FSMUser::getIamOwner();

        $clientBankSearchModel = new ClientBankSearch();
        $clientBankdataProvider = $clientBankSearchModel->search(['client_id' => $id, 'deleted' => 0]);

        return $this->render('view', [
                    'model' => $model,
                    'logoPath' => $logoPath,
                    'clientBankdataProvider' => $clientBankdataProvider,
                    'clientBankSearchModel' => $clientBankSearchModel,
                    'isAdmin' => $isAdmin,
                    'isOwner' => $isOwner,
        ]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $abonent = $model->mainForAbonent;
        if (!empty($abonent)) {
            Yii::$app->getSession()->setFlash('error', Yii::t('client', 'The client Cannot be removed. This is the main client of the abonent.'));
            $previousUrl = $model->getBackURL()/* Url::previous() */;
            return $this->redirect($previousUrl);
        }
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            if ($model->delete()) {
                $transaction->commit();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            $message = $e->getMessage();
            Yii::$app->getSession()->setFlash('error', $message);
            Yii::error($message, __METHOD__);
        } finally {
            if(!$this->pjaxIndex){
                return $this->redirect(['index']);
            }
        }
    }

    public function actionStaff($id)
    {
        $model = $this->findModel($id);

        $userList = [];
        foreach ($model->profiles as $profile) {
            $user = $profile->user;
            if (!$user) {
                continue;
            }
            $roleList = FSMUser::getUserRoleList($user->id);
            $roleArr = [];
            foreach ($roleList as $role) {
                if ($role->name == FSMUser::USER_ROLE_USER) {
                    continue;
                }
                $roleArr[] = $role->description;
            }
            $gravatar_id = $profile->gravatar_id;
            $userList[] = [
                /*
                  'content' => [
                  'body' => Html::encode($profile->name),
                  //'bodyOptions' => ['target' => '_blank'],
                  ],
                 * 
                 */
                'content' => ($gravatar_id ? "<img src='http://gravatar.com/avatar/{$gravatar_id}>?s=28' alt='{$user->username}' style='margin-right: 5px;'/>" : '') . Html::encode($profile->name),
                'url' => ['/user/profile/show', 'id' => $profile->user_id],
                'badge' => implode(', ', $roleArr),
                'options' => ['target' => '_blank'],
            ];
        }

        $isAdmin = FSMUser::getIsPortalAdmin();
        $isOwner = $isAdmin || FSMUser::getIamOwner();
        return $this->render('_staff', [
                    'model' => $model,
                    'userList' => $userList,
                    'isAdmin' => $isAdmin,
                    'isOwner' => $isOwner,
        ]);
    }

    public function actionAjaxNameList($q = null)
    {
        $q = trim($q);
        $args = $_GET;
        if (isset($args['q'])) {
            unset($args['q']);
        }
        $args['deleted'] = false;

        $out = [];
        $out['results'][] = ['id' => '', 'text' => ''];

        $data = Client::getNameList($q, $args);
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $out['results'][] = ['id' => $key, 'text' => $value]; // !!! 'text' is needed for Select2 templateResult & templateSelection functions
            }
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $out;
    }

    public function actionAjaxGetTax($id = null)
    {
        if (!$id) {
            $id = !empty($_POST['id']) ? $_POST['id'] : null;
        }
        $client = Client::findOne($id);
        $out[] = [
            'tax' => !empty($client) ? $client->tax : 0,
        ];
        echo Json::encode($out);
        return false;
    }

    public function actionAjaxGetClientListByAbonent()
    {
        $out = [];
        $selected = null;
        if (isset($_POST['depdrop_parents'])) {
            $id = end($_POST['depdrop_parents']);
            if (empty($id) || !is_numeric($id)) {
                echo Json::encode(['output' => '', 'selected' => $selected]);
                return false;
            }
            $list = Client::findAll(['abonent_id' => $id, 'deleted' => false]);
            if (count($list) > 0) {
                foreach ($list as $i => $item) {
                    $out[] = ['id' => $item['id'], 'name' => $item['name']];
                }
            }
            $selected = (count($list) == 1 ? strval($list[0]['id']) : '');
        }
        // Shows how you can preselect a value  
        echo Json::encode(['output' => $out, 'selected' => $selected]);
        return false;
    }

    public function actionAjaxGetProjectList()
    {
        $out = [];
        $selected = null;
        if (isset($_POST['depdrop_parents'])) {
            $id = end($_POST['depdrop_parents']);
            if (empty($id) || !is_numeric($id)) {
                echo Json::encode(['output' => '', 'selected' => $selected]);
                return false;
            }
            $list = $this->findModel($id)->clientProjects;
            if (count($list) > 0) {
                foreach ($list as $i => $item) {
                    $out[] = ['id' => $item['id'], 'name' => $item['name']];
                }
            }
            $selected = (count($list) == 1 ? strval($list[0]['id']) : '');
        }
        // Shows how you can preselect a value  
        echo Json::encode(['output' => $out, 'selected' => $selected]);
        return false;
    }

    public function actionAjaxGetClientBankList()
    {
        $out = [];
        $selected = null;
        if (isset($_POST['depdrop_parents'])) {
            $id = end($_POST['depdrop_parents']);
            if (empty($id) || !is_numeric($id)) {
                echo Json::encode(['output' => '', 'selected' => $selected]);
                return false;
            }
            $list = $this->findModel($id)->clientBanks;
            if (count($list) > 0) {
                foreach ($list as $i => $item) {
                    $bank = $item->bank;
                    $account = $bank->name . ' | ' . $item->account . (!empty($item->name) ? ' ( ' . $item->name . ' )' : '');
                    $out[] = ['id' => $item['id'], 'name' => $account];
                }
            }
            $selected = (count($list) == 1 ? strval($list[0]['id']) : '');
        }
        // Shows how you can preselect a value  
        echo Json::encode(['output' => $out, 'selected' => $selected]);
        return false;
    }

    public function actionAjaxGetClientPersonList()
    {
        $out = [];
        $selected = null;
        if (isset($_POST['depdrop_parents'])) {
            $id = end($_POST['depdrop_parents']);
            if (empty($id) || !is_numeric($id)) {
                echo Json::encode(['output' => '', 'selected' => $selected]);
                return false;
            }
            $args = $_GET;
            if (!empty($args)) {
                $args = ArrayHelper::merge(
                                $args, ['client_id' => $id]
                );
                $list = ClientContact::findAll($args);
            } else {
                $list = $this->findModel($id)->clientContacts;
            }
            if (count($list) > 0) {
                foreach ($list as $i => $item) {
                    $person = $item->first_name . ' ' . $item->last_name . (!empty($item->position_id) ? ' ( ' . $item->position->name . ' )' : '');
                    $out[] = ['id' => $item['id'], 'name' => $person];
                }
                $selected = (count($list) == 1 ? strval($list[0]['id']) : '');
            }
        }
        // Shows how you can preselect a value  
        echo Json::encode(['output' => $out, 'selected' => $selected]);
        return false;
    }

}
