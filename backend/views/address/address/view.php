<?php

use kartik\helpers\Html;
use kartik\detail\DetailView;

use common\models\user\FSMUser;

/* @var $this yii\web\View */
/* @var $model common\models\address\Address */

$this->title = $model->customer_address;;
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="address-view">

    <h1><?= Html::encode($model->customer_address) ?></h1>

    <p>
        <?= Html::a(Yii::t('common', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= \common\components\FSMBtnDialog::button(Yii::t('common', 'Delete'), ['delete', 'id' => $model->id], [
            'id' => 'btn-dialog-selected',
            'class' => 'btn btn-danger',
        ]); ?>        
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'address_type',
            'customer_address',
            [
                'attribute' => 'country_id',
                'value' => !empty($model->country_id) ? Html::a(Html::encode($model->country0->name), ['country/view', 'id' => $model->country_id]) : null,
                'format' => 'raw',
            ],
            [
                'attribute' => 'region_id',
                'value' => !empty($model->region_id) ? Html::a(Html::encode($model->region->name), ['region/view', 'id' => $model->region_id]) : null,
                'format' => 'raw',
            ],
            [
                'attribute' => 'city_id',
                'value' => !empty($model->city_id) ? Html::a(Html::encode($model->city->name), ['city/view', 'id' => $model->city_id]) : null,
                'format' => 'raw',
            ],
            [
                'attribute' => 'district_id',
                'value' => !empty($model->district_id) ? Html::a(Html::encode($model->district->name), ['/district/view', 'id' => $model->district_id]) : null,
                'format' => 'raw',
            ],
            'company_name',
            'contact_person',
            'contact_phone',
            'contact_email:email',
            
            'street_number',
            'route',
            'district',
            'political',
            'sublocality_level_1',
            'sublocality',
            'locality',
            'administrative_area_level_1',
            'country',
            'postal_code',
            'latitude',
            'longitude',
            'formated_address:ntext',
            
            [
                'attribute' => 'deleted',
                'format' => 'boolean',
                'visible' => FSMUser::getIsPortalAdmin(),
            ],
            /*
            'create_time',
            'create_user_id',
            'update_time',
            'update_user_id',
             * 
             */
        ],
    ]) ?>

</div>