<?php
namespace common\models;

use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel common\models\abonent\AbonentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row abonent-index">
    <div class="col-md-1">
        <?=
        $this->render('@frontend/views/client/client/v_menu', [
            'activeItem' => 'abonent_list',
            'profileId' => $profileId,
        ])
        ?>
    </div>    
    <div class="col-md-11">
        <?php /*= Html::pageHeader(Html::encode($this->title)); */?>

        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <p>
            <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
        </p>
        <?= GridView::widget([
            'responsive' => false,
            //'striped' => false,
            'hover' => true,
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'floatHeader' => true,
            'pjax' => true,
            'columns' => [
            //['class' => '\kartik\grid\SerialColumn'],

                [
                    'attribute' => 'id',
                    'width' => '75px',
                    'hAlign' => 'center',
                ],
                /*
                [ 
                    'attribute' => 'create_time', 
                    'value' => function ($model) { 
                        return !empty($model->create_time) ? $model->create_time : null; 
                    }, 
                    'headerOptions' => [ 
                        //'class' => 'col-md-2' 
                    ],
                    'filter' => DateRangePicker::widget([ 
                        'model' => $searchModel, 
                        'attribute' => 'create_time_range',
                        //'convertFormat' => true,
                        'presetDropdown' => true,
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
                        return Html::a($model->name, ['abonent/view', 'id' => $model->id], ['target' => '_blank', 'data-pjax' => 0]);
                    },                         
                    'format'=>'raw',
                ],                            
                [
                    'attribute'=>'client_name',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model->main_client_id) ? Html::a($model->client_name, ['/client/view', 'id' => $model->main_client_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                    },                         
                    'format'=>'raw',
                ],                          
                [
                    'attribute' => 'subscription_end_date',
                    'headerOptions' => [
                        'class' => 'td-mw-75',
                    ],
                    'value' => function ($model) {
                        return !empty($model->subscription_end_date) ? date('d-M-Y', strtotime($model->subscription_end_date)) : null;
                    },                 
                ],                             
                [
                    'attribute' => 'subscription_type',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return isset($model->subscription_type) ? $model->abonentTypeList[$model->subscription_type] : null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $searchModel->abonentTypeList,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => ['placeholder' => '...'],
                ],                            
                [
                    'attribute'=>'manager_name',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model->manager_id) ? Html::a($model->manager_name, ['/user/'.$model->manager_user_id]) : null;
                    },                         
                    'format'=>'raw',
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $managerList,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => ['placeholder' => '...'],
                ], 
                [
                    'attribute' => "deleted",                
                    'class' => '\kartik\grid\BooleanColumn',
                    'trueLabel' => 'Yes', 
                    'falseLabel' => 'No',
                    'width' => '100px',
                    'visible' => $isAdmin,
                ],
                [
                    'class' => '\common\components\FSMActionColumn',
                    'headerOptions' => ['class'=>'td-mw-125'],
                    'dropdown' => true,
                    'template' => '{view} {update} {delete}',
                ],
            ],
        ]); ?>

    </div>
</div>