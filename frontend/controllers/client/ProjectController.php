<?php

namespace frontend\controllers\client;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Response;

use common\models\client\Project;
use common\controllers\FilSMController;
use common\assets\ButtonDeleteAsset;
use common\models\user\FSMUser;
use common\models\address\Country;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\client\Project';
        $this->defaultSearchModel = 'common\models\client\search\ProjectSearch';
        $this->pjaxIndex = true;
    }
    
    public function actionAjaxGetAgreementList() {
        $out = [];  
        if (isset($_POST['depdrop_parents'])) {  
            $id = end($_POST['depdrop_parents']);  
            if(!is_numeric($id) && !is_array($id)){  
                echo Json::encode(['output' => '', 'selected' => '']);  
                return false;  
            } 
            if(!is_array($id)){
                $list = $this->findModel($id)->agreements; 
            }else{
                $projectList = Project::findAll($id);
                $resultList = [];
                foreach ($projectList as $project) {
                    $list = $project->agreements;
                    foreach ($list as $i => $item) { 
                        $resultList[] = $item;
                    }
                }
                $list = $resultList;
            }
            if (count($list) > 0) {  
                foreach ($list as $i => $item) {  
                    if(!empty($_GET['without-id']) && ($_GET['without-id'] == $item['id'])){
                        continue;
                    }
                    $out[] = [
                        'id' => $item['id'], 
                        'name' => $item['number'],
                        /*
                        'options' => [
                            'style' => 'color:gray', 
                            'disabled' => true,
                        ]
                         * 
                         */
                    ];  
                }  
                if(empty($_GET['without-selected'])){
                    $selected = (count($list) == 1 ? strval($list[0]['id']) : '');
                }else{
                    $selected = '';
                }
            }else{
                $selected = '';  
            }  
            // Shows how you can preselect a value  
            echo Json::encode(['output' => $out, 'selected' => $selected]);  
            return false;  
        }  
        echo Json::encode(['output' => '', 'selected'=>'']);
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
        $params['deleted'] = (empty($params) || empty($params['ProjectSearch'])) ?
                0 :
                (isset($params['ProjectSearch']['deleted']) && ($params['ProjectSearch']['deleted'] !== '') ?
                        $params['ProjectSearch']['deleted'] :
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
    public function actionCreate() {
        $model = new $this->defaultModel;

        $isPjax = Yii::$app->request->isPjax;
        if (!$isPjax) {
            $this->performAjaxValidation($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($isPjax) {
                return $this->actionAjaxModalNameList(['selected_id' => $model->id]);
            } else {
                return $this->redirect('index');
            }            
        } else {
            $isAdmin = FSMUser::getIsPortalAdmin();
            $countryList = Country::getNameArr();
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('create', [
                    'model' => $model,
                    'countryList' => $countryList,
                    'isAdmin' => $isAdmin,
                    'isModal' => true,
                ]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                    'countryList' => $countryList,
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

        if ($model->load(Yii::$app->request->post())) {
            if (!$model->save()) {
                throw new Exception('Cannot to save data! '.$model->errorMessage);
            }
            return $this->redirectToPreviousUrl($model->id);          
        } else {
            $this->rememberUrl($model->backURL, $id);            
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            $countryList = Country::getNameArr();
            return $this->render('update', [
                'model' => $model,
                'countryList' => $countryList,
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
    
}