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

use common\models\bill\PaymentOrder;

/* @var $this yii\web\View */
/* @var $searchModel common\models\bill\search\PaymentOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-order-index">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
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
                'attribute' => 'bank_id',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->bank_id) ? Html::a($model->bank_name, ['/bank/view', 'id' => $model->bank_id], ['target' => '_blank', 'data-pjax' => 0,]) : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $bankList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'format' => 'raw',
            ],            
            'number',
            'name',
            [
                'attribute' => 'pay_date',
                'width' => '150px',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->pay_date) ? date('d-M-Y', strtotime($model->pay_date)) : null;
                },                         
            ],            
            [
                'attribute' => 'status',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->status) ? $model->exportStateList[$model->status] : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $searchModel->exportStateList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
            ],
            [
                'attribute' => 'file_name',
                //'width' => '150px',
                //'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->file_id) ? Html::a($model->file->filename, $model->file->fileurl, ['target' => '_blank', 'data-pjax' => 0,]) : null;
                },          
                'format' => 'raw',
            ],            
            [
                'attribute' => 'action_time',
                'width' => '150px',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->action_time) ? date('d-M-Y H:i', strtotime($model->action_time)) : null;
                },                         
            ],            
            [
                'attribute' => 'action_user_id',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->action_user_id) ? Html::a($model->user_name, ['/user/profile/show', 'id' => $model->action_user_id], ['target' => '_blank', 'data-pjax' => 0,]) : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $userList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'format' => 'raw',
            ],
            'comment:ntext',
            
            // 'create_time',
            // 'create_user_id',
            // 'update_time',
            // 'update_user_id',

            [
                'class' => '\common\components\FSMActionColumn',
                'headerOptions' => ['class'=>'td-mw-125'],
                'dropdown' => true,
                'dropdownDefaultBtn' => 'send',
                'template' => '{send} {view} {update} {delete}',
                'buttons' => [
                    'send' => function (array $params) { 
                        return PaymentOrder::getButtonSend($params);
                    },
                ]                
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>