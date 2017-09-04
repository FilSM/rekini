<?php
namespace common\models\address; 

use Yii; 
use kartik\helpers\Html;
use kartik\detail\DetailView;
use yii\bootstrap\Tabs; 
use yii\helpers\ArrayHelper; 

/**
 * @var yii\web\View $this
 * @var common\models\address\Country $model
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="country-view">

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
            'name',
            'short_name',
            'currency',
        ],
    ]) ?>

</div>

<?php 

    $items = []; 
    foreach ($model->regions as $relatedModel) { 
        $items[] = ['content' => Html::encode($relatedModel->name), 'url' => ['region/view', 'id' => $relatedModel->id]]; 
    } 
    $regionsContent = '<br/>'.Html::listGroup($items); 

    $items = []; 
    foreach ($model->cities as $relatedModel) { 
        $items[] = ['content' => Html::encode($relatedModel->name), 'url' => ['city/view', 'id' => $relatedModel->id]]; 
    } 
    $citiesContent = '<br/>'.Html::listGroup($items); 

    $items = []; 
    foreach ($model->districts as $relatedModel) { 
        $items[] = ['content' => Html::encode($relatedModel->name), 'url' => ['district/view', 'id' => $relatedModel->id]]; 
    } 
    $districtsContent = '<br/>'.Html::listGroup($items); 

    echo Tabs::widget([ 
        'items' => [ 
            [ 
                'label' => Region::modelTitle(2), 
                'content' => $regionsContent, 
                'active' => true, 
            ], 
            [ 
                'label' => City::modelTitle(2), 
                'content' => $citiesContent, 
            ], 
            [ 
                'label' => District::modelTitle(2), 
                'content' => $districtsContent, 
            ], 
        ], 
        'navType' => 'nav-pills', 
    ]); 

?> 