<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\client\search\ClientContactSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="client-contact-search">

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

    <?= $form->field($model, 'first_name') ?>

    <?= $form->field($model, 'last_name') ?>

    <?php //echo $form->field($model, 'phone') ?>

    <?php //echo $form->field($model, 'email') ?>

    <?php //echo $form->field($model, 'position_id') ?>

    <?php //echo $form->field($model, 'can_sign')->widget(SwitchInput::classname(), [
//             'pluginOptions' => [
//                 'onText' => Yii::t('common', 'Yes'),
//                 'offText' => Yii::t('common', 'No'),
//             ],
//         ]) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('common', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('common', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>