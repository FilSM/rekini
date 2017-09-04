<?php

use kartik\helpers\Html;
use kartik\detail\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\abonent\Abonent */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])){
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $model->modelTitle() .' #'. $model->id;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="abonent-view">

    <?= Html::pageHeader(Html::encode($this->title)); ?>

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
            [
                'attribute' => 'main_client_id',
                'value' => !empty($model->main_client_id) ? Html::a($model->mainClient->name, ['/client/view', 'id' => $model->main_client_id], ['target' => '_blank']) : null,                         
                'format' => 'raw',
            ],            
            [
                'attribute' => 'subscription_end_date',
                'value' => !empty($model->subscription_end_date) ? date('d-M-Y', strtotime($model->subscription_end_date)) : null,                         
            ],            
            [
                'attribute' => 'subscription_type',
                'value' => isset($model->subscription_type) ? $model->abonentTypeList[$model->subscription_type] : null,
            ],                        
            [
                'attribute' => 'manager_id',
                'value' => isset($model->manager_id, $model->manager) ? Html::a($model->manager->name, ['/user/profile/show', 'id' => (isset($model->manager->user) ? $model->manager->user->id : null)], ['target' => '_blank']) : null,
                'format' => 'raw',
            ],
            'comment:ntext',
            [
                'attribute' => 'deleted',
                'format' => 'boolean',
                'visible' => $isAdmin,
            ],           
        ],
    ]) ?>

</div>