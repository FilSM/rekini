<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\address\search\AddressSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="address-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'version') ?>

    <?= $form->field($model, 'customer_address') ?>

    <?php //echo $form->field($model, 'country_id') ?>

    <?php //echo $form->field($model, 'region_id') ?>

    <?php //echo $form->field($model, 'city_id') ?>

    <?php //echo $form->field($model, 'district_id') ?>

    <?php //echo $form->field($model, 'contact_person') ?>

    <?php //echo $form->field($model, 'contact_phone') ?>

    <?php //echo $form->field($model, 'contact_email') ?>

    <?php //echo $form->field($model, 'street_number') ?>

    <?php //echo $form->field($model, 'route') ?>

    <?php //echo $form->field($model, 'sublocality_level_1') ?>

    <?php //echo $form->field($model, 'sublocality') ?>

    <?php //echo $form->field($model, 'locality') ?>

    <?php //echo $form->field($model, 'administrative_area_level_1') ?>

    <?php //echo $form->field($model, 'country') ?>

    <?php //echo $form->field($model, 'postal_code') ?>

    <?php //echo $form->field($model, 'latitude') ?>

    <?php //echo $form->field($model, 'longitude') ?>

    <?php //echo $form->field($model, 'formated_address') ?>

    <?php //echo $form->field($model, 'deleted')->widget(SwitchInput::classname(), [
//             'pluginOptions' => [
//                 'onText' => Yii::t('common', 'Yes'),
//                 'offText' => Yii::t('common', 'No'),
//             ],
//         ]) ?>

    <?php //echo $form->field($model, 'create_time') ?>

    <?php //echo $form->field($model, 'create_user_id') ?>

    <?php //echo $form->field($model, 'update_time') ?>

    <?php //echo $form->field($model, 'update_user_id') ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('common', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('common', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>