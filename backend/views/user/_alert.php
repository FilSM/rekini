<?php

use kartik\widgets\Alert;
use kartik\widgets\AlertBlock;

/**
 * @var dektrium\user\Module $module
 */
?>

<?php if ($module->enableFlashMessages): ?>

    <?= AlertBlock::widget([
        'delay' => 0,
        //'delay' => 5000,
        'alertSettings' => [
            'error' => ['type' => Alert::TYPE_DANGER],
            'danger' => ['type' => Alert::TYPE_DANGER],
            'success' => ['type' => Alert::TYPE_SUCCESS],
        ]
    ]); ?> 

<?php endif ?>
