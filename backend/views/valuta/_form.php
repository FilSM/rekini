<?php
namespace common\models;

use Yii;
use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/**
 * @var yii\web\View $this
 * @var common\models\Valuta $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="valuta-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
    ]); ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => 100]) ?>

    <div class="form-group">
        <div class="col-lg-offset-9 col-lg-3" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>