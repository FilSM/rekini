<?php

use kartik\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\client\ClientBankBalance */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Create a new '.$model->modelTitle(1, false));
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-bank-balance-create">

    <?php if(!$isModal): ?>
    <?= Html::pageHeader(Html::encode($this->title));?>
    <?php endif; ?>

    <?= $this->render('_form', [
        'model' => $model,
        'filesXMLModel' => $filesXMLModel,
        'filesPDFModel' => $filesPDFModel,
        'clientList' => $clientList,
        'bankList' => $bankList,
        'valutaList' => $valutaList,
        'isAdmin' => $isAdmin,
        'isModal' => $isModal,
    ]) ?>

</div>