<?php
namespace common\models\address;

use Yii;
use kartik\helpers\Html;
use kartik\detail\DetailView;

/**
 * @var yii\web\View $this
 * @var common\models\address\District $model
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="district-view">

    <?= Html::pageHeader(Html::encode($this->title));?>

    <p>
        <?= Html::a(Yii::t('common', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= \common\components\FSMBtnDialog::button(Yii::t('common', 'Delete'), ['delete', 'id' => $model->id], [
            'id' => 'btn-dialog-selected',
            'class' => 'btn btn-danger',
        ]); ?>  
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'attribute' => 'country_id',
                //'label' => $model->country->modelTitle(),
                'value' => $model->country !== null ?  Html::a(Html::encode($model->country->name), ['country/view', 'id' => $model->country->getPrimaryKey()]) : null,
                'format' => 'raw',
            ],
            [
                'attribute' => 'region_id',
                //'label' => Region::modelTitle(),
                'value' => $model->region !== null ?  Html::a(Html::encode($model->region->name), ['region/view', 'id' => $model->region->getPrimaryKey()]) : null,
                'format' => 'raw',
            ],
            [
                'attribute' => 'city_id',
                //'label' => $model->city->modelTitle(),
                'value' => $model->city !== null ?  Html::a(Html::encode($model->city->name), ['city/view', 'id' => $model->city->getPrimaryKey()]) : null,
                'format' => 'raw',
            ],
            'name',
        ],
    ]) ?>

</div>
