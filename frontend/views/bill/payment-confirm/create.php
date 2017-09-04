<?php

use kartik\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\bill\PaymentConfirm */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Upload '.$model->modelTitle(2, false));
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-confirm-create">

    <?php if(!$isModal): ?>
    <?= Html::pageHeader(Html::encode($this->title));?>
    <?php endif; ?>

    <?= $this->render('_upload', [
        'model' => $model,
        'filesXMLModel' => $filesXMLModel,
        'filesPDFModel' => $filesPDFModel,
        'isAdmin' => $isAdmin,
        'isModal' => $isModal,
    ]) ?>

</div>