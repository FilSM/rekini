<?php

namespace common\models\client;

use Yii;
use yii\helpers\ArrayHelper;

use kartik\helpers\Html;

use common\models\abonent\Abonent;
use common\models\user\FSMUser;
use common\models\Valuta;
use common\models\Files;
use common\models\bill\Bill;

/**
 * This is the model class for table "agreement".
 *
 * @property integer $id
 * @property integer $version
 * @property integer $deleted
 * @property string $agreement_type
 * @property integer $parent_id
 * @property integer $abonent_id
 * @property integer $first_client_id
 * @property integer $second_client_id
 * @property integer $third_client_id
 * @property integer $first_client_role_id
 * @property integer $second_client_role_id
 * @property integer $third_client_role_id
 * @property integer $project_id
 * @property string $number
 * @property string $signing_date
 * @property string $due_date
 * @property integer $deferment_payment
 * @property string $summa
 * @property integer $valuta_id
 * @property string $rate
 * @property string $rate_summa
 * @property string $rate_from_date
 * @property string $rate_till_date
 * @property string $status
 * @property string $conclusion
 * @property integer $uploaded_file_id
 * @property string $comment
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $update_time
 * @property integer $update_user_id
 *
 * @property Abonent $abonent
 * @property Client $firstClient
 * @property Client $secondClient
 * @property Client $thirdClient
 * @property ClientRole $firstClientRole
 * @property ClientRole $secondClientRole
 * @property ClientRole $thirdClientRole
 * @property Valuta $valuta
 * @property Files $attachment
 * @property FSMUser $createUser
 * @property FSMUser $updateUser
 */
class Agreement extends \common\models\mainclass\FSMVersionModel
{
 
    const AGREEMENT_TYPE_COOPERATION = 'cooperation';
    const AGREEMENT_TYPE_LOAN = 'loan';
    const AGREEMENT_TYPE_CESSION = 'cession';
    const AGREEMENT_TYPE_AGENCY= 'agency';
    
    const AGREEMENT_STATUS_POTENCIAL = 'potencial';
    const AGREEMENT_STATUS_NEW = 'new';
    const AGREEMENT_STATUS_SIGNED = 'signed';
    const AGREEMENT_STATUS_OVERDUE = 'overdue';
    const AGREEMENT_STATUS_CANCELED = 'canceled';

    const AGREEMENT_CONCLUSION_ORAL = 'oral';
    const AGREEMENT_CONCLUSION_WRITTEN = 'written';

    protected $_externalFields = [
        'first_client_name',
        'second_client_name',
        'third_client_name',
        'project_name',
    ];    
    static $nameField = 'number';

    public function init() {
        parent::init();
        $this->cascadeDeleting = true;
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agreement';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[/*'abonent_id', */'first_client_id', 'second_client_id', 
                'first_client_role_id', 'second_client_role_id', 'number', 
                'summa', 'valuta_id', 'project_id', 'deferment_payment'], 'required'],
            [['version', 'deleted', 'parent_id', 'abonent_id', 'first_client_id', 
                'second_client_id', 'third_client_id', 'first_client_role_id', 
                'second_client_role_id', 'third_client_role_id', 'valuta_id', 'project_id', 
                'uploaded_file_id', 'create_user_id', 'update_user_id', 'deferment_payment'], 'integer'],
            [['signing_date', 'due_date', 'rate_from_date', 'rate_till_date', 'create_time', 'update_time'], 'safe'],
            [['summa', 'rate', 'rate_summa'], 'number'],
            [['status', 'agreement_type', 'conclusion', 'comment'], 'string'],
            [['number'], 'string', 'max' => 20],
            [['first_client_id', 'project_id', 'number'], 'unique', 'targetAttribute' => ['first_client_id', 'project_id', 'number']],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Agreement::className(), 'targetAttribute' => ['parent_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['project_id' => 'id']],
            [['valuta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Valuta::className(), 'targetAttribute' => ['valuta_id' => 'id']],
            [['abonent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Abonent::className(), 'targetAttribute' => ['abonent_id' => 'id']],
            [['first_client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['first_client_id' => 'id']],
            [['second_client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['second_client_id' => 'id']],
            [['third_client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['third_client_id' => 'id']],
            [['first_client_role_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientRole::className(), 'targetAttribute' => ['first_client_role_id' => 'id']],
            [['second_client_role_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientRole::className(), 'targetAttribute' => ['second_client_role_id' => 'id']],
            [['third_client_role_id'], 'exist', 'skipOnError' => true, 'targetClass' => ClientRole::className(), 'targetAttribute' => ['third_client_role_id' => 'id']],
            [['uploaded_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => Files::className(), 'targetAttribute' => ['uploaded_file_id' => 'id']],
            [['create_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['create_user_id' => 'id']],
            [['update_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['update_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('client', 'Agreement|Agreements', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'version' => Yii::t('common', 'Version'),
            'deleted' => Yii::t('common', 'Deleted'),
            'agreement_type' => Yii::t('agreement', 'Agreement type'),
            'parent_id' => Yii::t('common', 'Main agreement'),
            'abonent_id' => Yii::t('client', 'Abonent'),
            'first_client_id' => Yii::t('agreement', 'First party'),
            'second_client_id' => Yii::t('agreement', 'Second party'),    
            'third_client_id' => Yii::t('agreement', 'Third party'),
            'first_client_role_id' => Yii::t('agreement', 'First party role'),
            'second_client_role_id' => Yii::t('agreement', 'Second party role'),
            'third_client_role_id' => Yii::t('agreement', 'Third party role'),
            'project_id' => Yii::t('agreement', 'Project name'),
            'number' => Yii::t('client', 'Number'),
            'signing_date' => Yii::t('client', 'Signing date'),
            'due_date' => Yii::t('client', 'Term'),
            'deferment_payment' => Yii::t('agreement', 'Deferment of payment'),
            'summa' => Yii::t('client', 'Summa'),
            'valuta_id' => Yii::t('common', 'Currency'),
            'rate' => Yii::t('client', 'Rate %'),
            'rate_summa' => Yii::t('client', 'Rate (summa)'),
            'rate_from_date' => Yii::t('client', 'Date from'),
            'rate_till_date' => Yii::t('client', 'Date till'),
            'status' => Yii::t('client', 'Status'),
            'conclusion' => Yii::t('client', 'Mode of conclusion'),
            'uploaded_file_id' => Yii::t('files', 'Attachment'),
            'comment' => Yii::t('common', 'Comment'),
            'create_time' => Yii::t('common', 'Create Time'),
            'create_user_id' => Yii::t('common', 'Create User'),
            'update_time' => Yii::t('common', 'Update Time'),
            'update_user_id' => Yii::t('common', 'Update User'),
            
            'first_client_name' => Yii::t('client', 'First party'),
            'second_client_name' => Yii::t('client', 'Second party'),
            'third_client_name' => Yii::t('client', 'Third party'),
            'project_name' => Yii::t('agreement', 'Project name'),
        ];
    }
    
    protected function getIgnoredFieldsForDelete() {
        $fields = parent::getIgnoredFieldsForDelete();
        $fields = ArrayHelper::merge(
            $fields, ['abonent_id', 'first_client_id', 'second_client_id', 'third_client_id',
                'first_client_role_id', 'second_client_role_id', 'third_client_role_id',
                'project_id', 'valuta_id', 'parent_id']
        );
        return $fields;
    }

    /** @inheritdoc */
    public function beforeValidate()
    {
        if(!empty($this->first_client_id) && !empty($this->second_client_id) && 
            (
                ($this->first_client_id == $this->second_client_id) ||
                ($this->first_client_id == $this->third_client_id) ||
                ($this->second_client_id == $this->third_client_id)
            )
        ){
            $this->addError('second_client_id', Yii::t('bill', 'The same clients are selected!'));
            return false;
        }else{
            return parent::beforeValidate();
        }
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Agreement::className(), ['id' => 'parent_id']);
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
    public function getCreateUser()
    {
        return $this->hasOne(FSMUser::className(), ['id' => 'create_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirstClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'first_client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSecondClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'second_client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getThirdClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'third_client_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirstClientRole()
    {
        return $this->hasOne(ClientRole::className(), ['id' => 'first_client_role_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSecondClientRole()
    {
        return $this->hasOne(ClientRole::className(), ['id' => 'second_client_role_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getThirdClientRole()
    {
        return $this->hasOne(ClientRole::className(), ['id' => 'third_client_role_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject() {
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
    public function getValuta() {
        return $this->hasOne(Valuta::className(), ['id' => 'valuta_id']);
    }    
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBills()
    {
        $result = $this->hasMany(Bill::className(), ['agreement_id' => 'id']);
        $result->where(['deleted' => 0]);
        return $result;
    }    
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachment()
    {
        return $this->hasOne(Files::className(), ['id' => 'uploaded_file_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedFile()
    {
        return $this->getAttachment();
    }
    
    public static function getAgreementTypeList() {
        return [
            Agreement::AGREEMENT_TYPE_COOPERATION => Yii::t('agreement', 'Cooperation'),
            Agreement::AGREEMENT_TYPE_LOAN => Yii::t('agreement', 'Loan'),
            Agreement::AGREEMENT_TYPE_CESSION => Yii::t('agreement', 'Cession'),
            Agreement::AGREEMENT_TYPE_AGENCY => Yii::t('agreement', 'Agency'),
        ];
    }    

    public static function getAgreementStatusList() {
        return [
            Agreement::AGREEMENT_STATUS_POTENCIAL => Yii::t('client', 'Potential'),
            Agreement::AGREEMENT_STATUS_NEW => Yii::t('client', 'New'),
            Agreement::AGREEMENT_STATUS_SIGNED => Yii::t('client', 'Concluded'),
            Agreement::AGREEMENT_STATUS_OVERDUE => Yii::t('client', 'Overdue'),
            Agreement::AGREEMENT_STATUS_CANCELED => Yii::t('client', 'Canceled'),
        ];
    }    

    public static function getAgreementConclusionList() {
        return [
            Agreement::AGREEMENT_CONCLUSION_ORAL => Yii::t('client', 'Oral'),
            Agreement::AGREEMENT_CONCLUSION_WRITTEN => Yii::t('client', 'Written'),
        ];
    }    

    public function getStatusBackgroundColor() {
        return $this->getStaticStatusBackgroundColor($this->status);
    }
    
    static public function getStaticStatusBackgroundColor($status = null) {
        $status = !empty($status) ? $status : null;
        switch ($status) {
            case Agreement::AGREEMENT_STATUS_POTENCIAL:
            case Agreement::AGREEMENT_STATUS_CANCELED:
                $class = 'badge-default';
                break;
            case Agreement::AGREEMENT_STATUS_NEW:  
                $class = 'badge-info';
                break;
            case Agreement::AGREEMENT_STATUS_SIGNED:
                $class = 'badge-success';
                break;
            case Agreement::AGREEMENT_STATUS_OVERDUE:
                $class = 'badge-error';
                break;
            default: 
                $class = 'badge-default';
                break;
        }  
        return $class;
    }    

    static public function getNameArr($where = null, $orderBy = 'number', $idField = 'id', $nameField = 'number')
    {
        if(isset($where)){
            return ArrayHelper::map(self::findByCondition($where)->orderBy($orderBy)->asArray()->all(), $idField, $nameField);
        }else{
            return ArrayHelper::map(self::find()->orderBy($orderBy)->asArray()->all(), $idField, $nameField);
        }
    }
    
    public function getLastNumber()
    {
        $list = $this->find()->where(['>=', 'create_time', date('Y-m-d')])->all();
        $lastNumber = count($list);
        return $lastNumber + 1;
    }
    
    static function getButtonPrint(array $params)
    {
        extract($params); //$url, $model, $key, $isBtn = true
        
        $disabled = empty($model->uploaded_file_id);
        if($isBtn){
            return Html::a(Html::icon('print'), ($disabled ? '#' : $url), [
                'class' => 'btn btn-xs '.($disabled ? 'btn-danger disabled': 'btn-success'),
                'title' => Yii::t('common', 'Print'),
                'target' => !$disabled ? '_blank' : null,
                'data-pjax' => !$disabled ? '0' : null,
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
            
}