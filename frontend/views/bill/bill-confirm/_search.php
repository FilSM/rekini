<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\bill\search\BillConfirmSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bill-confirm-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'payment_confirm_id') ?>

    <?= $form->field($model, 'history_bill_id') ?>

    <?= $form->field($model, 'bill_payment_id') ?>

    <?= $form->field($model, 'bill_id') ?>

    <?php //echo $form->field($model, 'first_client_account') ?>

    <?php //echo $form->field($model, 'second_client_name') ?>

    <?php //echo $form->field($model, 'second_client_reg_number') ?>

    <?php //echo $form->field($model, 'second_client_account') ?>

    <?php //echo $form->field($model, 'second_client_id') ?>

    <?php //echo $form->field($model, 'doc_date') ?>

    <?php //echo $form->field($model, 'doc_number') ?>

    <?php //echo $form->field($model, 'direction') ?>

    <?php //echo $form->field($model, 'summa') ?>

    <?php //echo $form->field($model, 'currency') ?>

    <?php //echo $form->field($model, 'comment') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('common', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('common', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>