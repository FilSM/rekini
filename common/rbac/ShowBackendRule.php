<?php

namespace rbac;

use Yii;
use yii\rbac\Rule;
use yii\helpers\ArrayHelper;
use common\models\user\FSMUser;

class ShowBackendRule extends Rule {

    public $name = 'userRole';

    public function execute($user, $item, $params) {
        //Получаем массив пользователя из базы
        $user = ArrayHelper::getValue($params, 'user', User::findOne($user));
        if ($user) {
            $role = $user->role; //Значение из поля role базы данных
            if ($item->name === 'admin') {
                return $role == User::USER_ROLE_SUPERUSER;
            } elseif ($item->name === 'moder') {
                return $role == User::USER_ROLE_SUPERUSER || $role == User::USER_ROLE_PORTALADMIN;
            } elseif ($item->name === 'user') {
                return $role == User::USER_ROLE_SUPERUSER || $role == User::USER_ROLE_PORTALADMIN || $role == User::USER_ROLE_USER;
            }
        }
        return false;
    }

}
