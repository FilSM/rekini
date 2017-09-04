<?php
namespace common\models;

use Yii;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\client\ClientContact */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal) ? 1 : 0;
?>

<div class="client-contact-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => $model->tableName().'-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
    ]); ?>

    <?php if(!empty($model->client_id)){
            echo Html::activeHiddenInput($model, 'client_id');
        }else{
            echo $form->field($model, 'client_id')->textInput();
        }
    ?>

    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'last_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone')->widget(MaskedInput::classname(), [
        'clientOptions' => [
            'greedy' => false,
        ],
        'mask' => '(+9{1,3}) 9{8,10}',
        'options' => [
            'class' => 'form-control',
            'placeholder' => Yii::t('common', 'Enter as') . ' (+999) 9999999999...',
        ],
    ]);
    ?>
    
    <?= $form->field($model, 'email')
        ->widget(MaskedInput::classname(), [
            'clientOptions' => [
                'alias' => 'email',
            ],
        ]); ?>

    <?= $form->field($model, 'position_id')->widget(Select2::classname(), [
            'data' => $positionList,
            'options' => [
                'id' => 'position-id',
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'controller' => 'person-position',
                    'isModal' => $isModal,
                ]),
            ],        
        ]); 
    ?>
    
    <?= $form->field($model, 'can_sign')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('common', 'Yes'),
            'offText' => Yii::t('common', 'No'),
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