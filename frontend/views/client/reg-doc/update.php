<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\client\ClientContact */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Update a '.$model->modelTitle(1, false)) . ': ' . $model->doc_number;
//}
$this->params['breadcrumbs'][] = ['label' => $clientModel->modelTitle(2), 'url' => ['client/index']];
$this->params['breadcrumbs'][] = ['label' => $clientModel->name, 'url' => ['client/view', 'id' => $clientModel->id]];
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index', 'client_id' => $clientModel->id]];
$this->params['breadcrumbs'][] = ['label' => $model->doc_number, 'url' => ['view', 'id' => $model->id, 'client_id' => $clientModel->id]];
$this->params['breadcrumbs'][] = Yii::t('common', 'Update');
?>
<div class="client-reg-doc-update">
    
    <div class="col-md-2">
        <?=
        $this->render('@frontend/views/client/client/_menu', [
            'client' => $clientModel,
            'activeItem' => $model->tableName(),
        ])
        ?>
    </div>
    
    <div class="col-md-10">
        <?php 
            $body = $this->render('_form', [
                'model' => $model,
                'clientModel' => $clientModel,
                'filesModel' => $filesModel,
                'regDocTypeList' => $regDocTypeList,
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
                    'id' => "panel-client-reg-doc-data",
                ]
            );             
        ?>
    </div>

</div>