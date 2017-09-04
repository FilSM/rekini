<?php

namespace common\models\client;

use Yii;

use common\models\FileXML;
use common\models\FilePDF;
use common\models\bill\PaymentConfirm;

/**
 * This is the model class for table "client_bank".
 *
 * @property integer $id
 * @property integer $payment_confirm_id
 * @property integer $account_id
 * @property string $start_date
 * @property string $end_date
 * @property integer $uploaded_file_id
 * @property integer $uploaded_pdf_file_id
 * @property string $balance
 * @property string $currency
 *
 * @property PaymentConfirm $paymentConfirm
 * @property ClientBank $account
 * @property FileXML $uploadedFile
 * @property FilePDF $uploadedPdfFile
 */
class ClientBankBalance extends \common\models\mainclass\FSMBaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'client_bank_balance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_id', 'start_date', 'end_date', 'balance', 'currency'], 'required'],
            [['account_id'], 'integer'],
            [['currency'], 'string', 'max' => 3],
            [['balance'], 'number'],
            [['payment_confirm_id'], 'exist', 'skipOnError' => true, 'targetClass' => PaymentConfirm::className(), 'targetAttribute' => ['payment_confirm_id' => 'id']],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientBank::className(), 'targetAttribute' => ['account_id' => 'id']],
            [['uploaded_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => FileXML::className(), 'targetAttribute' => ['uploaded_file_id' => 'id']],
            [['uploaded_pdf_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilePDF::className(), 'targetAttribute' => ['uploaded_pdf_file_id' => 'id']],
            [['account_id', 'start_date', 'end_date'], 'unique', 'targetAttribute' => ['account_id', 'start_date', 'end_date'], 'message' => 'The combination of Account, Start date and End date has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('client', 'Bank account balance|Bank account balances', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'payment_confirm_id' => Yii::t('bill', 'Payment confirmation'),
            'account_id' => Yii::t('client', 'Account'),
            'start_date' => Yii::t('bill', 'Start date'),
            'end_date' => Yii::t('bill', 'End date'),
            'uploaded_file_id' => Yii::t('client', 'XML file'),
            'uploaded_pdf_file_id' => Yii::t('client', 'PDF file'),
            'balance' => Yii::t('client', 'Balance'),
            'currency' => Yii::t('common', 'Currency'),
        ];
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
    public function getAccount()
    {
        return $this->hasOne(ClientBank::className(), ['id' => 'account_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedFile()
    {
        return $this->hasOne(FileXML::className(), ['id' => 'uploaded_file_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedPDFFile()
    {
        return $this->hasOne(FilePDF::className(), ['id' => 'uploaded_pdf_file_id']);
    }
}
