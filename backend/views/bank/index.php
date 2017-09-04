<?php

use kartik\helpers\Html;
use kartik\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var common\models\search\BankSearch $searchModel
 */

$this->title = $searchModel->modelTitle(2);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bank-index">

    <?= Html::pageHeader(Html::encode($this->title));?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'name',
            'reg_number',
            'swift',
            'address',
            [
                'attribute' => 'home_page',
                'hAlign' => 'center',
                'mergeHeader' => true,
                'value' => function ($model) {
                    return !empty($model->home_page) ? Html::a(Yii::t('bank', 'Connect'), $model->home_page, ['target' => '_blank']) : null;
                }, 
                'format' => 'raw',
            ],            
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
