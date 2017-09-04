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
/* @var $searchModel common\models\bill\search\ExpenseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div class="expense-index">

    <?= Html::tag('H1', Html::tag('small', $searchModel->modelTitle(2))); ?>
    
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
        'dataProvider' => $dataProvider,
        'showPageSummary' => true,
        'pjax' => true,        
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => '\kartik\grid\SerialColumn'],
            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
                'pageSummary' => 'Total',
            ],
            [
                'attribute' => 'expense_type_id',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->expense_type_id) ? $model->expense_type_name : null;
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $expenseTypeList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
            ],                         
            'doc_number',
            [
                'attribute' => 'doc_date',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->doc_date) ? date('d-M-Y', strtotime($model->doc_date)) : null;
                },
            ],                          
            [
                'attribute'=>'first_client_id',
                'headerOptions' => ['class'=>'td-mw-200'],
                'value' => function ($model) {
                    return !empty($model->first_client_id) ? 
                        Html::a($model->first_client_name, ['/client/view', 'id' => $model->first_client_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $clientList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'format'=>'raw',
            ],                          
            [
                'attribute'=>'second_client_id',
                'headerOptions' => ['class'=>'td-mw-200'],
                'value' => function ($model) {
                    return !empty($model->second_client_id) ?  
                        Html::a($model->second_client_name, ['/client/view', 'id' => $model->second_client_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $clientList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'format'=>'raw',
            ],                          
            [
                'attribute' => 'total',
                'hAlign' => 'right',
                'value' => function ($model) {
                    return isset($model->total) ? $model->total : null;
                },
                'format' => ['decimal', 2],
                'pageSummary' => true,              
            ],
            [
                'attribute' => 'valuta_id',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->valuta_id) ? $model->valuta->name : '';
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $valutaList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],   
                'filterInputOptions' => [
                    'placeholder' => '...',
                    'multiple' => true,
                ],                          
            ],                        
            [
                'class' => '\common\components\FSMActionColumn',
                'headerOptions' => ['class'=>'td-mw-125'],
                'dropdown' => true,
                'template' => '{view} {update} {delete}',
                'controller' => 'expense',
            ],
        ],
    ]); ?>
</div>