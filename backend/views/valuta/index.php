<?php
namespace common\models;

use Yii;
use kartik\helpers\Html;
use kartik\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var common\models\search\ValutaSearch $searchModel
 */

$this->title = $searchModel->modelTitle(2);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="valuta-index">

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
            'name',
            ['class' => '\kartik\grid\ActionColumn'],
        ],
    ]); ?>

</div>