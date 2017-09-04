<?php

namespace common\models\bill;

use Yii;
use yii\helpers\ArrayHelper;

use common\models\bill\PaymentOrder;
use common\models\bill\HistoryBill;

use common\components\FSMHelper;

/**
 * This is the model class for table "bill_payment".
 *
 * @property integer $id
 * @property intgere $history_bill_id
 * @property intgere $payment_order_id
 * @property integer $bill_id
 * @property string $summa
 * @property integer $confirmed
 *
 * @property Bill $bill
 * @property HistoryBill $historyBill
 * @property PaymentOrder $paymentOrder
 */
class BillPayment extends \common\models\bill\HistoryBill
{

    protected $_externalFields = [
        'bill_number',
    ]; 
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bill_payment';
    }
    
    public function attributes() { 
        $parentClass = get_parent_class($this);
        $parent = new $parentClass;
        $attributes = $parent->attributes();
        $attributes = ArrayHelper::merge(
            $attributes,
            [
                'history_bill_id',
                'payment_order_id',
                'summa',
                'confirmed',
            ]
        );
        return $attributes; 
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = ArrayHelper::merge(
            $rules, 
            [
                [['history_bill_id', 'payment_order_id', 'bill_id', 'summa'], 'required'],
                [['history_bill_id', 'payment_order_id', 'bill_id', 'confirmed'], 'integer'],
                [['summa'], 'number'],
                [['history_bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => HistoryBill::className(), 'targetAttribute' => ['history_bill_id' => 'id']],
                [['payment_order_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentOrder::className(), 'targetAttribute' => ['payment_order_id' => 'id']],
                [['bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bill::className(), 'targetAttribute' => ['bill_id' => 'id']],
            ]
        );
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('bill', 'Invoice payment|Invoice payments', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge(
            $labels, 
            [
                'id' => Yii::t('common', 'ID'),
                'history_bill_id' => Yii::t('bill', 'Invoice history ID'),
                'payment_order_id' => Yii::t('bill', 'Prepared payment'),
                'bill_id' => Yii::t('bill', 'Invoice number'),
                'summa' => Yii::t('bill', 'Summa'),
                'confirmed' => Yii::t('bill', 'Confirmed'),
            
                'bill_number' => Yii::t('bill', 'Invoice number'),
            ]
        );
        return $labels;
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
    public function getHistoryBill()
    {
        return $this->hasOne(HistoryBill::className(), ['id' => 'history_bill_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentOrder()
    {
        return $this->hasOne(PaymentOrder::className(), ['id' => 'payment_order_id']);
    }
    
    static function getButtonUpdate($url, $model, $key)
    {
        return 
            FSMHelper::vButton($model->id, [
            'title' => Yii::t('kvgrid', 'Update'),
            'controller' => 'bill-payment',
            'action' => 'update',
            'class' => 'primary',
            'size' => 'btn-xs',
            'icon' => 'pencil',
            'modal' => true,
        ]);        
    }    
}
