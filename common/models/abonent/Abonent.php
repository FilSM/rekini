<?php

namespace common\models\abonent;

use Yii;
use yii\helpers\ArrayHelper;

use common\models\client\Client;
use common\models\client\Agreement;
use common\models\bill\Bill;
use common\models\bill\Expense;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;

/**
 * This is the model class for table "abonent".
 *
 * @property integer $id
 * @property integer $version
 * @property integer $deleted
 * @property string $name
 * @property integer $main_client_id
 * @property string $subscription_end_date
 * @property string $subscription_type
 * @property integer $manager_id
 * @property string $comment
 * 
 * @property Client $mainClient
 * @property Profile $manager
 * @property Client[] $clients
 * @property Agreement[] $agreements
 * @property Bill[] $bills
 * @property Expense[] $expenses
 * @property FSMUser $createUser
 * @property FSMUser $updateUser
 */
class Abonent extends \common\models\mainclass\FSMVersionModel
{
    const ABONENT_TYPE_SILVER = 'silver';
    const ABONENT_TYPE_GOLD= 'gold';
    const ABONENT_TYPE_PLATINUM = 'platinum';
    
    protected $_externalFields = [
        'client_name',
        'manager_name',
        'manager_user_id',
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
        return 'abonent';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'main_client_id', 'subscription_end_date', 'manager_id'], 'required'],
            [['main_client_id', 'manager_id', 'deleted', 'create_user_id', 'update_user_id'], 'integer'],
            [['subscription_end_date'], 'safe'],
            [['subscription_type', 'comment'], 'string'],
            [['name'], 'string', 'max' => 64],
            [['main_client_id', 'name'], 'unique', 'targetAttribute' => ['main_client_id', 'name']],
            [['main_client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['main_client_id' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMProfile::className(), 'targetAttribute' => ['manager_id' => 'id']],
            [['create_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['create_user_id' => 'id']],
            [['update_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['update_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('abonent', 'Abonent|Abonents', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'version' => Yii::t('common', 'Version'),
            'name' => Yii::t('common', 'Abonent name'),
            'main_client_id' => Yii::t('abonent', 'Main client'),
            'subscription_end_date' => Yii::t('abonent', 'Subscription end date'),
            'subscription_type' => Yii::t('abonent', 'Subscription type'),
            'manager_id' => Yii::t('client', 'Our support'),
            'comment' => Yii::t('common', 'Abonent comment'),
            'deleted' => Yii::t('common', 'Deleted'),
            'create_time' => Yii::t('common', 'Create Time'),
            'create_user_id' => Yii::t('common', 'Create User'),
            'update_time' => Yii::t('common', 'Update Time'),
            'update_user_id' => Yii::t('common', 'Update User'),
            
            'client_name' => Yii::t('client', 'Main Client'),
            'manager_name' => Yii::t('client', 'Our support'),
        ];
    }
    
    protected function getIgnoredFieldsForDelete() {
        $fields = parent::getIgnoredFieldsForDelete();
        $fields = ArrayHelper::merge(
            $fields, ['manager_id']
        );
        return $fields;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMainClient()
    {
        return $this->hasOne(Client::className(), ['id' => 'main_client_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(FSMProfile::className(), ['id' => 'manager_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClients()
    {
        return $this->hasMany(Client::className(), ['abonent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBills()
    {
        return $this->hasMany(Bill::className(), ['abonent_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpenses()
    {
        return $this->hasMany(Expense::className(), ['abonent_id' => 'id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgreements()
    {
        return $this->hasMany(Agreement::className(), ['abonent_id' => 'id']);
    }
    
    static public function getAbonentTypeList() {
        return [
            Abonent::ABONENT_TYPE_SILVER => Yii::t('abonent', 'Silver'),
            Abonent::ABONENT_TYPE_GOLD => Yii::t('abonent', 'Gold'),
            Abonent::ABONENT_TYPE_PLATINUM => Yii::t('abonent', 'Platinum'),
        ];
    }    
}