<?php
namespace common\models;

use Yii;
use yii\widgets\Pjax;
use yii\helpers\Url;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\bill\BillConfirm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="bill-confirm-form">
    <?php if($isModal) : Pjax::begin(Yii::$app->params['PjaxModalOptions']); endif; ?>

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => $model->tableName().'-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
        'formConfig' => [
            'labelSpan' => 3,
        ],        
        'options' => [
            'data-pjax' => $isModal,
        ],          
    ]); ?>

    <?= Html::activeHiddenInput($model, 'id'); ?>
    <?= Html::activeHiddenInput($model, 'payment_confirm_id'); ?>
    <?= Html::activeHiddenInput($model, 'history_bill_id'); ?>
    <?= Html::activeHiddenInput($model, 'bill_payment_id'); ?>
    <?= Html::activeHiddenInput($model, 'second_client_id'); ?>
    <?= Html::activeHiddenInput($model, 'currency'); ?>

    <?= $form->field($model, 'bill_id')->widget(Select2::classname(), [
            'data' => $billModelList,
            'options' => [
                'id' => 'bill-id',
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => !$model->isAttributeRequired('bill_id'),
            ],            
            'addon' => [
                'prepend' => [
                    'content' => 
                    $model->getModalButton([
                        'formId' => $form->id,
                        'controller' => 'bill',
                        'isModal' => $isModal,
                    ]).
                    Html::a(Html::icon('eye-open'), 
                        Url::to(['/bill/view', 'id' => $model->bill_id]),
                        [
                            'id' => 'btn-view-bill', 
                            'class'=>'btn btn-info',
                            'target' => '_blank',
                            'data-pjax' => 0,
                            'style' => empty($model->bill_id) ? 'display: none;' : '',
                        ]
                    ),
                    'asButton' => true, 
                ],
            ],
            'pluginEvents' => [
                "change" => "function() {
                    var form = $('#{$model->tableName()}' + '-form');
                    var id = $(this).val();
                    var emptyId = empty(id);

                    var btnViewBill = form.find('#btn-view-bill');
                    var href = btnViewBill.attr('href').split('?');
                    var display = (emptyId ? 'none' : 'inline-block');
                    btnViewBill.prop('href', href[0] + '?id=' + id);
                    btnViewBill.css({'display': display});
                }",
            ],        
        ]); 
    ?>
    
    <?= $form->field($model, 'first_client_account', [
        'options' => [
            'id' => 'first-client-account-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->first_client_account) ? $model->first_client_account : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'second_client_name', [
        'options' => [
            'id' => 'second-client-name-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->second_client_name) ? $model->second_client_name : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'second_client_reg_number', [
        'options' => [
            'id' => 'second-client-reg-number-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->second_client_reg_number) ? $model->second_client_reg_number : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'second_client_account', [
        'options' => [
            'id' => 'second-client-account-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->second_client_account) ? $model->second_client_account : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'doc_date', [
        'options' => [
            'id' => 'doc-date-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->doc_date) ? $model->doc_date : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'doc_number', [
        'options' => [
            'id' => 'doc-number-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->doc_number) ? $model->doc_number : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'direction', [
        'options' => [
            'id' => 'direction-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->direction) ? $model->directionList[$model->direction] : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'summa', [
        'options' => [
            'id' => 'summa-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->summa) ? number_format($model->summa, 2).(!empty($model->currency) ? ' '.$model->currency : '') : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'comment')->textarea(['rows' => 6, 'disabled' => true]) ?>

    <div class="form-group <?php if($isModal) : echo 'modal-button-group'; endif; ?>">
        <div class="col-lg-offset-3 col-md-9" style="text-align: right;">
            <?= $model->SaveButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php if($isModal) : Pjax::end(); endif; ?>

</div>