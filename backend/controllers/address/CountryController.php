<?php

namespace backend\controllers\address;

use Yii;
use yii\helpers\Json; 
use yii\web\Response;

use common\controllers\FilSMController;
use common\models\address\Country;

/**
 * CountryController implements the CRUD actions for Country model.
 */
class CountryController extends FilSMController {
    
    /**
     * Initializes the controller.
     */
    public function init() {
        parent::init();
        $this->defaultModel = 'common\models\address\Country';
        $this->defaultSearchModel = 'common\models\address\search\CountrySearch';
    }

    public function actionAjaxGetRegions() {
        $out = [];  
        $selected = null;  
        if (isset($_POST['depdrop_parents'])) {  
            $id = end($_POST['depdrop_parents']);  
            if(empty($id) || !is_numeric($id)){  
                echo Json::encode(['output' => '', 'selected' => $selected]);  
                return false;  
            }  
            $list = $this->findModel($id)->regions;            
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
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('create', [
                    'model' => $model,
                    'isModal' => true,
                ]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                    'isModal' => false,
                ]);
            }
        }
    } 
}