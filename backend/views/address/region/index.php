<?php
namespace common\models\address;

use Yii;
use yii\helpers\ArrayHelper;

use kartik\helpers\Html;
use kartik\grid\GridView;

//use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var common\models\address\search\RegionSearch $searchModel
 */

$this->title = $searchModel->modelTitle(2);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="region-index">

    <?= Html::pageHeader(Html::encode($this->title));?>
    <?php //echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => '\kartik\grid\SerialColumn'],

            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
            ],
            [
                'attribute'=>'country_id',
                'value' => function ($model) {
                    return Html::a($model->country_name, ['country/view', 'id' => $model->country_id]);
                },
                'format'=>'raw',       
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $dataFilterCountry,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => Yii::t('common', 'Any...')],
            ],
            'name',

            ['class' => '\kartik\grid\ActionColumn'],
        ],
    ]); ?>

</div>