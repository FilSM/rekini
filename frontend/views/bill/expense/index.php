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

if(empty($reportDetails)){
    $this->title = $searchModel->modelTitle(2);
    $this->params['breadcrumbs'][] = $this->title;
}
?>
<div class="expense-index">

    <?php if(empty($reportDetails)): ?>    
    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>
    <?php else:
        echo Html::tag('H1', Html::tag('small', $searchModel->modelTitle(2)));
    endif; ?>
    
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
        'pjax' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => '\kartik\grid\SerialColumn'],
            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
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
            //'abonent_id',
            [
                'attribute'=>'project_id',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return !empty($model->project_id) ? Html::a($model->project_name, ['/project/view', 'id' => $model->project_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $projectList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'format'=>'raw',
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
                'attribute' => 'summa',
                'hAlign' => 'right',
                'value' => function ($model) {
                    return isset($model->summa) ? number_format($model->summa, 2) : null;
                },
            ],                          
            [
                'attribute' => 'vat',
                'hAlign' => 'right',
                'value' => function ($model) {
                    return isset($model->vat) ? number_format($model->vat, 2) : null;
                },
            ],                          
            [
                'attribute' => 'total',
                'hAlign' => 'right',
                'value' => function ($model) {
                    return isset($model->valuta_id) ? $model->total . ' ' . $model->valuta->name : $model->total;
                },
            ],                          
            // 'comment:ntext',
            // 'create_time',
            // 'create_user_id',
            // 'update_time',
            // 'update_user_id',

            [
                'class' => '\common\components\FSMActionColumn',
                'headerOptions' => ['class'=>'td-mw-125'],
                'dropdown' => true,
                'template' => '{view} {update} {delete}',
                'controller' => 'expense',
                //'visible' => empty($reportDetails),
            ],
        ],
    ]); ?>
</div>