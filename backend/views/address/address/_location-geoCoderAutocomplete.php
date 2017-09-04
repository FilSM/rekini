<?php

//use Yii;
use yii\helpers\ArrayHelper;

use kartik\helpers\Html;

use common\models\address\Address;
use common\widgets\GeocodeAutocompleteInput;

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
    ],
    (isset($pluginOptions) ? $pluginOptions : []) 
);

?>

<?= $form->field($addressModel, 'customer_address', [
        'options' => [
            'class' => 'geocoder-container'.(!empty($formGroup) ? ' form-group' : '').(!empty($required) ? ' required' : ''),
        ],
        'showLabels' => $showLabels,
        'addon' => [
            'append' => [
                'content' => Html::icon('map-marker'),                           
            ], 
        ],
    ])->widget(GeocodeAutocompleteInput::classname(), [
        'model' => $addressModel,
        'attribute' => 'customer_address',
        'options' => $options,
        'pluginOptions' => $pluginOptions,
    ])
        ->hint(Yii::t('address', 'Type address into the text field and select postal address from Google list.'))
        ->label(isset($label) ? $label : '');
?>
