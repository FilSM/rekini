<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2; 

/**
 * @var yii\web\View $this
 * @var common\models\address\search\RegionSearch $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="region-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

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

    <?= $form->field($model, 'name') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('common', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('common', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>