<?php
//namespace common\models;

//use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;
use yii\helpers\Url;

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

use common\models\bill\Bill;
use common\components\FSMHelper;

/* @var $this yii\web\View */
/* @var $searchModel common\models\bill\search\BillSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
?>
<div class="bill-index">

    <div id="page-content">
        
        <?= Html::tag('H1', Html::tag('small', $searchModel->modelTitle(2)));; ?>

        <?php
            $columns = [
                //['class' => '\kartik\grid\SerialColumn'],

                [
                    'attribute' => 'id',
                    'width' => '75px',
                    'hAlign' => 'center',
                    'pageSummary' => 'Total',
                ],
                [
                    'attribute'=>'doc_number',
                    'headerOptions' => ['class'=>'td-mw-100'],
                    'value' => function ($model) {
                        return !empty($model->doc_number) ? 
                            (isset($model->doc_type) ? $model->billDocTypeList[$model->doc_type].'<br/>' : null).
                            Html::a($model->doc_number, ['/bill/view', 'id' => $model->id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                    },                         
                    'format'=>'raw',
                ],                      
                [
                    'attribute' => 'status',
                    'hAlign' => 'center',
                    'vAlign' => 'middle',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model->status) ? 
                            Html::a(Html::badge($model->billStatusList[$model->status], ['class' => $model->statusBackgroundColor.' status-badge']), 
                                Url::to(['/bill/index']).'?BillSearch[status]='.$model->status, 
                                ['target' => '_blank', 'data-pjax' => 0]) 
                            : null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $searchModel->billStatusList,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => [
                        'placeholder' => '...',
                        'multiple' => true,
                    ],
                    'format'=>'raw',
                ],                         
                [
                    'header' => Yii::t('common', 'Progress'),
                    'headerOptions' => ['class'=>'td-mw-125'],
                    'hAlign' => 'center',
                    'vAlign' => 'middle',
                    'mergeHeader' => true,
                    'value' => function ($model) {
                        return in_array($model->doc_type, [
                            Bill::BILL_DOC_TYPE_AVANS,
                            Bill::BILL_DOC_TYPE_BILL,
                            Bill::BILL_DOC_TYPE_INVOICE,
                            Bill::BILL_DOC_TYPE_CESSION,
                            ]) ? $model->getProgressButtons() : '&nbsp;';
                    },
                    'format' => 'raw',
                    //'visible' => $isForwarder,
                ],                        
                [
                    'attribute'=>'first_client_name',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model->first_client_name) ? 
                            (!empty($model->first_client_role_name) ? $model->first_client_role_name.'<br/>' : '') . 
                            Html::a($model->first_client_name, ['/client/view', 'id' => $model->first_client_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                    },                         
                    'format'=>'raw',
                ],                          
                [
                    'attribute'=>'second_client_name',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model->second_client_name) ?  
                            (!empty($model->second_client_role_name) ? $model->second_client_role_name.'<br/>' : '') . 
                            Html::a($model->second_client_name, ['/client/view', 'id' => $model->second_client_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                    },                         
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
                    'attribute' => 'doc_date',
                    'hAlign' => 'center',
                    'vAlign' => 'middle',
                    'headerOptions' => ['class'=>'td-mw-100'],
                    //'label' => Yii::t('bill', 'Summa'),
                    'value' => function ($model) {
                        return isset($model->doc_date) && ($model->doc_date == date('Y-m-d')) ? 
                            Html::a(Html::badge(Yii::t('common', 'Today'), ['class' => 'badge-success']), 
                                Url::to(['/bill/index']).'?BillSearch[doc_date]='.$model->doc_date, 
                                ['target' => '_blank', 'data-pjax' => 0]) 
                            : date('d-M-Y', strtotime($model->doc_date));
                    },
                    'format'=>'raw',        
                ],                         
                [
                    'attribute' => 'pay_date',
                    'hAlign' => 'center',
                    'vAlign' => 'middle',
                    'headerOptions' => ['class'=>'td-mw-100'],
                    'value' => function ($model) {
                        $class = 'badge-success';
                        if(in_array($model->status, [
                            Bill::BILL_STATUS_CANCELED,
                            Bill::BILL_STATUS_COMPLETE,
                            ]))
                        {
                            $badge = '';
                        }else{
                            $firstDate = new DateTime(date('Y-m-d'));
                            $secondDate = new DateTime($model->pay_date);
                            $dateDiff = date_diff($firstDate, $secondDate, true)->days;

                            $firstDate = $firstDate->format('Y-m-d');
                            $secondDate = $secondDate->format('Y-m-d');
                            if($firstDate > $secondDate){
                                $dateDiff = -1 * $dateDiff;
                                $class = 'badge-danger';
                            }elseif($dateDiff >= 0){
                                $class = 'badge-success';
                            }

                            $badge = isset($model->pay_date) && ($model->pay_date == date('Y-m-d')) ? 
                                '' : Html::badge($dateDiff, ['class' => $class]);
                        }
                        return isset($model->pay_date) && ($model->pay_date == date('Y-m-d')) ? 
                            Html::a(Html::badge(Yii::t('common', 'Today'), ['class' => $class]) , 
                                Url::to(['/bill/index']).'?BillSearch[pay_date]='.$model->pay_date, 
                                ['target' => '_blank', 'data-pjax' => 0]) 
                            : date('d-M-Y', strtotime($model->pay_date)).'<br/>'.$badge;
                    },
                    'format'=>'raw',
                ],              
                [
                    'attribute' => 'paid_date',
                    'hAlign' => 'center',
                    'vAlign' => 'middle',
                    'headerOptions' => ['class'=>'td-mw-100'],
                    'value' => function ($model) {
                        return isset($model->paid_date) ? date('d-M-Y', strtotime($model->paid_date)) : null;
                    },
                ],                                          
                [
                    'attribute' => 'pay_status',
                    'hAlign' => 'center',
                    'vAlign' => 'middle',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        $class = $model->delayed ? 'badge-danger' : $model->payStatusBackgroundColor;
                        return isset($model->pay_status) && 
                                isset($model->status) && 
                                in_array($model->status, [
                                    Bill::BILL_STATUS_SIGNED, 
                                    Bill::BILL_STATUS_PREP_PAYMENT, 
                                    Bill::BILL_STATUS_PAYMENT, 
                                    Bill::BILL_STATUS_PAID]) 
                            ? 
                            Html::a(Html::badge($model->billPayStatusList[$model->pay_status], ['class' => $class.' pay-status-badge']), 
                                Url::to(['/bill/index']).'?BillSearch[pay_status]='.$model->pay_status, 
                                ['target' => '_blank', 'data-pjax' => 0]
                            ).
                            ($model->pay_status == Bill::BILL_PAY_STATUS_PART ? 
                                '<br/><span style="padding-top: 5px;">'.Yii::t('bill', 'Paid').': '.number_format($model->paymentsSumma, 2).'</span>'.
                                '<br/><span style="padding-top: 5px; font-weight: bold;">'.Yii::t('bill', 'Debt').': '.number_format($model->paymentsSummaLess, 2).'</span>' :
                                ($model->pay_status == Bill::BILL_PAY_STATUS_OVER ? 
                                        '<br/><span style="padding-top: 5px;">'.number_format($model->paymentsSummaGrate, 2) .'</span>': 
                                    '')
                            )
                            : '&nbsp;';
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $searchModel->billPayStatusList,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => [
                        'placeholder' => '...',
                        'multiple' => true,
                    ],
                    'format'=>'raw',
                ],     
                [
                    'class' => '\common\components\FSMActionColumn',
                    'header' => Yii::t('common', 'Options'),
                    'headerOptions' => ['class'=>'td-mw-125'],
                    'dropdown' => true,
                    'isDropdownActionColumn' => true,
                    'dropdownDefaultBtn' => 'pay',
                    'template' => '{pay} {write-on-basis} {credit-invoice} {mutual-settlement} {cession} {debt-relief} {cancel}',
                    'buttons' => $searchModel->getOptionsActionButtons('xs'),
                ],      
                [
                    'attribute' => "deleted",   
                    'vAlign' => 'middle',
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
                    'dropdownDefaultBtn' => 'print',
                    'template' => '{print} {view} {update} {delete}',
                    'buttons' => [
                        'print' => function (array $params) { 
                            return Bill::getButtonPrint($params);
                        },
                    ],
                    'controller' => 'bill',
                ],
            ];
        ?>

        <?= GridView::widget([
            'id' => 'grid-view',
            'responsive' => false,
            //'striped' => false,
            'hover' => true,
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => $columns,
            'showPageSummary' => true,
            'pjax' => true,        
            'floatHeader' => true,
            //'floatHeaderOptions' => ['scrollingTop' => '50'],
            'tableOptions' => [
                'id' => 'bill-list',
            ],
        ]);
        ?>

    </div>    
</div>