<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\models\user\FSMProfile;

/**
 * This is the model class for table "language".
 *
 * @property integer $id
 * @property string $language
 * @property string $name
 * @property string $native
 * @property integer $enabled
 *
 * @property FSMProfile[] $profiles
 * @property UserProfile[] $userProfiles
 */
class Language extends \common\models\mainclass\FSMBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'languages';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['language', 'name', 'native'], 'required'],
            [['enabled'], 'integer'],
            [['language'], 'string', 'max' => 2],
            [['name', 'native'], 'string', 'max' => 64],
            [['language'], 'unique']
        ];
    }

    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('languages', 'Language|Languages', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'language' => Yii::t('languages', 'Language'),
            'name' => Yii::t('languages', 'Name'),
            'native' => Yii::t('languages', 'Native'),
            'enabled' => Yii::t('common', 'Enabled'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfiles()
    {
        return $this->hasMany(FSMProfile::className(), ['language_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserProfiles()
    {
        return $this->hasMany(UserProfile::className(), ['language' => 'id']);
    }

    public static function getEnabledLanguageList()
    {
        return ArrayHelper::map(self::find()->where(['enabled' => 1])->orderBy('name')->asArray()->all(), 'id', 'native');
    }

}
