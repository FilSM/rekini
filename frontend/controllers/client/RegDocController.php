<?php

namespace frontend\controllers\client;

use Yii;
use yii\helpers\Url;

use common\controllers\FilSMController;
use common\models\mainclass\FSMBaseModel;
use common\models\user\FSMUser;
use common\models\client\Client;
use common\models\client\RegDocType;
use common\models\Files;
use common\assets\ButtonDeleteAsset;

/**
 * RegDocController implements the CRUD actions for RegDoc model.
 */
class RegDocController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\client\RegDoc';
        $this->defaultSearchModel = 'common\models\client\search\RegDocSearch';
    }
    
    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex($client_id = null) {
        ButtonDeleteAsset::register(Yii::$app->getView());
        $isAdmin = FSMUser::getIsPortalAdmin();
        
        $searchModel = new $this->defaultSearchModel;
        $params = Yii::$app->request->getQueryParams();
        $dataProvider = $searchModel->search($params);

        $clientModel = !empty($client_id) ? Client::findOne($client_id) : new Client();
        return $this->render('index', [
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
        $filesModel = new Files();

        $modelArr = [
            'RegDoc' => $model,
            'Files' => $filesModel,
        ];
        
        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            $transaction = Yii::$app->getDb()->beginTransaction();
            try {
                $model->doc_date = !empty($model->doc_date) ? date('Y-m-d', strtotime($model->doc_date)) : null;
                $model->expiration_date = !empty($model->expiration_date) ? date('Y-m-d', strtotime($model->expiration_date)) : null;
                if (!$result = $model->save()) {
                    throw new Exception('Cannot to save data! '.$model->errorMessage);
                }
                $file = $filesModel->uploadFile('pdf/reg-doc/'.$model->id);
                if(!empty($file) && $filesModel->save()){
                    $model->updateAttributes(['uploaded_file_id' => $filesModel->id]);            
                }
                $transaction->commit();
            } catch (\Exception $e) {
                $message = $e->getMessage();
                $transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->redirect(empty($client_id) ? 'index' : ['index', 'client_id' => $client_id]);
            }
        } else {
            $isAdmin = FSMUser::getIsPortalAdmin();
            $clientModel = !empty($client_id) ? Client::findOne($client_id) : new Client();
            $model->client_id = $clientModel->id;
            
            $regDocTypeList = RegDocType::getNameArr();
            return $this->render('create', [
                'model' => $model,
                'clientModel' => $clientModel,
                'filesModel' => $filesModel,
                'regDocTypeList' => $regDocTypeList,
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
        $filesModel = $model->attachment;
        $filesModel = (!empty($filesModel) ? $filesModel : new Files());
        
        $modelArr = [
            'RegDoc' => $model,
            'Files' => $filesModel,
        ];        
        $this->performAjaxMultipleValidation($modelArr);

        if ((FSMBaseModel::loadMultiple($modelArr, Yii::$app->request->post(), ''))) {
            //$transaction = Yii::$app->getDb()->beginTransaction();
            try {
                $oldFileName = $filesModel->filepath;
                $file = $filesModel->uploadFile('pdf/reg-doc/'.$model->id);
                $result = true;
                if(!empty($file)){
                    $filesModel->oldFileName = $oldFileName;
                    $result = $filesModel->save();
                    $model->uploaded_file_id = $filesModel->id;                
                }else{
                    $model->uploaded_file_id = null;                
                }
                
                $model->doc_date = !empty($model->doc_date) ? date('Y-m-d', strtotime($model->doc_date)) : null;
                $model->expiration_date = !empty($model->expiration_date) ? date('Y-m-d', strtotime($model->expiration_date)) : null;
                if(!$model->save()){
                    throw new Exception('Cannot to save data! '.$model->errorMessage);
                }
                if(empty($file) && !empty($oldFileName)){
                    $filesModel->delete();
                }
                //$transaction->commit();
            } catch (\Exception $e) {
                $message = $e->getMessage();
                //$transaction->rollBack();
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return $this->refresh();
            } finally {
                return $this->redirectToPreviousUrl($model->id);              
            }            
        } else {
            $this->rememberUrl($model->backURL, $id);            
            
            $model->doc_date = !empty($model->doc_date) ? date('d-M-Y', strtotime($model->doc_date)) : null;
            $model->expiration_date = !empty($model->expiration_date) ? date('d-M-Y', strtotime($model->expiration_date)) : null;
            $isAdmin = FSMUser::getIsPortalAdmin();
            $clientModel = !empty($client_id) ? Client::findOne($client_id) : new Client();
            $regDocTypeList = RegDocType::getNameArr();
            return $this->render('update', [
                'model' => $model,
                'clientModel' => $clientModel,
                'filesModel' => $filesModel,
                'regDocTypeList' => $regDocTypeList,
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