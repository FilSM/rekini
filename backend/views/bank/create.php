<?php

use kartik\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\Bank $model
 */

$this->title = Yii::t($model->tableName(), 'Create a new '. $model->modelTitle(1, false));
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bank-create">

    <?php if(!$isModal): ?>
    <?= Html::pageHeader(Html::encode($this->title));?>
    <?php endif; ?>

    <?= $this->render('_form', [
        'model' => $model,
        'isModal' => $isModal,
    ]) ?>

</div>
