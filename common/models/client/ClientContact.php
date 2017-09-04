<?php

namespace common\models\client;

use Yii;

use common\models\client\PersonPosition;

/**
 * This is the model class for table "client_contact".
 *
 * @property integer $id
 * @property integer $deleted
 * @property integer $client_id
 * @property string $first_name
 * @property string $last_name
 * @property string $phone
 * @property string $email
 * @property string $position_id
 * @property integer $can_sign
 *
 * @property Client $client
 * @property PersonPosition $position
 */
class ClientContact extends \common\models\mainclass\FSMBaseModel
{
    protected $_externalFields = [
        'position_name',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'client_contact';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['deleted', 'client_id', 'position_id', 'can_sign'], 'integer'],
            [['client_id', 'first_name', 'last_name'], 'required'],
            [['first_name', 'last_name'], 'string', 'max' => 30],
            [['phone'], 'string', 'max' => 20],
            [['email'], 'string', 'max' => 50],
            [['email'], 'email'],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['position_id'], 'exist', 'skipOnError' => true, 'targetClass' => PersonPosition::className(), 'targetAttribute' => ['position_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('client', 'Contact person|Contact persons', $n, $translate);
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
            'first_name' => Yii::t('client', 'First name'),
            'last_name' => Yii::t('client', 'Last name'),
            'phone' => Yii::t('client', 'Phone'),
            'email' => Yii::t('client', 'Email'),
            'position_id' => Yii::t('client', 'Position'),
            'can_sign' => Yii::t('client', 'Can sign'),
            
            'position_name' => Yii::t('client', 'Position'),
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
    public function getPosition()
    {
        return $this->hasOne(PersonPosition::className(), ['id' => 'position_id']);
    }

}
