<?php

namespace common\models\bill;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use kartik\helpers\Html;

use common\models\abonent\Abonent;
use common\models\client\Client;
use common\models\client\Project;
use common\models\client\Agreement;
use common\models\client\ClientBank;
use common\models\client\ClientRole;
use common\models\client\ClientContact;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;
use common\models\Valuta;
use common\models\Language;

use common\printDocs\PrintModule;
use common\components\FSMHelper;

/**
 * This is the model class for table "bill".
 *
 * @property integer $id
 * @property integer $version
 * @property integer $deleted
 * @property integer $abonent_id
 * @property integer $project_id
 * @property integer $agreement_id
 * @property integer $parent_id
 * @property integer $child_id
 * @property string $doc_type
 * @property string $doc_number
 * @property string $doc_date
 * @property string $pay_date
 * @property string $paid_date
 * @property string $complete_date
 * @property string $status
 * @property string $pay_status
 * @property integer $delayed
 * @property integer $first_client_bank_id
 * @property integer $second_client_bank_id
 * @property integer $first_client_person_id
 * @property integer $second_client_person_id
 * @property integer $according_contract
 * @property string $summa
 * @property string $vat
 * @property string $total
 * @property integer $valuta_id
 * @property integer $manager_id
 * @property integer $language_id
 * @property string $services_period
 * @property string $doc_key
 * @property string $loading_address
 * @property string $unloading_address
 * @property string $carrier
 * @property string $transport
 * @property string $comment
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $update_time
 * @property integer $update_user_id
 *
 * @property Abonent $abonent
 * @property Project $project
 * @property Agreement $agreement
 * @property Client $client
 * @property Client $secondClient
 * @property Bill $parent
 * @property Bill[] $parents
 * @property Bill[] $child
 * @property FSMProfile $manager
 * @property ClientBank $firstClientBank
 * @property ClientBank $secondClientBank
 * @property ClientContact $firstClientPerson
 * @property ClientContact $secondClientPerson
 * @property Valuta $valuta
 * @property FSMUser $createUser
 * @property FSMUser $updateUser
 * @property BillPayment[] $billPayments
 * @property BillConfirm[] $billConfirms
 * @property HistoryBill[] $historyBills
 */
class Bill extends \common\models\mainclass\FSMVersionModel
{
    const BILL_DEFAULT_PAYMENT_DAYS = 10;
    const BILL_DEFAULT_PRINT_LANGUAGE = 'en';

    const BILL_DOC_TYPE_BILL = 'bill';
    const BILL_DOC_TYPE_AVANS = 'avans';
    const BILL_DOC_TYPE_CRBILL = 'cr_bill';
    const BILL_DOC_TYPE_INVOICE = 'invoice';
    const BILL_DOC_TYPE_DEBT = 'debt';
    const BILL_DOC_TYPE_CESSION = 'cession';
    
    const BILL_STATUS_PREPAR = 'prepar';
    const BILL_STATUS_NEW = 'new';
    const BILL_STATUS_READY = 'ready';
    const BILL_STATUS_SIGNED = 'signed';
    const BILL_STATUS_PREP_PAYMENT = 'prepar_payment';
    const BILL_STATUS_PAYMENT = 'payment';
    const BILL_STATUS_PAID = 'paid';
    const BILL_STATUS_COMPLETE = 'complete';
    const BILL_STATUS_CANCELED = 'canceled';
    
    const BILL_PAY_STATUS_NOT = 'not';
    const BILL_PAY_STATUS_PART = 'part';
    const BILL_PAY_STATUS_FULL = 'full';
    const BILL_PAY_STATUS_OVER = 'over';
    const BILL_PAY_STATUS_DELAYED = 'delayed';

    protected $_externalFields = [
        'abonent_name',
        'project_name',
        'agreement_number',
        'first_client_id',
        'first_client_name',
        'first_client_role_name',
        'second_client_id',
        'second_client_name',
        'second_client_role_name',
        'third_client_id',
        'third_client_name',
        'third_client_role_name',
        'manager_name',
        'manager_user_id',
        
        'project_id',
        'project_sales',
        'project_purchases',
        'project_profit',
        
        'client_id',
        'client_name',
        'client_sales',
        'client_vat_plus',
        'client_purchases',
        'client_vat_minus',
        'client_vat_result',
        
        'debtors_summa',
        'creditors_summa',
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
        return 'bill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[/*'abonent_id', */'project_id',  'agreement_id', 'doc_number', 'doc_date', 
                'pay_date', 'first_client_bank_id', 'second_client_bank_id',
                'summa', 'vat', 'total', 'valuta_id', 'manager_id'], 'required'],
            [['id', 'version', 'deleted', 'abonent_id',  'project_id', 'agreement_id', 
                'parent_id', 'child_id', 'delayed', 'first_client_bank_id', 'second_client_bank_id', 
                'first_client_person_id', 'second_client_person_id', 'according_contract',
                'valuta_id', 'manager_id', 'language_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['doc_type', 'status', 'pay_status', 'doc_key',
                'loading_address', 'unloading_address', 'carrier', 'transport',
                'services_period', 'comment', 'cession_direction'], 'string'],
            [['doc_date', 'pay_date', 'paid_date', 'complete_date', 'create_time', 'update_time'], 'safe'],
            [['summa', 'vat', 'total'], 'number'],
            [['doc_number', 'transport'], 'string', 'max' => 20],
            [['carrier', 'services_period'], 'string', 'max' => 50],
            [['loading_address', 'unloading_address'], 'string', 'max' => 100],
            [['doc_number', 'deleted'], 'unique', 'targetAttribute' => ['doc_number', 'deleted'], 'message' => 'The combination of Doc.number and Deleted has already been taken.'],
            [['abonent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Abonent::className(), 'targetAttribute' => ['abonent_id' => 'id']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bill::className(), 'targetAttribute' => ['parent_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['project_id' => 'id']],
            [['agreement_id'], 'exist', 'skipOnError' => true, 'targetClass' => Agreement::className(), 'targetAttribute' => ['agreement_id' => 'id']],
            [['first_client_bank_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientBank::className(), 'targetAttribute' => ['first_client_bank_id' => 'id']],
            [['second_client_bank_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientBank::className(), 'targetAttribute' => ['second_client_bank_id' => 'id']],
            [['first_client_person_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientContact::className(), 'targetAttribute' => ['first_client_person_id' => 'id']],
            [['second_client_person_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientContact::className(), 'targetAttribute' => ['second_client_person_id' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMProfile::className(), 'targetAttribute' => ['manager_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['language_id' => 'id']],
            [['valuta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Valuta::className(), 'targetAttribute' => ['valuta_id' => 'id']],
            [['create_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['create_user_id' => 'id']],
            [['update_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['update_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('bill', 'Invoice|Invoices', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'version' => Yii::t('bill', 'Version'),
            'deleted' => Yii::t('common', 'Deleted'),
            'project_id' => Yii::t('bill', 'Project'),
            'agreement_id' => Yii::t('bill', 'Agreement'),
            'parent_id' => Yii::t('bill', '"Parent"'),
            'child_id' => Yii::t('bill', '"Child"'),
            'abonent_id' => Yii::t('bill', 'Abonent'),
            'doc_type' => Yii::t('bill', 'Doc.type'),
            'doc_number' => Yii::t('bill', 'Doc.number'),
            'doc_date' => Yii::t('bill', 'Doc.date'),
            'pay_date' => Yii::t('bill', 'Due date'),
            'paid_date' => Yii::t('bill', 'Paid'),
            'complete_date' => Yii::t('bill', 'Final payment date'),
            'first_client_bank_id' => Yii::t('bill', 'First party bank account'),
            'second_client_bank_id' => Yii::t('bill', 'Second party bank account'),
            'first_client_person_id' => Yii::t('bill', 'First party signing person'),
            'second_client_person_id' => Yii::t('bill', 'Second party signing person'),
            'status' => Yii::t('bill', 'Status'),
            'pay_status' => Yii::t('bill', 'Payment status'),
            'delayed' => Yii::t('bill', 'Delayed'),
            'according_contract' => Yii::t('bill', 'According contract'),
            'summa' => Yii::t('bill', 'Summa'),
            'vat' => Yii::t('bill', 'Vat'),
            'total' => Yii::t('bill', 'Total'),
            'valuta_id' => Yii::t('common', 'Currenсy'),
            'manager_id' => Yii::t('bill', 'Author'),
            'language_id' => Yii::t('common', 'Print language'),
            'services_period' => Yii::t('bill', 'Service period'),
            'loading_address' => Yii::t('bill', 'Loading address'),
            'unloading_address' => Yii::t('bill', 'Unloading address'),
            'carrier' => Yii::t('bill', 'Carrier'),
            'transport' => Yii::t('bill', 'Transport'),
            'comment' => Yii::t('common', 'Comment'),
            'create_time' => Yii::t('common', 'Create Time'),
            'create_user_id' => Yii::t('common', 'Create User'),
            'update_time' => Yii::t('common', 'Update Time'),
            'update_user_id' => Yii::t('common', 'Update User'),
            
            'abonent_name' => Yii::t('bill', 'Abonent'),
            'project_name' => Yii::t('bill', 'Project'),
            'agreement_number' => Yii::t('bill', 'Agreement'),
            'first_client_name' => Yii::t('bill', 'First party'),
            'second_client_name' => Yii::t('bill', 'Second party'),
            'manager_name' => Yii::t('client', 'Our support'),
            
            'project_id' => Yii::t('bill', 'Project'),
            'project_sales' => Yii::t('report', 'Sales'),
            'project_purchases' => Yii::t('report', 'Purchases'),
            'project_profit' => Yii::t('report', 'Profit or Loss'),
            
            'client_id' => Yii::t('client', 'Client'),
            'client_name' => Yii::t('client', 'Client'),
            'client_sales' => Yii::t('report', 'Sales'),
            'client_vat_plus' => Yii::t('report', 'VAT+'),
            'client_purchases' => Yii::t('report', 'Purchases'),
            'client_vat_minus' => Yii::t('report', 'VAT-'),
            'client_vat_result' => Yii::t('report', 'VAT result'),
            
            'debtors_summa' => Yii::t('report', 'Debtors'),
            'creditors_summa' => Yii::t('report', 'Creditors'),
        ];
    }
    
    protected function getIgnoredFieldsForDelete() {
        $fields = parent::getIgnoredFieldsForDelete();
        $fields = ArrayHelper::merge(
            $fields, ['abonent_id', 'agreement_id', 'manager_id', 'language_id', 
                'first_client_bank_id', 'second_client_bank_id', 
                'first_client_person_id', 'second_client_person_id',
                'parent_id', 'project_id', 'valuta_id']
        );
        return $fields;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAbonent()
    {
        return $this->hasOne(Abonent::className(), ['id' => 'abonent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgreement()
    {
        return $this->hasOne(Agreement::className(), ['id' => 'agreement_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthor()
    {
        return $this->hasOne(FSMProfile::className(), ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(Language::className(), ['id' => 'language_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirstClientBank()
    {
        return $this->hasOne(ClientBank::className(), ['id' => 'first_client_bank_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSecondClientBank()
    {
        return $this->hasOne(ClientBank::className(), ['id' => 'second_client_bank_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirstClientPerson()
    {
        return $this->hasOne(ClientContact::className(), ['id' => 'first_client_person_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSecondClientPerson()
    {
        return $this->hasOne(ClientContact::className(), ['id' => 'second_client_person_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirstClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'first_client_id'])->viaTable('agreement', ['id' => 'agreement_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSecondClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'second_client_id'])->viaTable('agreement', ['id' => 'agreement_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirstClientRole()
    {
        return $this->hasOne(ClientRole::className(), ['id' => 'first_client_role_id'])->viaTable('agreement', ['id' => 'agreement_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSecondClientRole()
    {
        return $this->hasOne(ClientRole::className(), ['id' => 'second_client_role_id'])->viaTable('agreement', ['id' => 'agreement_id']);
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
    public function getParent()
    {
        return $this->hasOne(Bill::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        $result = $this->hasMany(Bill::className(), ['child_id' => 'id']);
        $result->where(['deleted' => 0]);
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChild()
    {
        return $this->hasOne(Bill::className(), ['id' => 'child_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChilds()
    {
        $result = $this->hasMany(Bill::className(), ['parent_id' => 'id']);
        $result->where(['deleted' => 0]);
        return $result;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBillProducts() {
        $result = $this->hasMany(BillProduct::className(), ['bill_id' => 'id']);
        $result->where(['deleted' => 0]);
        return $result;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBillConfirms() {
        return $this->hasMany(BillConfirm::className(), ['bill_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateUser()
    {
        return $this->hasOne(FSMUser::className(), ['id' => 'update_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getValuta()
    {
        return $this->hasOne(Valuta::className(), ['id' => 'valuta_id']);
    }     

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBillPayments()
    {
        return $this->hasMany(BillPayment::className(), ['bill_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getHistoryBills()
    {
        return $this->hasMany(HistoryBill::className(), ['bill_id' => 'id']);
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParentPaymentsSumma()
    {
        $summa = 0;
        $parentList = $this->parents;
        foreach ($parentList as $parent) {
            $summa += $parent->paymentsSumma;
        }
        return $summa;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentsSumma()
    {
        $summa = 0;
        $parentList = $this->parents;
        foreach ($parentList as $parent) {
            $summa += $parent->paymentsSumma;
        }
        $paymentList = $this->billPayments;
        foreach ($paymentList as $payment){
            $summa += $payment->summa;
        }
        return $summa;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentsSummaGrate()
    {
        return $this->paymentsSumma - $this->total;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentsSummaLess()
    {
        return $this->total - $this->paymentsSumma;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentsSummaIsGrate()
    {
        return $this->total < $this->paymentsSumma;
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentsSummaIsLess()
    {
        return $this->total > $this->paymentsSumma;
    }
        
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentsSummaIsEqual()
    {
        return $this->total = $this->paymentsSumma;
    }
    
    static public function getBillDocTypeList() {
        /*
        return [
            Bill::BILL_DOC_TYPE_AVANS => Yii::t('bill', 'Prepayment'),
            Bill::BILL_DOC_TYPE_BILL => Yii::t('bill', 'Invoice'),
            Bill::BILL_DOC_TYPE_CRBILL => Yii::t('bill', 'Credit invoice'),
            Bill::BILL_DOC_TYPE_INVOICE => Yii::t('bill', 'Waybill'),
            Bill::BILL_DOC_TYPE_DEBT => Yii::t('bill', 'Debt relief'),
            Bill::BILL_DOC_TYPE_CESSION => Yii::t('bill', 'Cession'),
        ];
         * 
         */
        return \lajax\translatemanager\helpers\Language::a([
            Bill::BILL_DOC_TYPE_AVANS => 'Proforma',
            Bill::BILL_DOC_TYPE_BILL => 'Invoice',
            Bill::BILL_DOC_TYPE_CRBILL => 'Credit invoice',
            Bill::BILL_DOC_TYPE_INVOICE => 'Waybill',
            Bill::BILL_DOC_TYPE_DEBT => 'Debt relief',
            Bill::BILL_DOC_TYPE_CESSION => 'Cession',
        ]);
    }     
    
    static public function getBillStatusList() {
        return [
            Bill::BILL_STATUS_PREPAR => Yii::t('bill', 'Preparation'),
            Bill::BILL_STATUS_NEW => Yii::t('bill', 'New'),
            Bill::BILL_STATUS_READY => Yii::t('bill', 'Ready'),
            Bill::BILL_STATUS_SIGNED => Yii::t('bill', 'Signed'),
            Bill::BILL_STATUS_PREP_PAYMENT => Yii::t('bill', 'Payment prep.'),
            Bill::BILL_STATUS_PAYMENT => Yii::t('bill', 'Payment'),
            Bill::BILL_STATUS_PAID => Yii::t('bill', 'Paid'),
            Bill::BILL_STATUS_COMPLETE => Yii::t('bill', 'Complete'),
            Bill::BILL_STATUS_CANCELED => Yii::t('bill', 'Canceled'),
        ];
    }     
    
    static public function getBillPayStatusList() {
        return [
            Bill::BILL_PAY_STATUS_NOT => Yii::t('bill', 'Not paid'),
            Bill::BILL_PAY_STATUS_PART => Yii::t('bill', 'Partially paid'),
            Bill::BILL_PAY_STATUS_FULL => Yii::t('bill', 'Full paid'),
            Bill::BILL_PAY_STATUS_OVER => Yii::t('bill', 'Overpaid'),
            Bill::BILL_PAY_STATUS_DELAYED => Yii::t('bill', 'Delayed'),
        ];
    } 

    public function getStatusBackgroundColor() {
        return $this->getStaticStatusBackgroundColor($this->status);
    }
    
    static public function getStaticStatusBackgroundColor($status = null) {
        $status = !empty($status) ? $status : null;
        switch ($status) {
            case Bill::BILL_STATUS_PREPAR:
            case Bill::BILL_STATUS_CANCELED:
                $class = 'badge-default';
                break;
            case Bill::BILL_STATUS_NEW:  
                $class = 'badge-info';
                break;
            case Bill::BILL_STATUS_READY:
                $class = 'badge-primary';
                break;
            case Bill::BILL_STATUS_SIGNED:
                $class = 'badge-danger';
                break;
            case Bill::BILL_STATUS_PREP_PAYMENT:
                $class = 'badge-warning';
                break;
            case Bill::BILL_STATUS_PAYMENT:
                $class = 'badge-warning';
                break;
            case Bill::BILL_STATUS_PAID:
            case Bill::BILL_STATUS_COMPLETE:
                $class = 'badge-success';
                break;
            default: 
                $class = 'badge-default';
                break;
        }  
        return $class;
    }    

    public function getPayStatusBackgroundColor() {
        return $this->getStaticPayStatusBackgroundColor($this->pay_status);
    }
    
    static public function getStaticPayStatusBackgroundColor($status = null) {
        $status = !empty($status) ? $status : null;
        switch ($status) {
            case Bill::BILL_PAY_STATUS_NOT:
                $class = 'badge-default';
                break;
            case Bill::BILL_PAY_STATUS_PART:
                $class = 'badge-warning';
                break;
            case Bill::BILL_PAY_STATUS_FULL:
                $class = 'badge-success';
                break;
            case Bill::BILL_PAY_STATUS_OVER:  
                $class = 'badge-info';
                break;
            default: 
                $class = 'badge-default';
                break;
        }  
        return $class;
    }  
    
    public function createPdf() {
        // length of hash to generate, up to the output length of the hash function used
        $length = 32;
        $today = date("m.d.y H:i:s");
        $docKey = substr(hash('md5', $this->id.'-'.$today), 0, $length); // Hash it
        $agreement = $this->agreement;
        if(!$agreement){
            return false;
        }
        
        $firstClient = ($this->doc_type != Bill::BILL_DOC_TYPE_CESSION) ? $agreement->firstClient : ($this->cession_direction == 'D' ? $agreement->firstClient : $agreement->thirdClient);
        $secondClient = ($this->doc_type != Bill::BILL_DOC_TYPE_CESSION) ? $agreement->secondClient : ($this->cession_direction == 'D' ? $agreement->thirdClient : $agreement->secondClient);
        
        $parentList = $this->parents;
        $avansNumberList = '';
        $avansSumma = 0;
        if(!empty($parentList)){
            $avansSumma = $this->parentPaymentsSumma;
            $avansNumberList = [];
            foreach ($parentList as $bill) {
                $avansNumberList[] = $bill->doc_number;
            }
            $avansNumberList = ((count($avansNumberList) > 1) ? '## ' : '# ').implode(', ', $avansNumberList);
        }
        $data = [
            'doc-type' => $this->doc_type,
            'template-dir' => 'invoice',
            'clientId' => $firstClient->id,
            //'mode' => 'FI',
            'mode' => 'F',
            'doc-key' => $docKey,
            'agreement' => $agreement,
            'invoice' => $this,
            'billProducts' => $this->billProducts,
            'firstClient' => $firstClient,
            'secondClient' => $secondClient,
            'firstClientBank' => $this->firstClientBank,
            'secondClientBank' => $this->secondClientBank,
            'firstClientPerson' => $this->firstClientPerson,
            'secondClientPerson' => $this->secondClientPerson,
            'firstClientAddress' => $firstClient->legal_address,
            'secondClientAddress' => $secondClient->legal_address,
            'avansNumberList' => $avansNumberList,
            'avansSumma' => $avansSumma,
        ];
                
        $printModule = new PrintModule($data);
        $pdfFilename = $printModule->printDoc();
        unset($printModule);
        
        $result = $pdfFilename && (bool)$this->updateAttributes([
            'doc_key' => $docKey
        ]);
        
        if(in_array($data['mode'], ['I', 'FI'])){
            exit;
        }
        
        return $result;
    }    

    public function changeStatus($status, $params = []) {
        $transaction = Yii::$app->getDb()->beginTransaction(); 
        try {        
            $arrForUpdate = ['status' => $status];
            $arrForUpdate = ArrayHelper::merge($arrForUpdate, $params);
            
            switch ($status) {
                case Bill::BILL_STATUS_PREPAR:
                case Bill::BILL_STATUS_NEW:
                case Bill::BILL_STATUS_READY:
                case Bill::BILL_STATUS_CANCELED:
                    $arrForUpdate['pay_status'] = null;
                    break;
                case Bill::BILL_STATUS_SIGNED:
                    $arrForUpdate['pay_status'] = Bill::BILL_PAY_STATUS_NOT;
                    break;
                case Bill::BILL_STATUS_PAYMENT:
                case Bill::BILL_STATUS_PAID:
                    $arrForUpdate['pay_status'] = ($this->paymentsSummaIsLess ? Bill::BILL_PAY_STATUS_PART : ($this->paymentsSummaIsGrate ? Bill::BILL_PAY_STATUS_OVER :Bill::BILL_PAY_STATUS_FULL));
                    $arrForUpdate['delayed'] = (int)(($this->pay_date < date('Y-m-d')) && $this->paymentsSummaIsLess);
                    if(($status == Bill::BILL_STATUS_PAID) && in_array($arrForUpdate['pay_status'], [Bill::BILL_PAY_STATUS_FULL, Bill::BILL_PAY_STATUS_OVER])){
                        $arrForUpdate['status'] = Bill::BILL_STATUS_COMPLETE;
                        $arrForUpdate['complete_date'] = date('Y-m-d');
                        $historyModule = new HistoryBill();
                        $historyModule->saveHistory($this->id, HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_COMPLETE'], Bill::BILL_STATUS_COMPLETE);
                        unset($historyModule);
                    }
                    if(empty($arrForUpdate['paid_date'])){
                        $arrForUpdate['paid_date'] = date('Y-m-d');
                    }
                    break;
                case Bill::BILL_STATUS_COMPLETE:
                    $arrForUpdate['pay_status'] = Bill::BILL_PAY_STATUS_FULL;
                    $arrForUpdate['delayed'] = 0;
                    $arrForUpdate['complete_date'] = date('Y-m-d');
                    if(empty($arrForUpdate['paid_date'])){
                        $arrForUpdate['paid_date'] = date('Y-m-d');
                    }
                    break;
                default:
                    break;
            }
            
            $this->updateAttributes($arrForUpdate);
            $transaction->commit();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('error', $message);
            Yii::error($message, __METHOD__);
        }        
        return true;
    }    
    
    public function getProgressButtons($btnSize = '', $labeled = false)
    {
        if(!empty($btnSize)){
            $btnSize = 'btn-'.$btnSize;
        }
        
        switch ($this->status) {
            case Bill::BILL_STATUS_PREPAR:
                return 
                    FSMHelper::aButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'Register') : null),
                        'title' => Yii::t('bill', 'Register'),
                        'controller' => 'history-bill',
                        'action' => 'bill-register',
                        'class' => 'info',
                        'size' => $btnSize,
                        'icon' => 'arrow-up',
                        //'modal' => true,
                    ]);
                break;
            case Bill::BILL_STATUS_NEW:
                return 
                    FSMHelper::vButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'To preparing') : null),
                        'title' => Yii::t('bill', 'Rollback to preparing'),
                        'controller' => 'history-bill',
                        'action' => 'bill-rollback-prepar',
                        'class' => 'default',
                        'size' => $btnSize,
                        'icon' => 'arrow-down',
                        'modal' => true,
                    ]).'&nbsp;'.
                    FSMHelper::aButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'To signature') : null),
                        'title' => Yii::t('bill', 'Send for signature'),
                        'controller' => 'history-bill',
                        'action' => 'bill-send-signature',
                        'class' => 'primary',
                        'size' => $btnSize,
                        'icon' => 'arrow-up',
                        //'modal' => true,
                    ]);
                break;
            case Bill::BILL_STATUS_READY:
                return 
                    FSMHelper::vButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'To verification') : null),
                        'title' => Yii::t('bill', 'Rollback to verification'),
                        'controller' => 'history-bill',
                        'action' => 'bill-rollback-new',
                        'class' => 'info',
                        'size' => $btnSize,
                        'icon' => 'arrow-down',
                        'modal' => true,
                    ]).'&nbsp;'.
                    FSMHelper::aButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'Sign') : null),
                        'title' => Yii::t('bill', 'Sign'),
                        'controller' => 'history-bill',
                        'action' => 'bill-sign',
                        'class' => 'danger',
                        'size' => $btnSize,
                        'icon' => 'arrow-up',
                        //'modal' => true,
                    ]);
                break;
            case Bill::BILL_STATUS_SIGNED:
                return 
                    FSMHelper::vButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'To signing') : null),
                        'title' => Yii::t('bill', 'Rollback to signing'),
                        'controller' => 'history-bill',
                        'action' => 'bill-rollback-ready',
                        'class' => 'primary',
                        'size' => $btnSize,
                        'icon' => 'arrow-down',
                        'modal' => true,
                    ]).'&nbsp;'.
                    FSMHelper::aButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'Prepare payment') : null),
                        'title' => Yii::t('bill', 'Prepare payment'),
                        'controller' => 'history-bill',
                        'action' => 'bill-prep-payment',
                        'class' => 'warning',
                        'size' => $btnSize,
                        'icon' => 'arrow-up',
                        'modal' => true,
                    ]);
                break;
            case Bill::BILL_STATUS_PREP_PAYMENT:
                return 
                    FSMHelper::vButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'To verification') : null),
                        'title' => Yii::t('bill', 'Rollback to verification'),
                        'controller' => 'history-bill',
                        'action' => 'bill-rollback-signed',
                        'class' => 'danger ',
                        'size' => $btnSize,
                        'icon' => 'arrow-down',
                        'modal' => true,
                    ]).'&nbsp;'.
                    FSMHelper::vButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'Pay now') : null),
                        'title' => Yii::t('bill', 'Pay now'),
                        'controller' => 'bill-payment',
                        'action' => 'ajax-create',
                        'class' => 'warning',
                        'size' => $btnSize,
                        'icon' => 'arrow-up',
                        'modal' => true,
                    ]);
                break;
            case Bill::BILL_STATUS_PAYMENT:
                return 
                    FSMHelper::vButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'To payment preparation') : null),
                        'title' => Yii::t('bill', 'Rollback to payment preparation'),
                        'controller' => 'history-bill',
                        'action' => 'bill-rollback-prep-payment',
                        'class' => 'warning',
                        'size' => $btnSize,
                        'icon' => 'arrow-down',
                        'modal' => true,
                    ]).'&nbsp;'.
                    FSMHelper::aButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'Confirm payment') : null),
                        'title' => Yii::t('bill', 'Confirm payment'),
                        'controller' => 'history-bill',
                        'action' => 'bill-pay',
                        'class' => 'success',
                        'size' => $btnSize,
                        'icon' => 'arrow-up',
                        //'modal' => true,
                    ]);
                break;
            case Bill::BILL_STATUS_PAID:
                if(!in_array($this->pay_status, [Bill::BILL_PAY_STATUS_FULL, Bill::BILL_PAY_STATUS_OVER])) {
                    return 
                        FSMHelper::vButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'To payment') : null),
                        'title' => Yii::t('bill', 'Rollback to payment'),
                        'controller' => 'history-bill',
                        'action' => 'bill-rollback-payment',
                        'class' => 'warning',
                        'size' => $btnSize,
                        'icon' => 'arrow-down',
                        'modal' => true,
                    ]).'&nbsp;'.
                    FSMHelper::aButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'Confirm payment') : null),
                        'title' => Yii::t('bill', 'Confirm payment'),
                        'controller' => 'history-bill',
                        'action' => 'bill-pay',
                        'class' => 'success',
                        'size' => $btnSize,
                        'icon' => 'arrow-up',
                        //'modal' => true,
                    ]);
                }else{
                    return FSMHelper::aButton($this->id, [
                        'label' => ($labeled ? Yii::t('bill', 'Complete') : null),
                        'title' => Yii::t('bill', 'Complete invoice'),
                        'controller' => 'history-bill',
                        'action' => 'bill-complete',
                        'class' => 'success',
                        'size' => $btnSize,
                        'icon' => 'arrow-up',
                        //'modal' => true,
                    ]);
                }
                break;
            default:
                return '';
                break;
        }
    }
    
    static function getButtonPrint(array $params)
    {
        extract($params); //$url, $model, $key, $isBtn = true
        
        if($disabled = !in_array($model->doc_type, [
            Bill::BILL_DOC_TYPE_AVANS,
            Bill::BILL_DOC_TYPE_BILL,
            Bill::BILL_DOC_TYPE_INVOICE,
            Bill::BILL_DOC_TYPE_CRBILL,
            Bill::BILL_DOC_TYPE_DEBT,
            Bill::BILL_DOC_TYPE_CESSION,
        ])){
            return '';
        }

        $disabled = false;
        $url = $disabled ? '#' : ['view-pdf', 'id' => $model->id];
        if($isBtn){
            return Html::a(Html::icon('print'), $url, [
                'class' => 'btn btn-xs '.($disabled ? 'btn-danger disabled': 'btn-success'),
                'title' => Yii::t('common', 'Print'),
                'target' => !$disabled ? '_blank' : null,
                'data-pjax' =>  !$disabled ? 0 : null,
                'disabled' => $disabled,
            ]);
        }else{
            $label = Html::icon('print') . '&nbsp;' . Yii::t('common', 'Print');
            $options = [
                'class' => $disabled ? 'disabled' : null,
                'target' => !$disabled ? '_blank' : null,
                'disabled' => $disabled,
            ];
            return '<li>' . Html::a($label, ($disabled ? '#' : $url), $options) . '</li>' . PHP_EOL;
        }
    }
        
    static function getButtonWriteOnBasis(array $params)
    {
        extract($params); //'model', 'btnSize', 'labeled', 'isBtn'
        $result = '';
        if(($model->doc_type == Bill::BILL_DOC_TYPE_AVANS) && empty($model->child_id)){
            if($isBtn){
                $result = FSMHelper::aButton($model->id, [
                    'title' => Yii::t('bill', 'Write out on this basis'),
                    'icon' => 'share',
                    'action' => 'bill-write-on-basis',
                    'size' => $btnSize,
                ]);
            }else{
                $result = 
                    FSMHelper::aDropdown($model->id, [
                    'label' => Yii::t('bill', 'Write out on this basis'),
                    'title' => Yii::t('bill', 'Write out on this basis'),
                    'action' => 'bill-write-on-basis',
                    'icon' => 'share',
                ]);
            }
        }
        return $result;
    }
    
    static function getButtonCreatePayment(array $params)
    {
        extract($params); //'model', 'btnSize', 'labeled', 'isBtn'
        $result = '';
        $childs = $model->childs;
        $childIsCI = false;
        foreach ($childs as $child){
            $childIsCI = $childIsCI || ($child->doc_type == Bill::BILL_DOC_TYPE_CRBILL);
        }        
        if(in_array($model->doc_type, [
                Bill::BILL_DOC_TYPE_AVANS, 
                Bill::BILL_DOC_TYPE_BILL,
                Bill::BILL_DOC_TYPE_CESSION,
            ]) &&
            in_array($model->status, [
                Bill::BILL_STATUS_PREP_PAYMENT, 
                Bill::BILL_STATUS_PAYMENT, 
                Bill::BILL_STATUS_PAID,
            ]) &&
            !$childIsCI &&
            $model->paymentsSummaIsLess){
            
            if($isBtn){
                $result = 
                    FSMHelper::vButton($model->id, [
                    'title' => ($labeled ? null : Yii::t('bill', 'Pay now')),
                    'controller' => 'bill-payment',
                    'action' => 'ajax-create',
                    'class' => 'primary',
                    'size' => $btnSize,
                    'icon' => 'eur',
                    'modal' => true,
                ]);
            }else{
                $result = 
                    FSMHelper::vDropdown($model->id, [
                    'label' => Yii::t('bill', 'Pay now'),
                    'title' => Yii::t('bill', 'Pay now'),
                    'controller' => 'bill-payment',
                    'action' => 'ajax-create',
                    'icon' => 'eur',
                    'modal' => true,
                ]);
            }
        }
        return $result;
    }
    
    static function getButtonCreditInvoice(array $params)
    {
        extract($params); //'model', 'btnSize', 'labeled', 'isBtn'
        $result = '';
        $childs = $model->childs;
        $childIsCI = false;
        foreach ($childs as $child){
            $childIsCI = $childIsCI || ($child->doc_type == Bill::BILL_DOC_TYPE_CRBILL);
        }
        if(in_array($model->doc_type, [
                Bill::BILL_DOC_TYPE_BILL,
                Bill::BILL_DOC_TYPE_CESSION,
            ]) &&
            in_array($model->status, [
                Bill::BILL_STATUS_SIGNED, 
                Bill::BILL_STATUS_PREP_PAYMENT, 
                Bill::BILL_STATUS_PAYMENT, 
                Bill::BILL_STATUS_PAID,
            ]) &&
            !$childIsCI &&
            $model->paymentsSummaIsLess){
            
            if($isBtn){
                $result = FSMHelper::aButton($model->id, [
                    'title' => Yii::t('bill', 'Credit invoice'),
                    'icon' => 'duplicate',
                    'action' => 'bill-credit-invoice',                                        
                    'size' => $btnSize,
                ]);
            }else{
                $result = 
                    FSMHelper::aDropdown($model->id, [
                    'label' => Yii::t('bill', 'Credit invoice'),
                    'title' => Yii::t('bill', 'Credit invoice'),
                    'action' => 'bill-credit-invoice',
                    'icon' => 'duplicate',
                ]);
            }
        }
        return $result;
    }
    
    static function getButtonMutualSettlement(array $params)
    {
        extract($params); //'model', 'btnSize', 'labeled', 'isBtn'
        $result = '';
        $childs = $model->childs;
        $childIsCI = false;
        foreach ($childs as $child){
            $childIsCI = $childIsCI || ($child->doc_type == Bill::BILL_DOC_TYPE_CRBILL);
        }        
        if(in_array($model->doc_type, [
                Bill::BILL_DOC_TYPE_BILL,
                Bill::BILL_DOC_TYPE_CESSION,
            ]) &&
            in_array($model->status, [
                Bill::BILL_STATUS_SIGNED, 
                Bill::BILL_STATUS_PREP_PAYMENT, 
                Bill::BILL_STATUS_PAYMENT, 
                Bill::BILL_STATUS_PAID,
            ]) &&
            !$childIsCI &&
            $model->paymentsSummaIsLess ){
            
            if($isBtn){
                $result = FSMHelper::aButton($model->id, [
                    'title' => Yii::t('bill', 'Mutual settlement'),
                    'icon' => 'transfer',
                    'action' => '#',                                        
                    //'action' => 'bill-mutual-settlement',                                        
                    'size' => $btnSize,
                ]);
            }else{
                $result = 
                    FSMHelper::aDropdown($model->id, [
                    'label' => Yii::t('bill', 'Mutual settlement'),
                    'title' => Yii::t('bill', 'Mutual settlement'),
                    'action' => '#',                                        
                    //'action' => 'bill-mutual-settlement',                                        
                    'icon' => 'transfer',
                ]);
            }
        }
        return $result;
    }
    
    static function getButtonCession(array $params)
    {
        extract($params); //'model', 'btnSize', 'labeled', 'isBtn'
        $result = '';
        $childs = $model->childs;
        $childIsCI = false;
        foreach ($childs as $child){
            $childIsCI = $childIsCI || ($child->doc_type == Bill::BILL_DOC_TYPE_CRBILL);
        }        
        if(in_array($model->doc_type, [
                Bill::BILL_DOC_TYPE_BILL,
                Bill::BILL_DOC_TYPE_CESSION,
            ]) &&
            in_array($model->status, [
                Bill::BILL_STATUS_SIGNED, 
                Bill::BILL_STATUS_PREP_PAYMENT, 
                Bill::BILL_STATUS_PAYMENT, 
                Bill::BILL_STATUS_PAID,
            ]) &&
            !$childIsCI &&
            $model->paymentsSummaIsLess){
            
            $agreementList = Agreement::findAll([
                'first_client_id' => $model->firstClient->id, 
                'second_client_id' => $model->secondClient->id, 
                'agreement_type' => Agreement::AGREEMENT_TYPE_CESSION,
                'status' => Agreement::AGREEMENT_STATUS_SIGNED,
                'deleted' => 0]);
            if(empty($agreementList)){
                return ''; 
            }
            if($isBtn){
                $result = FSMHelper::aButton($model->id, [
                    'title' => Yii::t('bill', 'Cession'),
                    'action' => 'bill-cession',                                        
                    'icon' => 'random',
                    'size' => $btnSize,
                ]);
            }else{
                $result = 
                    FSMHelper::aDropdown($model->id, [
                    'label' => Yii::t('bill', 'Cession'),
                    'title' => Yii::t('bill', 'Cession'),
                    'action' => 'bill-cession',                                        
                    'icon' => 'random',
                ]);
            }
        }
        return $result;
    }
    
    static function getButtonDebtRelief(array $params)
    {
        extract($params); //'model', 'btnSize', 'labeled', 'isBtn'
        $result = '';
        $childs = $model->childs;
        $childIsCI = false;
        foreach ($childs as $child){
            $childIsCI = $childIsCI || ($child->doc_type == Bill::BILL_DOC_TYPE_CRBILL);
        }        
        if(in_array($model->doc_type, [
                Bill::BILL_DOC_TYPE_BILL,
                Bill::BILL_DOC_TYPE_CESSION,
            ]) &&
            in_array($model->status, [
                Bill::BILL_STATUS_SIGNED, 
                Bill::BILL_STATUS_PREP_PAYMENT, 
                Bill::BILL_STATUS_PAYMENT, 
                Bill::BILL_STATUS_PAID,
            ]) &&
            !$childIsCI &&
            $model->paymentsSummaIsLess){
            
            if($isBtn){
                $result = FSMHelper::aButton($model->id, [
                    'title' => Yii::t('bill', 'Debt relief'),
                    'icon' => 'remove-circle',
                    'action' => '#',                                        
                    //'action' => 'bill-debt-relief',  
                    'class' => 'danger',
                    'size' => $btnSize,
                ]);
            }else{
                $result = 
                    FSMHelper::aDropdown($model->id, [
                    'label' => Yii::t('bill', 'Debt relief'),
                    'title' => Yii::t('bill', 'Debt relief'),
                    'action' => '#',                                        
                    //'action' => 'bill-debt-relief',  
                    'icon' => 'remove-circle',
                ]);
            }
        }
        return $result;
    }
    
    static function getButtonCancel(array $params)
    {
        extract($params); //'model', 'btnSize', 'labeled', 'isBtn'
        $result = '';
        switch ($model->status) {
            case Bill::BILL_STATUS_NEW:
            //case Bill::BILL_STATUS_READY:
            //case Bill::BILL_STATUS_SIGNED:
            //case Bill::BILL_STATUS_PAYMENT:
            //case Bill::BILL_STATUS_PAID:
                if($model->paymentsSumma == 0) {
                    if($isBtn){
                        $result = FSMHelper::vButton($model->id, [
                            'label' => ($labeled ? Yii::t('common', 'Cancel') : null),
                            'title' => ($labeled ? null : Yii::t('common', 'Cancel')),
                            'controller' => 'history-bill',
                            'action' => 'bill-cancel',
                            'class' => 'default',
                            'size' => $btnSize,
                            'icon' => 'ban-circle',
                            'modal' => true,
                        ]);
                    }else{
                        $result = 
                            FSMHelper::vDropdown($model->id, [
                            'label' => Yii::t('common', 'Cancel'),
                            'title' => Yii::t('common', 'Cancel'),
                            'controller' => 'history-bill',
                            'action' => 'bill-cancel',
                            'icon' => 'ban-circle',
                            'modal' => true,
                        ]);
                    }
                }
                break;
            default:
                break;
        }
        return $result;
    }
    
    public function getOptionsButtons($btnSize = '', $labeled = false)
    {
        if(!empty($btnSize)){
            $btnSize = 'btn-'.$btnSize;
        }
        
        if(in_array($this->status, [
            Bill::BILL_STATUS_PREPAR,
            Bill::BILL_STATUS_CANCELED,
            ]))
        {
            return '';
        }
        
        $result = [];
        $result[] = Bill::getButtonCreatePayment(['model' => $this, 'btnSize' => $btnSize, 'labeled' => $labeled, 'isBtn' => true]);
        $result[] = Bill::getButtonWriteOnBasis(['model' => $this, 'btnSize' => $btnSize, 'labeled' => $labeled, 'isBtn' => true]);
        $result[] = Bill::getButtonCreditInvoice(['model' => $this, 'btnSize' => $btnSize, 'labeled' => $labeled, 'isBtn' => true]);
        $result[] = Bill::getButtonMutualSettlement(['model' => $this, 'btnSize' => $btnSize, 'labeled' => $labeled, 'isBtn' => true]);
        $result[] = Bill::getButtonCession(['model' => $this, 'btnSize' => $btnSize, 'labeled' => $labeled, 'isBtn' => true]);
        $result[] = Bill::getButtonDebtRelief(['model' => $this, 'btnSize' => $btnSize, 'labeled' => $labeled, 'isBtn' => true]);
        $result[] = Bill::getButtonCancel(['model' => $this, 'btnSize' => $btnSize, 'labeled' => $labeled, 'isBtn' => true]);
        foreach ($result as $key => $btn) {
            if(empty($btn)){
                unset($result[$key]);
            }
        }
        
        $result = implode('&nbsp;', $result);
        return $result;
    }
    
    public function getOptionsActionButtons($btnSize = '', $labeled = false)
    {
        if(!empty($btnSize)){
            $btnSize = 'btn-'.$btnSize;
        }
        
        $result = [];
        
        $result['pay'] = function (array $params) use ($btnSize, $labeled) { 
            $params = ArrayHelper::merge($params, [
                'btnSize' => $btnSize,
                'labeled' => $labeled,
            ]);
            return Bill::getButtonCreatePayment($params);
        };
        $result['write-on-basis'] = function (array $params) use ($btnSize, $labeled) { 
            $params = ArrayHelper::merge($params, [
                'btnSize' => $btnSize,
                'labeled' => $labeled,
            ]);
            return Bill::getButtonWriteOnBasis($params);
        };
        $result['credit-invoice'] = function (array $params) use ($btnSize, $labeled) { 
            $params = ArrayHelper::merge($params, [
                'btnSize' => $btnSize,
                'labeled' => $labeled,
            ]);
            return Bill::getButtonCreditInvoice($params);
        };
        $result['mutual-settlement'] = function (array $params) use ($btnSize, $labeled) {
            $params = ArrayHelper::merge($params, [
                'btnSize' => $btnSize,
                'labeled' => $labeled,
            ]);
            return Bill::getButtonMutualSettlement($params);
        };
        $result['cession'] = function (array $params) use ($btnSize, $labeled) { 
            $params = ArrayHelper::merge($params, [
                'btnSize' => $btnSize,
                'labeled' => $labeled,
            ]);
            return Bill::getButtonCession($params);
        };
        $result['debt-relief'] = function (array $params) use ($btnSize, $labeled) { 
            $params = ArrayHelper::merge($params, [
                'btnSize' => $btnSize,
                'labeled' => $labeled,
            ]);
            return Bill::getButtonDebtRelief($params);
        };
        $result['cancel'] = function (array $params) use ($btnSize, $labeled) { 
            $params = ArrayHelper::merge($params, [
                'btnSize' => $btnSize,
                'labeled' => $labeled,
            ]);
            return Bill::getButtonCancel($params);
        };
        return  $result;
    }    
    
    public function getLastNumber()
    {
        $list = $this->find()
            ->where(['>=', 'create_time', date('Y-m-d')])
            ->andWhere(['=','doc_type', $this->doc_type])
            ->all();
        $lastNumber = count($list);
        return $lastNumber + 1;
    }
    
}