<?php
namespace common\models;

use Yii;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\Pjax;
use yii\widgets\MaskedInput;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\DatePicker;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;

/* @var $this yii\web\View */
/* @var $model common\models\client\ClientBankBalance */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal);
?>

<div class="client-bank-balance-form">
    <?php if($isModal) : Pjax::begin(Yii::$app->params['PjaxModalOptions']); endif; ?>
    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => $model->tableName().'-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
        'options' => [
            'data-pjax' => $isModal,
        ],        
    ]); ?>

    <?php /* $form->field($model, 'payment_confirm_id')->textInput()*/ ?>

    <div class="form-group required">
        <label class="control-label col-md-2" for="client-id" title="Required field"><?= Yii::t('client', 'Client'); ?></label>
        <div class="col-md-10">
            <?= Select2::widget([
                'data' => $clientList,
                'name' => 'client_id',
                'value' => null,
                'options' => [
                    'id' => 'client-id',
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
            ]); ?>
            <div class="help-block"></div>
        </div>
    </div>
    
    <?= $form->field($model, 'account_id')->widget(DepDrop::classname(), [
        'type' => DepDrop::TYPE_SELECT2,
        'data' => empty($model->account_id) ? null : 
            [$model->account_id => $model->account->bank->name . ' | ' . $model->account->account . (!empty($model->account->name) ? ' ( '.$model->account->name . ' )' : '')],
        //'options' => ['placeholder' => '...'],
        'select2Options' => [
            'pluginOptions' => [
                'allowClear' => false,
            ],
            /*
            'addon' => [
                'prepend' => [
                    'content' => 
                    $model->getModalButton([
                        'formId' => $form->id,
                        'controller' => 'client-bank',
                        'isModal' => $isModal,
                        'parent' => (!empty($model->project_id) ? 
                            [
                                'field_name' => 'project_id',
                                'id' => $model->project_id
                            ] :
                            null),
                        'options' => [
                            'disabled' => empty($model->project_id),
                        ],
                    ]),
                    'asButton' => true, 
                ],
            ],
             * 
             */                            
        ],
        'pluginOptions' => [
            'depends' => ['client-id'],
            'initDepends' => ['client-id'],
            'initialize' => true,            
            'url' => Url::to(['/client/ajax-get-client-bank-list']),
            'placeholder' => '...',
        ],
        'pluginEvents' => [
            "depdrop:afterChange" => "function() {
                var form = $('#{$model->tableName()}' + '-form');
                checkRequiredInputs(form);
            }",
        ],        
    ]); ?>
    
    <?= $form->field($model, 'start_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]); 
    ?>

    <?= $form->field($model, 'end_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]); 
    ?>

    <?= $form->field($filesXMLModel, 'filename', [
            'options' => [
                'class' => 'required form-group',
            ],
        ])->widget(FileInput::classname(), [
            'language' =>  strtolower(substr(Yii::$app->language, 0, 2)),
            'sortThumbs' => false,
            'options' => [
                'id' => 'xml-file',
            ],
            'pluginOptions' => [
                'allowedFileExtensions' => ['xml'],
                'maxFileSize' => 20000,
                'showRemove' => false,
                'showUpload' => false,
            ],
        ])->label(Yii::t('bill', 'XML filename to import')); 
    ?>
   
    <?= $form->field($filesPDFModel, 'filename', [
            'options' => [
                'class' => 'required form-group',
            ],
        ])->widget(FileInput::classname(), [
            'language' =>  strtolower(substr(Yii::$app->language, 0, 2)),
            'sortThumbs' => false,
            'options' => [
                'id' => 'pdf-file',
            ],
            'pluginOptions' => [
                'allowedFileExtensions' => ['pdf'],
                'maxFileSize' => 20000,
                'showRemove' => false,
                'showUpload' => false,
            ],
        ])->label(Yii::t('bill', 'PDF filename to import')); 
    ?>

    <?= $form->field($model, 'balance', [
        'addon' => [
            'append' => [
                'content' => Select2::widget([
                    'name' => 'valuta_id',
                    'data' => $valutaList, 
                    'value' => Valuta::VALUTA_DEFAULT,
                    'options' => [
                        'id' => 'valuta-id', 
                        'placeholder' => '...',
                        //'style' => 'min-width: 90px;'
                    ],
                    'pluginOptions' => [
                        'allowClear' => false,
                    ],            
                    'size' => 'control-width-90',
                ]),
                'asButton' => true,
            ],
        ],
    ])->widget(MaskedInput::classname(), [
        'mask' => '9{1,10}[.9{1,2}]',
        'options' => [
            'class' => 'form-control number-field',
            //'readonly' => true,
        ],
    ])->label($model->getAttributeLabel('balance').' / '.Yii::t('common', 'Currency'));
    ?>
    
    <div class="form-group <?php if($isModal) : echo 'modal-button-group'; endif; ?>">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php if($isModal) : Pjax::end(); endif; ?>

</div>