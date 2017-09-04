<?php

namespace common\models\client;

use Yii;
use yii\helpers\ArrayHelper;

use common\models\address\Country;
use common\models\user\FSMUser;
use common\models\bill\Bill;
use common\models\bill\Expense;

/**
 * This is the model class for table "project".
 *
 * @property integer $id
 * @property integer $version
 * @property integer $deleted
 * @property string $name
 * @property integer $contry_id
 * @property string $address
 * @property integer $vat_taxable
 * @property string $comment
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $update_time
 * @property integer $update_user_id
 *
 * @property Bill[] $bills
 * @property Expense[] $expense
 * @property FSMUser $createUser
 * @property FSMUser $updateUser
 */
class Project extends \common\models\mainclass\FSMVersionModel
{
    
    protected $_externalFields = [
        'country_name',
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
        return 'project';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['version', 'deleted', 'country_id', 'vat_taxable', 
                'create_user_id', 'update_user_id'], 'integer'],
            [['comment'], 'string'],
            [['create_time', 'update_time'], 'safe'],
            [['name'], 'string', 'max' => 64],
            [['address'], 'string', 'max' => 100],
            [['country_id', 'name'], 'unique', 'targetAttribute' => ['country_id', 'name']],
            [['country_id'], 'exist', 'skipOnError' => true, 'targetClass' => Country::className(), 'targetAttribute' => ['country_id' => 'id']],
            [['create_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['create_user_id' => 'id']],
            [['update_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['update_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('client', 'Project|Projects', $n, $translate);
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
            'name' => Yii::t('agreement', 'Project name'),
            'country_id' => Yii::t('address', 'Country'),
            'address' => Yii::t('address', 'Address'),
            'vat_taxable' => Yii::t('agreement', 'VAT taxable transaction'),
            'comment' => Yii::t('common', 'Comment'),
            'create_time' => Yii::t('common', 'Create Time'),
            'create_user_id' => Yii::t('common', 'Create User'),
            'update_time' => Yii::t('common', 'Update Time'),
            'update_user_id' => Yii::t('common', 'Update User'),
            
            'country_name' => Yii::t('address', 'Country'),
        ];
    }
    
    protected function getIgnoredFieldsForDelete() {
        $fields = parent::getIgnoredFieldsForDelete();
        $fields = ArrayHelper::merge(
            $fields, ['country_id']
        );
        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCountry()
    {
        return $this->hasOne(Country::className(), ['id' => 'country_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgreements() {
        return $this->hasMany(Agreement::className(), ['project_id' => 'id']);
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
    public function getUpdateUser()
    {
        return $this->hasOne(FSMUser::className(), ['id' => 'update_user_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBills()
    {
        $result = $this->hasMany(Bill::className(), ['project_id' => 'id'])->where(['deleted' => 0]);
        return $result;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpenses()
    {
        $result = $this->hasMany(Expense::className(), ['project_id' => 'id']);
        return $result;
    }
  
}