<?php

use kartik\helpers\Html;
use kartik\detail\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\client\Agreement */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])){
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $model->modelTitle() .' #'. $model->number;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agreement-view">

    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <div class='col-md-12'>                    
        <div class='col-md-6'>
            <?= Html::a(Yii::t('common', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= \common\components\FSMBtnDialog::button(Yii::t('common', 'Delete'), ['delete', 'id' => $model->id], [
                'id' => 'btn-dialog-selected',
                'class' => 'btn btn-danger',
            ]); ?> 
        </div>
        <div class='col-md-6' style="text-align: right;">
            <?= Html::a(Html::icon('print').'&nbsp;'.Yii::t('common', 'Print'), 
                    (empty($model->uploaded_file_id) ? '#' : ['print', 'id' => $model->id]), 
                    [
                        'class' => 'btn '.(empty($model->uploaded_file_id) ? 'btn-danger': 'btn-success'),
                        'disabled' => empty($model->uploaded_file_id),
                    ]);
            ?>
        </div>
    </div>
    
    <div class='col-md-12'>
        <p></p>
    </div>                    

    <div class='col-md-12'>
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'id',
                [
                    'attribute' => 'agreement_type',
                    'value' => isset($model->agreement_type) ? $model->agreementTypeList[$model->agreement_type] : null,
                ],
                [
                    'attribute' => 'project_id',
                    'value' => !empty($model->project_id) ? Html::a($model->project->name, ['/project/view', 'id' => $model->project_id], ['target' => '_blank']) : null,
                    'format' => 'raw',
                ],            
                [
                    'attribute' => 'parent_id',
                    'value' => !empty($model->parent_id) ? Html::a($model->parent->number, ['/agreement/view', 'id' => $model->parent_id], ['target' => '_blank']) : null,
                    'format' => 'raw',
                    'visible' => !empty($model->parent_id),
                ],
                [
                    'attribute' => 'abonent_id',
                    'value' => !empty($model->abonent_id) ? Html::a($model->abonent->name, ['/abonent/view', 'id' => $model->abonent_id], ['target' => '_blank']) : null,
                    'format' => 'raw',
                    'visible' => $isAdmin && !empty($model->abonent_id),
                ],            
                [
                    'attribute' => 'first_client_id',
                    'value' => !empty($model->first_client_id) ? 
                        (!empty($model->first_client_role_id) ? $model->firstClientRole->name.': ' : '') .
                        Html::a($model->firstClient->name, ['/client/view', 'id' => $model->first_client_id], ['target' => '_blank']) : null,
                    'format' => 'raw',
                ],            
                [
                    'attribute' => 'second_client_id',
                    'value' => !empty($model->second_client_id) ?   
                        (!empty($model->second_client_role_id) ? $model->secondClientRole->name.': ' : '') . 
                        Html::a($model->secondClient->name, ['/client/view', 'id' => $model->second_client_id], ['target' => '_blank']) : null,
                    'format' => 'raw',
                ],            
                [
                    'attribute' => 'third_client_id',
                    'value' => !empty($model->third_client_id) ?   
                        (!empty($model->third_client_role_id) ? $model->thirdClientRole->name.': ' : '') . 
                        Html::a($model->thirdClient->name, ['/client/view', 'id' => $model->third_client_id], ['target' => '_blank']) : null,
                    'format' => 'raw',
                    'visible' => !empty($model->third_client_id),
                ],            
                'number',
                'signing_date',
                'due_date',
                'summa',
                [
                    'attribute' => 'rate',
                    'visible' => !empty($model->rate),
                ],
                [
                    'attribute' => 'rate_summa',
                    'visible' => !empty($model->rate_summa),
                ],
                [
                    'attribute' => 'rate_from_date',
                    'label' => Yii::t('client', 'Interest period'),
                    'value' => isset($model->rate_from_date) && isset($model->rate_till_date) ? 
                        date('d-M-Y', strtotime($model->rate_from_date)) . ' | ' . date('d-M-Y', strtotime($model->rate_till_date)) : null,
                    'visible' => !empty($model->rate_from_date) && !empty($model->rate_till_date),
                ],
                [
                    'attribute' => 'status',
                    'value' => isset($model->status) ? $model->agreementStatusList[$model->status] : null,
                ],
                [
                    'attribute' => 'conclusion',
                    'value' => isset($model->conclusion) ? $model->agreementConclusionList[$model->conclusion] : null,
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

</div>