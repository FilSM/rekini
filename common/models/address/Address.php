<?php

namespace common\models\address;

use Yii;
use yii\helpers\ArrayHelper;

use common\models\address\Country;
use common\models\address\Region;
use common\models\address\City;
use common\models\address\District;
use common\models\client\Client;
use common\models\user\FSMProfile;

/**
 * This is the model class for table "address".
 *
 * @property integer $id
 * @property integer $version
 * @property integer $deleted
 * @property integer $client_id
 * @property string $address_type
 * @property string $company_name
 * @property integer $country_id
 * @property integer $region_id
 * @property integer $city_id
 * @property integer $district_id
 * @property string $contact_person
 * @property string $contact_phone
 * @property string $contact_email
 * @property string $customer_address
 * @property string $apartment_number
 * @property string $street_number
 * @property string $route
 * @property string $district
 * @property string $political
 * @property string $sublocality_level_1
 * @property string $sublocality
 * @property string $locality
 * @property string $administrative_area_level_1
 * @property string $postal_code
 * @property double $latitude
 * @property double $longitude
 * @property string $formated_address
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $update_time
 * @property integer $update_user_id
 *
 * @property Client $owner
 * @property Country $country0
 * @property Region $region
 * @property City $city
 * @property Profile $userProfile
 * @property District $district0
 * @property User $createUser
 * @property User $updateUser
 * @property FSMProfile[] $profiles
 * @property Route[] $routes
 * @property Cargo[] $cargo
 * @property Client[] $companies
 */
class Address extends \common\models\mainclass\FSMVersionModel {
    
    const ADDRESS_TYPE_USER = 'user';
    const ADDRESS_TYPE_COMPANY = 'company';
    const ADDRESS_TYPE_CUSTOM = 'customs';
    const ADDRESS_TYPE_START = 'start';
    const ADDRESS_TYPE_FINISH = 'finish';
    const ADDRESS_TYPE_INTERIM = 'interim';

    public $gmapLocationFieldSet = '';

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'address';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['version', 'street_number', 'route', 'locality', 'country', 'postal_code'], 'required'],
            //['company_name', 'required', 'on' => ['default']],
            [['version', 'client_id', 'country_id', 'region_id', 'city_id', 'district_id', 
                'deleted', 'create_user_id', 'update_user_id'], 'integer'],
            [['latitude', 'longitude'], 'number'],
            [['formated_address', 'address_type'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['customer_address'], 'string', 'max' => 255],
            [['contact_person', 'contact_email', 'locality', 'country'], 'string', 'max' => 50],
            [['contact_phone'], 'string', 'max' => 20],
            [['route', 'district', 'political', 'company_name', 'route'
                /*, 'sublocality_level_1', 'sublocality'*/, 'administrative_area_level_1'], 'string', 'max' => 100],
            [['apartment_number', 'street_number', 'postal_code'], 'string', 'max' => 10],
            [['contact_email'], 'email'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('location', 'Address|Addresses', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'version' => Yii::t('common', 'Version'),
            'client_id' => Yii::t('location', 'Owner'),
            'address_type' => Yii::t('location', 'Address type'),
            'country_id' => Yii::t('location', 'Country'),
            'region_id' => Yii::t('location', 'State / Province / Region'),
            'city_id' => Yii::t('location', 'City'),
            'district_id' => Yii::t('location', 'District'),
            'customer_address' => Yii::t('location', 'Customer Address'),
            'company_name' => Yii::t('location', 'Company name'),
            'contact_person' => Yii::t('location', 'Contact person'),
            'contact_phone' => Yii::t('location', 'Contact phone'),
            'contact_email' => Yii::t('location', 'Contact Email'),
            'apartment_number' => Yii::t('location', 'Apartment number'),
            'street_number' => Yii::t('location', 'Street Number'),
            'route' => Yii::t('location', 'Street'),
            'district' => Yii::t('location', 'District'),
            'political' => Yii::t('location', 'District'),
            'sublocality_level_1' => Yii::t('location', 'District'),
            'sublocality' => Yii::t('location', 'Sub District'),
            'locality' => Yii::t('location', 'City'),
            'administrative_area_level_1' => Yii::t('location', 'State / Province / Region'),
            'country' => Yii::t('location', 'Country'),
            'postal_code' => Yii::t('location', 'Postal code'),
            'latitude' => Yii::t('location', 'Latitude'),
            'longitude' => Yii::t('location', 'Longitude'),
            'formated_address' => Yii::t('location', 'Formatted Address'),
            'deleted' => Yii::t('common', 'Deleted'),
            'create_time' => Yii::t('common', 'Create Time'),
            'create_user_id' => Yii::t('common', 'Create User'),
            'update_time' => Yii::t('common', 'Update Time'),
            'update_user_id' => Yii::t('common', 'Update User'),
        ];
    }
    
    protected function getIgnoredFieldsForDelete() {
        $fields = parent::getIgnoredFieldsForDelete();
        $fields = ArrayHelper::merge(
            $fields,
            ['country_id', 'region_id', 'city_id', 'district_id']
        );
        return $fields;
    } 
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOwner() {
        return $this->hasOne(Client::className(), ['id' => 'client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry0() {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion() {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity() {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDistrict0() {
        return $this->hasOne(District::className(), ['id' => 'district_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreateUser() {
        return $this->hasOne(User::className(), ['id' => 'create_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateUser() {
        return $this->hasOne(User::className(), ['id' => 'update_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfiles() {
        return $this->hasMany(FSMProfile::className(), ['address_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes() {
        //return $this->hasMany(Route::className(), ['base_address_id' => 'id']);
        return [];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCargo() {
        //return $this->hasMany(Cargo::className(), ['b_address_id' => 'id']);
        return [];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies() {
        return $this->hasMany(Client::className(), ['address_id' => 'id']);
    }

    public function getAddressText() {
        $result = [];
        
        if(!empty($this->route)){
            $result[] = 
                (!empty($this->apartment_number) ? $this->apartment_number.', ' : '').
                $this->route . 
                (!empty($this->street_number) ? ' - '.$this->street_number : '');
        }
        $district = !empty($this->district) ? 
                $this->district : 
                (!empty($this->political) ? 
                    $this->political : 
                    (!empty($this->sublocality_level_1) ? 
                        $this->sublocality_level_1 : 
                        $this->sublocality    
                    )
                );
        if(!empty($district)){
            $result[] = $district;
        }
        if(!empty($this->locality)){
            $result[] = $this->locality;
        }
        if(!empty($this->administrative_area_level_1)){
            $result[] = $this->administrative_area_level_1;
        }   
        if(!empty($this->postal_code)){
            $result[] = $this->postal_code;
        }        
        if(!empty($this->country)){
            $result[] = $this->country;
        }        
        $result = implode(', ', $result);
        if(!empty($result)){
            return $result;
        }
        if(!empty($this->formated_address)){
            return $this->formated_address;
        }elseif(!empty($this->customer_address)){
            return $this->customer_address;
        }
    }
                
    public function getAddressTypeList() {
        return [
            'start' => Yii::t('address', 'Start'),
            'finish' =>  Yii::t('address', 'Finish'),
            'interim' =>  Yii::t('address', 'Interim'),
            'user' =>  Yii::t('address', 'User'),
            'company' =>  Yii::t('address', 'Company'),
            'custom' =>  Yii::t('address', 'Customs'),
        ];
    }
    
    public function beforeValidate() {
        /*
        $dataToSave = Yii::$app->request->post();
        if(!empty($dataToSave) && empty($this->company_name)){
            $error = Yii::t('cargo', 'Need to define one of those: weight, volume or LDM. Or describe your shipment in the comments.');
            $this->addError('company_name', $error);
            return false;
        } 
         * 
         */       

        return parent::beforeValidate();
    }
    
    public function beforeSave($insert) {
        if(!parent::beforeSave($insert)){
            return false;
        }
        
        $dataToSave = Yii::$app->request->post();
        
        if(empty($dataToSave)){
            return true;
        }

        $gMapLocation = !empty($this->gmapLocationFieldSet) && isset($dataToSave[$this->gmapLocationFieldSet]) ? 
            $dataToSave[$this->gmapLocationFieldSet] :
            (!empty($dataToSave['address-customer_address']) ? $dataToSave['address-customer_address'] : null);
        
        $this->customer_address = !empty($this->customer_address) ? $this->customer_address : $this->addressText;
        if(empty($this->customer_address)){
            $this->addError('customer_address', 
                    Yii::t('user', 
                        'The address cannot be empty. '.
                        'Type address into the text field and select postal address from Google list or select location on the map')
            );
            $message = 'Address not updated due to validation error.';
            Yii::error($message, __METHOD__);
            Yii::$app->getSession()->setFlash('error', Yii::t('user', $message));
            return false;
        }
        
        //$gMapLocation['customer_address'] = $this->customer_address;
        //$gMapLocation['contact_person'] = isset($this->contact_person) ? $this->contact_person : null;
        //$gMapLocation['contact_phone'] = isset($this->contact_phone) ? $this->contact_phone : null;
        //$gMapLocation['contact_email'] = isset($this->contact_email) ? $this->contact_email : null;
        
        if(empty($gMapLocation['formated_address'])){
            $gMapLocation = ArrayHelper::merge($gMapLocation, $this->attributes);
        }else{
            $gMapLocation = ArrayHelper::merge($this->attributes, $gMapLocation);
        }
                
        $gMapLocation['latitude'] = isset($gMapLocation['latitude']) ? doubleval($gMapLocation['latitude']) : null;
        $gMapLocation['longitude'] = isset($gMapLocation['longitude']) ? doubleval($gMapLocation['longitude']) : null;
        
        $address = $this->getLocationData($gMapLocation);
        $result = true;
        if(!empty($address)){
            if(isset($address['address_state'])){
                if($address['address_state'] == 'error'){
                    $this->addError('location', $address['message']);
                    return false;
                }elseif($address['address_state'] == 'saved') {
                    return true;
                }
            }else{
                foreach ($address as $field => $value){
                    if($result = $this->hasAttribute($field)){
                        $this->$field = $value;
                    }else{
                        $this->addError($field, 'Field "'.$field.'" not exists!');
                        return false;
                    }
                }
                $this->formated_address = $this->addressText;
            }
        }        
        return $result;
    }
    
    public function saveDataFromGMap($data) {
        $addressData = $this->getLocationData($data);
        if (!empty($addressData)) {
            if (isset($addressData['address_state']) && ($addressData['address_state'] == 'error')) {
                Yii::$app->getSession()->setFlash('error', $addressData['message']);
                Yii::error($addressData['message'], __METHOD__);
                return [
                    'address_id' => null,
                    'address_state' => 'error',
                    'message' => $addressData['message'],
                ];
            }elseif(isset($addressData['address_state']) && ($addressData['address_state'] == 'saved')){
                return $addressData;
            }
        }

        $this->setAttributes($addressData);
        if (!$this->save(false)) {
            if ($this->hasErrors()) {
                $message = [];
                foreach ($this->getErrors() as $attribute) {
                    foreach ($attribute as $error) {
                        $message[] = $error;
                    }
                }
                $message = implode(PHP_EOL, $message);
            } else {
                $message = Yii::t('location', $this->modelTitle() . ' not inserted due to validation error.');
            }
            Yii::$app->getSession()->setFlash('error', $message);
            Yii::error($message, __METHOD__);
            return [
                'address_id' => null,
                'address_state' => 'error',
                'message' => $message,
            ];
        } else {
            return [
                'address_id' => $this->id,
                'address_state' => 'saved',
            ];
        }
    }

    public function getLocationData($data) {
        $addressData = $this->getAttributes(null, [
            'create_time',
            'create_user_id',
            'update_time',
            'update_user_id'
            ]
        );
        $addressData['country_short_name'] = isset($data['country_short_name']) ? $data['country_short_name'] : null;

        //$result = array_diff($data, $addressData);
        $result = [];
        foreach ($data as $key => $value) {
            if(array_key_exists($key, $addressData) && ($addressData[$key]) != $value){
                $result[$key] = $value;
            }
        }
        if (empty($result) && isset($this->id)) {
            return [
                'address_id' => $this->id,
                'address_state' => 'saved',
            ];
        }

        $addressData = ArrayHelper::merge($addressData, $data);

        //----------------------------------------------------------------------
        $addressObj = new Country;
        if(!empty($data['country_short_name'])){
            $idCountry = $addressObj->getIdByGMapData(
                [
                'name' => $data['country'],
                'short_name' => $data['country_short_name'],
                ], 
                'short_name'
            );
        }else{
            $idCountry = $addressObj->getIdByGMapData(
                [
                'name' => $data['country'],
                ] 
            );
        }
        if (is_array($idCountry) && !empty($idCountry['message'])) {
            return $idCountry;
        } else {
            $addressData['country_id'] = $idCountry;
        }
        //----------------------------------------------------------------------
        $addressObj = new Region;
        $idRegion = $addressObj->getIdByGMapData(
            [
                'country_id' => $idCountry,
                'name' => $data['administrative_area_level_1'],
            ]
        );
        if (is_array($idRegion) && !empty($idRegion['message'])) {
            return $idRegion;
        } else {
            $addressData['region_id'] = $idRegion;
        }
        //----------------------------------------------------------------------
        $addressObj = new City;
        $idCity = $addressObj->getIdByGMapData(
            [
                'country_id' => $idCountry,
                'region_id' => $idRegion,
                'name' => $data['locality'],
            ]
        );
        if (is_array($idCity) && !empty($idCity['message'])) {
            return $idCity;
        } else {
            $addressData['city_id'] = $idCity;
        }
        //----------------------------------------------------------------------
        $addressObj = new District;
        $idDistrict = $addressObj->getIdByGMapData(
            [
                'country_id' => $idCountry,
                'region_id' => $idRegion,
                'city_id' => $idCity,
                'name' => !empty($data['district']) ? 
                    $data['district'] : 
                    (!empty($data['political']) ? 
                        $data['political'] : 
                        (!empty($data['sublocality_level_1']) ? 
                            $data['sublocality_level_1'] : 
                            $data['sublocality']
                        )
                    ),
            ]
        );
        if (is_array($idDistrict) && !empty($idDistrict['message'])) {
            return $idDistrict;
        } else {
            $addressData['district_id'] = $idDistrict;
        }
        
        unset($addressObj, $addressData['country_short_name']);

        return $addressData;
    }

    public function delete() {
        $result = parent::delete();
        if (!$result) {
            Yii::$app->getSession()->setFlash('error', Yii::t('user', 'Cant`t delete address'));
        }
        return $result;
    }

    static public function getPostalCodeList($search, $limit = null) {
        Yii::$app->db->createCommand("SET sql_mode = ''")->execute();
        $query = self::find()
            ->where(['LIKE', 'postal_code', $search])
            ->groupBy('postal_code')
            ->orderBy(['postal_code' => SORT_ASC, 'district' => SORT_DESC]);
        if(!empty($limit)){
            $query->limit($limit);
        }
        $result = $query->asArray()->all();
        return $result;
    }
    
    static public function getRouteNameList($search, array $args = null, $limit = null) {
        Yii::$app->db->createCommand("SET sql_mode = ''")->execute();
        
        $query = self::find()->where(['LIKE', 'route', $search]);
        if(!empty($args)){
            $query->andWhere($args);
        }
        if(!empty($limit)){
            $query->limit($limit);
        }
        $data = $query
                ->groupBy('route')
                ->orderBy('route')
                ->asArray()
                ->all();
        $result = ArrayHelper::map($data, 'id', 'route');
        return $result;        
    }     
    
}
