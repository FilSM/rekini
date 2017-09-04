<?php

use yii\helpers\Url;

use kartik\widgets\Typeahead;
use kartik\helpers\Html;

$prefix = (isset($prefix) ? $prefix : 'address');
$prefixName = (isset($prefixName) ? $prefixName : 'Address');
$formGroup = (isset($formGroup) ? $formGroup : true);
?>

<?= $form->field($addressModel, 'postal_code', [
        'options' => ['id' => $prefix.'-postal_code-input', 'class' => 'required'.(!empty($formGroup) ? ' form-group' : '')],
        //'addon' => ['append' => ['content' => Html::icon('map-marker'),]]
    ])->widget(Typeahead::classname(), [
    'options' => [
        'id' =>$prefix.'-postal_code',
        'name' => $prefixName.'[postal_code]',
        //'placeholder' => Yii::t('common', 'Start typing to get a list of possible matches.'),
    ],
    'scrollable' => true,
    'pluginOptions' => [
        'highlight' => true,
        'minLength' => 3,
    ],        
    'dataset' => [
        [
            'displayKey' => 'postal_code',
            'remote' => [
                'url' => Url::to(['address/ajax-postal-code-list']).'?q=%QUERY&limit=10',
                'wildcard' => '%QUERY',
            ],
            'limit' => 10,
        ],
    ],
    'pluginEvents' => [
        "typeahead:selected" => "function(_event, _object, _dataset) {
            var form = $('#{$form->id}');
            form.find('input#{$prefix}-district').val(
                _object.district ? _object.district : 
                    (_object.political ? _object.political :
                        (_object.sublocality_level_1 ? _object.sublocality_level_1 :
                            (_object.sublocality ? _object.sublocality : '')
                        )
                    )
                );
                
            var initUrl = {$prefix}_administrative_area_level_1_data_1.remote.initUrl;
            if(!initUrl){
                initUrl = {$prefix}_administrative_area_level_1_data_1.remote.url;
                {$prefix}_administrative_area_level_1_data_1.remote.initUrl = initUrl;
            }
            if(_object.country_id){
                {$prefix}_administrative_area_level_1_data_1.remote.url = initUrl + '+&country_id=' + _object.country_id;
            }
            form.find('input#{$prefix}-country').val(_object.country);
            
            initUrl = {$prefix}_locality_data_1.remote.initUrl;
            if(!initUrl){
                initUrl = {$prefix}_locality_data_1.remote.url;
                {$prefix}_locality_data_1.remote.initUrl = initUrl;
            }
            if(_object.region_id){
                {$prefix}_locality_data_1.remote.url = initUrl + '+&region_id=' + _object.region_id;
            }
            form.find('input#{$prefix}-administrative_area_level_1').val(_object.administrative_area_level_1);
            
            initUrl = {$prefix}_district_data_1.remote.initUrl;
            if(!initUrl){
                initUrl = {$prefix}_district_data_1.remote.url;
                {$prefix}_district_data_1.remote.initUrl = initUrl;
            }
            if(_object.city_id){
                {$prefix}_district_data_1.remote.url = initUrl + '+&city_id=' + _object.city_id;
            }
            form.find('input#{$prefix}-locality').val(_object.locality);
            
            initUrl = {$prefix}_route_data_1.remote.initUrl;
            if(!initUrl){
                initUrl = {$prefix}_route_data_1.remote.url;
                {$prefix}_route_data_1.remote.initUrl = initUrl;
            }
            if(_object.district_id){
                {$prefix}_route_data_1.remote.url = initUrl + '+&district_id=' + _object.district_id;
            }

            //alert('selected id = ' +_object.id);
        }",
    ],
]);?>

<?= $form->field($addressModel, 'country', [
        'options' => ['id' => $prefix.'-country-input', 'class' => (!empty($formGroup) ? ' form-group' : '')],
        //'addon' => ['append' => ['content' => Html::icon('map-marker'),]]
    ])->widget(Typeahead::classname(), [
    'options' => [
        'id' =>$prefix.'-country',
        'name' => $prefixName.'[country]',
        //'placeholder' => Yii::t('common', 'Start typing to get a list of possible matches.'),
    ],
    'scrollable' => true,
    'pluginOptions' => [
        'highlight' => true,
        'minLength' => 3,
    ],        
    'dataset' => [
        [
            'displayKey' => 'name',
            'remote' => [
                'url' => Url::to(['country/ajax-name-list']).'?q=%QUERY',
                'wildcard' => '%QUERY',
            ],
            'limit' => 10,
        ],
    ],
    'pluginEvents' => [
        "typeahead:active" => "function() {
            var initUrl = {$prefix}_administrative_area_level_1_data_1.remote.initUrl;
            if(!initUrl){
                initUrl = {$prefix}_administrative_area_level_1_data_1.remote.url;
                {$prefix}_administrative_area_level_1_data_1.remote.initUrl = initUrl;
            }
        }",
        "typeahead:selected" => "function(_event, _object, _dataset) {
            var initUrl = {$prefix}_administrative_area_level_1_data_1.remote.initUrl;
            {$prefix}_administrative_area_level_1_data_1.remote.url = initUrl + '+&country_id=' + _object.id;

            //alert('selected id = ' +_object.id);
        }",
    ],
]);?>

<?= $form->field($addressModel, 'administrative_area_level_1', [
        'options' => ['id' => $prefix.'-region-input', 'class' => ''.(!empty($formGroup) ? ' form-group' : '')],
        //'addon' => ['append' => ['content' => Html::icon('map-marker'),]]
    ])->widget(Typeahead::classname(), [
    'options' => [
        'id' =>$prefix.'-administrative_area_level_1',
        'name' => $prefixName.'[administrative_area_level_1]',
        //'placeholder' => Yii::t('common', 'Start typing to get a list of possible matches.'),
    ],
    'scrollable' => true,
    'pluginOptions' => [
        'highlight' => true,
        'minLength' => 3,
    ],        
    'dataset' => [
        [
            'displayKey' => 'name',
            'remote' => [
                'url' => Url::to(['region/ajax-name-list']).'?q=%QUERY',
                'wildcard' => '%QUERY',
            ],
            'limit' => 10,
        ],
    ],
    'pluginEvents' => [
        "typeahead:active" => "function() {
            var initUrl = {$prefix}_locality_data_1.remote.initUrl;
            if(!initUrl){
                initUrl = {$prefix}_locality_data_1.remote.url;
                {$prefix}_locality_data_1.remote.initUrl = initUrl;
            }
        }",
        "typeahead:selected" => "function(_event, _object, _dataset) {
            var initUrl = {$prefix}_locality_data_1.remote.initUrl;
            {$prefix}_locality_data_1.remote.url = initUrl + '+&region_id=' + _object.id;

            //alert('selected id = ' +_object.id);
        }",
    ],
]);?>

<?= $form->field($addressModel, 'locality', [
        'options' => ['id' => $prefix.'-city-input', 'class' => (!empty($formGroup) ? ' form-group' : '')],
        //'addon' => ['append' => ['content' => Html::icon('map-marker'),]]
    ])->widget(Typeahead::classname(), [
    'options' => [
        'id' =>$prefix.'-locality',
        'name' => $prefixName.'[locality]',
        //'placeholder' => Yii::t('common', 'Start typing to get a list of possible matches.'),
    ],
    'scrollable' => true,
    'pluginOptions' => [
        'highlight' => true,
        'minLength' => 3,
    ],        
    'dataset' => [
        [
            'displayKey' => 'name',
            'remote' => [
                'url' => Url::to(['city/ajax-name-list']).'?q=%QUERY',
                'wildcard' => '%QUERY',
            ],
            'limit' => 10,
        ],
    ],
    'pluginEvents' => [
        "typeahead:active" => "function() {
            var initUrl = {$prefix}_district_data_1.remote.initUrl;
            if(!initUrl){
                initUrl = {$prefix}_district_data_1.remote.url;
                {$prefix}_district_data_1.remote.initUrl = initUrl;
            }
        }",
        "typeahead:selected" => "function(_event, _object, _dataset) {
            var initUrl = {$prefix}_district_data_1.remote.initUrl;
            {$prefix}_district_data_1.remote.url = initUrl + '+&city_id=' + _object.id;

            //alert('selected id = ' +_object.id);
        }",
    ],
]);?>

<?php /*
<?= $form->field($addressModel, 'district', [
        'options' => ['id' => $prefix.'-district-input', 'class' => ''.(!empty($formGroup) ? ' form-group' : '')],
        //'addon' => ['append' => ['content' => Html::icon('map-marker'),]]
    ])->widget(Typeahead::classname(), [
    'options' => [
        'id' =>$prefix.'-district',
        'name' => $prefixName.'[district]',
        //'placeholder' => Yii::t('common', 'Start typing to get a list of possible matches.'),
    ],
    'scrollable' => true,
    'pluginOptions' => [
        'highlight' => true,
        'minLength' => 3,
    ],        
    'dataset' => [
        [
            'displayKey' => 'name',
            'remote' => [
                'url' => Url::to(['district/ajax-name-list']).'?q=%QUERY',
                'wildcard' => '%QUERY',
            ],
            'limit' => 10,
        ],
    ],
    'pluginEvents' => [
        "typeahead:active" => "function() {
            var initUrl = {$prefix}_route_data_1.remote.initUrl;
            if(!initUrl){
                initUrl = {$prefix}_route_data_1.remote.url;
                {$prefix}_route_data_1.remote.initUrl = initUrl;
            }
        }",
        "typeahead:selected" => "function(_event, _object, _dataset) {
            var initUrl = {$prefix}_route_data_1.remote.initUrl;
            {$prefix}_route_data_1.remote.url = initUrl + '+&district_id=' + _object.id;

            //alert('selected id = ' +_object.id);
        }",
    ],
]);?>
 * 
 */?>

<?= $form->field($addressModel, 'route', [
        'options' => ['id' => $prefix.'-route-input', 'class' => (!empty($formGroup) ? ' form-group' : '')],
        //'addon' => ['append' => ['content' => Html::icon('map-marker'),]]
    ])->widget(Typeahead::classname(), [
    'options' => [
        'id' =>$prefix.'-route',
        'name' => $prefixName.'[route]',
        //'placeholder' => Yii::t('common', 'Start typing to get a list of possible matches.'),
    ],
    'scrollable' => true,
    'pluginOptions' => [
        'highlight' => true,
        'minLength' => 3,
    ],        
    'dataset' => [
        [
            'displayKey' => 'name',
            'remote' => [
                'url' => Url::to(['address/ajax-route-name-list']).'?q=%QUERY&limit=10',
                'wildcard' => '%QUERY',
            ],
            'limit' => 10,
        ],
    ],
]);?>

<?= $form->field($addressModel, 'street_number', [
    'options' => ['id' => $prefix.'-street_number', 'class' => (!empty($formGroup) ? ' form-group' : '')], 
    //'addon' => ['append' => ['content' => Html::icon('map-marker'),]]
    ])->textInput(['name' => $prefixName.'[street_number]']); ?>

<?= $form->field($addressModel, 'apartment_number', [
    'options' => [
        'id' => $prefix.'-apartment_number', 
        'class' => (!empty($formGroup) ? ' form-group' : ''),
        //'style' => 'display: none;',
    ], 
    //'addon' => ['append' => ['content' => Html::icon('map-marker'),]]
    ])->textInput(['name' => $prefixName.'[apartment_number]']); ?>
