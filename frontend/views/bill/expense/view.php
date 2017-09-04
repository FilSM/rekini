<?php

use kartik\helpers\Html;
use kartik\detail\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\bill\Expense */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])){
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $model->modelTitle() .' #'. $model->doc_number;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expense-view">

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
            [
                'attribute' => 'expense_type_id',
                'value' => isset($model->expense_type_id) ? $model->expenseType->name : null,
            ],    
            /*
            [
                'attribute' => 'abonent_id',
                'value' => !empty($model->abonent_id) ? Html::a($model->abonent->name, ['/abonent/view', 'id' => $model->abonent_id], ['target' => '_blank']) : null,
                'format' => 'raw',
                'visible' => $isAdmin && !empty($model->abonent_id),
            ], 
             * 
             */
            [
                'attribute' => 'project_id',
                'value' => !empty($model->project_id) ? Html::a($model->project->name, ['/project/view', 'id' => $model->project_id], ['target' => '_blank']) : null,
                'format' => 'raw',
            ],
            'doc_number',
            [
                'attribute' => 'doc_date',
                'value' => isset($model->doc_date) ? date('d-M-Y', strtotime($model->doc_date)) : null,
            ],            
            [
                'attribute' => 'first_client_id',
                'value' => !empty($model->first_client_id) ? 
                    Html::a($model->firstClient->name, ['/client/view', 'id' => $model->first_client_id], ['target' => '_blank']) : null,
                'format' => 'raw',
            ],            
            [
                'attribute' => 'second_client_id',
                'value' => !empty($model->second_client_id) ?   
                    Html::a($model->secondClient->name, ['/client/view', 'id' => $model->second_client_id], ['target' => '_blank']) : null,
                'format' => 'raw',
            ],              
            'summa',          
            'vat',
            [
                'attribute' => 'total',
                'value' => isset($model->valuta_id) ? $model->total . ' ' . $model->valuta->name : $model->total,
            ],
            'comment:ntext',
        ],
    ]) ?>

</div>