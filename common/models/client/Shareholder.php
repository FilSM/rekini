<?php

namespace common\models\client;

use Yii;

/**
 * This is the model class for table "shareholder".
 *
 * @property integer $id
 * @property integer $deleted
 * @property integer $client_id
 * @property integer $shareholder_id
 * @property string $term_from
 * @property string $term_till
 * @property string $share
 *
 * @property Client $client
 * @property Client $shareholder
 */
class Shareholder extends \common\models\mainclass\FSMBaseModel
{
    protected $_externalFields = [
        'client_name',
        'shareholder_name',
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shareholder';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'shareholder_id', 'share'], 'required'],
            [['deleted', 'client_id', 'shareholder_id'], 'integer'],
            [['term_from', 'term_till'], 'safe'],
            [['share'], 'number'],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['shareholder_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['shareholder_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('client', 'Shareholder|Shareholders', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'deleted' => Yii::t('common', 'Deleted'),
            'client_id' => Yii::t('client', 'Client'),
            'shareholder_id' => Yii::t('client', 'Shareholder'),
            'term_from' => Yii::t('client', 'Term from'),
            'term_till' => Yii::t('client', 'Term till'),
            'share' => Yii::t('client', 'Share (%)'),
            
            'client_name' => Yii::t('client', 'Client'),
            'shareholder_name' => Yii::t('client', 'Shareholder'),
        ];
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
    public function getShareholder()
    {
        return $this->hasOne(Client::className(), ['id' => 'shareholder_id']);
    }

    /** @inheritdoc */
    public function beforeValidate()
    {
        if(!empty($this->client_id) && !empty($this->shareholder_id) && ($this->client_id == $this->shareholder_id)){
            $this->addError('shareholder_id', Yii::t('bill', 'The same shareholder are selected!'));
            return false;
        }else{
            return parent::beforeValidate();
        }
    }    
}