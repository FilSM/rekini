<?php
namespace common\models;

use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;
use yii\widgets\Pjax;
use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel common\models\client\search\RegDocTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reg-doc-type-index">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => '\kartik\grid\SerialColumn'],
            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
            ],
            'name',

            ['class' => '\kartik\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>