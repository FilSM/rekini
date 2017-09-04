<?php
namespace common\models\address;

use Yii;
use yii\widgets\Pjax;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/**
 * @var yii\web\View $this
 * @var common\models\address\Country $model
 * @var yii\widgets\ActiveForm $form
 */
$isModal = !empty($isModal);
?>

<div class="country-form">
    <?php if($isModal) : Pjax::begin(Yii::$app->params['PjaxModalOptions']); endif; ?>
    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,       
        'options' => [
            'data-pjax' => $isModal,
        ],        
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 50]) ?>

    <?= $form->field($model, 'short_name')->textInput(['maxlength' => 2]) ?>

    <?= $form->field($model, 'currency')->textInput(['maxlength' => 30]) ?>
    
    <div class="form-group <?php if($isModal) : echo 'modal-button-group'; endif; ?>">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php if($isModal) : Pjax::end(); endif; ?>
</div>