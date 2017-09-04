<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\client\search\ClientBankSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="client-bank-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'deleted')->widget(SwitchInput::classname(), [
            'pluginOptions' => [
                'onText' => Yii::t('common', 'Yes'),
                'offText' => Yii::t('common', 'No'),
            ],
        ]) ?>

    <?= $form->field($model, 'client_id') ?>

    <?= $form->field($model, 'bank_id') ?>

    <?= $form->field($model, 'account') ?>

    <?php //echo $form->field($model, 'name') ?>

    <?php //echo $form->field($model, 'uploaded_file_id') ?>

    <?php //echo $form->field($model, 'balance') ?>

    <?php //echo $form->field($model, 'currency') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('common', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('common', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>