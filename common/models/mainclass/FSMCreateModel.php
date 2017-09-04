<?php

namespace common\models\mainclass;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;

class FSMCreateModel extends FSMBaseModel {

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge(
            $behaviors,
            [
                'attributeStamp' => [
                    'class' => AttributeBehavior::className(),
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_INSERT => 'create_user_id',
                    ],
                    'value' => function ($event) {
                        return Yii::$app->user->id;
                    },
                ],
                'timestamp' => [
                    'class' => TimestampBehavior::className(),
                    'attributes' => [
                        ActiveRecord::EVENT_BEFORE_INSERT => 'create_time',
                    ],
                    'value' => new Expression('NOW()'),
                ],
            ]
        );
        return $behaviors;        
    }

    protected function getIgnoredFieldsForDelete() {
        $fields = parent::getIgnoredFieldsForDelete();
        $fields = ArrayHelper::merge(
            $fields,
            ['create_user_id']
        );
        return $fields;
    }
}
