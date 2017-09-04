<?php

namespace common\models\bill;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use kartik\helpers\Html;

use common\models\bill\ExpenseType;
use common\models\abonent\Abonent;
use common\models\client\Project;
use common\models\client\Client;
use common\models\Valuta;
use common\models\user\FSMUser;
use common\models\user\FSMProfile;

/**
 * This is the model class for table "expense".
 *
 * @property integer $id
 * @property integer $expense_type_id
 * @property integer $abonent_id
 * @property integer $project_id
 * @property string $doc_number
 * @property string $doc_date
 * @property integer $first_client_id
 * @property integer $second_client_id
 * @property string $summa
 * @property string $vat
 * @property string $total
 * @property integer $valuta_id
 * @property string $comment
 * @property string $create_time
 * @property integer $create_user_id
 * @property string $update_time
 * @property integer $update_user_id
 *
 * @property Abonent $abonent
 * @property Project $project
 * @property ExpenseType $expenseType
 * @property Client $firstClient
 * @property Client $secondClient
 * @property Valuta $valuta
 * @property FSMUser $createUser
 * @property FSMUser $updateUser
 */
class Expense extends \common\models\mainclass\FSMCreateUpdateModel
{

    protected $_externalFields = [
        'abonent_name',
        'project_name',
        'first_client_name',
        'second_client_name',
        'expense_type_name',
    ]; 
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'expense';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['expense_type_id', 'project_id', 'doc_number', 'doc_date', 
                'first_client_id', 'second_client_id', 'summa', 'vat', 'total', 
                'valuta_id'], 'required'],
            [['expense_type_id', 'abonent_id', 'project_id', 'first_client_id', 
                'second_client_id', 'valuta_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['doc_date', 'create_time', 'update_time'], 'safe'],
            [['summa', 'vat', 'total'], 'number'],
            [['comment'], 'string'],
            [['doc_number'], 'string', 'max' => 20],
            [['doc_number'], 'unique'],
            [['expense_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ExpenseType::className(), 'targetAttribute' => ['expense_type_id' => 'id']],
            [['abonent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Abonent::className(), 'targetAttribute' => ['abonent_id' => 'id']],
            [['project_id'], 'exist', 'skipOnError' => true, 'targetClass' => Project::className(), 'targetAttribute' => ['project_id' => 'id']],
            [['first_client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['first_client_id' => 'id']],
            [['second_client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['second_client_id' => 'id']],
            [['valuta_id'], 'exist', 'skipOnError' => true, 'targetClass' => Valuta::className(), 'targetAttribute' => ['valuta_id' => 'id']],
            [['create_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['create_user_id' => 'id']],
            [['update_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => FSMUser::className(), 'targetAttribute' => ['update_user_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('bill', 'Expense|Expenses', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'expense_type_id' => Yii::t('bill', 'Expense type'),
            'abonent_id' => Yii::t('bill', 'Abonent'),
            'project_id' => Yii::t('bill', 'Project'),
            'doc_number' => Yii::t('bill', 'Doc.number'),
            'doc_date' => Yii::t('bill', 'Doc.date'),
            'first_client_id' => Yii::t('bill', 'First party'),
            'second_client_id' => Yii::t('bill', 'Second party'),
            'summa' => Yii::t('bill', 'Summa'),
            'vat' => Yii::t('bill', 'Vat'),
            'total' => Yii::t('bill', 'Total'),
            'valuta_id' => Yii::t('common', 'Currency'),
            'comment' => Yii::t('bill', 'Comment'),
            'create_time' => Yii::t('bill', 'Create Time'),
            'create_user_id' => Yii::t('bill', 'Create User'),
            'update_time' => Yii::t('bill', 'Update Time'),
            'update_user_id' => Yii::t('bill', 'Update User'),
        ];
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
    public function getProject()
    {
        return $this->hasOne(Project::className(), ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExpenseType()
    {
        return $this->hasOne(ExpenseType::className(), ['id' => 'expense_type_id']);
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
    public function getValuta()
    {
        return $this->hasOne(Valuta::className(), ['id' => 'valuta_id']);
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
    
    public function getLastNumber()
    {
        $list = $this->find()
            ->where(['>=', 'create_time', date('Y-m-d')])
            ->all();
        $lastNumber = count($list);
        return $lastNumber + 1;
    }    
}