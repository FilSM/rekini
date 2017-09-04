<?php

namespace common\models\bill;

use Yii;
use yii\helpers\ArrayHelper;

use kartik\helpers\Html;

use common\models\bill\Bill;
use common\models\bill\BillPayment;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;
use common\models\Files;
use common\models\Bank;

use common\components\FSMHelper;

/**
 * This is the model class for table "payment_order".
 *
 * @property integer $id
 * @property integer $bank_id
 * @property string $number
 * @property string $name
 * @property string $pay_date
 * @property string $status
 * @property integer $file_id
 * @property string $comment
 * @property string $action_time
 * @property integer $action_user_id
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $update_time
 * @property integer $update_user_id
 *
 * @property BillPayment[] $billPayments
 * @property Bank $bank
 * @property Bill[] $bills
 * @property FSMUser $actionUser
 * @property FSMUser $createUser
 * @property Files $file
 * @property FSMUser $updateUser
 * @property FSMProfile $actionUserProfile
 */
class PaymentOrder extends \common\models\mainclass\FSMCreateUpdateModel
{
    const EXPORT_STATE_PREPARE = 'prepare';
    const EXPORT_STATE_SENT = 'sent';

    protected $_externalFields = [
        'file_name',
        'user_name',
        'bank_name',
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
        return 'payment_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bank_id', 'name', 'pay_date'], 'required'],
            [['status', 'comment'], 'string'],
            [['bank_id', 'file_id', 'action_user_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['pay_date', 'action_time', 'create_time', 'update_time'], 'safe'],
            [['number'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 100],
            [['bank_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bank::className(), 'targetAttribute' => ['bank_id' => 'id']],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => Files::className(), 'targetAttribute' => ['file_id' => 'id']],
            [['action_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['action_user_id' => 'id']],
            [['create_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['create_user_id' => 'id']],
            [['update_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['update_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('bill', 'Prepared payment|Prepared payments', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'bank_id' => Yii::t('bill', 'Bank'),
            'number' => Yii::t('bill', 'Number'),
            'name' => Yii::t('common', 'Name'),
            'pay_date' => Yii::t('bill', 'Payment date'),
            'status' => Yii::t('bill', 'State'),
            'file_id' => Yii::t('bill', 'File name'),
            'comment' => Yii::t('bill', 'Comment'),
            'action_time' => Yii::t('bill', 'Sent time'),
            'action_user_id' => Yii::t('bill', 'Performer'),
            'create_time' => Yii::t('bill', 'Create Time'),
            'create_user_id' => Yii::t('bill', 'Create User ID'),
            'update_time' => Yii::t('bill', 'Update Time'),
            'update_user_id' => Yii::t('bill', 'Update User ID'),
            
            'file_name' => Yii::t('bill', 'XML file name'),
            'user_name' => Yii::t('bill', 'Performer'),
            'bank_name' => Yii::t('bill', 'Bank'),
        ];
    }
    
    protected function getIgnoredFieldsForDelete() {
        $fields = parent::getIgnoredFieldsForDelete();
        $fields = ArrayHelper::merge(
            $fields, ['bank_id', 'action_user_id']
        );
        return $fields;
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
    public function getBillPayments()
    {
        return $this->hasMany(BillPayment::className(), ['payment_order_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBills()
    {
        return $this->hasMany(Bill::className(), ['id' => 'bill_id'])->viaTable('bill_payment', ['payment_order_id' => 'id']);
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
    public function getCreateUser()
    {
        return $this->hasOne(FSMUser::className(), ['id' => 'create_user_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFile()
    {
        return $this->hasOne(Files::className(), ['id' => 'file_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdateUser()
    {
        return $this->hasOne(FSMUser::className(), ['id' => 'update_user_id']);
    }
    
    static public function getExportStateList()
    {
        return [
            PaymentOrder::EXPORT_STATE_PREPARE => Yii::t('bill', 'Preparation'),
            PaymentOrder::EXPORT_STATE_SENT => Yii::t('bill', 'Sent'),
        ];
    }
    
    public function getOptionsButtons($btnSize = '', $labeled = false)
    {
        if(!empty($btnSize)){
            $btnSize = 'btn-'.$btnSize;
        }
        
        $result = [];
        $result[] = PaymentOrder::getButtonSend(['model' => $this, 'btnSize' => $btnSize, 'labeled' => $labeled, 'isBtn' => true]);
        foreach ($result as $key => $btn) {
            if(empty($btn)){
                unset($result[$key]);
            }
        }
        
        $result = implode('&nbsp;', $result);
        return $result;
    }
    
    static function getButtonSend(array $params)
    {
        extract($params); //'model', 'btnSize', 'labeled', 'isBtn'
        $result = '';
        if(in_array($model->status, [
            PaymentOrder::EXPORT_STATE_PREPARE,
            ]))
        {
            $label = Yii::t('common', 'Export');
            $title = Yii::t('common', 'Export');
            if($isBtn){
                $result = 
                    FSMHelper::aButton($model->id, [
                    'label' => (!empty($labeled) ? $label : null),
                    'title' => (!empty($labeled) ? null : $title),
                    'controller' => 'payment-order',
                    'action' => 'send',
                    //'class' => 'primary',
                    'size' => !empty($btnSize) ? $btnSize : null,
                    'icon' => 'export',
                    'modal' => true,
                ]);
            }else{
                $result = 
                    FSMHelper::aDropdown($model->id, [
                    'label' => $label,
                    'title' => $title,
                    'controller' => 'payment-order',
                    'action' => 'send',
                    'icon' => 'export',
                    'modal' => true,
                ]);
            }
        }
        return $result;
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
            $result[$row['id']] = (!empty($row['number']) ? $row['number'].' | ' : '').$row['name'];
        }
        return $result;
    }  
    
    public function getLastNumber()
    {
        $list = $this->find()
            ->where(['>=', 'create_time', date('Y-m-d')])
            ->all();
        $lastNumber = count($list);
        return $lastNumber + 1;
    }    
}