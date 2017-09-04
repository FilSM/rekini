<?php

namespace common\components\rbac;

use yii\rbac\Item;

class FilSMRole extends Item {

    /**
     * @inheritdoc
     */
    public $id;

    /**
     * @inheritdoc
     */
    public $type = self::TYPE_ROLE;

}
