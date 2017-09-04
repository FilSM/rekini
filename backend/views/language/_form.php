<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/**
 * @var yii\web\View $this
 * @var common\models\Language $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="language-form">

    <?php $form = ActiveForm::begin([
            'type' => ActiveForm::TYPE_HORIZONTAL,
            'id' => 'language-form',
            'fieldConfig' => [
                'showHints' => true,
            ],        
    ]); ?>    

    <?= $form->field($model, 'language')->textInput(['maxlength' => 2]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 64]) ?>

    <?= $form->field($model, 'native')->textInput(['maxlength' => 64]) ?>

    <?= $form->field($model, 'enabled')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('common', 'Yes'),
            'offText' => Yii::t('common', 'No'),
        ],
    ]);
    ?>    

    <div class="form-group">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
