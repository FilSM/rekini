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
/* @var $searchModel common\models\client\search\ProjectSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-index">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'floatHeader' => true,
        'pjax' => true,
        'columns' => [
        //['class' => '\kartik\grid\SerialColumn'],

            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
            ],
            'name',
            [
                'attribute'=>'country_name',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return !empty($model->country_name) ? $model->country_name : null;
                },                         
            ],            
            'address',
            [
                'attribute' => "vat_taxable",                
                'class' => '\kartik\grid\BooleanColumn',
                'trueLabel' => 'Yes', 
                'falseLabel' => 'No',
                'width' => '100px',
            ],
            'comment:ntext',
            //'create_time',
            //'create_user_id',
            //'update_time',
            // 'update_user_id',
            [
                'attribute' => "deleted",                
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
                'template' => '{view} {update} {delete}',
            ],
        ],
    ]); ?>
</div>