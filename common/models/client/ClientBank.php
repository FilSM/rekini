<?php

namespace common\models\client;

use Yii;
use common\models\Bank;
use common\models\FileXML;
use common\models\FilePDF;

/**
 * This is the model class for table "client_bank".
 *
 * @property integer $id
 * @property integer $deleted
 * @property integer $client_id
 * @property integer $bank_id
 * @property string $account
 * @property string $name
 * @property integer $uploaded_file_id
 * @property integer $uploaded_pdf_file_id
 * @property string $balance
 * @property string $currency
 *
 * @property Bank $bank
 * @property Client $client
 * @property ClientBankBalance $balances
 * @property FileXML $uploadedFile
 * @property FilePDF $uploadedPdfFile
 */
class ClientBank extends \common\models\mainclass\FSMBaseModel
{

    protected $_externalFields = [
        'client_name',
        'bank_name',
        'swift',
        'home_page',
        'file_name_xml',
        'file_name_pdf',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'client_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'bank_id', 'deleted'], 'integer'],
            [['client_id', 'bank_id', 'account'], 'required'],
            [['account'], 'string', 'max' => 34],
            [['name'], 'string', 'max' => 64],
            [['currency'], 'string', 'max' => 3],
            [['balance'], 'number'],
            [['bank_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bank::className(), 'targetAttribute' => ['bank_id' => 'id']],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['uploaded_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => FileXML::className(), 'targetAttribute' => ['uploaded_file_id' => 'id']],
            [['uploaded_pdf_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilePDF::className(), 'targetAttribute' => ['uploaded_pdf_file_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('client', 'Client bank|Client banks', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'client_id' => Yii::t('client', 'Client'),
            'bank_id' => Yii::t('bill', 'Bank'),
            'account' => Yii::t('client', 'Account'),
            'name' => Yii::t('common', 'Account name'),
            'deleted' => Yii::t('common', 'Deleted'),
            'uploaded_file_id' => Yii::t('client', 'XML file'),
            'uploaded_pdf_file_id' => Yii::t('client', 'PDF file'),
            'balance' => Yii::t('client', 'Balance'),
            'currency' => Yii::t('common', 'Currency'),
            
            'client_name' => Yii::t('client', 'Client'),
            'bank_name' => Yii::t('client', 'Bank name'),
            'swift' => Yii::t('client', 'SWIFT code'),
            'home_page' => Yii::t('bank', 'WWW'),
            'file_name_xml' => Yii::t('bill', 'XML file name'),
            'file_name_pdf' => Yii::t('bill', 'PDF file name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBank()
    {
        return $this->hasOne(Bank::className(), ['id' => 'bank_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'client_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBalances()
    {
        return $this->hasMany(ClientBankBalance::className(), ['account_id' => 'id']);
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
    public function getUploadedPdfFile()
    {
        return $this->hasOne(FilePDF::className(), ['id' => 'uploaded_pdf_file_id']);
    }    
}
