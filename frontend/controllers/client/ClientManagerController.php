<?php

namespace frontend\controllers\client;

use Yii;
use yii\helpers\Url;

use common\models\user\FSMUser;
use common\models\client\Client;
use common\assets\ButtonDeleteAsset;

use frontend\controllers\client\ClientContactController;

/**
 * ClientManagerController implements the CRUD actions for ClientManager model.
 */
class ClientManagerController extends ClientContactController
{

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = 'common\models\client\ClientManager';
        $this->defaultSearchModel = 'common\models\client\search\ClientManagerSearch';
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
            $params['deleted'] = (empty($params) || empty($params['ClientManagerSearch'])) ?
                    0 :
                    (isset($params['ClientManagerSearch']['deleted']) && ($params['ClientManagerSearch']['deleted'] !== '') ?
                            $params['ClientManagerSearch']['deleted'] :
                            0
                    );
        }
        $params['top_manager'] = 1;
        $dataProvider = $searchModel->search($params);

        $clientModel = !empty($client_id) ? Client::findOne($client_id) : new Client();
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'clientModel' => $clientModel,
            'isAdmin' => $isAdmin,
        ]);
    }
}