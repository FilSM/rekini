<?php

//use Yii;
use yii\helpers\ArrayHelper;

use kartik\helpers\Html;

use common\models\address\Address;
use common\widgets\AutocompleteGMapInput;

$showLabels = (isset($showLabels) ? $showLabels : true);
$formGroup = (isset($formGroup) ? $formGroup : true);

$options = ArrayHelper::merge(
    [
        'placeholder' => Yii::t('common', 'Type').' '.Address::modelTitle(),
        'maxlength' => 255,
    ],
    (isset($options) ? $options : [])    
);
$pluginOptions = ArrayHelper::merge(
    [
        'visible' => false,
        'controlBtn' => true,
        'myLocationBtn' => '#address_id-btn-my-location',
        'detailInputs' => [
            'street_number' => '#address-street_number input',
            'route' => 'input#address-route',
            'district' => 'input#address-district',
            //'political' => '#address-district input',
            //'sublocality' => '#address-district input',
            //'sublocality_level_1' => '#address-district input',
            'locality' => 'input#address-locality',
            'administrative_area_level_1' => 'input#address-administrative_area_level_1',
            'administrative_area_level_2' => 'input#address-administrative_area_level_1',
            'country' => 'input#address-country',
            'postal_code' => 'input#address-postal_code',
        ],
        'center' => [
            'lat' => (!empty($addressModel->id)? $addressModel->latitude : null), 
            'lng' => (!empty($addressModel->id)? $addressModel->longitude : null), 
        ],
        'zoom' => 15,
    ],
    (isset($pluginOptions) ? $pluginOptions : []) 
);

$hint = !empty($pluginOptions['controlBtn']) ?
        Yii::t('address', 'Type address into the text field and select postal address from Google list or select location on the map.') :
        Yii::t('address', 'Type address into the text field and select postal address from Google list.');
?>

<?= $form->field($addressModel, 'customer_address', [
        'options' => [
            'class' => 'gmap-container'.(!empty($formGroup) ? ' form-group' : '').(!empty($required) ? ' required' : ''),
        ],
        'showLabels' => $showLabels,
        'addon' => [
            'prepend' => !empty($pluginOptions['controlBtn']) ? [
                'content' => 
                    //Html::button(Yii::t('address', 'Open map'), ['id' => 'address_id-btn-open-map', 'class'=>'btn btn-primary btn-open-map']).
                    Html::button(Html::icon('globe'), ['id' => 'address_id-btn-open-map', 'class'=>'btn btn-primary btn-open-map', 'title' => Yii::t('address', 'Open map')]).
                    (!empty($pluginOptions['myLocationBtn']) ?
                        Html::button(Html::icon('map-marker'), ['id' => 'address_id-btn-my-location', 'class' => 'btn btn-success btn-my-location', 'title' => Yii::t('address', 'Get my Location')]) :
                        ''
                    ),
                'asButton' => true, 
            ] : [],
            'append' => [
                'content' => Html::icon('map-marker'),                           
            ], 
        ],
    ])->widget(AutocompleteGMapInput::classname(), [
        'model' => $addressModel,
        'attribute' => 'customer_address',
        'options' => $options,
        'pluginOptions' => $pluginOptions,
    ])
        ->hint($hint)
        ->label(isset($label) ? $label : '');
?>
