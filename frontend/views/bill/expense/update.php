<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\bill\Expense */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Update a '.$model->modelTitle(1, false)) . ': ' . $model->doc_number;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->doc_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('common', 'Update');
?>
<div class="expense-update">

    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <?= $this->render('_form', [
        'model' => $model,
        'projectList' => $projectList,
        'clientList' => $clientList,
        'expenseTypeList' => $expenseTypeList,
        'valutaList' => $valutaList,
        'isAdmin' => $isAdmin,        
    ]) ?>

</div>