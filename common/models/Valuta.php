<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "valuta".
 *
 * @property integer $id
 * @property string $name
 *
 * @property CargoMoney[] $cargoMoneys
 */
class Valuta extends \common\models\mainclass\FSMBaseModel
{
    const VALUTA_DEFAULT = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'valuta';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 20],
            [['name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('common', 'Currency|Currency', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'name' => Yii::t('common', 'Name'),
        ];
    }

}
