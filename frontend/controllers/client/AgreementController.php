<?php

namespace frontend\controllers\client;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\filters\VerbFilter;
use yii\web\Response;

use common\controllers\FilSMController;
use common\assets\ButtonDeleteAsset;
use common\models\mainclass\FSMBaseModel;
use common\models\user\FSMUser;
use common\models\Valuta;
use common\models\Files;
use common\models\client\Client;
use common\models\client\ClientRole;
use common\models\client\Project;
use common\models\client\Agreement;

use frontend\assets\AgreementUIAsset;

/**
 * AgreementController implements the CRUD actions for Agreement model.
 */
class AgreementController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\client\Agreement';
        $this->defaultSearchModel = 'common\models\client\search\AgreementSearch';
        $this->pjaxIndex = true;
    }

    public function behaviors() {
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
    
    public function actionAjaxGetModel($id) {
        if (empty($id)) {
            return [];
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        $out['agreement'] = Agreement::findOne($id);        
        $out['first_client'] = $out['agreement']->firstClient;
        $out['first_client_role'] = !empty($out['agreement']->first_client_role_id) ? $out['agreement']->firstClientRole->name : '';
        $out['first_client_address'] = $out['first_client']->legal_address;
        $out['second_client'] = $out['agreement']->secondClient;
        $out['second_client_role'] = !empty($out['agreement']->second_client_role_id) ? $out['agreement']->secondClientRole->name : '';
        $out['second_client_address'] = $out['second_client']->legal_address;
        return $out;
    }
    
    public function actionAjaxGetFullAgreementList() {
        $out = [];  
        $list = Agreement::getNameArr(['deleted' => false]);
        if (count($list) > 0) {  
            foreach ($list as $id => $name) {  
                $out[] = [
                    'id' => $id, 
                    'name' => $name,
                ];  
            }  
            $selected = (count($list) == 1 ? strval($list[0]['id']) : '');
        }else{
            $selected = '';  
        }  
        // Shows how you can preselect a value  
        echo Json::encode(['output' => $out, 'selected' => $selected]);  
        return false;  
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
        $params['deleted'] = (empty($params) || empty($params['AgreementSearch'])) ?
                0 :
                (isset($params['AgreementSearch']['deleted']) && ($params['AgreementSearch']['deleted'] !== '') ?
                        $params['AgreementSearch']['deleted'] :
                        0
                );

        $dataProvider = $searchModel->search($params);

        $isAdmin = FSMUser::getIsPortalAdmin();
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'isAdmin' => $isAdmin,
        ]);
    }
    
    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($project_id = null) {
        $model = new $this->defaultModel;
        $filesModel = new Files();

        $modelArr = [
            'Agreement' => $model,
            'Files' => $filesModel,
        ];
        
        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxMultipleValidation($modelArr);
        }

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            if(($model->first_client_id == $model->second_client_id) ||
                ($model->first_client_id == $model->third_client_id) ||
                ($model->second_client_id == $model->third_client_id)){
                throw new Exception('The same clients are selected!');
            }
            $client = $model->firstClient; 
            if(isset($client, $client->abonent_id)) {
                $model->abonent_id = $client->abonent_id;
            }else{
                $client = $model->secondClient;
                if(isset($client, $client->abonent_id)) {
                    $model->abonent_id = $client->abonent_id;
                }else{
                    $client = $model->thirdClient;
                    if(isset($client, $client->abonent_id)) {
                        $model->abonent_id = $client->abonent_id;
                    }else{
                        //throw new Exception('It is impossible to determine the Abonent!');
                    }
                }
            }
            
            $file = $filesModel->uploadFile('pdf/agreements');
            $result = true;
            if(!empty($file)){
                $result = $filesModel->save();
            }
            
            $model->uploaded_file_id = $filesModel->id;
            $model->signing_date = !empty($model->signing_date) ? date('Y-m-d', strtotime($model->signing_date)) : null;
            $model->due_date = !empty($model->due_date) ? date('Y-m-d', strtotime($model->due_date)) : null;
            $model->rate_from_date = !empty($model->rate_from_date) ? date('Y-m-d', strtotime($model->rate_from_date)) : null;
            $model->rate_till_date = !empty($model->rate_till_date) ? date('Y-m-d', strtotime($model->rate_till_date)) : null;
            if (!$result || !$model->save()) {
                throw new Exception('Cannot to save data! '.$model->errorMessage);
            }
            
            if ($isPjax) {
                return $this->actionAjaxModalNameList(['project_id' => $model->project_id, 'selected_id' => $model->id]);
            } else {
                return $this->redirect('index');
            } 
        } else {
            AgreementUIAsset::register(Yii::$app->getView());
            
            $model->number = 'L-EC'.date('dmY').'/'.$model->lastNumber;
            $model->project_id = (!empty($project_id) ? $project_id : null);
            $model->valuta_id = Valuta::VALUTA_DEFAULT;
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            $projectList = Project::getNameArr(['deleted' => false]);
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            $clientRoleList = ClientRole::getNameArr();
            $valutaList = Valuta::getNameArr();
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('create', [
                    'model' => $model,
                    'filesModel' => $filesModel,
                    'projectList' => $projectList,
                    'valutaList' => $valutaList,
                    'clientList' => $clientList,
                    'clientRoleList' => $clientRoleList,
                    'isAdmin' => $isAdmin,
                    'isModal' => true,
                ]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                    'filesModel' => $filesModel,
                    'projectList' => $projectList,
                    'valutaList' => $valutaList,
                    'clientList' => $clientList,
                    'clientRoleList' => $clientRoleList,
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
        $filesModel = $model->attachment;
        $filesModel = (!empty($filesModel) ? $filesModel : new Files());
        
        $modelArr = [
            'Agreement' => $model,
            'Files' => $filesModel,
        ];
        
        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            if(($model->first_client_id == $model->second_client_id) ||
                ($model->first_client_id == $model->third_client_id) ||
                ($model->second_client_id == $model->third_client_id)){
                throw new Exception('The same clients are selected!');
            }
            $client = $model->firstClient; 
            if(isset($client, $client->abonent_id)) {
                $model->abonent_id = $client->abonent_id;
            }else{
                $client = $model->secondClient;
                if(isset($client, $client->abonent_id)) {
                    $model->abonent_id = $client->abonent_id;
                }else{
                    $client = $model->thirdClient;
                    if(isset($client, $client->abonent_id)) {
                        $model->abonent_id = $client->abonent_id;
                    }else{
                        //throw new Exception('It is impossible to determine the Abonent!');
                    }
                }
            }
            
            $oldFileName = $filesModel->filepath;
            $file = $filesModel->uploadFile('pdf/agreements');
            $result = true;
            if(!empty($file)){
                $filesModel->oldFileName = $oldFileName;
                $result = $filesModel->save();
                $model->uploaded_file_id = $filesModel->id;                
            }else{
                $model->uploaded_file_id = null;                
            }
            
            $model->signing_date = !empty($model->signing_date) ? date('Y-m-d', strtotime($model->signing_date)) : null;
            $model->due_date = !empty($model->due_date) ? date('Y-m-d', strtotime($model->due_date)) : null;
            $model->rate_from_date = !empty($model->rate_from_date) ? date('Y-m-d', strtotime($model->rate_from_date)) : null;
            $model->rate_till_date = !empty($model->rate_till_date) ? date('Y-m-d', strtotime($model->rate_till_date)) : null;
            if (!$result || !$model->save()) {
                throw new Exception('Cannot to save data! '.$model->errorMessage);
            }
            if(empty($file) && !empty($oldFileName)){
                $filesModel->delete();
            }
            
            return $this->redirectToPreviousUrl($model->id);              
        } else {
            $this->rememberUrl($model->backURL, $id);            
            
            AgreementUIAsset::register(Yii::$app->getView());

            $model->signing_date = !empty($model->signing_date) ? date('d-M-Y', strtotime($model->signing_date)) : null;
            $model->due_date = !empty($model->due_date) ? date('d-M-Y', strtotime($model->due_date)) : null;
            $model->rate_from_date = !empty($model->rate_from_date) ? date('d-M-Y', strtotime($model->rate_from_date)) : null;
            $model->rate_till_date = !empty($model->rate_till_date) ? date('d-M-Y', strtotime($model->rate_till_date)) : null;
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            $projectList = Project::getNameArr();
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            $clientRoleList = ClientRole::getNameArr();
            $valutaList = Valuta::getNameArr();
            return $this->render('update', [
                'model' => $model,
                'filesModel' => $filesModel,
                'projectList' => $projectList,
                'valutaList' => $valutaList,
                'clientList' => $clientList,
                'clientRoleList' => $clientRoleList,
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
        
        $isAdmin = FSMUser::getIsPortalAdmin();
        return $this->render('view', [
            'model' => $this->findModel($id),
            'isAdmin' => $isAdmin,
        ]);
    }      
    
    public function actionPrint($id)
    {
        $model = $this->findModel($id);
        $filesModel = $model->attachment;
        
        if(!empty($filesModel)){
            if (is_file($filesModel->filepath)) {
                // Set up PDF headers
                header('Content-type: '. $filesModel->filemime);
                header('Content-Disposition: inline; filename="' . $filesModel->filename . '"');
                header('Content-Transfer-Encoding: binary');
                header('Content-Length: ' . filesize($filesModel->filepath));
                header('Accept-Ranges: bytes');

                // Render the file
                return readfile($filesModel->filepath);
            } else {
                // PDF doesn't exist so throw an error or something
                Yii::$app->getSession()->setFlash('error', Yii::t('cargo', 'Attachment pdf file doesn`t exist!'));
                return $this->redirect($model->getBackUrl());
            }
        }
    }
}