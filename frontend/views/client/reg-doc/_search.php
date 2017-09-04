<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\client\search\RegDocSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="reg-doc-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'client_id') ?>

    <?= $form->field($model, 'reg_doc_type_id') ?>

    <?= $form->field($model, 'doc_number') ?>

    <?= $form->field($model, 'doc_date') ?>

    <?php //echo $form->field($model, 'expiration_date') ?>

    <?php //echo $form->field($model, 'placement') ?>

    <?php //echo $form->field($model, 'notification_days') ?>

    <?php //echo $form->field($model, 'comment') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('common', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('common', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>