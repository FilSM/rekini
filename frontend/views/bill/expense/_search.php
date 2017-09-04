<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\bill\search\ExpenseSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="expense-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'expense_type_id') ?>

    <?= $form->field($model, 'abonent_id') ?>

    <?= $form->field($model, 'project_id') ?>

    <?= $form->field($model, 'doc_number') ?>

    <?php //echo $form->field($model, 'doc_date') ?>

    <?php //echo $form->field($model, 'first_client_id') ?>

    <?php //echo $form->field($model, 'second_client_id') ?>

    <?php //echo $form->field($model, 'summa') ?>

    <?php //echo $form->field($model, 'vat') ?>

    <?php //echo $form->field($model, 'total') ?>

    <?php //echo $form->field($model, 'valuta_id') ?>

    <?php //echo $form->field($model, 'comment') ?>

    <?php //echo $form->field($model, 'create_time') ?>

    <?php //echo $form->field($model, 'create_user_id') ?>

    <?php //echo $form->field($model, 'update_time') ?>

    <?php //echo $form->field($model, 'update_user_id') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('common', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('common', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>