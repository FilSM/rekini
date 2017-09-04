<?php

namespace common\models\client;

use Yii;

/**
 * This is the model class for table "reg_doc_type".
 *
 * @property integer $id
 * @property string $name
 *
 * @property RegDoc[] $regDocs
 */
class RegDocType extends \common\models\mainclass\FSMBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'reg_doc_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 64],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('client', 'Registration document type|Registration document types', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'name' => Yii::t('common', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRegDocs()
    {
        return $this->hasMany(RegDoc::className(), ['reg_doc_type_id' => 'id']);
    }
}