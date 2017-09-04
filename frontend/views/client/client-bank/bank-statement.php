<?php
namespace common\models;

use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

use common\components\FSMHelper;
use common\models\client\ClientBankBalance;

/* @var $this yii\web\View */
/* @var $searchModel common\models\client\search\ClientBankSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = Yii::t('report', 'Bank statements');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-bank-index">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'showPageSummary' => true,
        'pjax' => true,
        'panel'=>[
            'type'=>GridView::TYPE_PRIMARY,
            'before' => FSMHelper::vButton(null, [
                'label' => ClientBankBalance::modelTitle(),
                'title' => Yii::t('client', 'Add new bank account balance'),
                'controller' => 'client-bank-balance',
                'action' => 'create',
                'class' => 'success',
                'icon' => 'plus',
                'modal' => true,
                'options' => [
                    'data-pjax' => 0,
                ]
            ]),
            //'heading' => false,
        ],        
        'toolbar' => [
            '{toggleData}',
            //'{export}',
        ],
        'export' => [
            'options' => ['class' => 'btn btn-danger'],
            'showConfirmAlert' => false,
            'target' => GridView::TARGET_SELF,
            'header' => '',
        ],
        'exportConfig' => [
            GridView::EXCEL => ['label' => Yii::t('kvgrid', 'Excel')],
            //GridView::CSV => ['label' => Yii::t('kvgrid', 'CSV')],
            GridView::PDF => ['label' => Yii::t('kvgrid', 'PDF')],
        ],      
        'columns' => [
            [
                'class' => '\kartik\grid\SerialColumn',
            ],
            [
                'attribute' => 'client_id',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->client_id) ? Html::a($model->client_name, ['/client/view', 'id' => $model->client_id], ['target' => '_blank', 'data-pjax' => 0,]) : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $clientList,
                'filterWidgetOptions'=>[
                    'options' => [
                        'multiple' => true,
                    ],
                    'pluginOptions' => ['allowClear' => true],
                ],
                'filterInputOptions' => ['placeholder' => '...'],
                'format' => 'raw',
                'group' => true,
                'pageSummary' => 'Total',
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
                'group' => true,  // enable grouping
                'subGroupOf' => 1                        
            ],              
            'account',
            [
                //'attribute' => 'file_name_xml',
                'label' => Yii::t('common', 'Attachments'),
                'mergeHeader' => true,
                'headerOptions' => ['style' => 'text-align: center;'],
                'contentOptions' => ['style' => 'white-space: nowrap;'],
                'value' => function ($model) {
                    $result = [];
                    $fileName = isset($model->uploaded_file_id) ? 
                        '<span style="font-weight: bold;">XML: </span>'.Html::a($model->file_name_xml, $model->uploadedFile->fileurl, ['target' => '_blank', 'data-pjax' => 0,]) : null;
                    if($fileName){
                        $result[] = $fileName;
                    }
                    $fileName = isset($model->uploaded_pdf_file_id) ? 
                        '<span style="font-weight: bold;">PDF: </span>'.Html::a($model->file_name_pdf, $model->uploadedPdfFile->fileurl, ['target' => '_blank', 'data-pjax' => 0,]) : null;
                    if($fileName){
                        $result[] = $fileName;
                    }
                    return implode('<br/>', $result);
                },          
                'format' => 'raw',
            ], 
            [
                'attribute' => 'start_date',
                'width' => '100px',
                'value' => function ($model) {
                    return isset($model->start_date) ? date('d-M-Y', strtotime($model->start_date)) : null;
                },
            ],                          
            [
                'attribute' => 'end_date',
                'width' => '100px',
                'value' => function ($model) {
                    return isset($model->end_date) ? date('d-M-Y', strtotime($model->end_date)) : null;
                },
            ],                          
            [
                'hAlign' => 'center',
                'width' => '75px',
                'mergeHeader' => true,
                'value' => function ($model) {
                    return Html::a(Html::icon('th-list').' '.Yii::t('report', 'Details'), 
                        [
                            '/client-bank-balance/report', 
                            'account_id' => $model['id'], 
                        ], 
                        ['target' => '_blank', 'data-pjax' => 0,]);
                },
                'format'=>'raw',                           
            ],                         
            [
                'attribute' => 'balance',
                'width' => '150px',
                'hAlign' => 'right',
                'value' => function ($model) {
                    return isset($model->balance) ? $model->balance : null;
                    //return isset($model->balance) ? number_format($model->balance, 2).' '.$model->currency : null;
                },
                'format' => ['decimal', 2],
                'pageSummary' => true,
            ],                          
            [
                'attribute' => 'currency',
                'width' => '100px',
                'value' => function ($model) {
                    return isset($model->currency) ? $model->currency : null;
                },
            ],                          
            [
                //'attribute' => 'home_page',
                'width' => '100px',
                'hAlign' => 'center',
                'mergeHeader' => true,
                'value' => function ($model) {
                    return !empty($model->home_page) ? Html::a(Yii::t('bank', 'Connect'), $model->home_page, ['target' => '_blank', 'data-pjax' => 0,]) : null;
                }, 
                'format' => 'raw',
            ],            
        ],
    ]); ?>
</div>