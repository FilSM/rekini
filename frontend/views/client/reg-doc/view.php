<?php

use kartik\helpers\Html;
use kartik\detail\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\client\ClientContact */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])){
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $model->modelTitle() .' #'. $model->doc_number;
//}
$this->params['breadcrumbs'][] = ['label' => $clientModel->modelTitle(2), 'url' => ['client/index']];
$this->params['breadcrumbs'][] = ['label' => $clientModel->name, 'url' => ['client/view', 'id' => $clientModel->id]];
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index', 'client_id' => $clientModel->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-reg-doc-view">
    <div class="col-md-2">
        <?= $this->render('@frontend/views/client/client/_menu', [
            'client' => $clientModel,
            'activeItem' => $model->tableName(),
        ])
        ?>
    </div>
    
    <div class="col-md-10">

        <?php /*= Html::pageHeader(Html::encode($this->title)); */?>

        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <?php 
            $body = 
                '<p>'.
                $model->getBackButton().'&nbsp;'.
                Html::a(Yii::t('common', 'Update'), ['update', 'id' => $model->id, 'client_id' => $clientModel->id], ['class' => 'btn btn-primary']).'&nbsp;'.
                \common\components\FSMBtnDialog::button(Yii::t('common', 'Delete'), ['delete', 'id' => $model->id, 'client_id' => $clientModel->id], [
                    'id' => 'btn-dialog-selected',
                    'class' => 'btn btn-danger',
                ]).
                '</p>';
            $body .= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    //'client_id',
                    [
                        'attribute' => 'reg_doc_type_id',
                        'value' => !empty($model->reg_doc_type_id) ? $model->regDocType->name : null,
                    ],            
                    'doc_number',
                    [
                        'attribute' => 'doc_date',
                        'value' => !empty($model->doc_date) ? date('d-m-y', strtotime($model->doc_date)) : null,
                    ],            
                    [
                        'attribute' => 'expiration_date',
                        'value' => !empty($model->expiration_date) ? date('d-m-y', strtotime($model->expiration_date)) : null,
                    ],            
                    'placement',
                    'notification_days',
                    [
                        'attribute' => 'file_name',
                        'value' => isset($model->uploaded_file_id) ? Html::a($model->attachment->filename, $model->attachment->fileurl, ['target' => '_blank']) : null,
                        'format' => 'raw',
                    ],                                  
                    'comment:ntext',
                ],
            ]); 

            $panelContent = [
                'heading' => Html::encode($this->title),
                'preBody' => '<div class="panel-body">',
                'body' => $body,
                'postBody' => '</div>',
            ];
            echo Html::panel(
                $panelContent, 
                'primary', 
                [
                    'id' => "panel-client-reg-doc-data",
                ]
            );        
        ?>
    </div>

</div>