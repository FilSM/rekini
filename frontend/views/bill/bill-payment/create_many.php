<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\bill\BillPayment */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model[0]->tableName(), 'Create a new '.$model[0]->modelTitle(1, false));
//}
$this->params['breadcrumbs'][] = ['label' => $model[0]->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bill-payment-create">

    <?php if(!$isModal): ?>
    <?= Html::pageHeader(Html::encode($this->title));?>
    <?php endif; ?>
    
    <?= $this->render('_form_many', [
        'model' => $model,
        'historyModel' => $historyModel,
        'paymentOrderList' => $paymentOrderList,
        'isModal' => $isModal,
    ]) ?>

</div>