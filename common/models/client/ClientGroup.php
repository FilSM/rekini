<?php

namespace common\models\client;

use Yii;

/**
 * This is the model class for table "client_group".
 *
 * @property integer $id
 * @property string $name
 * @property integer $enabled
 *
 * @property Client[] $clients
 */
class ClientGroup extends \common\models\mainclass\FSMBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'client_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['enabled'], 'integer'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('client', 'Client group|Client groups', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'name' => Yii::t('common', 'Name'),
            'enabled' => Yii::t('common', 'Enabled'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClients()
    {
        return $this->hasMany(Client::className(), ['client_group_id' => 'id']);
    }
}