<?php

use kartik\helpers\Html;
use kartik\detail\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\client\ClientContact */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])){
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $model->first_name.' '.$model->last_name;
//}
$this->params['breadcrumbs'][] = ['label' => $clientModel->modelTitle(2), 'url' => ['client/index']];
$this->params['breadcrumbs'][] = ['label' => $clientModel->name, 'url' => ['client/view', 'id' => $clientModel->id]];
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index', 'client_id' => $clientModel->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-contact-view">
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
            $body = '<p>'.
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
                    'first_name',
                    'last_name',
                    'phone',
                    'email:email',
                    [
                        'attribute' => 'position_id',
                        'value' => !empty($model->position_id) ? $model->position->name : null,
                    ],            
                    'can_sign:boolean',
                    [
                        'attribute' => 'deleted',
                        'format' => 'boolean',
                        'visible' => $isAdmin,
                    ],
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
                    'id' => "panel-client-contact-data",
                ]
            );        
        ?>
    </div>

</div>