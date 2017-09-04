<?php

namespace common\models\user;

use Yii;
use yii\base\Exception;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use common\models\mainclass\FSMBaseModel;
use common\models\address\Address;
use common\models\client\Client;

class FSMProfile extends \dektrium\user\models\Profile
{
    public $clientItIs;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = parent::rules();
        $rules = ArrayHelper::merge(
                        [
                    [['client_id', 'name', 'phone'], 'required'],
                    [['version', 'user_id', 'language_id', 'client_id', 'deleted'], 'integer'],
                    [['phone'], 'string', 'max' => 20],
                    [['timezone'], 'string', 'max' => 50]
                        ], $rules);
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = parent::attributeLabels();
        $labels = ArrayHelper::merge(
                        $labels, [
                    'id' => Yii::t('common', 'ID'),
                    'version' => Yii::t('common', 'Version'),
                    'user_id' => Yii::t('user', 'User ID'),
                    'name' => Yii::t('client', 'Full name'),
                    'language_id' => Yii::t('languages', 'Communication language'),
                    'client_id' => Yii::t('fsmuser', 'User client'),
                    'phone' => Yii::t('fsmuser', 'Phone'),
                    //'public_email'   => Yii::t('user', 'Public email'),
                    //'location' => Yii::t('user', 'Postal address'),
                    'bio' => Yii::t('common', 'Comment'),
                    'timezone' => Yii::t('user', 'Timezone'),
                    'deleted' => Yii::t('common', 'Deleted'),
                        ]
        );
        return $labels;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge(
                        $behaviors, [
                    'modelBehavior' => array(
                        'class' => \common\behaviors\FSMProfileModelBehavior::className(),
                    ),
                    'modelVersioning' => array(
                        'class' => \common\behaviors\SAModelVersioning::className(),
                    ),
                    'attributeStamp' => [
                        'class' => AttributeBehavior::className(),
                        'attributes' => [
                            ActiveRecord::EVENT_BEFORE_INSERT => 'create_user_id',
                            ActiveRecord::EVENT_BEFORE_UPDATE => 'update_user_id',
                        ],
                        'value' => function ($event) {
                            return Yii::$app->getUser()->getId();
                        },
                    ],
                    'timestamp' => [
                        'class' => TimestampBehavior::className(),
                        'attributes' => [
                            ActiveRecord::EVENT_BEFORE_INSERT => 'create_time',
                            ActiveRecord::EVENT_BEFORE_UPDATE => 'update_time',
                        ],
                        'value' => new Expression('NOW()'),
                    ],
                        ]
        );
        return $behaviors;
    }

    public static function modelTitle($n = 1, $translate = true)
    {
        return self::label('user', 'Profile|Profiles', $n, $translate);
    }

    public static function label($category, $message, $n = 1, $translate = true)
    {
        if (strpos($message, '|') !== false) {
            $chunks = explode('|', $message);
            $message = $chunks[$n - 1];
        }
        return $translate ? Yii::t($category, $message) : $message;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLanguage()
    {
        return $this->hasOne(\common\models\Language::className(), ['id' => 'language_id']);
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

    public function getNonVersionFields()
    {
        return [];
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $dataToSave = Yii::$app->request->post();

        if (empty($dataToSave)) {
            return true;
        }

        // will created new profile from user creating form
        if (!empty($dataToSave['Client'])) {
            $this->name = $dataToSave['Client']['name'];
            $this->phone = $dataToSave['Client']['contact_phone'];
            $this->language_id = $dataToSave['Client']['language_id'];
        } elseif (!empty($dataToSave['FSMUser'])) {
            $this->name = '';
            $this->phone = '';
            $this->timezone = '';
            $this->deleted = false;
            $this->create_user_id = Yii::$app->getUser()->getId();
            $this->create_time = new Expression('NOW()');
            $this->update_user_id = Yii::$app->getUser()->getId();
            $this->update_time = new Expression('NOW()');
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        /*
          $post = Post::findOne(['id' => $this->post_id]);
          if($post){
          $post->updateAttributes(['profile_id' => $this->id]);
          }
         * 
         */
    }

    public function beforeMarkAsDeleted()
    {
        $event = new ModelEvent;
        $this->trigger(FSMBaseModel::EVENT_BEFORE_MARK_AS_DELETED, $event);

        return $event->isValid;
    }

    private function markAsDeleted()
    {
        $result = false;
        if ((!$this->deleted) && ($result = $this->beforeMarkAsDeleted())) {
            $result = $this->updateAttributes([
                'user_id' => null,
                'phone' => (!empty($this->phone) ? $this->phone : '-'),
                'deleted' => true,
            ]);
        }
        return $result;
    }

    public function delete()
    {
        if (!$this->hasAttribute('deleted') || empty($this->name)) {
            $result = parent::delete();
        } else {
            $result = $this->markAsDeleted();
        }
        if (!$result || $this->hasErrors()) {
            $message = [];
            foreach ($this->getErrors() as $attribute) {
                foreach ($attribute as $error) {
                    $message[] = $error;
                }
            }
            $message[] = Yii::t('user', 'Can`t delete User profile');
            $message = implode(PHP_EOL, $message);
            Yii::$app->getSession()->setFlash('error', $message);
            Yii::error($message, __METHOD__);
        }

        return $result;
    }

    static public function getManagerList()
    {
        $result = ArrayHelper::map(self::find()
                                ->leftJoin('user', 'user.id = profile.user_id')
                                ->leftJoin('client', 'client.id = profile.client_id')
                                ->where(['client.it_is' => 'broker'])
                                ->andWhere(['like', 'user.role', 'manager'])
                                ->andWhere(['profile.deleted' => 0])
                                ->orderBy('name')
                                ->asArray()
                                ->all(), 'id', 'name');

        return $result;
    }

    static public function getUserList()
    {
        $result = ArrayHelper::map(self::find()
                                ->leftJoin('user', 'user.id = profile.user_id')
                                ->leftJoin('client', 'client.id = profile.client_id')
                                ->where(['client.it_is' => 'broker'])
                                ->andWhere(['like', 'user.role', 'user'])
                                ->andWhere(['profile.deleted' => 0])
                                ->orderBy('name')
                                ->asArray()
                                ->all(), 'id', 'name');

        return $result;
    }

    static public function getProfileListByRole($role, $client_id = null, $itIs = null)
    {
        $role = (array) $role;
        $userIDList = Yii::$app->authManager->getUserIdsByRole($role);

        $query = self::find()
                ->innerJoin('user', 'profile.user_id = user.id')
                ->innerJoin('client', 'client.id = profile.client_id')
                ->where(['profile.deleted' => 0])
                ->andWhere(['user_id' => $userIDList])
                ->andWhere(['user.blocked_at' => null])
                ->orderBy('name');
        if (!empty($client_id)) {
            $query->andWhere(['client.id' => $client_id]);
        }
        if (!empty($itIs)) {
            $query->andWhere(['client.it_is' => $itIs]);
        }
        $result = ArrayHelper::map($query->asArray()->all(), 'id', 'name');
        return $result;
    }

    static public function getProfileList($params = [])
    {
        $result = ArrayHelper::map(self::find()
                                ->where(['deleted' => 0])
                                ->andWhere((isset($params['search']) ? 'name LIKE "%' . $params['search'] . '%"' : 'name IS NOT NULL'))
                                ->andWhere((isset($params['id']) ? ['id' => $params['id']] : 'id IS NOT NULL'))
                                ->orderBy('name')
                                ->asArray()
                                ->all(), 'id', 'name');
        return $result;
    }

    static public function getNameArr($where = null, $orderBy = 'name', $idField = 'id', $nameField = 'name')
    {
        if (isset($where)) {
            return ArrayHelper::map(self::findByCondition($where)->orderBy($orderBy)->asArray()->all(), $idField, $nameField);
        } else {
            return ArrayHelper::map(self::find()->orderBy($orderBy)->asArray()->all(), $idField, $nameField);
        }
    }

    public function actionAjaxGetDriverList()
    {
        $out = [];
        $selected = null;
        if (isset($_POST['depdrop_all_params'])) {
            $carrier_id = $_POST['depdrop_all_params']['carrier-id'];
            $transport_id = $_POST['depdrop_all_params']['transport-id'];
            if (empty($transport_id) || !is_numeric($transport_id)) {
                echo Json::encode(['output' => '', 'selected' => $selected]);
                return;
            }
            if (($carrier = Client::findOne($carrier_id)) == null) {
                throw new NotFoundHttpException('The requested page does not exist.');
            }

            $transport = Transport::findOne($transport_id);
            $currentDriver = !empty($transport) ? $transport->driver : null;
            $selected = !empty($currentDriver) ? $currentDriver->id : null;

            $driverList = [];
            if (!empty($carrier->id)) {
                $driverList = FSMProfile::getProfileListByRole(FSMUser::USER_ROLE_DRIVER, $carrier->id);
                if (empty($driverList)) {
                    $driverList[] = !empty($currentDriver) ? $currentDriver : $profile;
                }
            } else {
                $driverList[] = $profile;
            }

            if (count($driverList) > 0) {
                foreach ($driverList as $i => $item) {
                    $out[] = ['id' => $i, 'name' => $item];
                }
            }
        }
        // Shows how you can preselect a value 
        echo Json::encode(['output' => $out, 'selected' => $selected]);
        return;
    }

}
