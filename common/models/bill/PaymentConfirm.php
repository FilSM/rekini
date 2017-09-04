<?php

namespace common\models\bill;

use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

use common\components\fidavista\FSMFidavistaStatement;
use omj\financetools\statementstandart\iAccountStatementDbController;

use common\models\user\FSMUser;
use common\models\user\FSMProfile;
use common\models\Bank;
use common\models\FileXML;
use common\models\FilePDF;
use common\models\client\Client;
use common\models\client\ClientBankBalance;
use common\models\bill\Bill;
use common\models\bill\BillConfirm;
use common\components\FSMHelper;

/**
 * This is the model class for table "payment_confirm".
 *
 * @property integer $id
 * @property integer $bank_id
 * @property string $client_name
 * @property string $client_reg_number
 * @property integer $client_id
 * @property string $name
 * @property string $start_date
 * @property string $end_date
 * @property string $pay_date
 * @property string $status
 * @property integer $uploaded_file_id
 * @property integer $uploaded_pdf_file_id
 * @property string $comment
 * @property string $action_time
 * @property integer $action_user_id
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $update_time
 * @property integer $update_user_id
 *
 * @property Bank $bank
 * @property Client $client
 * @property Bill[] $bills
 * @property BillConfirm[] $billConfirms
 * @property FileXML $uploadedFile
 * @property FilePDF $uploadedPdfFile
 * @property FSMUser $actionUser
 * @property FSMUser $createUser
 * @property FSMUser $updateUser
 * @property FSMProfile $actionUserProfile
 */
class PaymentConfirm extends \common\models\mainclass\FSMCreateUpdateModel
{

    const IMPORT_STATE_PROCESS = 'process';
    const IMPORT_STATE_COMPLETE = 'complete';

    protected $_externalFields = [
        'bank_name',
        'file_name_xml',
        'file_name_pdf',
        'user_name',
    ];

    public function init() {
        parent::init();
        $this->cascadeDeleting = true;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payment_confirm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bank_id', 'client_id', 'name', 'start_date', 'end_date', 'pay_date'], 'required'],
            [['bank_id', 'client_id', 'uploaded_file_id', 'uploaded_pdf_file_id', 
                'action_user_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['start_date', 'end_date', 'pay_date', 'action_time', 'create_time', 'update_time'], 'safe'],
            [['client_name', 'client_reg_number', 'status', 'comment'], 'string'],
            [['name'], 'string', 'max' => 100],
            [['uploaded_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => FileXML::className(), 'targetAttribute' => ['uploaded_file_id' => 'id']],
            [['uploaded_pdf_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => FilePDF::className(), 'targetAttribute' => ['uploaded_pdf_file_id' => 'id']],
            [['bank_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bank::className(), 'targetAttribute' => ['bank_id' => 'id']],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['action_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['action_user_id' => 'id']],
            [['create_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['create_user_id' => 'id']],
            [['update_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['update_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('bill', 'Payment confirmation |Payment confirmations', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'bank_id' => Yii::t('bill', 'Bank'),
            'client_name' => Yii::t('bill', 'Client name'),
            'client_reg_number' => Yii::t('bill', 'Client reg.number'),
            'client_id' => Yii::t('bill', 'Client name'),
            'name' => Yii::t('bill', 'Name'),
            'start_date' => Yii::t('bill', 'Start date'),
            'end_date' => Yii::t('bill', 'End date'),
            'pay_date' => Yii::t('bill', 'Preparation date'),
            'status' => Yii::t('bill', 'Status'),
            'uploaded_file_id' => Yii::t('bill', 'XML file name'),
            'uploaded_pdf_file_id' => Yii::t('bill', 'PDF file name'),
            'comment' => Yii::t('bill', 'Comment'),
            'action_time' => Yii::t('bill', 'Import time'),
            'action_user_id' => Yii::t('bill', 'Performer'),
            'create_time' => Yii::t('bill', 'Create Time'),
            'create_user_id' => Yii::t('bill', 'Create User'),
            'update_time' => Yii::t('bill', 'Update Time'),
            'update_user_id' => Yii::t('bill', 'Update User'),
            
            'file_name_xml' => Yii::t('bill', 'XML file name'),
            'file_name_pdf' => Yii::t('bill', 'PDF file name'),
            'user_name' => Yii::t('bill', 'Performer'),
            'bank_name' => Yii::t('bill', 'Bank'),
        ];
    }
    
    protected function getIgnoredFieldsForDelete() {
        $fields = parent::getIgnoredFieldsForDelete();
        $fields = ArrayHelper::merge(
            $fields, ['bank_id', 'client_id', 'action_user_id']
        );
        return $fields;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActionUser()
    {
        return $this->hasOne(FSMUser::className(), ['id' => 'action_user_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getActionUserProfile()
    {
        return $this->hasOne(FSMProfile::className(), ['user_id' => 'action_user_id']);
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
    public function getClientBankBalances()
    {
        return $this->hasMany(ClientBankBalance::className(), ['payment_confirm_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBillConfirms()
    {
        return $this->hasMany(BillConfirm::className(), ['payment_confirm_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBills()
    {
        $result = $this->hasMany(Bill::className(), ['id' => 'bill_id'])->viaTable('bill_confirm', ['payment_confirm_id' => 'id']);
        $result->where(['deleted' => 0]);
        return $result;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreateUser()
    {
        return $this->hasOne(FSMUser::className(), ['id' => 'create_user_id']);
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateUser()
    {
        return $this->hasOne(FSMUser::className(), ['id' => 'update_user_id']);
    }

    static public function getImportStateList()
    {
        return [
            PaymentConfirm::IMPORT_STATE_PROCESS => Yii::t('bill', 'In process'),
            PaymentConfirm::IMPORT_STATE_COMPLETE => Yii::t('bill', 'Complete'),
        ];
    }

    public function getOptionsButtons($btnSize = '', $labeled = false)
    {
        if (!empty($btnSize)) {
            $btnSize = 'btn-' . $btnSize;
        }

        $result = [];
        $result[] = PaymentConfirm::getButtonImport(['model' => $this, 'btnSize' => $btnSize, 'labeled' => $labeled, 'isBtn' => true]);
        foreach ($result as $key => $btn) {
            if (empty($btn)) {
                unset($result[$key]);
            }
        }

        $result = implode('&nbsp;', $result);
        return $result;
    }

    static function getButtonImport(array $params)
    {
        extract($params); //'model', 'btnSize', 'labeled', 'isBtn'
        $result = '';
        if (in_array($model->status, [
                    PaymentConfirm::IMPORT_STATE_PROCESS,
                ])) {
            $label = Yii::t('common', 'Import');
            $title = Yii::t('common', 'Import');
            if ($isBtn) {
                $result = FSMHelper::aButton($model->id, [
                            'label' => (!empty($labeled) ? $label : null),
                            'title' => (!empty($labeled) ? null : $title),
                            'controller' => 'payment-confirm',
                            'action' => 'import',
                            //'class' => 'primary',
                            'size' => !empty($btnSize) ? $btnSize : null,
                            'icon' => 'import',
                            'modal' => true,
                ]);
            } else {
                $result = FSMHelper::aDropdown($model->id, [
                            'label' => $label,
                            'title' => $title,
                            'controller' => 'payment-confirm',
                            'action' => 'import',
                            'icon' => 'import',
                            'modal' => true,
                ]);
            }
        }
        return $result;
    }

    public function parseImportXML($fileModel)
    {
        $result = false;
        $xmlString = file_get_contents($fileModel->filepath);
        if (!empty($xmlString) && FSMFidavistaStatement::isFidaVista($xmlString)) {
            $dbController = new FidavistaDbController();
            // Into original FSMFidavistaStatement class need to change privat variables to public
            $fidavista = new FSMFidavistaStatement($xmlString, $dbController);
            if(!empty($dbController->transactData)){
                $bank = Bank::findOne(['reg_number' => $fidavista->bankId, 'enabled' => true]);
                if(!$bank){
                    throw new Exception(Yii::t('bill', 'Cannot find bank by its reg.number!'));
                }
                $client = Client::findOne(['reg_number' => $fidavista->beneficiaryId, 'deleted' => false]);
                if(!$client){
                    throw new Exception(Yii::t('bill', 'Cannot find client by its reg.number!'));
                }

                $this->bank_id = !empty($bank) ? $bank->id : null;
                $this->client_id = !empty($client) ? $client->id : null;
                $this->client_name = $fidavista->beneficiaryName;
                $this->client_reg_number = $fidavista->beneficiaryId;
                $this->start_date = $fidavista->startDate;
                $this->end_date = $fidavista->endDate;
                $this->pay_date = $fidavista->prepDate;
                $this->comment = empty($this->comment) ? $fidavista->from : $this->comment."\n".$fidavista->from;
                
                $result = $dbController->transactData;
            }
        }

        return $result;
    }

}

class FidavistaDbController implements iAccountStatementDbController
{
    public $transactData;

    public function executeDataImport(){
        
    }
    
    public function executeDataImportIncoming(){
        
    }
    
    public function setData($data){
        $this->transactData = $data;
    }

}
