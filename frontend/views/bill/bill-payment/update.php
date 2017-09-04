<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\bill\BillPayment */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Update a '.$model->modelTitle(1, false)) . ': ' . $model->id;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('common', 'Update');
?>
<div class="bill-payment-update">

    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>