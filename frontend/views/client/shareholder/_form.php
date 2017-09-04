<?php
namespace common\models;

use Yii;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\client\Share */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal) ? 1 : 0;
?>

<div class="share-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => $model->tableName().'-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::activeHiddenInput($model, 'client_id'); ?>

    <?= $form->field($model, 'shareholder_id')->widget(Select2::classname(), [
            'data' => $clientList,
            'options' => [
                //'id' => 'first-client-id', 
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'controller' => 'client',
                    'isModal' => $isModal,
                ]),
            ],        
        ]); 
    ?>
    
    <?= $form->field($model, 'term_from', [
            ])->widget(DatePicker::classname(), [
            'type' => DatePicker::TYPE_RANGE,
            'attribute' => 'term_from',
            'attribute2' => 'term_till',
            'options' => [
                'placeholder' => Yii::t('common', 'Start date'),
            ],
            'options2' => [
                'placeholder' => Yii::t('common', 'End date'),
            ],
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ])->label(Yii::t('common', 'Period')); 
     ?>

    <?= $form->field($model, 'share')->widget(MaskedInput::classname(), [
            'mask' => '9{1,3}[.9{1,2}]',
            'options' => [
                'class' => 'form-control number-field',
            ],
    ]) ?>

    <?php if(!empty($model->id) && !empty($isAdmin)){
        echo $form->field($model, 'deleted')->widget(SwitchInput::classname(), [
            'pluginOptions' => [
                'onText' => Yii::t('common', 'Yes'),
                'offText' => Yii::t('common', 'No'),
            ],
        ]);
    } ?>
    
    <div class="form-group">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>