<?php
namespace common\models;

use Yii;
use yii\widgets\MaskedInput;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;

/* @var $this yii\web\View */
/* @var $model common\models\bill\Expense */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal);
?>

<div class="expense-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => $model->tableName().'-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
    ]); ?>

    <?= $form->field($model, 'expense_type_id')->widget(Select2::classname(), [
            'data' => $expenseTypeList,
            'options' => [
                'id' => 'expense-type-id',
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => !$model->isAttributeRequired('expense_type_id'),
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'controller' => 'expense-type',
                    'isModal' => $isModal,
                ]),
            ],        
        ]); 
    ?>
    
    <?php /*= $form->field($model, 'abonent_id')->textInput()*/ ?>

    <?= $form->field($model, 'project_id')->widget(Select2::classname(), [
            'data' => $projectList,
            'options' => [
                'id' => 'project-id',
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => !$model->isAttributeRequired('project_id'),
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'controller' => 'project',
                    'isModal' => $isModal,
                ]),
            ],        
        ]); 
    ?>

    <?= $form->field($model, 'doc_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]); 
    ?>

    <?= $form->field($model, 'first_client_id')->widget(Select2::classname(), [
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
                    'prefix' => 'first-',
                    'controller' => 'client',
                    'isModal' => $isModal,
                ]),
            ],        
        ]); 
    ?>
    
    <?= $form->field($model, 'second_client_id')->widget(Select2::classname(), [
            'data' => $clientList,
            'options' => [
                //'id' => 'second-client-id', 
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'prefix' => 'second-',
                    'controller' => 'client',
                    'isModal' => $isModal,
                ]), 
            ],        
        ]); 
    ?>

    <?= $form->field($model, 'summa', [
        'addon' => [
            'append' => [
                'content' => Select2::widget([
                    'model' => $model,
                    'attribute' => 'valuta_id',
                    'data' => $valutaList, 
                    'options' => [
                        'id' => 'valuta-id', 
                        'placeholder' => '...',
                        //'style' => 'min-width: 90px;'
                    ],
                    'pluginOptions' => [
                        'allowClear' => !$model->isAttributeRequired('valuta_id'),
                    ],            
                    'size' => 'control-width-90',
                ]),
                'asButton' => true,
            ],
        ],
        //'labelOptions' => ['class' => 'col-md-3'],
    ])->widget(MaskedInput::classname(), [
        'mask' => '9{1,10}[.9{1,2}]',
        'options' => [
            'class' => 'form-control number-field',
        ],
    ])->label($model->getAttributeLabel('summa').' / '.$model->getAttributeLabel('valuta_id'));
    ?>

    <?= $form->field($model, 'vat')->widget(MaskedInput::classname(), [
        'mask' => '9{1,10}[.9{1,2}]',
        'options' => [
            'class' => 'form-control number-field',
        ],
    ]); ?>
    
    <?= $form->field($model, 'total')->textInput([
        'readonly' => true,
    ]); ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>