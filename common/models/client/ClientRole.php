<?php

namespace common\models\client;

use Yii;

/**
 * This is the model class for table "client_role".
 *
 * @property integer $id
 * @property string $name
 * @property integer $enabled
 *
 * @property Agreement[] $agreements
 * @property Agreement[] $agreements0
 * @property Agreement[] $agreements1
 */
class ClientRole extends \common\models\mainclass\FSMBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'client_role';
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
        return parent::label('client', 'Client role|Client roles', $n, $translate);
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
    public function getAgreements()
    {
        return $this->hasMany(Agreement::className(), ['first_client_role_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgreements0()
    {
        return $this->hasMany(Agreement::className(), ['second_client_role_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAgreements1()
    {
        return $this->hasMany(Agreement::className(), ['third_client_role_id' => 'id']);
    }
}