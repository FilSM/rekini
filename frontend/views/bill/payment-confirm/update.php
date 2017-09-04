<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\bill\PaymentConfirm */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Update a '.$model->modelTitle(1, false)) . ': ' . $model->name;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('common', 'Update');
?>
<div class="payment-confirm-update">

    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <?= $this->render('_upload', [
        'model' => $model,
        'filesXMLModel' => $filesXMLModel,
        'filesPDFModel' => $filesPDFModel,
        'isAdmin' => $isAdmin,
        'isModal' => $isModal,
    ]) ?>

</div>