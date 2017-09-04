<?php

use yii\web\View;
use yii\helpers\Url;

$imgPath = Url::to('@web/assets_b/images', true);
$script = <<< JS
    var backendImagesPath = '{$imgPath}/';
JS;
Yii::$app->getView()->registerJs($script, View::POS_BEGIN);