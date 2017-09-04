<?php

namespace common\models\bill;

use Yii;
use yii\base\Exception;

use common\models\Action;
use common\models\bill\HistoryBill;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;

/**
 * This is the model class for table "history_bill".
 *
 * @property integer $id
 * @property integer $bill_id
 * @property integer $action_id
 * @property string $comment
 * @property string $create_time
 * @property integer $create_user_id
 *
 * @property Action $action
 * @property Bill $bill
 * @property FSMUser $createUser
 */
class HistoryBill extends \common\models\mainclass\FSMCreateModel
{
    const HISTORYBILL_ACTIONS = [
        'ACTION_CREATE'                     => 1,
        'ACTION_EDIT'                       => 2,
        'ACTION_DELETE'                     => 3,
        'ACTION_STATUS_UP_NEW'              => 4,
        'ACTION_STATUS_UP_READY'            => 5,
        'ACTION_STATUS_UP_SIGNED'           => 6,
        'ACTION_STATUS_UP_PREP_PAYMENT'     => 7,
        'ACTION_STATUS_UP_PAYMENT'          => 8,
        'ACTION_STATUS_UP_PAID'             => 9,
        'ACTION_STATUS_UP_COMPLETE'         => 10,
        'ACTION_STATUS_UP_CANCELED'         => 11,
        'ACTION_STATUS_DOWN_PREPAR'         => 12,
        'ACTION_STATUS_DOWN_NEW'            => 13,
        'ACTION_STATUS_DOWN_READY'          => 14,
        'ACTION_STATUS_DOWN_SIGNED'         => 15,
        'ACTION_STATUS_DOWN_PREP_PAYMENT'   => 16,
        'ACTION_STATUS_DOWN_PAYMENT'        => 17,
    ];

    protected $_externalFields = [
        'bill_number',
        'user_name',
    ]; 
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'history_bill';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bill_id', 'action_id', 'create_user_id'], 'integer'],
            [['comment'], 'string'],
            [['create_time'], 'safe'],
            [['bill_id'], 'exist', 'skipOnError' => true, 'targetClass' => Bill::className(), 'targetAttribute' => ['bill_id' => 'id']],
            [['create_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['create_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('bill', 'Invoice history|Invoices history', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'bill_id' => Yii::t('bill', 'Bill number'),
            'action_id' => Yii::t('bill', 'Action'),
            'comment' => Yii::t('bill', 'Comment'),
            'create_time' => Yii::t('bill', 'Action time'),
            'create_user_id' => Yii::t('bill', 'Performer'),
            
            'bill_number' => Yii::t('bill', 'Bill number'),
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
    public function getCreateUser()
    {
        return $this->hasOne(FSMUser::className(), ['id' => 'create_user_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreateUserProfile()
    {
        return $this->hasOne(FSMProfile::className(), ['user_id' => 'create_user_id']);
    }    
    
    static public function getBillActionList() {
        return [
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_CREATE'] => Yii::t('action', 'Creation'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_EDIT'] => Yii::t('action', 'Editing'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_DELETE'] => Yii::t('action', 'Deleting'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_NEW'] => Yii::t('action', 'Change status to "New"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_READY'] => Yii::t('action', 'Change status to "Ready"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_SIGNED'] => Yii::t('action', 'Change status to "Signed"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_PREP_PAYMENT'] => Yii::t('action', 'Change status to "Payment preparation"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_PAYMENT'] => Yii::t('action', 'Change status to "Payment"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_PAID'] => Yii::t('action', 'Confirmed payment'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_COMPLETE'] => Yii::t('action', 'Change status to "Complete"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_UP_CANCELED'] => Yii::t('action', 'Change status to "Canceled"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_PREPAR'] => Yii::t('action', 'Rollback status to "Preparing"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_NEW'] => Yii::t('action', 'Rollback status to "New"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_READY'] => Yii::t('action', 'Rollback status to "Ready"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_SIGNED'] => Yii::t('action', 'Rollback status to "Signed"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_PAYMENT'] => Yii::t('action', 'Rollback status to "Payment"'),
            HistoryBill::HISTORYBILL_ACTIONS['ACTION_STATUS_DOWN_PREP_PAYMENT'] => Yii::t('action', 'Rollback status to "Payment preparation"'),
        ];
    }    
    
    public function saveHistory($bill_id, $action_id, $bill_status_id)
    {
        $this->bill_id = !empty($bill_id) ? $bill_id : null;
        $this->action_id = $action_id;
        $this->create_time = date('d-M-Y H:i:s');
        $this->create_user_id = Yii::$app->user->id;
        
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            if (!$this->save()) {
                throw new Exception('Cannot to save data! '.$model->errorMessage);
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $transaction->rollBack();
            Yii::$app->getSession()->setFlash('error', $message);
            Yii::error($message, __METHOD__);
            return false;
        }
        return true;
    }
}