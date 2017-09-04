<?php
namespace common\models;

use Yii;
use yii\widgets\Pjax;
use yii\widgets\MaskedInput;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;

/* @var $this yii\web\View */
/* @var $model common\models\bill\BillPayment */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal);
?>

<div class="bill-payment-form">
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

    <?= $form->field($historyModel, 'bill_id', [
        'options' => [
            'id' => 'bill-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($historyModel->bill_id) ? $historyModel->bill->doc_number : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($historyModel, 'create_time', [
        'options' => [
            'id' => 'create-time-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($historyModel->create_time) ? date('d-M-Y H:i:s', strtotime($historyModel->create_time)) : date('d-M-Y H:i:s')),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($historyModel, 'create_user_id', [
        'options' => [
            'id' => 'create-user-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($historyModel->create_user_id) ? $historyModel->createUserProfile->name : Yii::$app->user->identity->profile->name),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'payment_order_id')->widget(Select2::classname(), [
            'data' => $paymentOrderList,
            'options' => [
                'id' => 'payment-order-id',
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => !$model->isAttributeRequired('payment_order_id'),
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'controller' => 'payment-order',
                    'isModal' => $isModal,
                ]),
            ],        
        ]); 
    ?>
    
    <?= $form->field($model, 'summa')->widget(MaskedInput::classname(), [
        'mask' => '9{1,10}[.9{1,2}]',
        'options' => [
            'class' => 'form-control number-field',
        ],
    ]); ?>
    
    <?php if(!empty($model->id) && !empty($isAdmin)){
        echo $form->field($model, 'confirmed')->widget(SwitchInput::classname(), [
                'pluginOptions' => [
                    'onText' => Yii::t('common', 'Yes'),
                    'offText' => Yii::t('common', 'No'),
                ],
        ]);
    } ?>    
    
    <?= $form->field($historyModel, 'comment')->textarea(['rows' => 6]) ?>

    <div class="form-group <?php if($isModal) : echo 'modal-button-group'; endif; ?>">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SaveButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php if($isModal) : Pjax::end(); endif; ?>
</div>