<?php

namespace common\components\rbac;

use yii\rbac\Permission;

class FilSMPermission extends Permission {

    /**
     * @inheritdoc
     */
    public $id;

    /**
     * @inheritdoc
     */
    public $type = self::TYPE_PERMISSION;

}
