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
    $this->title = Yii::t('report', 'Debtors/Creditors');
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
            'action' => 'debitor-creditor',
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
                    'attribute' => 'debtors_summa',
                    'hAlign' => 'right',
                    'mergeHeader' => true,
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model) {
                        return !empty($model['debtors_summa']) ? $model['debtors_summa'] : 0;
                    },
                    'format' => ['decimal', 2],
                    'pageSummary' => true,                            
                ],                         
                [
                    'hAlign' => 'center',
                    'width' => '75px',
                    'mergeHeader' => true,
                    'value' => function ($model) use($dateFrom, $dateTill) {
                        if(empty((float)$model['debtors_summa'])){
                            return '';
                        }
                        return Html::a(Html::icon('th-list').' '.Yii::t('report', 'Details'), 
                            [
                                '/bill/report-details-debitor-creditor', 
                                'client_id' => $model['client_id'],
                                'direction' => 'in',
                                'doc_date_from_till' => [$dateFrom, $dateTill],
                            ], 
                            ['target' => '_blank', 'data-pjax' => 0,]);
                    },
                    'format'=>'raw',                           
                ],                         
                [
                    'attribute' => 'creditors_summa',
                    'hAlign' => 'right',
                    'mergeHeader' => true,
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model) {
                        return !empty($model['creditors_summa']) ? $model['creditors_summa'] : 0;
                    },
                    'format' => ['decimal', 2],
                    'pageSummary' => true,                            
                ],                         
                [
                    'hAlign' => 'center',
                    'width' => '75px',
                    'mergeHeader' => true,
                    'value' => function ($model) use($dateFrom, $dateTill) {
                        if(empty((float)$model['creditors_summa'])){
                            return '';
                        }
                        return Html::a(Html::icon('th-list').' '.Yii::t('report', 'Details'), 
                            [
                                '/bill/report-details-debitor-creditor', 
                                'client_id' => $model['client_id'], 
                                'direction' => 'out',
                                'doc_date_from_till' => [$dateFrom, $dateTill],
                            ], 
                            ['target' => '_blank', 'data-pjax' => 0,]);
                    },
                    'format'=>'raw',                           
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