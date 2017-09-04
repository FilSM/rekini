<?php

namespace common\models\address;

use Yii;

/**
 * This is the model class for table "region".
 *
 * @property integer $id
 * @property integer $country_id
 * @property string $name
 *
 * @property Address[] $addresses
 * @property City[] $cities
 * @property District[] $districts
 * @property Country $country
 * @property Route[] $routes
 */
class Region extends LocationBaseModel
{
    public $_externalFields = [
        'country_name',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'location_region';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['country_id', 'name'], 'required'],
            [['country_id'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['name', 'country_id'], 'unique', 'targetAttribute' => ['name', 'country_id'], 'message' => 'The combination of Country ID and Name has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('location', 'Region|Regions', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'country_id' => Country::modelTitle(),
            'name' => Yii::t('common', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasMany(Address::className(), ['region_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCities()
    {
        return $this->hasMany(City::className(), ['region_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDistricts()
    {
        return $this->hasMany(District::className(), ['region_id' => 'id']);
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
    public function getRoutes()
    {
        return $this->hasMany(Route::className(), ['delivery_region_to' => 'id']);
    }

}
