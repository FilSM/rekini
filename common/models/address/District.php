<?php

namespace common\models\address;

use Yii;

/**
 * This is the model class for table "district".
 *
 * @property integer $id
 * @property integer $country_id
 * @property integer $region_id
 * @property integer $city_id
 * @property string $name
 *
 * @property Address[] $addresses
 * @property Country $country
 * @property Region $region
 * @property City $city
 * @property Route[] $routes
 */
class District extends LocationBaseModel
{
    public $_externalFields = [
        'country_name',
        'region_name',
        'city_name',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'location_district';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id', 'city_id', 'name'], 'required'],
            [['country_id', 'region_id', 'city_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['name', 'region_id', 'country_id', 'city_id'], 'unique', 'targetAttribute' => ['name', 'region_id', 'country_id', 'city_id'], 'message' => 'The combination of Country ID, Region ID, City ID and Name has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('location', 'District|Districts', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'country_id' => Country::modelTitle(),
            'region_id' => Region::modelTitle(),
            'city_id' => City::modelTitle(),
            'name' => Yii::t('common', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasMany(Address::className(), ['district_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegion()
    {
        return $this->hasOne(Region::className(), ['id' => 'region_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCity()
    {
        return $this->hasOne(City::className(), ['id' => 'city_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes()
    {
        return $this->hasMany(Route::className(), ['delivery_micro_to' => 'id']);
    }

}
