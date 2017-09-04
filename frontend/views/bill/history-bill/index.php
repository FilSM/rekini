<?php
namespace common\models;

use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel common\models\bill\search\HistoryBillSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="history-bill-index">

    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <?php /*Pjax::begin();*/ ?>
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pjax' => true,
        'pjaxSettings' => [
            'enablePushState' => false,
        ],        
        'columns' => [
            //['class' => '\kartik\grid\SerialColumn'],
            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
            ],
            [
                'attribute' => 'bill_number',
                'value' => function ($model) {
                    return isset($model->bill_id) ? $model->bill_number : null;
                },                         
            ],
            [
                'attribute' => 'create_time',
                'width' => '150px',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->create_time) ? date('d-M-Y H:i:s', strtotime($model->create_time)) : null;
                },                         
            ],
            [
                'attribute' => 'action_id',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->action_id) ? $model->billActionList[$model->action_id] : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $searchModel->billActionList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
            ],
            [
                'attribute' => 'create_user_id',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->create_user_id) ? Html::a($model->user_name, ['/user/profile/show', 'id' => $model->create_user_id], ['target' => '_blank']) : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $userList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'format' => 'raw',
            ],
            'comment:ntext',

            //['class' => '\kartik\grid\ActionColumn'],
        ],
    ]); ?>
    <?php /*Pjax::end();*/ ?>
</div>