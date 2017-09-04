<?php
namespace common\models\address;

use Yii;
use kartik\helpers\Html;
use kartik\grid\GridView;

//use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel common\models\address\search\AddressSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $searchModel->modelTitle(2);
$this->params['breadcrumbs'][] = $this->title;
?>
<div id="address-index" class="page-index">

    <?= Html::pageHeader(Html::encode($this->title));?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
        <?= \common\components\FSMBtnDialog::button(Yii::t('common', 'Delete selected'), ['delete-selected'], [
            'model' => $searchModel,
            'grid' => 'grid-view',
            'id' => 'btn-dialog-selected',
            'class' => 'btn btn-danger',
            'confirm' => Yii::t('common', 'Are you sure you want to delete selected item(s)?'),
            'disabled' => true,
        ]); ?>
    </p>

    <?= GridView::widget([
        'id' => 'grid-view',
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => '\kartik\grid\SerialColumn'],
            [
                'class' => 'yii\grid\CheckboxColumn',
            ],
            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
            ],
            [
                'attribute'=>'address_type',
                'value' => function ($model) {
                    return $model->addressTypeList[$model->address_type];
                },                
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $searchModel->addressTypeList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => Yii::t('common', 'Any...')],
            ],
            [
                'attribute'=>'country_id',
                'value' => function ($model) {
                    return !empty($model->country_id) ? Html::a(Html::encode($model->country0->name), ['country/view', 'id' => $model->country_id]) : null;
                }, 
                'format'=>'raw',        
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $countryList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => Yii::t('common', 'Any...')],
            ],
            [
                'attribute'=>'region_id',
                'value' => function ($model) {
                    return !empty($model->region_id) ? Html::a(Html::encode($model->region->name), ['region/view', 'id' => $model->region_id]) : null;
                }, 
                'format'=>'raw',        
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $regionList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => Yii::t('common', 'Any...')],                        
            ],
            [
                'attribute'=>'city_id',
                'value' => function ($model) {
                    return !empty($model->city_id) ? Html::a(Html::encode($model->city->name), ['city/view', 'id' => $model->city_id]) : null;
                }, 
                'format'=>'raw',        
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $cityList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => Yii::t('common', 'Any...')],                        
            ],
            [
                'attribute'=>'district_id',
                'value' => function ($model) {
                    return !empty($model->district_id) ? Html::a(Html::encode($model->district0->name), ['/district/'.$model->district_id]) : null;
                },
                'format'=>'raw',       
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $districtList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => Yii::t('common', 'Any...')],
            ],            
            'company_name',
            'customer_address',
            'contact_person',
            'contact_phone',
            'contact_email:email',
            // 'street_number',
            // 'route',
            // 'sublocality_level_1',
            // 'sublocality',
            // 'locality',
            // 'administrative_area_level_1',
            // 'country',
            // 'postal_code',
            // 'latitude',
            // 'longitude',
            // 'formated_address:ntext',
            [
                'attribute' => "deleted",                
                'class' => '\kartik\grid\BooleanColumn',
                'trueLabel' => 'Yes', 
                'falseLabel' => 'No',
                'width' => '100px',
            ],
            // 'create_time',
            // 'create_user_id',
            // 'update_time',
            // 'update_user_id',

            ['class' => '\kartik\grid\ActionColumn'],
        ],
    ]); ?>

</div>