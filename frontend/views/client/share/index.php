<?php
namespace common\models;

use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel common\models\client\search\ShareSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="share-index">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'floatHeader' => true,
        'columns' => [
            [
                'class' => '\kartik\grid\SerialColumn',
                'visible' => false,//!$isAdmin,
            ],
            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
                //'visible' => $isAdmin,
            ],
            'client_name',
            'shareholder_name',
            [
                'label' => Yii::t('common', 'Period'),
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    if(empty($model->term_from) && empty($model->term_till)){
                        $period = '';
                    }elseif(!empty($model->term_from) && empty($model->term_till)){
                        $period = Yii::t('common', 'From').' '.date('d-M-Y', strtotime($model->term_from));
                    }elseif(!empty($model->term_from) && !empty($model->term_till)){
                        $period = date('d-M-Y', strtotime($model->term_from)).' / '.date('d-M-Y', strtotime($model->term_till));
                    }elseif(empty($model->term_from) && !empty($model->term_till)){
                        $period = Yii::t('common', 'Till').' '.date('d-M-Y', strtotime($model->term_till));
                    }
                    return $period;
                },                         
            ],
            'share',
            [
                'attribute' => "deleted",                
                'class' => '\kartik\grid\BooleanColumn',
                'trueLabel' => 'Yes', 
                'falseLabel' => 'No',
                'headerOptions' => [
                    'class' => 'td-mw-100',
                ],
                'visible' => $isAdmin,
            ],

            [
                'class' => '\common\components\FSMActionColumn',
                'headerOptions' => ['class'=>'td-mw-100'],
                'viewOptions' => ['class' => 'btn btn-xs btn-info', 'noTarget' => true],
                'updateOptions' => ['class' => 'btn btn-xs btn-primary'],
                'deleteOptions' => ['class' => 'btn btn-xs btn-danger'],
                //'width' => '150px',
                'template' => '{view} {update} {delete}',
                'linkedObj' => [
                    ['fieldName' => 'client_id', 'id' => (!empty($clientModel->id) ? $clientModel->id : null)],
                ],
            ],  
        ],
    ]); ?>
</div>