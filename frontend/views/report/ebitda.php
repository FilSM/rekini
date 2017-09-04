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
    $this->title = Yii::t('report', 'EBITDA');
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
        
        <?= $this->render('_search-from-till_project', [
            'model' => $searchModel,
            'projectList' => $projectList,
            'action' => 'ebitda',
        ])?>

        <p></p>
        
        <?php
            $columns = [
                ['class' => '\kartik\grid\SerialColumn'],
                [
                    'attribute'=>'project_id',
                    'headerOptions' => ['class'=>'td-mw-150'],
                    'value' => function ($model) {
                        return !empty($model['project_id']) ? 
                            Html::a($model['project_name'], ['/project/view', 'id' => $model['project_id']], ['target' => '_blank', 'data-pjax' => 0,]) : null;
                    },
                    'format'=>'raw',
                    'pageSummary' => 'Total',
                ],                          
                [
                    'attribute' => 'project_sales',
                    'hAlign' => 'right',
                    'mergeHeader' => true,
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model) {
                        return !empty($model['project_sales']) ? $model['project_sales'] : 0;
                    },
                    'format' => ['decimal', 2],
                    'pageSummary' => true,                            
                ],                         
                [
                    'hAlign' => 'center',
                    'width' => '75px',
                    'mergeHeader' => true,
                    'value' => function ($model) use($dateFrom, $dateTill) {
                        if(empty((float)$model['project_sales'])){
                            return '';
                        }
                        return Html::a(Html::icon('th-list').' '.Yii::t('report', 'Details'), 
                            [
                                '/bill/report-details-ebitda', 
                                'project_id' => $model['project_id'], 
                                'direction' => 'in',
                                'doc_date_from_till' => [$dateFrom, $dateTill],
                            ], 
                            ['target' => '_blank', 'data-pjax' => 0,]);
                    },
                    'format'=>'raw',                           
                ],                         
                [
                    'attribute' => 'project_purchases',
                    'hAlign' => 'right',
                    'mergeHeader' => true,
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model) {
                        return !empty($model['project_purchases']) ? $model['project_purchases'] : 0;
                    },
                    'format' => ['decimal', 2],
                    'pageSummary' => true,                            
                ],
                [
                    'hAlign' => 'center',
                    'width' => '75px',
                    'mergeHeader' => true,
                    'value' => function ($model) use($dateFrom, $dateTill) {
                        if(empty((float)$model['project_purchases'])){
                            return '';
                        }
                        return Html::a(Html::icon('th-list').' '.Yii::t('report', 'Details'), 
                            [
                                '/bill/report-details-ebitda', 
                                'project_id' => $model['project_id'], 
                                'direction' => 'out',
                                'doc_date_from_till' => [$dateFrom, $dateTill],
                            ], 
                            ['target' => '_blank', 'data-pjax' => 0,]);
                    },
                    'format'=>'raw',                           
                ],                         
                [
                    'attribute' => 'project_profit',
                    'hAlign' => 'right',
                    'mergeHeader' => true,
                    'headerOptions' => ['style' => 'text-align: center;'],
                    'value' => function ($model) {
                        return !empty($model['project_profit']) ? $model['project_profit'] : 0;
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
                    'projectList' => $projectList,
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