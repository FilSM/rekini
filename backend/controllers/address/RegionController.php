<?php

namespace backend\controllers\address;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Json; 
use yii\helpers\Url;

use common\controllers\FilSMController;
use common\models\address\Region;
use common\models\address\search\RegionSearch;

/**
 * RegionController implements the CRUD actions for Region model.
 */
class RegionController extends FilSMController {

    /**
     * Initializes the controller.
     */
    public function init() {
        parent::init();
        $this->defaultModel = 'common\models\address\Region';
    }

    /**
     * Lists all Region models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new RegionSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        $dataFilterCountry = \common\models\address\Country::getNameArr();
        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'dataFilterCountry' => $dataFilterCountry,
        ]);
    }

    /**
     * Creates a new Region model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Region;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            $countryList = \common\models\address\Country::getNameArr();
            return $this->render('create', [
                'model' => $model,
                'countryList' => $countryList, 
            ]);
        }
    }

    /**
     * Updates an existing Region model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirectToPreviousUrl($model->id);
        } else {
            $this->rememberUrl($model->backURL, $id);            
            
            $countryList = \common\models\address\Country::getNameArr();
            return $this->render('update', [
                'model' => $model,
                'countryList' => $countryList, 
            ]);
        }
    }

    public function actionAjaxGetCities() { 
        $out = []; 
        $selected = null; 
        if (isset($_POST['depdrop_parents'])) { 
            $id = end($_POST['depdrop_parents']); 
            if (empty($id) || !is_numeric($id)) { 
                echo Json::encode(['output' => '', 'selected' => $selected]); 
                return false; 
            } 
            $list = $this->findModel($id)->cities; 
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

}