<?php

use yii\web\View;
use yii\helpers\Url;

$appUrl = Url::base();
$script = <<< JS
    var appUrl = '{$appUrl}';
JS;
Yii::$app->getView()->registerJs($script, View::POS_BEGIN);