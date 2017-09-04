<?php

use yii\web\View;
use yii\helpers\Url;

$imgPath = Url::to('@web/frontend/assets/images', true);
$script = <<< JS
    var frontendImagesPath = '{$imgPath}/';
JS;
Yii::$app->getView()->registerJs($script, View::POS_BEGIN);