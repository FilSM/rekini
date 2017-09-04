<?php

use yii\widgets\Pjax;

use kartik\helpers\Html;
use kartik\grid\GridView;

use common\models\bill\HistoryBill;
?>

<?php 
    ob_start();
    ob_implicit_flush(false);
?>

<div class="history-bill-index">
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'pjax' => true, 
        'pjaxSettings' => [
            'enablePushState' => false,
            'enableReplaceState' => false,
        ],
        'columns' => [
            //['class' => '\kartik\grid\SerialColumn'],
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
            ],
            [
                'attribute' => 'create_user_id',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->create_user_id) ? Html::a($model->user_name, ['/user/profile/show', 'id' => $model->create_user_id], ['target' => '_blank']) : null;
                },          
                'format' => 'raw',
            ],
            'comment:ntext',
        ],
    ]); ?>
</div>

<?php
    $body = ob_get_contents();
    ob_get_clean(); 

    $panelContent = [
        'heading' => HistoryBill::modelTitle(),
        'preBody' => '<div class="panel-body">',
        'body' => $body,
        'postBody' => '</div>',
    ];
    echo Html::panel(
        $panelContent, 
        'success', 
        [
            'id' => "panel-history-data",
        ]
    );
?>