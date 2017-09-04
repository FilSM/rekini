<?php

namespace backend\controllers\address;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

use common\controllers\FilSMController;
use common\models\address\City;
use common\models\address\search\CitySearch;

/**
 * CityController implements the CRUD actions for City model.
 */
class CityController extends FilSMController {
    
    /**
     * Initializes the controller.
     */
    public function init() {
        parent::init();
        $this->defaultModel = 'common\models\address\City';
        $this->defaultSearchModel = 'common\models\address\search\CitySearch';
    }
    
    /**
     * Lists all City models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new CitySearch;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        $dataFilterCountry = \common\models\address\Country::getNameArr();
        $dataFilterRegion = \common\models\address\Region::getNameArr();

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
                    'dataFilterCountry' => $dataFilterCountry,
                    'dataFilterRegion' => $dataFilterRegion,
        ]);
    }

    /**
     * Creates a new City model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new City;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            $countryList = \common\models\address\Country::getNameArr();
            $regionList = \common\models\address\Region::getNameArr();
            return $this->render('create', [
                        'model' => $model,
                        'countryList' => $countryList,
                        'regionList' => $regionList,
            ]);
        }
    }

    /**
     * Updates an existing City model.
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
            $regionList = \common\models\address\Region::getNameArr();
            return $this->render('update', [
                        'model' => $model,
                        'countryList' => $countryList,
                        'regionList' => $regionList,
            ]);
        }
    }

    public function actionAjaxGetDistricts() {
        $out = [];
        $selected = null;
        if (isset($_POST['depdrop_parents'])) {
            $parents = end($_POST['depdrop_parents']);
            $ids = is_array($parents) ? $parents : [];
            $id = empty($ids) ? $parents : '';
            if(empty($ids)){
                if (empty($id) || !is_array($id) || !is_numeric($id)) {
                    echo Json::encode(['output' => '', 'selected' => $selected]);
                    return false;
                }
            }
            $list = [];
            if(!empty($id)){
                $list = $this->findModel($id)->districts;
                if (count($list) > 0) {
                    foreach ($list as $i => $item) {
                        $out[] = ['id' => $item['id'], 'name' => $item['name']];
                    }
                }
            }elseif(!empty($ids)){
                foreach ($ids as $id) {
                    $list = $this->findModel($id)->districts;
                    if (count($list) > 0) {
                        foreach ($list as $i => $item) {
                            $out[] = ['id' => $item['id'], 'name' => $item['name']];
                        }
                    }
                }
            }
            $selected = (count($list) == 1 ? strval($list[0]['id']) : '');
        }
        // Shows how you can preselect a value 
        echo Json::encode(['output' => $out, 'selected' => $selected]);
        return false;
    }

}
