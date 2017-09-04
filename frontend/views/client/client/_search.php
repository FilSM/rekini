<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\client\search\ClientSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="client-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'version') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'reg_number') ?>

    <?= $form->field($model, 'vat_number') ?>

    <?php //echo $form->field($model, 'address_id') ?>

    <?php //echo $form->field($model, 'invoice_email') ?>

    <?php //echo $form->field($model, 'manager_id') ?>

    <?php //echo $form->field($model, 'language_id')->widget(Select2::classname(), [
//             'data' => $languageList,
//             'options' => [
//                 'placeholder' => '...',
//             ],
//         ]); ?>

    <?php //echo $form->field($model, 'deleted')->widget(SwitchInput::classname(), [
//             'pluginOptions' => [
//                 'onText' => Yii::t('common', 'Yes'),
//                 'offText' => Yii::t('common', 'No'),
//             ],
//         ]) ?>

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