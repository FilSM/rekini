<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\address\Address */

$this->title = Yii::t($model->tableName(), 'Update a '.$model->modelTitle(1, false)) . ': ' . $model->customer_address;
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->customer_address, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('common', 'Update');
?>
<div class="address-update">

    <?= Html::pageHeader(Html::encode($this->title));?>

    <?= $this->render('_form', [
        'model' => $model,
        'ownerList' => $ownerList,
        'countryList' => $countryList,
        'regionList' => $regionList,
        'cityList' => $cityList,
        'districtList' => $districtList,
    ]) ?>

</div>