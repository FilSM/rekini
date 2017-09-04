<?php

use yii\widgets\Pjax;

use kartik\helpers\Html;
use kartik\grid\GridView;

use common\models\bill\BillPayment;
?>

<?php 
    ob_start();
    ob_implicit_flush(false);
?>

<div class="bill-payment-index">
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
            [
                'attribute' => 'summa',
                'hAlign' => 'right',
                'headerOptions' => ['class'=>'td-mw-150'],
                'width' => '150px',
                //'label' => Yii::t('bill', 'Summa'),
                'value' => function ($model) {
                    return isset($model->summa) ? number_format($model->summa, 2) : null;
                },
            ],                    
            [
                'attribute'=>'confirmed',                
                'class' => '\kartik\grid\BooleanColumn',
                'trueLabel' => 'Yes', 
                'falseLabel' => 'No',
                'width' => '150px',
            ],                        
            [
                'class' => '\common\components\FSMActionColumn',
                'headerOptions' => ['class'=>'td-mw-125'],
                //'dropdown' => true,
                //'isDropdownActionColumn' => true,
                'template' => '{update} {delete}',
                'buttons' => [
                    'update' => function ($url, $model, $key) { 
                        return BillPayment::getButtonUpdate($url, $model, $key);
                    },
                ], 
                'controller' => 'bill-payment',
                'linkedObj' => [
                    ['fieldName' => 'payment_order_id', 'id' => (!empty($linkedModel->id) ? $linkedModel->id : null)],
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