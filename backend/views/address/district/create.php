<?php

use kartik\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\address\District $model
 */

$this->title = Yii::t($model->tableName(), 'Create a new '. $model->modelTitle(1, false));
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="district-create">

    <?= Html::pageHeader(Html::encode($this->title));?>

    <?= $this->render('_form', [
        'model' => $model,
        'countryList' => $countryList,
        'regionList' => $regionList,
        'cityList' => $cityList,
    ]) ?>

</div>
