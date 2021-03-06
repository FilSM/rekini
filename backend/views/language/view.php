<?php

use kartik\helpers\Html;
use kartik\detail\DetailView;

/**
 * @var yii\web\View $this
 * @var common\models\Language $model
 */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="language-view">

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
            'language',
            'name',
            'native',
            'enabled:boolean',
        ],
    ]) ?>

</div>
