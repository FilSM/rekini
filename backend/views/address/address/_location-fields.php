<?php
use kartik\helpers\Html;
?>

<?= $form->field($addressModel, 'postal_code', [
    'options' => ['id' => 'address-postal_code'], 
    'addon' => ['append' => ['content' => Html::icon('map-marker'),]]])->textInput(); ?>
<?= $form->field($addressModel, 'country', [
    'options' => ['id' => 'address-country'], 
    'addon' => ['append' => ['content' => Html::icon('map-marker'),]]])->textInput(); ?>
<?= $form->field($addressModel, 'administrative_area_level_1', [
    'options' => ['id' => 'address-administrative_area_level_1'], 
    'addon' => ['append' => ['content' => Html::icon('map-marker'),]]])->textInput(); ?>
<?= $form->field($addressModel, 'locality', [
    'options' => ['id' => 'address-locality'], 
    'addon' => ['append' => ['content' => Html::icon('map-marker'),]]])->textInput(); ?>
<?= $form->field($addressModel, 'district', [
    'options' => ['id' => 'address-district'], 
    'addon' => ['append' => ['content' => Html::icon('map-marker'),]]])->textInput(); ?>
<?= $form->field($addressModel, 'route', [
    'options' => ['id' => 'address-route'],
    'addon' => ['append' => ['content' => Html::icon('map-marker'),]]])->textInput(); ?>
<?= $form->field($addressModel, 'street_number', [
    'options' => ['id' => 'address-street_number'], 
    'addon' => ['append' => ['content' => Html::icon('map-marker'),]]])->textInput(); ?>
<?= $form->field($addressModel, 'apartment_number', [
    'options' => ['id' => 'address-apartment_number'], 
    'addon' => ['append' => ['content' => Html::icon('map-marker'),]]])->textInput(); ?>
