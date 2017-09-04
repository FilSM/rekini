<?php

use kartik\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\bill\Bill */
$this->title = $reportTitle;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="ebitda-details">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
    <?= $this->render('ebitda-index', [
        'dataProvider' => $dataProvider,
        'searchModel' => $searchModel,
        'valutaList' => $valutaList,
        'isAdmin' => $isAdmin,
    ]) ?>

    <?php if(!empty($direction) && ($direction == 'out')) : ?>
    <?= $this->render('@frontend/views/bill/expense/ebitda-index', [
        'dataProvider' => $dataExpenseProvider,
        'searchModel' => $searchExpenseModel,
        'projectList' => $projectList,
        'clientList' => $clientList,
        'valutaList' => $valutaList,
        'expenseTypeList' => $expenseTypeList,
    ]) ?>
    <?php endif; ?>

</div>