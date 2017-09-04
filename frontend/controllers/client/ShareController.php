<?php

namespace frontend\controllers\client;

use Yii;
use yii\helpers\Url;

use common\models\user\FSMUser;
use common\models\client\Client;
use common\assets\ButtonDeleteAsset;
use common\controllers\FilSMController;

/**
 * ShareController implements the CRUD actions for Share model.
 */
class ShareController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\client\Share';
        $this->defaultSearchModel = 'common\models\client\search\ShareSearch';
    }
    
    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex($client_id = null) {
        ButtonDeleteAsset::register(Yii::$app->getView());
        $isAdmin = FSMUser::getIsPortalAdmin();
        
        $searchModel = new $this->defaultSearchModel;
        if(!$searchModel){
            $className = $this->className();
            throw new Exception("For the {$className} defaultSearchModel not defined.");
        }        
        
        $params = Yii::$app->request->getQueryParams();
        if(!$isAdmin){
            $params['deleted'] = (empty($params) || empty($params['ShareSearch'])) ?
                    0 :
                    (isset($params['ShareSearch']['deleted']) && ($params['ShareSearch']['deleted'] !== '') ?
                            $params['ShareSearch']['deleted'] :
                            0
                    );
        }
        //$params['top_manager'] = 1;
        $dataProvider = $searchModel->search($params);

        $clientModel = !empty($client_id) ? Client::findOne($client_id) : new Client();
        return $this->render((empty($client_id) ? 'index' : 'client/index'), [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'clientModel' => $clientModel,
            'isAdmin' => $isAdmin,
        ]);
    }
    
    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($client_id = null) {
        $model = new $this->defaultModel;
        
        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post())) {
            $model->term_from = !empty($model->term_from) ? date('Y-m-d', strtotime($model->term_from)) : null;
            $model->term_till = !empty($model->term_till) ? date('Y-m-d', strtotime($model->term_till)) : null;
            $model->save();
            
            return $this->redirect(empty($client_id) ? 'index' : ['index', 'client_id' => $client_id]);
        } else {
            $isAdmin = FSMUser::getIsPortalAdmin();
            $clientModel = !empty($client_id) ? Client::findOne($client_id) : new Client();
            
            $model->client_id = null;
            $model->shareholder_id = $client_id;
            
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            return $this->render('create', [
                'model' => $model,
                'clientModel' => $clientModel,
                'clientList' => $clientList,
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
    public function actionUpdate($id, $client_id = null) {
        $model = $this->findModel($id);
        
        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post())) {
            $model->term_from = !empty($model->term_from) ? date('Y-m-d', strtotime($model->term_from)) : null;
            $model->term_till = !empty($model->term_till) ? date('Y-m-d', strtotime($model->term_till)) : null;
            $model->save();
            
            return $this->redirectToPreviousUrl($model->id);              
        } else {
            $this->rememberUrl($model->backURL, $id);            
            
            $model->term_from = !empty($model->term_from) ? date('d-M-Y', strtotime($model->term_from)) : null;
            $model->term_till = !empty($model->term_till) ? date('d-M-Y', strtotime($model->term_till)) : null;
            $isAdmin = FSMUser::getIsPortalAdmin();
            $clientModel = !empty($client_id) ? Client::findOne($client_id) : new Client();
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            return $this->render('update', [
                'model' => $model,
                'clientModel' => $clientModel,
                'clientList' => $clientList,
                'isAdmin' => $isAdmin,
            ]);
        }
    }  
    
    /**
     * Displays a single model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id, $client_id = null) {
        ButtonDeleteAsset::register(Yii::$app->getView());
        
        $isAdmin = FSMUser::getIsPortalAdmin();
        $clientModel = !empty($client_id) ? Client::findOne($client_id) : new Client();
        return $this->render('view', [
            'model' => $this->findModel($id),
            'clientModel' => $clientModel,
            'isAdmin' => $isAdmin,
        ]);
    }     
    
    /**
     * Deletes an existing single model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id, $client_id = null) {
        $this->findModel($id)->delete();
        return $this->redirect(['index', 'client_id' => $client_id]);
    }      
}