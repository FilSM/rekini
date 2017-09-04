<?php
namespace common\models;

use Yii;
use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;

use common\widgets\EnumInput;

/* @var $this yii\web\View */
/* @var $model common\models\bill\PaymentOrder */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal);
?>

<div class="payment-order-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => $model->tableName().'-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
    ]); ?>

    <?= $form->field($model, "bank_id")->widget(Select2::classname(), [
        'data' => $bankList, 
        'options' => [
            'placeholder' => '...',
        ],
        'pluginOptions' => [
            'allowClear' => true
        ],
        'addon' => [
            'prepend' => $model->getModalButtonContent([
                'formId' => $form->id,
                'controller' => 'bank',
                'isModal' => $isModal,
            ]),
        ],                          
    ]); ?>
    
    <?= $form->field($model, 'number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'pay_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]); 
    ?>
    
    <?php if(!empty($model->id) && !empty($isAdmin)) :
        echo $form->field($model, 'status')->widget(EnumInput::classname(), [
            'type' => EnumInput::TYPE_RADIOBUTTON,
            'options' => [
                'translate' => $model->exportStateList,
            ],
        ]);
    else: ?>
        <?= Html::activeHiddenInput($model, 'status'); ?>
    <?php endif; ?>
    
    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>


    <div class="form-group">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>