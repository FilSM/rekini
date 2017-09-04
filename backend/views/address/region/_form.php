<?php
namespace common\models\address;

use Yii;
use yii\helpers\Url;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\DepDrop; 
use kartik\widgets\Select2; 

/**
 * @var yii\web\View $this
 * @var common\models\address\Region $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="region-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

    <?php if($model->isNewRecord) : ?>

    <?= $form->field($model, 'country_id')->widget(Select2::classname(), [
       'data' => $countryList,
       'options' => [
           'id' => 'country-id',
           'placeholder' => '...',
       ],
       'pluginOptions' => [
           'allowClear' => true
       ],           
    ]); ?>

    <?php else: ?>

    <?= $form->field($model, 'country_name')->textInput(['disabled' => true, 'value' => $model->country->name])->label($model->country->modelTitle()) ?>

    <?php endif; ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => 50]) ?>

    <div class="form-group">
        <div class="col-lg-offset-2 col-md-10">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>