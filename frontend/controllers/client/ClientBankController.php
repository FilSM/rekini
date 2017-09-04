<?php

namespace frontend\controllers\client;

use Yii;
use yii\helpers\Url;

use common\controllers\FilSMController;
use common\models\user\FSMUser;
use common\models\client\Client;
use common\models\Bank;
use common\assets\ButtonDeleteAsset;

/**
 * ClientBankController implements the CRUD actions for ClientBank model.
 */
class ClientBankController extends FilSMController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\client\ClientBank';
        $this->defaultSearchModel = 'common\models\client\search\ClientBankSearch';
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
        if(!$isAdmin){
            $params['deleted'] = (empty($params) || empty($params['ClientBankSearch'])) ?
                    0 :
                    (isset($params['ClientBankSearch']['deleted']) && ($params['ClientBankSearch']['deleted'] !== '') ?
                            $params['ClientBankSearch']['deleted'] :
                            0
                    );
        }
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
     * Lists all models.
     * @return mixed
     */
    public function actionReport() {
        ButtonDeleteAsset::register(Yii::$app->getView());
        
        $searchModel = new $this->defaultSearchModel;
        $params = Yii::$app->request->getQueryParams();
        $params['deleted'] = 0;       
        $dataProvider = $searchModel->search($params);
        
        $bankList = Bank::getNameArr(['enabled' => true]);
        $clientList = Client::getClientListByItIs([Client::CLIENT_IT_IS_ABONENT, Client::CLIENT_IT_IS_CLIENT]);

        return $this->render('bank-statement', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'bankList' => $bankList,
            'clientList' => $clientList,
        ]);
    }     
}