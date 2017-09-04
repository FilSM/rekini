<?php

namespace common\models\bill;

use Yii;

use common\models\Product;
use common\models\Measure;

/**
 * This is the model class for table "bill_product".
 *
 * @property integer $id
 * @property integer $deleted
 * @property integer $bill_id
 * @property integer $product_id
 * @property string $product_name
 * @property integer $measure_id
 * @property string $amount
 * @property string $price
 * @property string $vat
 * @property integer $revers
 * @property string $summa
 * @property string $summa_vat
 * @property string $total
 *
 * @property Bill $bill
 * @property Product $product
 * @property Measure $measure
 */
class BillProduct extends \common\models\mainclass\FSMBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bill_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bill_id'], 'required'],
            [['id', 'deleted', 'bill_id', 'product_id', 'measure_id', 'revers'], 'integer'],
            [['amount', 'price', 'vat', 'summa', 'summa_vat', 'total'], 'number'],
            [['product_name'], 'string', 'max' => 100],
            [['bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bill::className(), 'targetAttribute' => ['bill_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::className(), 'targetAttribute' => ['product_id' => 'id']],
            [['measure_id'], 'exist', 'skipOnError' => true, 'targetClass' => Measure::className(), 'targetAttribute' => ['measure_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('bill', 'Invoice product|Invoice products', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'deleted' => Yii::t('common', 'Deleted'),
            'bill_id' => Yii::t('bill', 'Invoice'),
            'product_id' => Yii::t('bill', 'Product'),
            'product_name' => Yii::t('bill', 'Product'),
            'measure_id' => Yii::t('product', 'Measure'),
            'amount' => Yii::t('bill', 'Amount'),
            'price' => Yii::t('bill', 'Price'),
            'vat' => Yii::t('bill', 'Vat %'),
            'revers' => Yii::t('bill', 'Revers'),
            'summa' => Yii::t('bill', 'Summa'),
            'summa_vat' => Yii::t('bill', 'Vat'),
            'total' => Yii::t('bill', 'Total'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBill()
    {
        return $this->hasOne(Bill::className(), ['id' => 'bill_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }
    
    public function getProductName()
    {
        return !empty($this->product_name) ? $this->product_name : $this->product->name;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMeasure()
    {
        return $this->hasOne(Measure::className(), ['id' => 'measure_id']);
    }    
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMeasureName()
    {
        return !empty($this->measure_id) ? ($this->measure ? $this->measure->name : '') : (isset($this->product, $this->product->measure) ? $this->product->measure->name : '');
    }    
}