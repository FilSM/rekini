<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\bill\search\PaymentConfirmSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="payment-confirm-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'bank_id') ?>

    <?= $form->field($model, 'client_name') ?>

    <?= $form->field($model, 'client_reg_number') ?>

    <?= $form->field($model, 'client_id') ?>

    <?php //echo $form->field($model, 'name') ?>

    <?php //echo $form->field($model, 'start_date') ?>

    <?php //echo $form->field($model, 'end_date') ?>

    <?php //echo $form->field($model, 'pay_date') ?>

    <?php //echo $form->field($model, 'status') ?>

    <?php //echo $form->field($model, 'file_id') ?>

    <?php //echo $form->field($model, 'comment') ?>

    <?php //echo $form->field($model, 'action_time') ?>

    <?php //echo $form->field($model, 'action_user_id') ?>

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