<?php

use yii\widgets\Pjax;

use kartik\helpers\Html;
use kartik\grid\GridView;

use common\models\bill\BillConfirm;
?>

<?php 
    ob_start();
    ob_implicit_flush(false);
?>

<div class="bill-confirm-index">
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
            ['class' => '\kartik\grid\SerialColumn'],
            [
                'attribute'=>'bill_number',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return !empty($model->bill_id) ? Html::a($model->bill_number, ['/bill/view', 'id' => $model->bill_id], ['target' => '_blank', 'data-pjax' => 0,]) : null;
                },                         
                'format'=>'raw',
            ],  
            'bank_ref',
            'first_client_account',
            [
                'attribute' => 'second_client_name',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->second_client_id) ? Html::a($model->second_client_name, ['/client/view', 'id' => $model->second_client_id], ['target' => '_blank', 'data-pjax' => 0,]) : $model->second_client_name;
                },                         
                'format' => 'raw',
            ],                          
            'second_client_reg_number',
            'second_client_account',
            // 'second_client_id',
            [
                'attribute' => 'summa',
                'hAlign' => 'right',
                'headerOptions' => ['class'=>'td-mw-150'],
                'width' => '150px',
                //'label' => Yii::t('bill', 'Summa'),
                'value' => function ($model) {
                    return isset($model->summa) ? number_format($model->summa, 2).(!empty($model->currency) ? ' '.$model->currency : '') : null;
                },
            ],                    
            [
                'attribute' => 'doc_date',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->doc_date) ? date('d-M-Y', strtotime($model->doc_date)) : null;
                },
            ],                         
            'doc_number',
            [
                'attribute' => 'direction',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return !empty($model->direction) ? $model->directionList[$model->direction] : null;
                },
            ],                          
            'comment:ntext',
                      
            [
                'class' => '\common\components\FSMActionColumn',
                'headerOptions' => ['class'=>'td-mw-125'],
                //'dropdown' => true,
                //'isDropdownActionColumn' => true,
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model, $key) { 
                        return BillConfirm::getButtonUpdate($url, $model, $key);
                    },
                ], 
                'controller' => 'bill-confirm',
                'linkedObj' => [
                    ['fieldName' => 'payment_confirm_id', 'id' => (!empty($linkedModel->id) ? $linkedModel->id : null)],
                ],                
            ],                        
        ],
    ]); ?>
</div>

<?php
    $body = ob_get_contents();
    ob_get_clean(); 

    $panelContent = [
        'heading' => Yii::t('bill', 'Invoice list'),
        'preBody' => '<div class="panel-body">',
        'body' => $body,
        'postBody' => '</div>',
    ];
    echo Html::panel(
        $panelContent, 
        'success', 
        [
            'id' => "panel-bill-payment-data",
        ]
    );
?>