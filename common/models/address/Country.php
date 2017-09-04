<?php

namespace common\models\address;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "country".
 *
 * @property integer $id
 * @property string $name
 * @property string $short_name
 * @property string $currency
 *
 * @property Address[] $addresses
 * @property City[] $cities
 * @property District[] $districts
 * @property Region[] $regions
 * @property Route[] $routes
 */
class Country extends LocationBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'location_country';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
            [['short_name'], 'string', 'max' => 2],
            [['currency'], 'string', 'max' => 30],
            [['name', 'short_name'], 'unique', 'targetAttribute' => ['name', 'short_name'], 'message' => 'The combination of Name and Short name has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('location', 'Country|Countries', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'name' => Yii::t('common', 'Name'),
            'short_name' => Yii::t('location', 'Short Name'),
            'currency' => Yii::t('common', 'Currency'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses()
    {
        return $this->hasMany(Address::className(), ['country_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCities()
    {
        return $this->hasMany(City::className(), ['country_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDistricts()
    {
        return $this->hasMany(District::className(), ['country_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegions()
    {
        return $this->hasMany(Region::className(), ['country_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes()
    {
        return $this->hasMany(Route::className(), ['delivery_country_to' => 'id']);
    }

}
