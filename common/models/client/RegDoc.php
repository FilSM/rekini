<?php

namespace common\models\client;

use Yii;
use yii\helpers\ArrayHelper;

use common\models\client\RegDocType;
use common\models\client\Client;
use common\models\Files;

/**
 * This is the model class for table "reg_doc".
 *
 * @property integer $id
 * @property integer $client_id
 * @property integer $reg_doc_type_id
 * @property string $doc_number
 * @property string $doc_date
 * @property string $expiration_date
 * @property string $placement
 * @property integer $notification_days
 * @property integer $uploaded_file_id
 * @property string $comment
 *
 * @property Client $client
 * @property RegDocType $regDocType
 * @property Files $attachment
 */
class RegDoc extends \common\models\mainclass\FSMBaseModel
{

    protected $_externalFields = [
        'reg_doc_type_name',
        'file_name',
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'reg_doc';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['client_id', 'reg_doc_type_id', 'doc_number', 'doc_date'], 'required'],
            [['client_id', 'reg_doc_type_id', 'notification_days', 'uploaded_file_id'], 'integer'],
            [['doc_date', 'expiration_date'], 'safe'],
            [['comment'], 'string'],
            [['doc_number'], 'string', 'max' => 20],
            [['placement'], 'string', 'max' => 100],
            [['client_id'], 'exist', 'skipOnError' => true, 'targetClass' => Client::className(), 'targetAttribute' => ['client_id' => 'id']],
            [['reg_doc_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => RegDocType::className(), 'targetAttribute' => ['reg_doc_type_id' => 'id']],
            [['uploaded_file_id'], 'exist', 'skipOnError' => true, 'targetClass' => Files::className(), 'targetAttribute' => ['uploaded_file_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('client', 'Registration document|Registration documents', $n, $translate);
    }    
        
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'client_id' => Yii::t('client', 'Client'),
            'reg_doc_type_id' => Yii::t('client', 'Reg.document type'),
            'doc_number' => Yii::t('client', 'Doc.number'),
            'doc_date' => Yii::t('client', 'Doc.date'),
            'expiration_date' => Yii::t('client', 'Expiration date'),
            'placement' => Yii::t('client', 'Placement'),
            'notification_days' => Yii::t('client', 'Notification days'),
            'uploaded_file_id' => Yii::t('files', 'Attachment'),
            'comment' => Yii::t('client', 'Comment'),
            
            'reg_doc_type_name' => Yii::t('client', 'Reg.document type'),
            'file_name' => Yii::t('files', 'Attachment'),
        ];
    }
    
    protected function getIgnoredFieldsForDelete() {
        $fields = parent::getIgnoredFieldsForDelete();
        $fields = ArrayHelper::merge(
            $fields, ['client_id', 'reg_doc_type_id']
        );
        return $fields;
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
    public function getRegDocType()
    {
        return $this->hasOne(RegDocType::className(), ['id' => 'reg_doc_type_id']);
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttachment()
    {
        return $this->hasOne(Files::className(), ['id' => 'uploaded_file_id']);
    }    
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUploadedFile()
    {
        return $this->getAttachment();
    }     
}