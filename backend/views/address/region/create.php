<?php

use kartik\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\address\Region $model
 */

$this->title = Yii::t($model->tableName(), 'Create a new '. $model->modelTitle(1, false));
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="region-create">

    <?= Html::pageHeader(Html::encode($this->title));?>

    <?= $this->render('_form', [
        'model' => $model,
        'countryList' => $countryList,
    ]) ?>

</div>