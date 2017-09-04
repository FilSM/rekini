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

if(empty($reportDetails) || (!empty($reportDetails) && ($reportDetails != 'ebitda'))){
    $this->title = !empty($reportTitle) ? $reportTitle : $searchModel->modelTitle(2);
    $this->params['breadcrumbs'][] = $this->title;
}

$showWriteOnBasisBtn = 
    !empty($_GET['BillSearch']['agreement_id']) &&
    !empty($_GET['BillSearch']['doc_type']) &&
    ($_GET['BillSearch']['doc_type'] == Bill::BILL_DOC_TYPE_AVANS);
$showSendPaymentBtn = 
    !empty($_GET['BillSearch']['status']) &&
    ($_GET['BillSearch']['status'] == Bill::BILL_STATUS_PREP_PAYMENT);
?>
<div class="bill-index">

    <div id="page-content">
        
        <?php if(empty($reportDetails)): ?>
        <div>
            <div class="search-toggle-box">
                <?= Html::icon('search', ['class' => 'toggle-button']); ?>
            </div>
            <?= Html::pageHeader(Html::encode($this->title)); ?>
        </div>
        <?php echo $this->render('_search', [
            'model' => $searchModel,
            'projectList' => $projectList,
            'agreementList' => $agreementList,
            'clientList' => $clientList,
            'managerList' => $managerList,
        ]); ?>
                
        <div style="height: 34px;">                    
            <div class='col-xs-6' style="padding: 0;">
                <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
            </div>
            <div class='col-xs-offset-6' style="padding: 0; text-align: right;">
                <?php if($showWriteOnBasisBtn) : ?>
                    <?= \common\components\FSMBtnMultiAction::aButton(
                            Yii::t('bill', 'Write out on this basis'), 
                            Url::to(['bill-write-on-basis-many']),
                            [
                                'model' => $searchModel,
                                'grid' => 'grid-view',
                                'icon' => 'share',
                                //'confirm' => Yii::t('common', 'Are you sure you want to delete selected item(s)?'),
                            ],
                            [
                                'id' => 'bill-write-on-basis-many',
                                'class' => 'btn btn-success',
                                'disabled' => true,
                            ]
                    ); ?>           
                <?php endif; ?>
                <?php if($showSendPaymentBtn) : ?>
                    <?= \common\components\FSMBtnMultiAction::vButton(
                            Yii::t('bill', 'Add to prepared payment'), 
                            Url::to(['/bill-payment/ajax-create-many']),
                            [
                                'model' => $searchModel,
                                'grid' => 'grid-view',
                                'icon' => 'share',
                                //'confirm' => Yii::t('common', 'Are you sure you want to delete selected item(s)?'),
                            ],
                            [
                                'id' => 'bill-payments-create-many',
                                'class' => 'btn btn-success show-modal-button',
                                'disabled' => true,
                            ]
                    ); ?>           
                <?php endif; ?>
            </div>
        </div>
        <p></p>
        <?php elseif($reportDetails != 'ebitda') : ?>
            <?= Html::pageHeader(Html::encode($reportTitle)); ?>
        <?php else : ?>
            <?= Html::tag('H1', Html::tag('small', $searchModel->modelTitle(2)));; ?>
        <?php endif; ?>

        <?php
            $columns = [
                [
                    'class' => 'yii\grid\CheckboxColumn',
                    'visible' => $showWriteOnBasisBtn || $showSendPaymentBtn,
                ],
                
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
                /*
                [
                    'attribute' => 'doc_type',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return isset($model->doc_type) ? $model->billDocTypeList[$model->doc_type] : null;
                    },
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => $searchModel->billDocTypeList,
                    'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                    'filterInputOptions' => ['id' => 'doc-type-search', 'placeholder' => '...'],
                    'visible' => $isAdmin,
                ],     
                 * 
                 */  
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
                            
                            /*
                [
                    'attribute'=>'abonent_name',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model->abonent_id) ? Html::a($model->abonent_name, ['/abonent/view', 'id' => $model->abonent_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                    },                         
                    'format'=>'raw',
                ],                          
                [
                    'attribute'=>'project_name',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model->project_id) ? Html::a($model->project_name, ['/project/view', 'id' => $model->project_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                    },                         
                    'format'=>'raw',
                ],                          
                             * 
                             */    
                            /*
                [
                    'attribute'=>'agreement_number',
                    'headerOptions' => ['class'=>'td-mw-100'],
                    'value' => function ($model) {
                        return !empty($model->agreement_id) ? Html::a($model->agreement_number, ['/agreement/view', 'id' => $model->agreement_id], ['target' => '_blank', 'data-pjax' => 0]) : null;
                    },                         
                    'format'=>'raw',
                ],                      
                             * 
                             */
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
                            /*
                [
                    'attribute' => 'summa',
                    'hAlign' => 'right',
                ],                         
                [
                    'attribute' => 'vat',
                    'hAlign' => 'right',
                ],     
                             * 
                             */
                [
                    'attribute' => 'total',
                    'hAlign' => 'right',
                    //'headerOptions' => ['class'=>'td-mw-150'],
                    //'label' => Yii::t('bill', 'Summa'),
                    'value' => function ($model) {
                        return isset($model->valuta_id) ? $model->total . ' ' . $model->valuta->name : $model->total;
                    },
                ],                         
                // 'valuta_id',
                // 'delayed:datetime',
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
                // 'first_client_bank_id',
                // 'second_client_bank_id',
                // 'manager_id',
                // 'comment:ntext',
                // 'create_user_id',
                // 'update_time',
                // 'update_user_id',
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
                    //'visible' => empty($reportDetails),
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
            'pjax' => true,
            /*
            'pjaxSettings' => [
                'neverTimeout' => true,
                //'beforeGrid' => 'My fancy content before.',
                //'afterGrid' => 'My fancy content after.',
            ],
             * 
             */
            'floatHeader' => true,
            //'floatHeaderOptions' => ['scrollingTop' => '50'],
            'tableOptions' => [
                'id' => 'bill-list',
            ],
        ]);
        ?>

    </div>    
</div>