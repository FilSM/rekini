<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "bank".
 *
 * @property integer $id
 * @property string $name
 * @property string $reg_number
 * @property string $code
 * @property string $address
 * @property string $home_page
 * @property integer $enabled
 *
 * @property Client[] $companies
 */
class Bank extends \common\models\mainclass\FSMBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'reg_number', 'swift', 'address'], 'required'],
            [['enabled'], 'integer'],
            [['reg_number', 'address', 'home_page'], 'string'],
            [['reg_number'], 'string', 'max' => 30],
            [['home_page'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 100],
            [['swift'], 'string', 'max' => 11],
            [['name', 'swift'], 'unique', 'targetAttribute' => ['name', 'swift'], 'message' => 'The combination of Name and Code has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('bank', 'Bank|Banks', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'name' => Yii::t('common', 'Name'),
            'swift' => Yii::t('bank', 'SWIFT code'),
            'address' => Yii::t('address', 'Address'),
            'home_page' => Yii::t('bank', 'WWW'),
            'enabled' => Yii::t('common', 'Enabled'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanies()
    {
        return $this->hasMany(Client::className(), ['bank_id' => 'id']);
    }

}
