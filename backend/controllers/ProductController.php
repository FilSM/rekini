<?php
namespace backend\controllers;

use Yii;
use yii\helpers\Url;
use yii\helpers\Json; 
use yii\web\Response;

use common\controllers\AdminListController;

/**
 * ProductController implements the CRUD actions for Product model.
 */
class ProductController extends AdminListController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\Product';
        $this->defaultSearchModel = 'common\models\search\ProductSearch';
    }
    
    public function actionAjaxGetMeasure($id = null)
    {
        $id = !$id ? (!empty($_POST['id']) ? $_POST['id'] : null) : $id;
        if (!$id) {
            return false;
        }
        $model = $this->findModel($id);
        $out[] = [
            'measure' => !empty($model) ? ['id' => $model->measure_id, 'name' => $model->measure->name] : [],
        ];
        echo Json::encode($out);
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
            $measuraList = \common\models\Measure::getNameArr();
            if (Yii::$app->request->isAjax) {
                return $this->renderAjax('create', [
                    'model' => $model,
                    'measuraList' => $measuraList,
                    'isModal' => true,
                ]);
            } else {
                return $this->render('create', [
                    'model' => $model,
                    'measuraList' => $measuraList,
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

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirectToPreviousUrl($model->id);              
        } else {
            $measuraList = \common\models\Measure::getNameArr();
            return $this->render('update', [
                'model' => $model,
                'measuraList' => $measuraList,
            ]);
        }
    }    
}