<?php

use kartik\helpers\Html;
use kartik\grid\GridView;

//use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var common\models\search\LanguageSearch $searchModel
 */

$this->title = $searchModel->modelTitle(2);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="language-index">

    <?= Html::pageHeader(Html::encode($this->title));?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [

            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
            ],
            'language',
            'name',
            'native',
            [
                'attribute'=>'enabled',                
                'class' => '\kartik\grid\BooleanColumn',
                'trueLabel' => 'Yes', 
                'falseLabel' => 'No',
                'width' => 'auto',
            ],

            ['class' => '\kartik\grid\ActionColumn'],
        ],
    ]); ?>

</div>
