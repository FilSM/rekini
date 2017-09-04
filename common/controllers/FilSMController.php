<?php

namespace common\controllers;

use Yii;
use yii\base\ViewContextInterface;
use yii\base\Exception;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

use common\models\mainclass\FSMBaseModel;
use common\traits\AjaxValidationTrait;
use common\assets\ButtonDeleteAsset;

/**
 * Controller implements the CRUD actions for model.
 */
class FilSMController extends Controller  implements ViewContextInterface {
    
    use AjaxValidationTrait;
    
    protected $defaultModel = null;
    protected $defaultSearchModel = null;
    protected $_viewPath;
    protected $pjaxIndex = false;

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action) {
        if($action->id == 'delete'){
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    
    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex() {
        ButtonDeleteAsset::register(Yii::$app->getView());
        
        if(isset($this->defaultSearchModel)){
            $searchModel = new $this->defaultSearchModel;
            if(!$searchModel){
                $className = $this->className();
                throw new Exception("For the {$className} defaultSearchModel not defined.");
            }        
            $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

            return $this->render('index', [
                'dataProvider' => $dataProvider,
                'searchModel' => $searchModel,
            ]);
        }else{
            return $this->render('index');
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect('index');
        } else {
            return $this->render('create', [
                'model' => $model,
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirectToPreviousUrl($model->id);              
        } else {
            $this->rememberUrl($model->backURL, $id);            
            
            return $this->render('update', [
                'model' => $model,
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
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $this->findModel($id)->delete();
            $transaction->commit();            
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

    /**
     * Finds the single model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return The loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        $className = $this->className();
        $defaultModel = $this->defaultModel;
        if($defaultModel){
            if (($model = $defaultModel::findOne($id)) !== null) {
                return $model;
            } else {
                throw new Exception('The requested page does not exist.');
            }
        }else{
            throw new Exception("For the {$className} findModel function not defined.");
        }
    }
    
    public function actionAjaxGetModel($id) {
        if (empty($id)) {
            return [];
        }
        $model = $this->findModel($id);
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $model;
    }    

    public function actionAjaxNameList($q = null) {
        $q = trim($q);
        $args = $_GET;
        if(isset($args['q'])){
            unset($args['q']);
        }
        $out = [];
        if(isset($this->defaultModel)){
            $model = new $this->defaultModel;
            $data = $model::getNameList($q, $args);
            if(!empty($data)){
                foreach ($data as $key => $value) {
                    $out[] = ['id' => $key, 'name' => $value];
                }
            }
        }
        echo Json::encode($out);
        return false;
    }   
    
    public function actionAjaxSelect2NameList($q = null) {
        $q = trim($q);
        $args = $_GET;
        if (isset($args['q'])) {
            unset($args['q']);
        }
        $out = [];
        $out['results'][] = ['id' => '', 'text' => ''];
        if (isset($this->defaultModel)) {
            $model = new $this->defaultModel;
            $data = $model::getNameList($q, $args);
            if (!empty($data)) {
                foreach ($data as $key => $value) {
                    $out['results'][] = ['id' => $key, 'text' => $value]; // !!! 'text' is needed for Select2 templateResult & templateSelection functions
                }
            }
        }
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $out;
    }    
    
    public function actionAjaxModalNameList($param = [])
    {
        if(!isset($this->defaultModel)){
            return [];
        }
        
        $model = new $this->defaultModel;
        
        $selectedId = null;
        if(isset($param['selected_id'])){
            $selectedId = $param['selected_id'];
            unset($param['selected_id']);
        }
        $param = !empty($param) ? $param : null;
        
        if($model->hasAttribute('deleted') && !isset($param['deleted'])){
            $param['deleted'] = false;
        }
        
        // JSON response is expected in case of successful save
        Yii::$app->response->format = Response::FORMAT_JSON;
        $data = $model::getNameArr($param, $model::$nameField, $model::$keyField, $model::$nameField);
        unset($model);
        
        $result = [];
        foreach ($data as $key => $item) {
            $result[] = [
                'id' => $key,
                'text' => $item,
            ];
        }
        
        return [
            'success' => true,
            'selected' => $selectedId,
            'data' => $result,
        ];        
    }
    
    public function rememberUrl($url, $id)
    {
        Url::remember($url, get_class($this).'_'.$id);
    }
    
    public function redirectToPreviousUrl($id)
    {
        $previousUrl = Url::previous(get_class($this).'_'.$id);
        Yii::$app->getSession()->set(get_class($this).'_'.$id, null);
        return $this->redirect($previousUrl);
    }
    
}
