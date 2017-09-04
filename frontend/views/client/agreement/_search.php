<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\client\search\AgreementSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="agreement-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'version') ?>

    <?= $form->field($model, 'deleted')->widget(SwitchInput::classname(), [
            'pluginOptions' => [
                'onText' => Yii::t('common', 'Yes'),
                'offText' => Yii::t('common', 'No'),
            ],
        ]) ?>

    <?= $form->field($model, 'abonent_id') ?>

    <?= $form->field($model, 'first_client_id') ?>

    <?php //echo $form->field($model, 'second_client_id') ?>

    <?php //echo $form->field($model, 'third_client_id') ?>

    <?php //echo $form->field($model, 'number') ?>

    <?php //echo $form->field($model, 'signing_date') ?>

    <?php //echo $form->field($model, 'due_date') ?>

    <?php //echo $form->field($model, 'summa') ?>

    <?php //echo $form->field($model, 'rate') ?>

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