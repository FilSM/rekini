<?php

namespace backend\controllers\address;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use common\controllers\FilSMController;
use common\models\address\District;
use common\models\address\search\DistrictSearch;

/**
 * DistrictController implements the CRUD actions for District model.
 */
class DistrictController extends FilSMController {

    /**
     * Initializes the controller.
     */
    public function init() {
        parent::init();
        $this->defaultModel = 'common\models\address\District';
    }
    
    /**
     * Lists all District models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new DistrictSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        $dataFilterCountry = \common\models\address\Country::getNameArr();
        $dataFilterRegion = \common\models\address\Region::getNameArr();
        $dataFilterCity = \common\models\address\City::getNameArr();

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
                    'dataFilterCountry' => $dataFilterCountry,
                    'dataFilterRegion' => $dataFilterRegion,
                    'dataFilterCity' => $dataFilterCity,
        ]);
    }

    /**
     * Creates a new District model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new District;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            $countryList = \common\models\address\Country::getNameArr();
            $regionList = \common\models\address\Region::getNameArr();
            $cityList = \common\models\address\City::getNameArr();
            return $this->render('create', [
                        'model' => $model,
                        'countryList' => $countryList,
                        'regionList' => $regionList,
                        'cityList' => $cityList,
            ]);
        }
    }

    /**
     * Updates an existing District model.
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
            $cityList = \common\models\address\City::getNameArr();
            return $this->render('update', [
                        'model' => $model,
                        'countryList' => $countryList,
                        'regionList' => $regionList,
                        'cityList' => $cityList,
            ]);
        }
    }

}
