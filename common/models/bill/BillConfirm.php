<?php

namespace common\models\bill;

use Yii;

use common\components\FSMHelper;
use common\models\client\Client;
use common\models\bill\Bill;
use common\models\bill\BillPayment;
use common\models\bill\HistoryBill;
use common\models\bill\PaymentConfirm;

/**
 * This is the model class for table "bill_confirm".
 *
 * @property integer $id
 * @property integer $payment_confirm_id
 * @property integer $history_bill_id
 * @property integer $bill_payment_id
 * @property integer $bill_id
 * @property string $first_client_account
 * @property string $second_client_name
 * @property string $second_client_reg_number
 * @property string $second_client_account
 * @property integer $second_client_id
 * @property string $doc_date
 * @property string $doc_number
 * @property string $bank_ref
 * @property string $direction
 * @property string $summa
 * @property string $currency
 * @property string $comment
 *
 * @property Bill $bill
 * @property BillPayment $billPayment
 * @property Client $firstClient
 * @property HistoryBill $historyBill
 * @property PaymentConfirm $paymentConfirm
 * @property Client $secondClient
 */
class BillConfirm extends \common\models\mainclass\FSMBaseModel
{

    const DIRECTION_DEBET = 'D';
    const DIRECTION_CREDIT = 'C';

    protected $_externalFields = [
        'bill_number',
    ]; 
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bill_confirm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_confirm_id', 'first_client_account', 'doc_date', 'bank_ref', 'summa', 'currency'], 'required'],
            [['id', 'payment_confirm_id', 'history_bill_id', 'bill_payment_id', 'bill_id', 'second_client_id'], 'integer'],
            [['doc_date'], 'safe'],
            [['direction', 'comment'], 'string'],
            [['summa'], 'number'],
            [['second_client_name'], 'string', 'max' => 100],
            [['second_client_reg_number'], 'string', 'max' => 30],
            [['first_client_account', 'second_client_account'], 'string', 'max' => 34],
            [['doc_number', 'bank_ref'], 'string', 'max' => 35],
            [['currency'], 'string', 'max' => 3],
            [['id'], 'unique'],
            [['payment_confirm_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentConfirm::className(), 'targetAttribute' => ['payment_confirm_id' => 'id']],
            [['bill_payment_id'], 'exist', 'skipOnError' => true, 'targetClass' => BillPayment::className(), 'targetAttribute' => ['bill_payment_id' => 'id']],
            [['bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bill::className(), 'targetAttribute' => ['bill_id' => 'id']],
            [['history_bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => HistoryBill::className(), 'targetAttribute' => ['history_bill_id' => 'id']],
            [['second_client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['second_client_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('bill', 'BillConfirm|Bill Confirms', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'payment_confirm_id' => Yii::t('bill', 'Payment confirmation'),
            'history_bill_id' => Yii::t('bill', 'History ID'),
            'bill_payment_id' => Yii::t('bill', 'Invoice payment'),
            'bill_id' => Yii::t('bill', 'Invoice number'),
            'first_client_account' => Yii::t('bill', 'Client account'),
            'second_client_name' => Yii::t('bill', 'Second client name'),
            'second_client_reg_number' => Yii::t('bill', 'Second client reg.number'),
            'second_client_account' => Yii::t('bill', 'Second client account'),
            'second_client_id' => Yii::t('bill', 'Second client'),
            'doc_date' => Yii::t('bill', 'Doc.date'),
            'doc_number' => Yii::t('bill', 'Doc.number'),
            'bank_ref' => Yii::t('bill', 'Bank reference'),
            'direction' => Yii::t('bill', 'Direction'),
            'summa' => Yii::t('bill', 'Summa'),
            'currency' => Yii::t('common', 'Currency'),
            'comment' => Yii::t('bill', 'Comment'),
            
            'bill_number' => Yii::t('bill', 'Invoice number'),
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
    public function getBillPayment()
    {
        return $this->hasOne(BillPayment::className(), ['id' => 'bill_payment_id']);
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
    public function getPaymentConfirm()
    {
        return $this->hasOne(PaymentConfirm::className(), ['id' => 'payment_confirm_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSecondClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'second_client_id']);
    }
    
    static public function getDirectionList() {
        return [
            BillConfirm::DIRECTION_DEBET => Yii::t('bill', 'Debet'),
            BillConfirm::DIRECTION_CREDIT => Yii::t('bill', 'Credit'),
        ];
    }   
    
    static function getButtonUpdate($url, $model, $key)
    {
        return 
            FSMHelper::vButton($model->id, [
            'title' => Yii::t('kvgrid', 'Update'),
            'controller' => 'bill-confirm',
            'action' => 'update',
            'class' => 'primary',
            'size' => 'btn-xs',
            'icon' => 'pencil',
            'modal' => true,
        ]);        
    }    
}