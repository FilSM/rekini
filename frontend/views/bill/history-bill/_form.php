<?php
namespace common\models;

use Yii;
use yii\widgets\Pjax;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\bill\HistoryBill */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal);
?>

<div class="history-bill-form">
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

    <?= $form->field($model, 'bill_id', [
        'options' => [
            'id' => 'bill-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->bill_id) ? $model->bill->doc_number : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'action_id', [
        'options' => [
            'id' => 'action-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->action_id) ? $model->billActionList[$model->action_id] : ''),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'create_time', [
        'options' => [
            'id' => 'create-time-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->create_time) ? date('d-M-Y H:i:s', strtotime($model->create_time)) : date('d-M-Y H:i:s')),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?= $form->field($model, 'create_user_id', [
        'options' => [
            'id' => 'create-user-static-text',
            'class' => 'form-group',
        ],
        'staticValue' => (isset($model->create_user_id) ? $model->createUserProfile->name : Yii::$app->user->identity->profile->name),
    ])->staticInput(['class' => 'form-control', 'disabled' => true]); ?>

    <?php /*
    <?= $form->field($model, 'bill_id')->textInput() ?>
    <?= $form->field($model, 'action_id')->textInput() ?>
    <?= $form->field($model, 'create_time')->textInput() ?>
    <?= $form->field($model, 'create_user_id')->textInput() ?>
     * 
     */?>
    
    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

    <div class="form-group <?php if($isModal) : echo 'modal-button-group'; endif; ?>">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SaveButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php if($isModal) : Pjax::end(); endif; ?>
</div>