<?php
namespace common\models;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;
use yii\widgets\Pjax;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;
use kartik\widgets\DatePicker;
use kartik\widgets\FileInput;
use kartik\checkbox\CheckboxX;

use common\widgets\EnumInput;
use common\widgets\GeocodeAutocompleteInput;

/* @var $this yii\web\View */
/* @var $model common\models\client\Agreement */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal);
?>

<div class="agreement-form">
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

    <?= $form->field($model, 'agreement_type')->widget(EnumInput::classname(), [
            'type' => EnumInput::TYPE_RADIOBUTTON,
            'options' => [
                'translate' => $model->agreementTypeList,
            ],
        ]); 
    ?> 
    
    <?php if(empty($model->id) && !empty($model->project_id)) : ?>
        <?= Html::activeHiddenInput($model, 'project_id'); ?>
    <?php else: ?>    
        <?= $form->field($model, 'project_id')->widget(Select2::classname(), [
                'data' => $projectList,
                'options' => [
                    'placeholder' => '...',
                ],   
                'pluginOptions' => [
                    'allowClear' => true,
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
    <?php endif; ?>
    
    <?= $form->field($model, 'parent_id')->widget(DepDrop::classname(), [
        'type' => DepDrop::TYPE_SELECT2,
        'data' => empty($model->parent_id) ? null : [$model->parent_id => $model->parent->number],
        //'options' => ['placeholder' => '...'],
        'select2Options' => [
            'pluginOptions' => [
                'allowClear' => !$model->isAttributeRequired('parent_id'),
            ],
            /*
            'addon' => [
                'prepend' => [
                    'content' => 
                    $model->getModalButton([
                        'formId' => $form->id,
                        'controller' => 'agreement',
                        'isModal' => $isModal,
                        'parent' => (!empty($model->project_id) ? 
                            [
                                'field_name' => 'project_id',
                                'id' => $model->project_id
                            ] :
                            null),
                        'options' => [
                            'disabled' => empty($model->project_id),
                            //'style' => 'display: none;',
                        ],
                    ]).
                    Html::a(Html::icon('eye-open'), 
                        Url::to(['/agreement/view', 'id' => $model->parent_id]),
                        [
                            'id' => 'btn-view-agreement', 
                            'class'=>'btn btn-info',
                            'target' => '_blank',
                            'style' => empty($model->parent_id) ? 'display: none;' : '',
                            //'disabled' => empty($model->agreement_id),
                        ]
                    ),
                    'asButton' => true, 
                ],
            ], 
             * 
             */
        ],
        'pluginOptions' => [
            'depends' => ['agreement-project_id'],
            'initialize' => true,            
            //'initDepends' => ['agreement-project_id'],
            'url' => Url::to(['/project/ajax-get-agreement-list', 'without-id' => $model->id, 'without-selected' => true]),
            'placeholder' => '...',
        ],
    ]); ?>

    <?= $form->field($model, 'status')->widget(EnumInput::classname(), [
            'type' => EnumInput::TYPE_RADIOBUTTON,
            'options' => [
                'translate' => $model->agreementStatusList,
            ],
        ]); 
    ?> 

    <?= $form->field($model, 'conclusion')->widget(EnumInput::classname(), [
            'type' => EnumInput::TYPE_RADIOBUTTON,
            'options' => [
                'translate' => $model->agreementConclusionList,
            ],
        ]); 
    ?> 
    
    <?= $form->field($model, 'first_client_role_id')->widget(Select2::classname(), [
            'data' => $clientRoleList,
            'options' => [
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'prefix' => 'first-',
                    'controller' => 'client-role',
                    'isModal' => $isModal,
                ]),
            ],        
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
    
    <?= $form->field($model, 'second_client_role_id')->widget(Select2::classname(), [
            'data' => $clientRoleList,
            'options' => [
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'prefix' => 'second-',
                    'controller' => 'client-role',
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
    
    <?= $form->field($model, 'third_client_role_id')->widget(Select2::classname(), [
            'data' => $clientRoleList,
            'options' => [
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'prefix' => 'third-',
                    'controller' => 'client-role',
                    'isModal' => $isModal,
                ]), 
            ],        
        ]); 
    ?>
    
    <?= $form->field($model, 'third_client_id')->widget(Select2::classname(), [
            'data' => $clientList,
            'options' => [
                //'id' => 'third-client-id', 
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'prefix' => 'third-',
                    'controller' => 'client',
                    'isModal' => $isModal,
                ]), 
            ],        
        ]); 
    ?>
    
    <?= $form->field($model, 'number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'signing_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]); 
    ?>

    <?= $form->field($model, 'due_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
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
                        //'id' => 'valuta-id', 
                        'placeholder' => '...',
                        //'style' => 'min-width: 90px;'
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
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
        
    <?= $form->field($model, 'deferment_payment')->widget(MaskedInput::classname(), [
        'mask' => '9{1,3}',
        'options' => [
            'class' => 'form-control number-field',
        ],
    ]);
    ?>
    
    <?php
        echo Html::beginTag('div', [
            'class' => 'form-group',
        ]);
        echo Html::beginTag('div', [
            'class' => 'col-md-2',
        ]);
        echo Html::endTag('div');
        echo Html::beginTag('div', [
            'id' => 'cbx-loan-agreement-container',
            'class' => 'col-md-10',
        ]);
        echo CheckboxX::widget([
            'name' => 'loan_agreement',
            'value' => (!empty($model->rate) || !empty($model->rate_summa) || !empty($model->rate_from_date)),
            'options' => ['id' => 'cbx-loan-agreement'],
            'pluginOptions' => ['threeState' => false],
        ]);
        $labelLegalTxt = Yii::t('client', 'Loan agreement');
        echo Html::label($labelLegalTxt, 'cbx-loan-agreement',
            [
                'class' => 'cbx-label fsm-label',
            ]
        );
        echo '<div class="help-block"></div>';
        echo Html::endTag('div');
        echo Html::endTag('div');    
    ?>   
    
    <?= Html::beginTag('div', [
        'id' => 'rate-data-container',
    ]);?>        
    
    <?= $form->field($model, 'rate')->widget(MaskedInput::classname(), [
        'mask' => '9{1,10}[.9{1,2}]',
        'options' => [
            'class' => 'form-control number-field',
        ],
    ]);
    ?>
    
    <?= $form->field($model, 'rate_summa')->widget(MaskedInput::classname(), [
        'mask' => '9{1,10}[.9{1,2}]',
        'options' => [
            'class' => 'form-control number-field',
        ],
    ]);
    ?>

    <?= $form->field($model, 'rate_from_date', [
                //'template' => '{label} <div class="input-group col-md-9">{input}{error}{hint}</div>',
            ])->widget(DatePicker::classname(), [
            'type' => DatePicker::TYPE_RANGE,
            'attribute' => 'rate_from_date',
            'attribute2' => 'rate_till_date',
            'options' => [
                'placeholder' => Yii::t('common', 'Start date'),
            ],
            'options2' => [
                'placeholder' => Yii::t('common', 'End date'),
            ],
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ])->label(Yii::t('client', 'Interest period')); 
     ?>
    
    <?= Html::endTag('div');?>        
        
    <?php
    $preview = !empty($filesModel->uploadedFileUrl) ? [$filesModel->uploadedFileUrl] : [];
    echo $form->field($filesModel, 'filename')->widget(FileInput::classname(), [
        'language' =>  strtolower(substr(Yii::$app->language, 0, 2)),
        'sortThumbs' => false,
        /*
        'options' => [
            'multiple' => false,
        ],
         * 
         */
        'pluginOptions' => [
            'allowedFileExtensions' => ['pdf', 'doc', 'docx'],
            'maxFileSize' => 20000,
            'showRemove' => false,
            'showUpload' => false,
            'initialPreview' => $preview,
            'initialPreviewShowDelete' => false,            
            'initialPreviewAsData' => true,
            'initialPreviewConfig' => [
                [
                    'type' => "pdf",
                    'size' => $filesModel->filesize, 
                    'caption' => $filesModel->filename, 
                    //'url' => "$urlD", 
                    //'key' => 101,
                ],
            ],
            //'overwriteInitial' => false, 
        ],
    ])->label(Yii::t('files', 'Attachment')); ?>
    
    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

    <?php if(!empty($model->id) && !empty($isAdmin)){
        echo $form->field($model, 'deleted')->widget(SwitchInput::classname(), [
            'pluginOptions' => [
                'onText' => Yii::t('common', 'Yes'),
                'offText' => Yii::t('common', 'No'),
            ],
        ]);
    } ?>

    <div class="form-group <?php if($isModal) : echo 'modal-button-group'; endif; ?>">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php if($isModal) : Pjax::end(); endif; ?>
</div>

<?php
/*
    echo GeocodeAutocompleteInput::widget([
        'name' => 'tmpGMAPInput',
        'options' => [
            'style' => 'display: none;',
        ],
    ]);
 * 
 */
?>

<?php
/*
$api_key = @Yii::$app->params['googleMapsApiKey'];
Yii::$app->getView()->registerJsFile("https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places&language=LV");
 * 
 */
?>