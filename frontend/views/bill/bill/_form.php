<?php
namespace common\models;

use Yii;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\MaskedInput;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;
use kartik\widgets\DatePicker;

use common\widgets\GeocodeAutocompleteInput;
use common\widgets\EnumInput;
use common\models\bill\Bill;

/* @var $this yii\web\View */
/* @var $model common\models\bill\Bill */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal);
?>

<div class="bill-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => $model->tableName().'-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
    ]); ?>

    <?= Html::activeHiddenInput($model, 'id', ['id' => 'bill-id', 'value' => $model->id]); ?>
    <?= $form->field($model, 'project_id', [
        'template' => '<div class="col-md-2"></div><div class="col-md-10">{input}</div>{error}{hint}',
        'options' => [
            'id' => 'project-static-text',
            'class' => 'form-group',
            'style' => 'display: '.((isset($model->project_id) && ($model->project->vat_taxable == 0)) ? 'block' : 'none').'; font-size: x-large; color: red; text-shadow: 1px 1px 2px grey;',
        ],
        'staticValue' => ((isset($model->project_id) && ($model->project->vat_taxable == 0)) ? YII::t('javascript', 'This project is non taxable and you will not have the opportunity to charge VAT') : ''),
    ])->staticInput()->label(false); ?>

    <?= $form->field($model, 'project_id')->widget(Select2::classname(), [
            'data' => $projectList,
            'options' => [
                'id' => 'project-id',
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => !$model->isAttributeRequired('project_id'),
            ],            
            'addon' => in_array($model->doc_type, [
                    Bill::BILL_DOC_TYPE_CRBILL, 
                    Bill::BILL_DOC_TYPE_DEBT, 
                    Bill::BILL_DOC_TYPE_CESSION
                ]) ? 
                [] : 
                [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'controller' => 'project',
                    'isModal' => $isModal,
                ]),
            ],     
            'disabled' => in_array($model->doc_type, [
                Bill::BILL_DOC_TYPE_CRBILL, 
                Bill::BILL_DOC_TYPE_DEBT, 
                Bill::BILL_DOC_TYPE_CESSION
            ]),
        ]); 
    ?>

    <?= $form->field($model, 'agreement_id'/*, ['enableAjaxValidation' => false]*/)->widget(DepDrop::classname(), [
        'type' => DepDrop::TYPE_SELECT2,
        'data' => empty($model->agreement_id) ? null : [$model->agreement_id => $model->agreement->number],
        //'options' => ['placeholder' => '...'],
        'select2Options' => [
            'pluginOptions' => [
                'allowClear' => !$model->isAttributeRequired('agreement_id'),
            ],
            'addon' => in_array($model->doc_type, [
                    Bill::BILL_DOC_TYPE_CRBILL, 
                    Bill::BILL_DOC_TYPE_DEBT, 
                    Bill::BILL_DOC_TYPE_CESSION
                ]) ? 
                [] : 
                [
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
                        Url::to(['/agreement/view', 'id' => $model->agreement_id]),
                        [
                            'id' => 'btn-view-agreement', 
                            'class'=>'btn btn-info',
                            'target' => '_blank',
                            'style' => empty($model->agreement_id) ? 'display: none;' : '',
                            //'disabled' => empty($model->agreement_id),
                        ]
                    ),
                    'asButton' => true, 
                ],
            ], 
            'pluginEvents' => [
                "depdrop:change" => "function() {
                    var form = $('#{$model->tableName()}' + '-form');
                    var id = $(this).val();
                    var emptyId = empty(id);

                    var btnViewAgreement = form.find('#btn-view-agreement');
                    var href = btnViewAgreement.attr('href').split('?');
                    var display = (emptyId ? 'none' : 'inline-block');
                    btnViewAgreement.attr('href', href[0] + '?id=' + id);
                    btnViewAgreement.css({'display': display});
                    
                    var btnViewClient = form.find('#btn-view-first-client');
                    href = btnViewClient.attr('href').split('?');
                    btnViewClient.attr('href', href[0]);
                    btnViewClient.prop({disabled: true});

                    btnViewClient = form.find('#btn-view-second-client');
                    btnViewClient.attr('href', href[0]);
                    btnViewClient.prop({disabled: true});

                    if (emptyId){
                        form.find('#first-client-role').val('');
                        form.find('#first-client-id').val('');
                        form.find('#first-client-name').val('');
                        form.find('#first-client-reg').val('');
                        form.find('#first-client-vat').val('');
                        form.find('#first-client-address').val('');

                        form.find('#second-client-role').val('');
                        form.find('#second-client-id').val('');
                        form.find('#second-client-name').val('');
                        form.find('#second-client-reg').val('');
                        form.find('#second-client-vat').val('');
                        form.find('#second-client-address').val('');

                        form.find('#first-client-id').trigger('depdrop:change');
                        form.find('#second-client-id').trigger('depdrop:change');

                        return false;
                    }

                    var url = '".Url::to(['/agreement/ajax-get-model'])."';
                    $.get(
                        url,
                        {id: id}, 
                        function (data) {
                            var agreement = data.agreement;
                            var first_client = data.first_client;
                            var second_client = data.second_client;
                            
                            form.find('#first-client-role').val(data.first_client_role);
                            form.find('#first-client-id').val(first_client.id);
                            form.find('#first-client-name').val(first_client.name);
                            form.find('#first-client-reg').val(first_client.reg_number);
                            form.find('#first-client-vat').val(first_client.vat_number);
                            form.find('#first-client-address').val(data.first_client_address);

                            form.find('#second-client-role').val(data.second_client_role);
                            form.find('#second-client-id').val(second_client.id);
                            form.find('#second-client-name').val(second_client.name);
                            form.find('#second-client-reg').val(second_client.reg_number);
                            form.find('#second-client-vat').val(second_client.vat_number);
                            form.find('#second-client-address').val(data.second_client_address);

                            form.find('#first-client-id').trigger('depdrop:change');
                            form.find('#second-client-id').trigger('depdrop:change');
                            
                            var btnViewClient = form.find('#btn-view-first-client');
                            var href = btnViewClient.attr('href').split('?');
                            btnViewClient.attr('href', href[0]+'?id='+first_client.id);
                            btnViewClient.attr('disabled', empty(first_client.id));

                            btnViewClient = form.find('#btn-view-second-client');
                            btnViewClient.attr('href', href[0]+'?id='+second_client.id);
                            btnViewClient.attr('disabled', empty(second_client.id));
                            
                            var docDate = form.find('#bill-doc_date').val();
                            var dueDate = date('d-M-Y', strtotime('+' + agreement.deferment_payment + ' days', strtotime(docDate)));
                            form.find('#bill-pay_date').val(dueDate);
                        }
                    );
                    //alert('selected id = ' + clientId); 
                }",
            ],  
            'disabled' => in_array($model->doc_type, [
                Bill::BILL_DOC_TYPE_CRBILL, 
                Bill::BILL_DOC_TYPE_DEBT, 
                Bill::BILL_DOC_TYPE_CESSION
            ]),                            
        ],
        'pluginOptions' => [
            'depends' => ['project-id'],
            //'initialize' => true,            
            //'initDepends' => ['project-id'],
            'url' => Url::to(['/project/ajax-get-agreement-list']),
            'placeholder' => '...',
        ],
    ]); ?>

    <fieldset id="client-data" style="margin-bottom: 10px;">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-10">
                <div class="col-md-6 double-line-top double-line-bottom first-client-data" style="background: aliceblue;">
                    <div class="col-md-12" style="padding: 0;">
                        <div class="field-group-title"><h3 id="first-party-title"><?= Yii::t('bill', 'First party data'); ?></h3></div>
                    </div>

                    <div class="col-md-12" style="padding: 0;">
                        <?= Html::activeHiddenInput($firstClientModel, 'id', ['id' => 'first-client-id']); ?>

                        <div class="form-group">
                            <?= Html::label(Yii::t('bill', 'Party role'), 'first-client-address', ['class' => 'control-label col-md-3']); ?>
                            <div class="col-md-9">
                                <?= Html::textInput('first_client_role', 
                                    (!empty($agreementModel->first_client_role_id) ? $agreementModel->firstClientRole->name : null), [
                                    'id' => 'first-client-role', 
                                    'class' => 'form-control', 
                                    'disabled' => true,
                                ]); ?>
                            </div>
                        </div>                        
                        
                        <?= $form->field($firstClientModel, 'name',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                'wrapper' => 'col-md-9',
                            ], 
                            'template' => '{label} <div class="col-md-9">{input}</div>',
                            'addon' => [
                                'prepend' => [
                                    'content' => 
                                    Html::a(Html::icon('eye-open'), 
                                        Url::to(['/client/view', 'id' => $firstClientModel->id]),
                                        [
                                            'id' => 'btn-view-first-client', 
                                            'class'=>'btn btn-info',
                                            'target' => '_blank',
                                            //'style' => empty($firstClientModel->id) ? 'display: none;' : '',
                                            'disabled' => empty($firstClientModel->id),
                                        ]
                                    ),
                                    'asButton' => true, 
                                ],
                            ],
                        ])->textInput([
                            'id' => 'first-client-name', 
                            'disabled' => true,
                            'style' => 'font-weight: bold; font-size: x-large;',
                        ])->label(Yii::t('client', 'Full name')); 
                        ?>    
                        
                        <?= $form->field($firstClientModel, 'reg_number',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                'wrapper' => 'col-md-9',
                            ], 
                            'template' => '{label} <div class="col-md-9">{input}</div>',
                        ])->textInput([
                            'id' => 'first-client-reg',
                            'disabled' => true,
                        ])->label(Yii::t('client', 'Reg.number')); 
                        ?>
                        
                        <?= $form->field($firstClientModel, 'vat_number',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                'wrapper' => 'col-md-9',
                            ], 
                            'template' => '{label} <div class="col-md-9">{input}</div>',
                        ])->textInput([
                            'id' => 'first-client-vat',
                            'disabled' => true,
                        ])->label(Yii::t('client', 'VAT number')); 
                        ?>

                        <?= $form->field($firstClientModel, 'legal_address',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                'wrapper' => 'col-md-9',
                            ], 
                            'template' => '{label} <div class="col-md-9">{input}</div>',
                        ])->textInput([
                            'id' => 'first-client-address',
                            'value' => $firstClientModel->legal_address,
                            'disabled' => true,
                        ])->label(Yii::t('client', 'Legal address')); 
                        ?>

                        <?= $form->field($model, 'first_client_bank_id',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                //'offset' => 'col-sm-offset-4',
                                'wrapper' => 'col-md-9',
                                //'error' => '',
                                //'hint' => '',
                            ],                            
                        ])->widget(DepDrop::classname(), [
                            'type' => DepDrop::TYPE_SELECT2,
                            'data' => empty($model->first_client_bank_id) ? null : [$model->first_client_bank_id => $model->firstClientBank->bank->name . ' | ' . $model->firstClientBank->account . (!empty($model->firstClientBank->name) ? ' ( '.$model->firstClientBank->name . ' )' : '')],
                            'select2Options' => [
                                'pluginOptions' => [
                                    'allowClear' => !$model->isAttributeRequired('first_client_bank_id'),
                                ],
                            ],
                            'pluginOptions' => [
                                'depends' => ['first-client-id'],
                                'initDepends' => ['first-client-id'],
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
                         ])->label(Yii::t('bill', 'Bank account')); ?>
                        
                        <?= $form->field($model, 'first_client_person_id',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                'wrapper' => 'col-md-9',
                            ],                            
                        ])->widget(DepDrop::classname(), [
                            'type' => DepDrop::TYPE_SELECT2,
                            'data' => empty($model->first_client_person_id) ? null : 
                                [$model->first_client_person_id => $model->firstClientPerson->first_name . ' ' . $model->firstClientPerson->last_name . 
                                    (!empty($model->firstClientPerson->position_id) ? ' ( '.$model->firstClientPerson->position->name . ' )' : '')],
                            'select2Options' => [
                                'pluginOptions' => [
                                    'allowClear' => !$model->isAttributeRequired('first_client_person_id'),
                                ],
                            ],
                            'pluginOptions' => [
                                'depends' => ['first-client-id'],
                                'initDepends' => ['first-client-id'],
                                'initialize' => true,            
                                'url' => Url::to(['/client/ajax-get-client-person-list', 'can_sign' => true]),
                                'placeholder' => '...',
                            ],
                         ])->label(Yii::t('client', 'Signing person')); ?>
                    </div>    
                </div>

                <div class="col-md-6 double-line-top double-line-bottom second-client-data" style="background: antiquewhite;">
                    <div class="col-md-12" style="padding: 0;">
                        <div class="field-group-title"><h3 id="second-party-title"><?= Yii::t('bill', 'Second party data'); ?></h3></div>
                    </div>

                    <div class="col-md-12" style="padding: 0;">
                        <?= Html::activeHiddenInput($secondClientModel, 'id', ['id' => 'second-client-id']); ?>
                        
                        <div class="form-group">
                            <?= Html::label(Yii::t('bill', 'Party role'), 'second-client-role', ['class' => 'control-label col-md-3']); ?>
                            <div class="col-md-9">
                                <?= Html::textInput('second_client_role', 
                                    (!empty($agreementModel->second_client_role_id) ? $agreementModel->secondClientRole->name : null), [
                                    'id' => 'second-client-role', 
                                    'class' => 'form-control', 
                                    'disabled' => true,
                                ]); ?>
                            </div>
                        </div>                        

                        <?= $form->field($secondClientModel, 'name',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                'wrapper' => 'col-md-9',
                            ],  
                            'template' => '{label} <div class="col-md-9">{input}</div>',
                            'addon' => [
                                'prepend' => [
                                    'content' => 
                                    Html::a(Html::icon('eye-open'), 
                                        Url::to(['/client/view', 'id' => $secondClientModel->id]),
                                        [
                                            'id' => 'btn-view-second-client', 
                                            'class'=>'btn btn-info',
                                            'target' => '_blank',
                                            //'style' => empty($secondClientModel->id) ? 'display: none;' : '',
                                            'disabled' => empty($secondClientModel->id),
                                        ]
                                    ),
                                    'asButton' => true, 
                                ],
                            ],
                        ])->textInput([
                            'id' => 'second-client-name', 
                            //'value' => (!empty($secondClientModel->id) ? $secondClientModel->name : null),
                            'disabled' => true,
                            'style' => 'font-weight: bold; font-size: x-large;',
                        ])->label(Yii::t('client', 'Full name')); 
                        ?>
                        
                        <?= $form->field($secondClientModel, 'reg_number',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                'wrapper' => 'col-md-9',
                            ], 
                            'template' => '{label} <div class="col-md-9">{input}</div>',
                        ])->textInput([
                            'id' => 'second-client-reg',
                            'disabled' => true,
                        ])->label(Yii::t('client', 'Reg.number')); 
                        ?>
                        
                        <?= $form->field($secondClientModel, 'vat_number',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                'wrapper' => 'col-md-9',
                            ], 
                            'template' => '{label} <div class="col-md-9">{input}</div>',
                        ])->textInput([
                            'id' => 'second-client-vat',
                            'disabled' => true,
                        ])->label(Yii::t('client', 'VAT number')); 
                        ?>

                        <?= $form->field($secondClientModel, 'legal_address',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                'wrapper' => 'col-md-9',
                            ], 
                            'template' => '{label} <div class="col-md-9">{input}</div>',
                        ])->textInput([
                            'id' => 'second-client-address',
                            'value' => $secondClientModel->legal_address,
                            'disabled' => true,
                        ])->label(Yii::t('client', 'Legal address')); 
                        ?>

                        <?= $form->field($model, 'second_client_bank_id',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                //'offset' => 'col-sm-offset-4',
                                'wrapper' => 'col-md-9',
                                //'error' => '',
                                //'hint' => '',
                            ],                            
                        ])->widget(DepDrop::classname(), [
                            'type' => DepDrop::TYPE_SELECT2,
                            'data' => empty($model->second_client_bank_id) ? null : [$model->second_client_bank_id => $model->secondClientBank->bank->name . ' | ' . $model->secondClientBank->account . (!empty($model->secondClientBank->name) ? ' ( '.$model->secondClientBank->name . ' )' : '')],
                            'select2Options' => [
                                'pluginOptions' => [
                                    'allowClear' => !$model->isAttributeRequired('second_client_bank_id'),
                                ],
                            ],
                            'pluginOptions' => [
                                'depends' => ['second-client-id'],
                                'initDepends' => ['second-client-id'],
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
                         ])->label(Yii::t('bill', 'Bank account')); ?>
                        
                        <?= $form->field($model, 'second_client_person_id',[
                            'horizontalCssClasses' => [
                                'label' => 'col-md-3',
                                //'offset' => 'col-sm-offset-4',
                                'wrapper' => 'col-md-9',
                                //'error' => '',
                                //'hint' => '',
                            ],                            
                        ])->widget(DepDrop::classname(), [
                            'type' => DepDrop::TYPE_SELECT2,
                            'data' => empty($model->second_client_person_id) ? null : 
                                [$model->second_client_person_id => $model->secondClientPerson->first_name . ' ' . $model->secondClientPerson->last_name . 
                                    (!empty($model->secondClientPerson->position_id) ? ' ( '.$model->secondClientPerson->position->name . ' )' : '')],
                            'select2Options' => [
                                'pluginOptions' => [
                                    'allowClear' => !$model->isAttributeRequired('second_client_person_id'),
                                ]
                            ],
                            'pluginOptions' => [
                                'depends' => ['second-client-id'],
                                'initDepends' => ['second-client-id'],
                                'initialize' => true,            
                                'url' => Url::to(['/client/ajax-get-client-person-list', 'can_sign' => true]),
                                'placeholder' => '...',
                            ]
                         ])->label(Yii::t('client', 'Signing person')); ?>
                    </div>    
                </div>            
            </div>
        </div>
    </fieldset>
    
    <?php if(in_array($model->doc_type, [
                Bill::BILL_DOC_TYPE_CRBILL, 
                Bill::BILL_DOC_TYPE_DEBT, 
                Bill::BILL_DOC_TYPE_CESSION
            ]) || 
            !empty($hideDocType)) : ?>
        <?= Html::activeHiddenInput($model, 'doc_type'); ?>
    <?php else: ?>
        <?= $form->field($model, 'doc_type')->widget(EnumInput::classname(), [
                'type' => EnumInput::TYPE_RADIOBUTTON,
                'options' => [
                    'translate' => $model->billDocTypeList,
                ],
                'without' => [Bill::BILL_DOC_TYPE_CRBILL],
            ]); 
        ?>      
    <?php endif; ?>
    
    <?= $form->field($model, 'doc_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]); 
    ?>

    <?= $form->field($model, 'pay_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]); 
    ?>
    
    <?= $form->field($model, 'services_period')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'according_contract')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('common', 'Yes'),
            'offText' => Yii::t('common', 'No'),
        ],
        'pluginEvents' => [
            "switchChange.bootstrapSwitch" => "function() { console.log('switchChange'); }",
        ],
    ]); ?>
    
    <?php
        echo Html::beginTag('div', [
            'id' => 'product-data-container',
            'class' => 'form-group',
        ]);
        echo Html::beginTag('div', [
            'class' => 'col-md-2',
        ]);
        echo Html::endTag('div');
        echo Html::beginTag('div', [
            'class' => 'col-md-10',
        ]);
    ?>

    <?= $this->render('_product_table_edit', [
        'form' => $form,
        'model' => $billProductModel,
        'invoiceModel' => $model,
        'productList' => $productList,
        'measureList' => $measureList,
        'isModal' => $isModal,
    ]) ?>

    <?php
        echo Html::endTag('div');
        echo Html::endTag('div');           
    ?> 

    <?php
        echo Html::beginTag('div', [
            'id' => 'summa-data-container',
        ]);
        
        echo $form->field($model, 'summa')/*->textInput([
            'readonly' => true,
        ])*/->widget(MaskedInput::classname(), [
            'mask' => '9{1,10}[.9{1,2}]',
            'options' => [
                'class' => 'form-control number-field',
                'readonly' => true,
                //'disabled' => !$model->according_contract,
            ],
        ]);
        
        echo $form->field($model, 'vat')/*->textInput([
            'readonly' => true,
        ])*/->widget(MaskedInput::classname(), [
            'mask' => '9{1,10}[.9{1,2}]',
            'options' => [
                'class' => 'form-control number-field',
                'readonly' => true,
                //'disabled' => !$model->according_contract,
            ],
        ]);
        
        
        if(!$model->according_contract) :
            //echo Html::activeHiddenInput($model, 'summa');
            //echo Html::activeHiddenInput($model, 'vat');
        else :
            
        endif;
        echo Html::endTag('div');           
    ?>     
    
    <?= $form->field($model, 'total', [
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
    ])->textInput([
        'readonly' => true,
    ])/*->widget(MaskedInput::classname(), [
        'mask' => '9{1,10}[.9{1,2}]',
        'options' => [
            'class' => 'form-control number-field',
            'readonly' => true,
            //'disabled' => true,
        ],
    ])*/->label($model->getAttributeLabel('total').' / '.$model->getAttributeLabel('valuta_id'));
    ?>
    
    <div id="waybill-data">
        <?= $form->field($model, 'loading_address')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'unloading_address')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'carrier')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'transport')->textInput(['maxlength' => true]) ?>
    </div>

    <?= $form->field($model, 'manager_id')->widget(Select2::classname(), [
        'data' => $managerList,
        'options' => [
            'placeholder' => '...',
        ],   
        'pluginOptions' => [
            'allowClear' => !$model->isAttributeRequired('manager_id'),
        ],        
    ]); ?>
    
    <?= $form->field($model, 'language_id')->widget(Select2::classname(), [
        'data' => $languageList,
        'options' => [
            'placeholder' => '...',
        ],
        'pluginOptions' => [
            'allowClear' => boolval(!$model->isAttributeRequired('language_id')),
        ],            
    ]); ?>

    <?php if(!empty($model->id) && !empty($isAdmin)) :
        echo $form->field($model, 'status')->widget(EnumInput::classname(), [
                'type' => EnumInput::TYPE_RADIOBUTTON,
                'options' => [
                    'translate' => $model->billStatusList,
                ],
            ]); 

        echo $form->field($model, 'pay_status')->widget(EnumInput::classname(), [
                'type' => EnumInput::TYPE_RADIOBUTTON,
                'options' => [
                    'translate' => $model->billPayStatusList,
                ],
                'without' => [Bill::BILL_PAY_STATUS_DELAYED],
            ]); 
        
        echo $form->field($model, 'delayed')->widget(SwitchInput::classname(), [
            'pluginOptions' => [
                'onText' => Yii::t('common', 'Yes'),
                'offText' => Yii::t('common', 'No'),
            ],
        ]);
    else: ?>
        <?= Html::activeHiddenInput($model, 'status'); ?>
        <?= Html::activeHiddenInput($model, 'pay_status'); ?>
        <?= Html::activeHiddenInput($model, 'delayed'); ?>
    <?php endif; ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

    <?php if(!empty($model->id) && !empty($isAdmin)){
        echo $form->field($model, 'deleted')->widget(SwitchInput::classname(), [
            'pluginOptions' => [
                'onText' => Yii::t('common', 'Yes'),
                'offText' => Yii::t('common', 'No'),
            ],
        ]);
    } ?>

    <div class="form-group">
        <div class="col-lg-offset-9 col-lg-3" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>