<?php

use kartik\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\address\Address */

$this->title = Yii::t($model->tableName(), 'Create a new '.$model->modelTitle(1, false));
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="address-create">

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