<?php
namespace common\models;

use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

use common\models\client\Agreement;

/* @var $this yii\web\View */
/* @var $searchModel common\models\client\search\AgreementSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agreement-index">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
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
            [
                'attribute' => 'agreement_type',
                //'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->agreement_type) ? $model->agreementTypeList[$model->agreement_type] : null;
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $searchModel->agreementTypeList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'visible' => $isAdmin,
                'format'=>'raw',
            ],
            [
                'attribute'=>'project_name',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return !empty($model->project_id) ? Html::a($model->project_name, ['/project/view', 'id' => $model->project_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                },                         
                'format'=>'raw',
            ],            
            [
                'attribute' => 'status',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->status) ? Html::badge($model->agreementStatusList[$model->status], ['class' => $model->statusBackgroundColor.' status-badge']) : null;
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $searchModel->agreementStatusList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'visible' => $isAdmin,
                'format'=>'raw',
            ],
            [
                'attribute'=>'first_client_name',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return !empty($model->first_client_id) ? 
                        (!empty($model->first_client_role_id) ? $model->firstClientRole->name.': ' : '') . 
                        Html::a($model->first_client_name, ['/client/view', 'id' => $model->first_client_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                },                         
                'format'=>'raw',
            ],            
            [
                'attribute'=>'second_client_name',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return !empty($model->second_client_id) ?  
                        (!empty($model->second_client_role_id) ? $model->secondClientRole->name.': ' : '') . 
                        Html::a($model->second_client_name, ['/client/view', 'id' => $model->second_client_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                },                         
                'format'=>'raw',
            ],            
            [
                'attribute'=>'third_client_name',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return !empty($model->third_client_id) ?   
                        (!empty($model->third_client_role_id) ? $model->thirdClientRole->name.': ' : '') . 
                        Html::a($model->third_client_name, ['/client/view', 'id' => $model->third_client_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                },                         
                'format'=>'raw',
            ],            
            'number',
            [
                'attribute' => 'signing_date',
                'headerOptions' => [
                    'class' => 'td-mw-75',
                ],
                'value' => function ($model) {
                    return !empty($model->signing_date) ? date('d-M-Y', strtotime($model->signing_date)) : null;
                },                 
            ],                         
            [
                'attribute' => 'due_date',
                'headerOptions' => [
                    'class' => 'td-mw-75',
                ],
                'value' => function ($model) {
                    return !empty($model->due_date) ? date('d-M-Y', strtotime($model->due_date)) : null;
                },                 
            ],                                                 
            [
                'attribute' => 'summa',
                'hAlign' => 'right',
                'headerOptions' => [
                    'class' => 'td-mw-75',
                    //'style' => 'text-align: left;',
                ],
            ],                 
                        /*
            [
                'attribute' => 'rate',
                'hAlign' => 'right',
                'headerOptions' => [
                    'class' => 'td-mw-75',
                    //'style' => 'text-align: left;',
                ],
            ],      
                         * 
                         */
            [
                'attribute' => 'conclusion',
                'headerOptions' => ['class'=>'td-mw-150'],                
                'value' => function ($model) {
                    return isset($model->status) ? $model->agreementConclusionList[$model->conclusion] : null;
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $searchModel->agreementConclusionList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'visible' => $isAdmin,
            ],
                        
            // 'comment:ntext',
            // 'create_time',
            // 'create_user_id',
            // 'update_time',
            // 'update_user_id',

            [
                'attribute' => "deleted",                
                'class' => '\kartik\grid\BooleanColumn',
                'trueLabel' => 'Yes', 
                'falseLabel' => 'No',
                'headerOptions' => [
                    'class' => 'td-mw-100',
                ],
                'visible' => $isAdmin,
            ],

            [
                'class' => '\common\components\FSMActionColumn',
                'headerOptions' => ['class'=>'td-mw-125'],
                'dropdown' => true,
                'dropdownDefaultBtn' => 'print',
                'template' => '{print} {view} {update} {delete}',
                'buttons' => [
                    'print' => function (array $params) { 
                        return Agreement::getButtonPrint($params);
                    },
                ]                
            ],                        
        ],
    ]); ?>
</div>