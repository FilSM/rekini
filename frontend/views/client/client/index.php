<?php
namespace common\models\client;

use Yii;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

use common\models\user\FSMUser;
use common\models\Bank;

/* @var $this yii\web\View */
/* @var $searchModel common\models\client\search\ClientSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t('client', 'All clients');
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row client-index">
    <div class="col-md-1">
        <?php 
        if(isset($_GET['our_clients']) && !$_GET['our_clients']){
            $activeItem = 'ext_clients';
        }elseif(isset($_GET['our_clients']) && $_GET['our_clients']){
            $activeItem = 'our_clients';
        }elseif(isset($_GET['manager_id']) && ($_GET['manager_id'] == $profileId)){
            $activeItem = 'my_clients';
        }else{
            $activeItem = 'all_client';
        }
        ?>
        <?= $this->render('v_menu', [
            'activeItem' => $activeItem,
            'profileId' => $profileId,
        ])
        ?>
    </div>    
    <div class="col-md-11">
        <?php /*
        <?= Html::pageHeader(Html::encode($this->title));?>
         * 
         */ ?>
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <p>
            <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
        </p>

        <?php 
            $columns = [
                [
                    'class' => '\kartik\grid\SerialColumn',
                    'visible' => false,//!$isAdmin,
                ],
                [
                    'attribute' => 'id',
                    'width' => '75px',
                    'hAlign' => 'center',
                    //'visible' => $isAdmin,
                ],
                /*
                [ 
                    'attribute' => 'create_time', 
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) { 
                        return !empty($model->create_time) ? $model->create_time : null; 
                    }, 
                    'filter' => DateRangePicker::widget([ 
                        'model' => $searchModel, 
                        'attribute' => 'create_time_range',
                        //'convertFormat' => true,
                        //'presetDropdown' => true,
                        'pluginOptions' => [ 
                            'locale' => [
                                'firstDay' => 1,
                                'format' => 'dd-mm-yyyy',
                            ],
                        ]
                    ]), 
                    'visible' => $isAdmin,
                ],
                 * 
                 */
                [
                    'attribute'=>'name',
                    //'headerOptions' => ['class'=>'td-mw-200'],
                    'value' => function ($model) {
                        return Html::a($model->name, ['client/view', 'id' => $model->id], ['target' => '_blank', 'data-pjax' => 0]);
                    },                         
                    'format'=>'raw',
                ],                              
                [
                    'attribute'=>'legal_country_id',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model->legal_country_id) ? $model->country_name : null;
                    },                         
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $countryList,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => ['placeholder' => '...'],
                ],                             
                'reg_number',
                            /*
                'vat_number',
                             * 
                             */
                [
                    'attribute'=>'manager_name',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model->manager_id) ? Html::a($model->manager_name, ['/user/'.$model->manager_user_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                    },                         
                    'format'=>'raw',
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $managerList,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => ['placeholder' => '...'],
                ],                             
                [
                    'attribute' => 'status',
                    'headerOptions' => ['class'=>'td-mw-150'],                
                    'value' => function ($model) {
                        return isset($model->status) ? $model->clientStatusList[$model->status] : null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $searchModel->clientStatusList,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => ['placeholder' => '...'],
                    'visible' => $isAdmin,
                ],
                /* [
                    'attribute' => 'language_id',
                    'value' => function ($model) {
                        return $model->language->name;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $dataFilterLanguage,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => ['placeholder' => '...'],
                ], */
                [
                    'attribute' => 'it_is',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return isset($model->it_is) ? $model->clientItIsList[$model->it_is] : null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $itIsList,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => ['placeholder' => '...'],
                    'visible' => $isAdmin,
                ],
                [
                    'attribute' => 'client_type',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return isset($model->client_type) ? $model->clientTypeList[$model->client_type] : null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $searchModel->clientTypeList,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => ['placeholder' => '...'],
                    'visible' => $isAdmin,
                ],
                [
                    'attribute' => "vat_payer",                
                    'class' => '\kartik\grid\BooleanColumn',
                    'trueLabel' => 'Yes', 
                    'falseLabel' => 'No',
                    'width' => '100px',
                ],                        
                [
                    'attribute' => 'uploaded_file_id',
                    'hAlign' => 'center',
                    'vAlign' => 'middle',
                    'value' => function ($model) {
                        $logoModel = $model->logo;
                        if(empty($logoModel)){
                            return null;
                        }
                        $logoPath = $logoModel->uploadedFileUrl;
                        return Html::img($logoPath, ['width' => '70px']);
                    },
                    'format' => 'html',    
                ],                            
                [
                    'attribute' => "deleted",                
                    'class' => '\kartik\grid\BooleanColumn',
                    'trueLabel' => 'Yes', 
                    'falseLabel' => 'No',
                    'width' => '100px',
                    'visible' => $isAdmin,
                ],                        
                // 'create_time',
                // 'create_user_id',
                // 'update_time',
                // 'update_user_id',
            ];

            $columns = ArrayHelper::merge(
                    /*
                [            
                    [
                        'class' => '\common\components\FSMActionColumn',
                        'headerOptions' => ['class'=>'td-mw-100'],
                        'dropdown' => true,
                        'template' => '{view} {update} {delete}',
                    ],
                ],
                     * 
                     */
                $columns,
                [            
                    [
                        'class' => '\common\components\FSMActionColumn',
                        'headerOptions' => ['class'=>'td-mw-125'],
                        'dropdown' => true,
                        'template' => '{view} {update} {delete}',
                    ],
                ]
            );         
        ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'responsive' => false,
            //'striped' => false,
            'hover' => true,      
            'floatHeader' => true,
            'pjax' => true,
            'columns' => $columns,
            'rowOptions' => function ($model, $key, $index, $grid) {
                switch ($model->status) {
                    case Client::CLIENT_STATUS_ACTIVE:
                        return ['class' => 'client-status-active'];
                    case Client::CLIENT_STATUS_POTENTIAL:
                        return ['class' => 'client-status-potential'];
                    case Client::CLIENT_STATUS_ARHIVED:
                    default: 
                        return ['class' => 'client-status-arhived'];
                }        
            },        
        ]); ?>

    </div>
</div>