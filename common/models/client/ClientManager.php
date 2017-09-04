<?php

namespace common\models\client;

use Yii;
use yii\helpers\ArrayHelper;

use common\models\client\ClientContact;

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
 * @property string $term_from
 * @property string $term_till
 * @property string $share
 * @property integer $top_manager
 *
 * @property Client $client
 * @property PersonPosition $position
 */
class ClientManager extends ClientContact
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = ArrayHelper::merge(
            $rules,
            [
                [['top_manager'], 'integer'],
                [['term_from', 'term_till'], 'safe'],
                [['share'], 'number'],
            ]
        );
        return $rules;        
    }

    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true)
    {
        return parent::label('client', 'Top manager|Top managers', $n, $translate);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge(
            $labels,
            [
                'term_from' => Yii::t('client', 'Term from'),
                'term_till' => Yii::t('client', 'Term till'),
                'share' => Yii::t('client', 'Share'),
                'top_manager' => Yii::t('client', 'Is top manager'),
            ]
        );
        return $labels;        
    }

}
