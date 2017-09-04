<?php
//namespace common\models;

//use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;
use yii\helpers\Url;

use kartik\helpers\Html;
use kartik\grid\GridView;

use common\models\bill\Bill;
use common\components\FSMHelper;

/* @var $this yii\web\View */
/* @var $searchModel common\models\bill\search\BillSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t('report', 'VAT report');
//}
$this->params['breadcrumbs'][] = $this->title;
$dateFrom = isset($_GET['from']) ? date("Y-m-d", strtotime($_GET['from'])) : date('Y-m-01');
$dateTill = isset($_GET['till']) ? (!empty($_GET['till']) ? date("Y-m-d", strtotime($_GET['till'])) : date("Y-m-t")) : date("Y-m-t");
?>
<div class="bill-index">

    <div id="page-content">
        <div>
            <?= Html::pageHeader(Html::encode($this->title)); ?>
        </div>
        
        <?= $this->render('_search-from-till_client', [
            'model' => $searchModel,
            'clientList' => $clientList,
            'action' => 'vat',
        ])?>

        <p></p>
        
        <?php
            $columns = [
                ['class' => '\kartik\grid\SerialColumn'],
                [
                    'attribute'=>'client_id',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model['client_id']) ? 
                            Html::a($model['client_name'], ['/client/view', 'id' => $model['client_id']], ['target' => '_blank', 'data-pjax' => 0,]) : null;
                    },
                    'format'=>'raw',
                    'pageSummary' => 'Total',
                ],                          
                [
                    'attribute' => 'client_sales',
                    'hAlign' => 'right',
                    'mergeHeader' => true,
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model) {
                        return !empty($model['client_sales']) ? $model['client_sales'] : 0;
                    },
                    'format' => ['decimal', 2],
                    'pageSummary' => true,                            
                ],                         
                [
                    'attribute' => 'client_vat_plus',
                    'hAlign' => 'right',
                    'mergeHeader' => true,
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model) {
                        return !empty($model['client_vat_plus']) ? $model['client_vat_plus'] : 0;
                    },
                    'format' => ['decimal', 2],
                    'pageSummary' => true,                            
                ],                         
                [
                    'hAlign' => 'center',
                    'width' => '75px',
                    'mergeHeader' => true,
                    'value' => function ($model) use($dateFrom, $dateTill) {
                        if(empty((float)$model['client_sales'])){
                            return '';
                        }                        
                        return Html::a(Html::icon('th-list').' '.Yii::t('report', 'Details'), 
                            [
                                '/bill/report-details-vat', 
                                'client_id' => $model['client_id'],
                                'direction' => 'in', 
                                'doc_date_from_till' => [$dateFrom, $dateTill],
                            ], 
                            ['target' => '_blank', 'data-pjax' => 0,]);
                    },
                    'format'=>'raw',                           
                ],                         
                [
                    'attribute' => 'client_purchases',
                    'hAlign' => 'right',
                    'mergeHeader' => true,
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model) {
                        return !empty($model['client_purchases']) ? $model['client_purchases'] : 0;
                    },
                    'format' => ['decimal', 2],
                    'pageSummary' => true,                            
                ],                         
                [
                    'attribute' => 'client_vat_minus',
                    'hAlign' => 'right',
                    'mergeHeader' => true,
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model) {
                        return !empty($model['client_vat_minus']) ? $model['client_vat_minus'] : 0;
                    },
                    'format' => ['decimal', 2],
                    'pageSummary' => true,                            
                ],                         
                [
                    'hAlign' => 'center',
                    'width' => '75px',
                    'mergeHeader' => true,
                    'value' => function ($model) use($dateFrom, $dateTill) {
                        if(empty((float)$model['client_purchases'])){
                            return '';
                        }                        
                        return Html::a(Html::icon('th-list').' '.Yii::t('report', 'Details'), 
                            [
                                '/bill/report-details-vat', 
                                'client_id' => $model['client_id'], 
                                'direction' => 'out', 
                                'doc_date_from_till' => [$dateFrom, $dateTill],
                            ], 
                            ['target' => '_blank', 'data-pjax' => 0,]);
                    },
                    'format'=>'raw',                           
                ],                         
                [
                    'attribute' => 'client_vat_result',
                    'hAlign' => 'right',
                    'mergeHeader' => true,
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model) {
                        return !empty($model['client_vat_result']) ? $model['client_vat_result'] : 0;
                    },
                    'format' => ['decimal', 2],
                    'pageSummary' => true,                            
                ],                         
            ];
        ?>

        <?= GridView::widget([
            'responsive' => false,
            //'striped' => false,
            'hover' => true,
            'floatHeader' => true,
            'dataProvider' => $dataProvider,
            //'filterModel' => $searchModel,
            'showPageSummary' => true,
            'pjax' => true,
            'panel'=>[
                'type'=>GridView::TYPE_PRIMARY,
                /*
                'before' => $this->render('_search-from-till', [
                    'model' => $searchModel,
                    'clientList' => $clientList,
                ]),
                 * 
                 */
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
            'columns' => $columns,
        ]);
        ?>

    </div>    
</div>