<?php

use yii\widgets\Pjax;
use yii\widgets\MaskedInput;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/**
 * @var yii\web\View $this
 * @var common\models\Bank $model
 * @var yii\widgets\ActiveForm $form
 */
$isModal = !empty($isModal);
?>

<div class="bank-form">
    <?php if($isModal) : Pjax::begin(Yii::$app->params['PjaxModalOptions']); endif; ?>
    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => 'bank-form',  
        'options' => [
            'data-pjax' => $isModal,
        ],         
    ]); ?>   

    <?= $form->field($model, 'name')->textInput(['maxlength' => 100]) ?>

    <?= $form->field($model, 'reg_number')->textInput(['maxlength' => 30,]);  ?>
    
    <?= $form->field($model, 'swift')->textInput(['maxlength' => 20]) ?>
    
    <?= $form->field($model, 'address')->textInput(); ?>

    <?= $form->field($model, 'home_page')->widget(MaskedInput::classname(), [
        'clientOptions' => [
            'alias' =>  'url',
        ],
    ]); ?>
    
    <?php if(!empty($model->id)) : ?>
    <?= $form->field($model, 'enabled')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('common', 'Yes'),
            'offText' => Yii::t('common', 'No'),
        ],
    ]); ?>
    <?php endif; ?>
    
    <div class="form-group <?php if($isModal) : echo 'modal-button-group'; endif; ?>">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php if($isModal) : Pjax::end(); endif; ?>
</div>
