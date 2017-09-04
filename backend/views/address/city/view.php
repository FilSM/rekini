<?php
namespace common\models\address;

use Yii;
use yii\bootstrap\Tabs;

use kartik\helpers\Html;
use kartik\detail\DetailView;

/**
 * @var yii\web\View $this
 * @var common\models\address\City $model
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="city-view">

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
                //'label' => $model->region->modelTitle(),
                'value' => $model->region !== null ?  Html::a(Html::encode($model->region->name), ['region/view', 'id' => $model->region->getPrimaryKey()]) : null,
                'format' => 'raw',
            ],
            'name',
        ],
    ]) ?>

</div>

<?php

    $items = [];
    foreach ($model->districts as $relatedModel) {
        $items[] = ['content' => Html::encode($relatedModel->name), 'url' => ['district/view', 'id' => $relatedModel->id]];
    }
    $districtsContent = '<br/>'.Html::listGroup($items);
    
    echo Tabs::widget([
        'items' => [
            [
                'label' => District::modelTitle(2),
                'content' => $districtsContent,
                'active' => true,
            ],
        ],
        'navType' => 'nav-pills',
    ]);
    
?>