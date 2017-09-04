<?php

namespace backend\controllers\address;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json; 
use yii\helpers\Url;
use yii\db\Query;

use common\controllers\FilSMController;
use common\models\address\Address;
use common\models\address\Country;
use common\models\address\Region;
use common\models\address\City;
use common\models\address\District;
use common\models\address\search\AddressSearch;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;
use common\assets\UIAsset;
use common\assets\ButtonDeleteAsset;

/**
 * AddressController implements the CRUD actions for Address model.
 */
class AddressController extends FilSMController {
    
    /**
     * Initializes the controller.
     */
    public function init() {
        parent::init();
        $this->defaultModel = 'common\models\address\Address';
    }

    /**
     * Lists all Address models.
     * @return mixed
     */
    public function actionIndex() {
        UIAsset::register(Yii::$app->getView());
        ButtonDeleteAsset::register(Yii::$app->getView());
        
        $searchModel = new AddressSearch;
        $params = Yii::$app->request->getQueryParams();
        $params['deleted'] = (empty($params) || empty($params['AddressSearch'])) ? 
            0 : 
            (isset($params['AddressSearch']['deleted']) && ($params['AddressSearch']['deleted'] !== '') ? 
                $params['AddressSearch']['deleted'] : 
                0
            );        
        $dataProvider = $searchModel->search($params);
        
        $ownerList = FSMProfile::getNameArr();
        $countryList = Country::getNameArr();
        $regionList = Region::getNameArr();
        $cityList = City::getNameArr();
        $districtList = District::getNameArr();
        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'ownerList' => $ownerList,
            'countryList' => $countryList,
            'regionList' => $regionList,
            'cityList' => $cityList,
            'districtList' => $districtList,
        ]);
    }

    /**
     * Creates a new Address model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Address;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            $ownerList = FSMProfile::getNameArr();
            $countryList = Country::getNameArr();
            $regionList = Region::getNameArr();
            $cityList = City::getNameArr();
            $districtList = District::getNameArr();
            return $this->render('create', [
                'model' => $model,
                'ownerList' => $ownerList,
                'countryList' => $countryList,
                'regionList' => $regionList,
                'cityList' => $cityList,
                'districtList' => $districtList,
            ]);
        }
    }

    /**
     * Updates an existing Address model.
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
            
            $ownerList = FSMProfile::getNameArr();
            $countryList = Country::getNameArr();
            $regionList = Region::getNameArr();
            $cityList = City::getNameArr();
            $districtList = District::getNameArr();
            return $this->render('update', [
                'model' => $model,
                'ownerList' => $ownerList,
                'countryList' => $countryList,
                'regionList' => $regionList,
                'cityList' => $cityList,
                'districtList' => $districtList,
            ]);
        }
    }

    public function actionAjaxPostalCodeList($q = null, $limit = null) {
        $out = [];
        if(isset($this->defaultModel)){
            $data = Address::getPostalCodeList($q, $limit);
            if(!empty($data)){
                foreach ($data as $row) {
                    $out[] = [
                        'id' => $row['id'], 
                        'postal_code' => $row['postal_code'],
                        
                        'district_id' => $row['district_id'],
                        'city_id' => $row['city_id'],
                        'region_id' => $row['region_id'],
                        'country_id' => $row['country_id'],
                        
                        'political' => $row['political'],
                        'sublocality_level_1' => $row['sublocality_level_1'],
                        'sublocality' => $row['sublocality'],
                        
                        'district' => $row['district'],
                        'locality' => $row['locality'],
                        'administrative_area_level_1' => $row['administrative_area_level_1'],
                        'country' => $row['country'],
                    ];
                }
            }
        }
        echo Json::encode($out);
        return false;
    }     

    public function actionAjaxRouteNameList($q = null, $limit = null) {
        $q = trim($q);
        $args = $_GET;
        if(isset($args['q'])){
            unset($args['q']);
        }        
        if(isset($args['limit']) && isset($limit)){
            unset($args['limit']);
        }        
        $out = [];
        if(isset($this->defaultModel)){
            $data = Address::getRouteNameList($q, $args, $limit);
            if(!empty($data)){
                foreach ($data as $key => $value) {
                    $out[] = ['id' => $key, 'name' => $value];
                }
            }
        }
        echo Json::encode($out);
        return false;
    }
    
    public function actionAjaxGetAddressByCompany($client_id = null, $address_type = null, $q = null, $limit = null) {
        $query = new Query;

        $query->select(['*'])
            ->from(Address::tableName())
            ->orderBy('company_name');
        
        if($client_id){
            $query->andWhere(['client_id' => $client_id]);
        }else{
            $user = Yii::$app->user->identity;
            if(!$user->hasRole($user->id, [FSMUser::USER_ROLE_SUPERUSER, FSMUser::USER_ROLE_PORTALADMIN])){
                if($profile = $user->profile){
                    if($client = $profile->client){
                        $query->andWhere(['client_id' => $client->id]);
                    }
                }
            }
        }
        if($address_type){
            $query->andWhere(['address_type' => $address_type]);
        }
        $query->andWhere(['like', 'company_name', $q]);
        $query->andWhere(['not', ['customer_address' => null]]);
        $query->andWhere(['not', ['customer_address' => '']]);
        
        if($limit){
            $query->limit($limit);
        }
        $command = $query->createCommand();
        $data = $command->queryAll();
        $out = [];
        foreach ($data as $row) {
            $out[] = [
                'id' => $row['id'], 
                'company_name' => $row['company_name'],
                'contact_person' => $row['contact_person'],
                'contact_phone' => $row['contact_phone'],
                'contact_email' => $row['contact_email'],
                'customer_address' => $row['customer_address'],
                'formated_address' => $row['formated_address'],
                
                'street_number' => $row['street_number'],
                'route' => $row['route'],
                'district' => $row['district'],
                'locality' => $row['locality'],
                'administrative_area_level_1' => $row['administrative_area_level_1'],
                'country' => $row['country'],
                'postal_code' => $row['postal_code'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude'],
            ];
        }
        echo Json::encode($out);
        return false;
    }    
    
    public function actionDeleteSelected($ids) {
        if(empty($ids)){
            return $this->redirect(['index']);
        }
        $idsArr = explode(',', $ids);
        $transaction = Yii::$app->getDb()->beginTransaction(); 
        try {
            $result = true;
            foreach ($idsArr as $id) {
                $model = $this->findModel($id);
                $result = $result && $model->delete();
            }
            if($result){
            // delete into DB
            //if(Address::deleteAll(['id' => $idsArr])){
                $transaction->commit();
                Yii::$app->getSession()->setFlash('success', Yii::t('common', '{count, number} {count, plural, =1{entry} other{entries}} remove!.',  ['count' => count($idsArr)]));
            }else{
                $transaction->rollBack();
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('error', $message);
            Yii::error($message, __METHOD__);
        } finally {
            //return $this->redirect(['index']);
        }        
        $backUrl = \common\models\mainclass\FSMBaseModel::getBackURL('index');
        return $this->redirect($backUrl);
    }
}