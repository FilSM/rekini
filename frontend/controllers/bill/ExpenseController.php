<?php

namespace frontend\controllers\bill;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\Response;

use common\controllers\FilSMController;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;
use common\models\client\Client;
use common\models\bill\ExpenseType;
use common\models\client\Project;
use common\models\Valuta;
use common\models\mainclass\FSMBaseModel;

use common\assets\ButtonDeleteAsset;
use frontend\assets\ExpenseUIAsset;

/**
 * ExpenseController implements the CRUD actions for Expense model.
 */
class ExpenseController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\bill\Expense';
        $this->defaultSearchModel = 'common\models\bill\search\ExpenseSearch';
        $this->pjaxIndex = true;
    }

    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex()
    {
        ButtonDeleteAsset::register(Yii::$app->getView());

        $searchModel = new $this->defaultSearchModel;
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        $projectList = Project::getNameArr(['deleted' => false]);
        $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
        $expenseTypeList = ExpenseType::getNameArr();
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'projectList' => $projectList,
            'clientList' => $clientList,
            'expenseTypeList' => $expenseTypeList,
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

        if ($model->load(Yii::$app->request->post())) {
            $model->doc_date = date('Y-m-d', strtotime($model->doc_date));
            if(!$model->save()){
                $this->refresh();
            }
            return $this->redirect('index');
        } else {
            ExpenseUIAsset::register(Yii::$app->getView());
            
            $model->doc_number = 'EX-EC'.date('dmY').'/'.$model->lastNumber;
            $model->doc_date = date('d-M-Y');
            $model->valuta_id = Valuta::VALUTA_DEFAULT;
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            $projectList = Project::getNameArr(['deleted' => false]);
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            $expenseTypeList = ExpenseType::getNameArr();
            $valutaList = Valuta::getNameArr();
            return $this->render('create', [
                'model' => $model,
                'projectList' => $projectList,
                'clientList' => $clientList,
                'expenseTypeList' => $expenseTypeList,
                'valutaList' => $valutaList,
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
    public function actionUpdate($id) {
        $model = $this->findModel($id);
        
        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post())) {
            $model->doc_date = date('Y-m-d', strtotime($model->doc_date));
            if(!$model->save()){
                $this->refresh();
            }
            return $this->redirect('index');
        } else {
            ExpenseUIAsset::register(Yii::$app->getView());
            
            $model->doc_date = date('d-M-Y', strtotime($model->doc_date));
            
            $isAdmin = FSMUser::getIsPortalAdmin();
            $projectList = Project::getNameArr(['deleted' => false]);
            $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
            $expenseTypeList = ExpenseType::getNameArr();
            $valutaList = Valuta::getNameArr();
            return $this->render('update', [
                'model' => $model,
                'projectList' => $projectList,
                'clientList' => $clientList,
                'expenseTypeList' => $expenseTypeList,
                'valutaList' => $valutaList,
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
        
        $model = $this->findModel($id);
        $isAdmin = FSMUser::getIsPortalAdmin();
        return $this->render('view', [
            'model' => $model,
            'isAdmin' => $isAdmin,
        ]);
    }     
}