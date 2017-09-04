<?php

namespace frontend\controllers\report;

use Yii;
//use yii\base\Exception;
//use yii\helpers\Url;
//use yii\helpers\ArrayHelper;
//use yii\web\Response;

use common\models\bill\search\BillSearch;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;
use common\models\client\Project;
use common\models\client\Agreement;
use common\models\client\Client;

use frontend\assets\BillUIAsset;
use common\assets\ButtonDeleteAsset;
use common\assets\ButtonMultiActionAsset;

class BaseReportController extends \yii\web\Controller
{
    public function actionDebitorCreditor()
    {
        $params = Yii::$app->request->getQueryParams();
        $searchModel = new BillSearch();
        $dataProvider = $searchModel->searchDebitorCreditorReport($params);        
        
        $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
        return $this->render('debitor-creditor', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'clientList' => $clientList,
        ]);        
    }

    public function actionEbitda()
    {
        $params = Yii::$app->request->getQueryParams();
        $searchModel = new BillSearch();
        $dataProvider = $searchModel->searchEbitdaReport($params);        
        
        $projectList = Project::getNameArr();
        return $this->render('ebitda', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'projectList' => $projectList,
        ]);        
    }

    public function actionVat()
    {
        $params = Yii::$app->request->getQueryParams();
        $searchModel = new BillSearch();
        $dataProvider = $searchModel->searchVatReport($params);        
        
        $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);
        return $this->render('vat', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'clientList' => $clientList,
        ]);
    }

}
