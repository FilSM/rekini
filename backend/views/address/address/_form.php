<?php
namespace common\models\address;

use Yii;
use yii\helpers\Url;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\DepDrop;
use kartik\widgets\Select2;

use common\widgets\AutocompleteGMapInput;

/* @var $this yii\web\View */
/* @var $model common\models\address\Address */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="address-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
    ]); ?>

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
        'type' => DepDrop::TYPE_SELECT2,
        'data' => !empty($model->region_id) ? [ $model->region_id => $model->region->name] : ['' => ''],
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
            'loadingText' => Region::modelTitle(2).' loading...',
        ]
     ]); ?>

    <?= $form->field($model, 'city_id')->widget(DepDrop::classname(), [
        'type' => DepDrop::TYPE_SELECT2,
        'data' => !empty($model->city_id) ? [ $model->city_id => $model->city->name] : ['' => ''],
        'select2Options' => [
            'pluginOptions' => [
                'allowClear' => true,
            ]
        ],
        'options' => [
            'id' => 'city-id', 
            'placeholder' => '...',
        ],
        'pluginOptions' => [
            'depends' => ['region-id'],
            //'depends' => ['country-id', 'region-id'],
            'url' => Url::to(['/region/ajax-get-cities']),
            'loadingText' => City::modelTitle(2).' loading...',
        ]
     ]); ?>

    <?= $form->field($model, 'district_id')->widget(DepDrop::classname(), [
        'type' => DepDrop::TYPE_SELECT2,
        'data' => !empty($model->district_id) ? [ $model->district_id => $model->district->name] : ['' => ''],
        'select2Options' => [
            'pluginOptions' => [
                'allowClear' => true,
            ]
        ],
        'options' => [
            'id' => 'district-id', 
            'placeholder' => '...',
        ],
        'pluginOptions' => [
            'depends' => ['city-id'],
            //'depends' => ['country-id', 'region-id', 'city-id'],
            'url' => Url::to(['/city/ajax-get-districts']),
            'loadingText' => District::modelTitle(2).' loading...',
        ]
     ]); ?>

    <?= $form->field($model, 'customer_address', [
            'addon' => [
                'prepend' => [
                    'content' => Html::icon('map-marker'),
                ],
            ],
            'options' => [
                'class' => 'gmap-container',
            ],
            'labelOptions' => [
                'label' => Address::modelTitle(),
                'class' => 'col-md-2',
            ],
        ])->widget(AutocompleteGMapInput::classname(), [
            'model' => $model,
            'attribute' => 'customer_address',
            'options' => [
                'placeholder' => Yii::t('common', 'Type').' '.Address::modelTitle(),
                'maxlength' => 255,
            ],
            'pluginOptions' => [
                'center' => [
                    'lat' => (!empty($model->latitude)? $model->latitude : null), 
                    'lng' => (!empty($model->longitude)? $model->longitude : null), 
                ],
                'zoom' => 15,
            ],

        ]);
    ?>
    
    <?= $form->field($model, 'contact_person')->textInput(['maxlength' => 50]) ?>

    <?= $form->field($model, 'contact_phone')->textInput(['maxlength' => 20]) ?>

    <?= $form->field($model, 'contact_email')->textInput(['maxlength' => 50]) ?>

    <?php if(!empty($model->id) && \common\models\user\FSMUser::getIsPortalAdmin()){
        echo $form->field($model, 'deleted', [
            ])->widget(SwitchInput::classname(), [
            'pluginOptions' => [
                'onText' => Yii::t('common', 'Yes'),
                'offText' => Yii::t('common', 'No'),
            ],
        ]); 
    }?>

    <div class="form-group">
        <div class="col-lg-offset-2 col-md-10">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>