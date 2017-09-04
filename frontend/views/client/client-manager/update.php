<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\client\ClientManager */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Update a '.$model->modelTitle(1, false)) . ': ' . $model->first_name.' '.$model->last_name;
//}
$this->params['breadcrumbs'][] = ['label' => $clientModel->modelTitle(2), 'url' => ['client/index']];
$this->params['breadcrumbs'][] = ['label' => $clientModel->name, 'url' => ['client/view', 'id' => $clientModel->id]];
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index', 'client_id' => $clientModel->id]];
$this->params['breadcrumbs'][] = ['label' => $model->first_name.' '.$model->last_name, 'url' => ['view', 'id' => $model->id, 'client_id' => $clientModel->id]];
$this->params['breadcrumbs'][] = Yii::t('common', 'Update');
?>
<div class="client-manager-update">
    
    <div class="col-md-2">
        <?=
        $this->render('@frontend/views/client/client/_menu', [
            'client' => $clientModel,
            'activeItem' => 'client-manager',
        ])
        ?>
    </div>
    
    <div class="col-md-10">
        <?php 
            $body = $this->render('_form', [
                'model' => $model,
                'clientModel' => $clientModel,
                'positionList' => $positionList,
                'isAdmin' => $isAdmin,
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