<?php
namespace common\models;

use Yii;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\DatePicker;

use common\widgets\EnumInput;

/* @var $this yii\web\View */
/* @var $model common\models\abonent\Abonent */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="abonent-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
        'id' => 'client-form',
        'formConfig' => [
            'labelSpan' => 3,
        ],
        'fieldConfig' => [
            'showHints' => true,
        ],
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]); ?>

    <?= $form->field($model, 'subscription_end_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]); 
    ?>

    <?= $form->field($model, 'subscription_type')->widget(EnumInput::classname(), [
            'type' => EnumInput::TYPE_RADIOBUTTON,
            'options' => [
                'translate' => $model->abonentTypeList,
            ],
        ]); 
    ?>  
    
    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>
    
    <?= $this->render('@frontend/views/client/client/_form', [
        'form' => $form,
        'model' => $clientModel,
        'clientBankModel' => $clientBankModel,
        'filesModel' => $filesModel,
        'countryList' => $countryList,
        'languageList' => $languageList,
        'bankList' => $bankList,
        'valutaList' => $valutaList,
        'managerList' => $managerList,
        'isAdmin' => $isAdmin,
        'isOwner' => $isOwner,
        'isModal' => false,
        'registerAction' => false,
        'fromAbonent' => true,
    ]) ?>
    
    <div class="form-group clearfix double-line-top">
        <div class="col-lg-offset-8 col-lg-4" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>
    
    <?php ActiveForm::end(); ?>

</div>