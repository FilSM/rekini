<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "product".
 *
 * @property integer $id
 * @property string $name
 * @property integer $measure_id
 * @property string $article
 * @property string $description
 *
 * @property Measure $measure
 */
class Product extends \common\models\mainclass\FSMBaseModel
{
    public $_externalFields = [
        'measure_name',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'measure_id'], 'required'],
            [['measure_id'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['article'], 'string', 'max' => 10],
            [['description'], 'string', 'max' => 255],
            [['name', 'article'], 'unique', 'targetAttribute' => ['name', 'article'], 'message' => 'The combination of Name and Article has already been taken.'],
            [['measure_id'], 'exist', 'skipOnError' => true, 'targetClass' => Measure::className(), 'targetAttribute' => ['measure_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('product', 'Product|Products', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'name' => Yii::t('common', 'Name'),
            'measure_id' => Yii::t('product', 'Measure'),
            'article' => Yii::t('product', 'Article'),
            'description' => Yii::t('product', 'Description'),
            
            'measure_name' => Yii::t('measure', 'Measure'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMeasure()
    {
        return $this->hasOne(Measure::className(), ['id' => 'measure_id']);
    }
    
    static public function getNameArr($where = null, $orderBy = 'name', $idField = 'id', $nameField = 'name')
    {
        if(isset($where)){
            $arr = self::findByCondition($where)->orderBy($orderBy)->asArray()->all();
        }else{
            $arr = self::find()->orderBy($orderBy)->asArray()->all();
        }        
        
        $result = [];
        foreach ($arr as $row) {
            $result[$row['id']] = (!empty($row['article']) ? $row['article'].' | ' : '').$row['name'];
        }
        return $result;
    }    
}