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
 * @var common\models\address\City $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="city-form">

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

        <?= $form->field($model, 'region_id')->widget(DepDrop::classname(), [
            //'data'=> [6=>'Bank'],
            'type' => DepDrop::TYPE_SELECT2,
            'select2Options' => [
                'pluginOptions' => [
                    'allowClear' => true,
                ]
            ],
            'options' => [
                'id' => 'region-id', 
                'placeholder' => '...',
            ],
            'pluginOptions' => [
                'depends' => ['country-id'],
                'url' => Url::to(['/country/ajax-get-regions']),
                'loadingText' => 'Loading '.Region::modelTitle(2).' ...',
            ]
         ]); ?>
    
    <?php else: ?>
    
        <?php /*= $form->field($model, 'country_id')->textInput()*/ ?>
        <?php /*= $form->field($model, 'region_id')->textInput()*/ ?>
    
        <?= $form->field($model, 'country_name')->textInput(['disabled' => true, 'value' => $model->country->name])->label($model->country->modelTitle()) ?>
        <?= $form->field($model, 'region_name')->textInput(['disabled' => true, 'value' => $model->region->name])->label($model->region->modelTitle()) ?>
    
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
