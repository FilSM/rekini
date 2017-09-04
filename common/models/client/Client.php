<?php

namespace common\models\client;

use Yii;
use yii\helpers\ArrayHelper;

use common\models\user\FSMUser;
use common\models\user\FSMProfile;
use common\models\Bank;
use common\models\address\Country;
use common\models\address\Address;
use common\models\Language;
use common\models\Valuta;
use common\models\Files;
use common\models\abonent\Abonent;
use common\models\client\ClientContact;
use common\models\bill\BillConfirm;
use common\models\bill\Expense;
use common\models\bill\PaymentConfirm;

/**
 * This is the model class for table "client".
 *
 * @property integer $id
 * @property integer $version
 * @property integer $deleted
 * @property integer $abonent_id
 * @property integer $parent_id
 * @property string $it_is
 * @property integer $client_group_id
 * @property integer $manager_id
 * @property integer $language_id
 * @property string $client_type
 * @property string $status
 * @property string $name
 * @property integer $legal_country_id
 * @property string $legal_address
 * @property integer $office_country_id
 * @property string $office_address
 * @property string $invoice_email
 * @property string $reg_number
 * @property integer $vat_payer
 * @property string $vat
 * @property integer $tax
 * @property string $debit
 * @property integer $debit_valuta_id
 * @property integer $uploaded_file_id
 * @property string $comment
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $update_time
 * @property integer $update_user_id
 *
 * @property Abonent $abonent
 * @property Client $parent
 * @property ClientGroup $clientGroup
 * @property Language $language
 * @property Valuta $debitValuta
 * @property FSMProfile $manager
 * @property Country $legalCountry
 * @property Country $officeCountry
 * @property Bank[] $clientBanks
 * @property ClientContact $clientContacts
 * @property Project[] $clientProjects
 * @property Account[] $accounts
 * @property FSMProfile[] $profiles
 * @property Files $logo
 * @property FSMUser $createUser
 * @property FSMUser $updateUser
 */
class Client extends \common\models\mainclass\FSMVersionModel {

    const CLIENT_TYPE_PHYSICAL = 'physical';
    const CLIENT_TYPE_LEGAL = 'legal';
    
    const CLIENT_STATUS_ACTIVE = 'active';
    const CLIENT_STATUS_POTENTIAL = 'potential';
    const CLIENT_STATUS_ARHIVED = 'archived';
    
    const CLIENT_IT_IS_OWNER = 'owner';
    const CLIENT_IT_IS_ABONENT = 'abonent';
    const CLIENT_IT_IS_CLIENT = 'client';
    
    const CLIENT_VAT_PAYER_YES = 1;
    const CLIENT_VAT_PAYER_NO = 0;
    
    const CLIENT_DEFAULT_VAT_TAX = 21;

    protected $_externalFields = [
        'abonent_name',
        'parent_name',
        'manager_name',
        'manager_user_id',
        'country_name',
    ];

    public function init() {
        parent::init();
        $this->cascadeDeleting = true;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'client';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['reg_number', 'name', 'legal_address', 'legal_country_id'], 'required'],
            //['required', 'on' => ['default']],
            [['reg_number'], 'unique', 'targetAttribute' => ['deleted', 'reg_number'], 'message' => Yii::t('client', 'This registration number has already been taken.')],
            [['vat_number'], 'unique', 'targetAttribute' => ['deleted', 'vat_number'], 'message' => Yii::t('client', 'This VAT number has already been taken.')],
            [['abonent_id', 'parent_id', 'vat_payer', 'manager_id', 'language_id', 'deleted', 
                'client_group_id', 'debit_valuta_id', 'legal_country_id', 'office_country_id', 
                'tax', 'uploaded_file_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['it_is', 'client_type', 'status', 'comment', 'legal_address', 'office_address'], 'string'],
            [['debit'], 'number'],
            [['create_time', 'update_time'], 'safe'],
            [['reg_number', 'vat_number'], 'string', 'max' => 30],
            [['invoice_email'], 'string', 'max' => 50],
            [['name'], 'string', 'max' => 100],
            [['invoice_email'], 'email'],
            [['client_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientGroup::className(), 'targetAttribute' => ['client_group_id' => 'id']],
            [['debit_valuta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Valuta::className(), 'targetAttribute' => ['debit_valuta_id' => 'id']],
            [['language_id'], 'exist', 'skipOnError' => true, 'targetClass' => Language::className(), 'targetAttribute' => ['language_id' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMProfile::className(), 'targetAttribute' => ['manager_id' => 'id']],
            [['legal_country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['legal_country_id' => 'id']],
            [['office_country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['office_country_id' => 'id']],
            [['uploaded_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => Files::className(), 'targetAttribute' => ['uploaded_file_id' => 'id']],
            [['create_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['create_user_id' => 'id']],
            [['update_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['update_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('client', 'Client|Clients', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('common', 'ID'),
            'version' => Yii::t('common', 'Version'),
            'abonent_id' => Yii::t('client', 'Abonent'),
            'parent_id' => Yii::t('client', 'Parent company'),
            'it_is' => Yii::t('common', 'It is'),
            'client_group_id' => Yii::t('client', 'Client group'),
            'manager_id' => Yii::t('client', 'Our support'),
            'client_type' => Yii::t('client', 'Client type'),
            'status' => Yii::t('common', 'Status'),
            'name' => Yii::t('client', 'Full name'),
            'invoice_email' => Yii::t('client', 'Invoice Email'),
            'language_id' => Yii::t('languages', 'Communication language'),
            'reg_number' => Yii::t('client', 'Reg.number'),
            'vat_payer' => Yii::t('client', 'VAT payer'),
            'vat_number' => Yii::t('client', 'VAT number'),
            'tax' => Yii::t('cargo', 'Tax %'),
            'legal_country_id' => Yii::t('client', 'Country'),
            'legal_address' => Yii::t('client', 'Legal address'),
            'office_country_id' => Yii::t('client', 'Country'),
            'office_address' => Yii::t('client', 'Office address'),
            'debit' => Yii::t('client', 'Debit / Credit (+/-)'),
            'debit_valuta_id' => Yii::t('common', 'Currency'),
            'uploaded_file_id' => Yii::t('files', 'Logo'),
            'comment' => Yii::t('common', 'Comment'),
            'deleted' => Yii::t('common', 'Deleted'),
            'create_time' => Yii::t('common', 'Create Time'),
            'create_user_id' => Yii::t('common', 'Create User'),
            'update_time' => Yii::t('common', 'Update Time'),
            'update_user_id' => Yii::t('common', 'Update User'),
            
            'abonent_name' => Yii::t('client', 'Abonent'),
            'parent_name' => Yii::t('client', 'Parent company'),
            'manager_name' => Yii::t('client', 'Our support'),
            'country_id' => Yii::t('address', 'Country'),
            'country_name' => Yii::t('address', 'Country'),
        ];
    }
    
    protected function getIgnoredFieldsForDelete() {
        $fields = parent::getIgnoredFieldsForDelete();
        $fields = ArrayHelper::merge(
            $fields, ['abonent_id', 'client_group_id', 'main_client_id', 'parent_id', 
                'language_id', 'manager_id', 'debit_valuta_id', 'legal_country_id', 
                'office_country_id']
        );
        return $fields;
    }

    public function beforeMarkAsDeleted()
    {
        $client = Client::findOne(['reg_number' => $this->reg_number, 'deleted' => true]);
        if(isset($client)){
            $client->updateAttributes(['reg_number' => $client->id.'-'.$client->reg_number]);
        }
        return parent::beforeMarkAsDeleted();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAbonent() {
        return $this->hasOne(Abonent::className(), ['id' => 'abonent_id']);
    }    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMainForAbonent() {
        return $this->hasOne(Abonent::className(), ['main_client_id' => 'id'])->where(['deleted' => 0]);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientGroup() {
        return $this->hasOne(ClientGroup::className(), ['id' => 'client_group_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent() {
        return $this->hasOne(Client::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLegalCountry() {
        return $this->hasOne(Country::className(), ['id' => 'legal_country_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOfficeCountry() {
        return $this->hasOne(Country::className(), ['id' => 'office_country_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientBanks() {
        return $this->hasMany(ClientBank::className(), ['client_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgreements() {
        $id = $this->id;
        $agreementList = Agreement::find()
            ->where(['first_client_id' => $id])
            ->orWhere(['second_client_id' => $id])
            ->orWhere(['third_client_id' => $id]);
        return $agreementList;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpenses() {
        $id = $this->id;
        $agreementList = Expense::find()
            ->where(['first_client_id' => $id])
            ->orWhere(['second_client_id' => $id]);
        return $agreementList;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientProjects() {
        return $this->hasMany(Project::className(), ['client_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBillConfirms() {
        return $this->hasMany(BillConfirm::className(), ['second_client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAddresses() {
        return $this->hasMany(Address::className(), ['client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage() {
        return $this->hasOne(Language::className(), ['id' => 'language_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDebitValuta() {
        return $this->hasOne(Valuta::className(), ['id' => 'debit_valuta_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager() {
        return $this->hasOne(FSMProfile::className(), ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile() {
        $profileList = $this->profiles;
        if(count($profileList) > 0){
            return $profileList[0];
        }else{
            return null;
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfiles() {
        return $this->hasMany(FSMProfile::className(), ['client_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getContacts() {
        return $this->hasMany(Contact::className(), ['client_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClientContacts()
    {
        return $this->hasMany(ClientContact::className(), ['client_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLogo()
    {
        return $this->hasOne(Files::className(), ['id' => 'uploaded_file_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedFile()
    {
        return $this->getLogo();
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentConfirms()
    {
        return $this->hasOne(PaymentConfirm::className(), ['client_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegDocs()
    {
        return $this->hasOne(RegDoc::className(), ['client_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShareholders()
    {
        return $this->hasOne(Shareholder::className(), ['client_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreateUser() {
        return $this->hasOne(FSMUser::className(), ['id' => 'create_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateUser() {
        return $this->hasOne(FSMUser::className(), ['id' => 'update_user_id']);
    }

    public static function getClientItIsList() {
        return [
            Client::CLIENT_IT_IS_OWNER => Yii::t('client', 'Owner'),
            Client::CLIENT_IT_IS_ABONENT => Yii::t('abonent', 'Abonent'),
            Client::CLIENT_IT_IS_CLIENT => Yii::t('client', 'Simple client'),
        ];
    }

    public function getClientTypeList() {
        return [
            Client::CLIENT_TYPE_PHYSICAL => Yii::t('client', 'Physical person'),
            Client::CLIENT_TYPE_LEGAL => Yii::t('client', 'Legal person'),
        ];
    }

    static public function getClientStatusList() {
        return [
            Client::CLIENT_STATUS_ACTIVE => Yii::t('client', 'Active'),
            Client::CLIENT_STATUS_POTENTIAL => Yii::t('client', 'Potential'),
            Client::CLIENT_STATUS_ARHIVED => Yii::t('client', 'Archived'),
        ];
    }

    public function getVATPayerTypeList() {
        return [
            Client::CLIENT_VAT_PAYER_YES => Yii::t('common', 'Yes'),
            Client::CLIENT_VAT_PAYER_NO => Yii::t('common', 'No'),
        ];
    }
    
    static public function getClientList($search) {
        $result = ArrayHelper::map(self::find()
            ->where(['LIKE', 'name', $search])
            ->orderBy('name')
            ->asArray()
            ->all(), 'id', 'name');
        return $result;
    }

    static public function getClientListByItIs($itIs = null, $params = []) {
        $result = ArrayHelper::map(self::find()
            ->select(['id', 'name'])
            ->where(['deleted' => 0])
            ->andWhere((isset($itIs) ? ['it_is' => $itIs] : 'it_is IS NOT NULL'))
            ->andWhere((isset($params['search']) ? 'name LIKE "%' . $params['search'] . '%"' : 'name IS NOT NULL'))
            ->andWhere((isset($params['id']) ? ['id' => $params['id']] : 'id IS NOT NULL'))
            ->orderBy('name')
            ->asArray()
            ->all(), 'id', 'name');
        return $result;
    }

}
