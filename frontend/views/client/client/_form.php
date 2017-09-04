<?php
namespace common\models\client;

use Yii;
use yii\widgets\MaskedInput;
use yii\helpers\Url;
use yii\web\JsExpression;
//use yii\bootstrap\Collapse;
use yii\widgets\Pjax;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;
use kartik\widgets\Alert;
use kartik\widgets\AlertBlock;
use kartik\checkbox\CheckboxX;
use kartik\widgets\FileInput;

use common\widgets\EnumInput;
use common\models\Bank;
use common\models\user\FSMUser;

/* @var $this yii\web\View */
/* @var $model common\models\Client */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal);
?>

<div class="<?php empty($registerAction) ? 'client-form' : 'client-register-form'?>">
    <?php if($isModal) : Pjax::begin(Yii::$app->params['PjaxModalOptions']); endif; ?>
    <?php if(!isset($fromAbonent)) : ?>
    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
        'id' => $model->tableName().'-form',
        'formConfig' => [
            'labelSpan' => 3,
        ],
        'fieldConfig' => [
            //'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-sm-offset-3 col-lg-9\">\n{hint}\n{error}</div>",
            //'labelOptions' => ['class' => 'col-lg-3 control-label'],
            'showHints' => true,
        ],
        'options' => [
            'data-pjax' => $isModal,
        ],
    ]);
    ?>        
    <?php endif; ?>

    <?= $form->field($model, 'client_type')->widget(EnumInput::classname(), [
            'type' => EnumInput::TYPE_RADIOBUTTON,
            'options' => [
                'translate' => $model->clientTypeList,
            ],
        ])->label((empty($registerAction) ? $model->getAttributeLabel('client_type') : Yii::t('user', 'User type'))); 
    ?>  
    
    <?= $form->field($model, 'client_group_id')->widget(Select2::classname(), [
            'data' => $clientGroupList,
            'options' => [
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'controller' => 'client-group',
                    'isModal' => $isModal,
                    'options' => [
                        //'title' => 'Add new client group',
                    ]
                ]),
            ],        
        ]); 
    ?>
    
    <?php if(!isset($fromAbonent)) : ?>
    <?= $form->field($model, 'parent_id', [
        ])->widget(Select2::classname(), [
        'initValueText' => empty($model->parent_id) ? '' : $model->parent->name,
        'options' => [
            'placeholder' => '...',
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 3,
            'ajax' => [
                'url' => Url::to(['/client/ajax-name-list?it_is=abonent']),
                'dataType' => 'json',
                'data' => new JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new JsExpression('function(markup) { return markup; }'),
            'templateResult' => new JsExpression('function(data) { return data.text; }'),
            'templateSelection' => new JsExpression('function(data) { return data.text; }'),       
        ],           
    ]); ?>
    <?php endif; ?>
    
    <?= $form->field($model, 'name')->textInput([
        'maxlength' => 64,
        'data-labels' => [
            'physical' => Yii::t('client', 'Firstname, Lastname'),
            'legal' => $model->getAttributeLabel('name'),
        ],
    ]); ?>

    <?= $form->field($model, 'reg_number', [
        'addon' => [
            'prepend' => [
                'content' => 
                    Html::button(Html::icon('search'), [
                        'id' => 'btn-lursoft-search', 
                        'class'=>'btn btn-default',
                        'value' => Url::to(["/client/ajax-get-lursoft-data"]),
                        'title' => Yii::t('client', 'Get data from Lursoft service'),
                    ]),
                'asButton' => true, 
            ],
        ],
    ])->textInput([
            'maxlength' => 30,
            'data-labels' => [
                'physical' => Yii::t('client', 'Personal code'),
                'legal' => $model->getAttributeLabel('reg_number'),
            ],
        ]); 
    ?>

    <?= $form->field($model, 'vat_payer')->widget(EnumInput::classname(), [
            'data' => $model->getVATPayerTypeList(),
            'type' => EnumInput::TYPE_RADIOBUTTON,
        ]); 
    ?>   

    <?= $form->field($model, 'vat_number', [
        'addon' => [
            'prepend' => [
                'content' => 
                    Html::button(Html::icon('search'), [
                        'id' => 'btn-vies-search', 
                        'class'=>'btn btn-default',
                        'value' => Url::to(["/client/ajax-get-vies-data"]),
                        'title' => Yii::t('client', 'Get data from VIES service'),
                    ]),
                'asButton' => true, 
            ],
        ],
    ])->textInput(['maxlength' => 30]) ?>
    
    <?= $form->field($model, 'tax')->widget(MaskedInput::classname(), [
        'mask' => '9{1,2}',
    ]); ?>

    <?php 
        $body = $form->field($model, 'legal_address')->textInput()->label(Yii::t('address', 'Address'));

        $body .= $form->field($model, 'legal_country_id')->widget(Select2::classname(), [
            'data' => $countryList,
            'options' => [
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'controller' => 'country',
                    'isModal' => $isModal,
                ]),
            ],        
        ]); 
        
        $panelContent = [
            'heading' => $model->getAttributeLabel('legal_address'),
            'preBody' => '<div class="panel-body">',
            'body' => $body,
            'postBody' => '</div>',
        ];
        
        echo Html::beginTag('div', [
            'id' => 'legal-address-data-container',
            'class' => 'form-group',
        ]);
        if(!$isModal){
            echo Html::beginTag('div', [
                'class' => 'col-md-3',
            ]);
            echo Html::endTag('div');
        }
        echo Html::beginTag('div', [
            'class' => 'col-md-'.(!$isModal ? '9' : '12'),
        ]);
        
        echo Html::panel(
            $panelContent, 
            'default', 
            [
                'id' => "legal-address-data",
                'data-labels' => [
                    'physical' => Yii::t('client', 'Registration address'),
                    'legal' => $model->getAttributeLabel('legal_address'),
                ],
                //'style' => "background: antiquewhite;",
            ]
        );
        echo Html::endTag('div');
        echo Html::endTag('div');           
    ?>    
    
    <?php
        echo Html::beginTag('div', [
            'class' => 'form-group',
        ]);
        echo Html::beginTag('div', [
            'class' => 'col-md-3',
        ]);
        echo Html::endTag('div');
        echo Html::beginTag('div', [
            'id' => 'cbx-use-address-container',
            'class' => 'col-md-9',
        ]);
        echo CheckboxX::widget([
            'name' => 'use_legal_address',
            'value' => (empty($model->legal_address) && empty($model->office_address) || ($model->legal_address == $model->office_address)),
            'options' => ['id' => 'cbx-use-legal-address'],
            'pluginOptions' => ['threeState' => false],
        ]);
        $labelLegalTxt = Yii::t('client', 'For office address to use the legal address');
        echo Html::label($labelLegalTxt, 'cbx-use-legal-address',
            [
                'class' => 'cbx-label fsm-label',
                'data-labels' => [
                    'physical' => Yii::t('client', 'For home address to use the registration address'),
                    'legal' => $labelLegalTxt,
                ],
            ]
        );
        echo '<div class="help-block"></div>';
        echo Html::endTag('div');
        echo Html::endTag('div');    
    ?>
    
    <?php 
        $body = $form->field($model, 'office_address')->textInput()->label(Yii::t('address', 'Address'));

        $body .= $form->field($model, 'office_country_id')->widget(Select2::classname(), [
            'data' => $countryList,
            'options' => [
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'controller' => 'country',
                    'isModal' => $isModal,
                ]),
            ],        
        ]); 
        
        $panelContent = [
            'heading' => $model->getAttributeLabel('office_address'),
            'preBody' => '<div class="panel-body">',
            'body' => $body,
            'postBody' => '</div>',
        ];
        
        echo Html::beginTag('div', [
            'id' => 'office-address-data-container',
            'class' => 'form-group',
        ]);
        if(!$isModal){
            echo Html::beginTag('div', [
                'class' => 'col-md-3',
            ]);
            echo Html::endTag('div');
        }
        echo Html::beginTag('div', [
            'class' => 'col-md-'.(!$isModal ? '9' : '12'),
        ]);
        
        echo Html::panel(
            $panelContent, 
            'default', 
            [
                'id' => "office-address-data",
                'data-labels' => [
                    'physical' => Yii::t('client', 'Home address'),
                    'legal' => $model->getAttributeLabel('office_address'),
                ],
                //'style' => "background: antiquewhite;",
            ]
        );
        echo Html::endTag('div');
        echo Html::endTag('div');           
    ?>    
    
    <?php /*
    <?= $form->field($model, 'invoice_email')
        ->widget(MaskedInput::classname(), [
            'clientOptions' => [
                'alias' => 'email',
            ],
        ]); ?>
     * 
     */
    ?>    
    
    <?= $form->field($model, 'language_id')->widget(Select2::classname(), [
        'data' => $languageList,
        'options' => [
            'placeholder' => '...',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],            
    ]); ?>

    <?php 
    if(!empty($registerAction) || empty($isOwner)/* || isset($itIs)*/): 
        echo Html::activeHiddenInput($model, 'manager_id');    
    else : 
        echo $form->field($model, 'manager_id')->widget(Select2::classname(), [
            'data' => $managerList,
            'options' => [
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],            
        ]); 
    endif; ?>
    
    <?php /*
    <div class="padding-v-md">
        <div class="line line-dashed"></div>
    </div> 
     * 
     */?>
    
    <?php
        echo Html::beginTag('div', [
            'id' => 'bank-data-container',
            'class' => 'form-group',
        ]);
        if(!$isModal){
            echo Html::beginTag('div', [
                'class' => 'col-md-3',
            ]);
            echo Html::endTag('div');
        }
        echo Html::beginTag('div', [
            'class' => 'col-md-'.(!$isModal ? '9' : '12'),
        ]);
    ?>
    
    <?= $this->render('_bank_table_edit', [
        'form' => $form,
        'model' => $clientBankModel,
        'bankList' => $bankList,
        'isModal' => $isModal,
    ]) ?>

    <?php
        echo Html::endTag('div');
        echo Html::endTag('div');           
    ?>     
    
    <?= $form->field($model, 'debit', [
        'addon' => [
            'append' => [
                'content' => Select2::widget([
                    'model' => $model,
                    'attribute' => 'debit_valuta_id',
                    'data' => $valutaList, 
                    'options' => [
                        'id' => 'debit-valuta-id', 
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
        'mask' => '[-]9{1,10}[.9{1,2}]',
        'options' => [
            'class' => 'form-control number-field',
        ],
    ])->label($model->getAttributeLabel('debit').' / '.$model->getAttributeLabel('debit_valuta_id'));
    ?>
    
    <?php if(!$isModal) :
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
                'allowedFileExtensions' => ['png', 'jpg', 'jpeg'],
                'maxFileSize' => 5000,
                'showRemove' => false,
                'showUpload' => false,
                'initialPreview' => $preview,
                'initialPreviewShowDelete' => false,                
                'initialPreviewAsData' => true,
                'initialPreviewConfig' => [
                    [
                        'type' => "image",
                        'size' => $filesModel->filesize, 
                        'caption' => $filesModel->filename, 
                        //'url' => "$urlD", 
                        //'key' => 101,
                    ],
                ],
                //'overwriteInitial' => false, 
            ],
        ])->label(Yii::t('files', 'Logo')); 
    endif; ?>
    
    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>
    
    <?php if(!empty($model->id) && !empty($isAdmin)){
        echo $form->field($model, 'deleted')->widget(SwitchInput::classname(), [
            'pluginOptions' => [
                'onText' => Yii::t('common', 'Yes'),
                'offText' => Yii::t('common', 'No'),
            ],
        ]);
    } ?>
    
    <?php if(!isset($fromAbonent)) : ?>         
    <div class="form-group clearfix double-line-top">
        <div class="col-lg-offset-8 col-lg-4" style="text-align: right;">
            <?php if(empty($registerAction)) :
                echo $model->SubmitButton; 
            else :
                echo Html::submitButton(Yii::t('user', 'Sign up'), ['class' => 'btn btn-lg btn-success']); 
            endif; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>
    <?php endif; ?>

<?php if(!isset($fromAbonent)) : ?>    
<?php ActiveForm::end(); ?>
<?php endif; ?>
<?php if($isModal) : Pjax::end(); endif; ?>
</div>