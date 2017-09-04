<?php

use kartik\helpers\Html;

/**
 * @var yii\web\View $this
 * @var common\models\client\Client $model
 */

$this->title = Yii::t($model->tableName(), 'Update a '.$model->modelTitle(1, false)) . ': ' . $model->name;
if(!empty($isOwner)){
    $this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
}
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('common', 'Update');
?>

<div class="row client-update">

    <div class="col-md-2">
        <?=
        $this->render('_menu', [
            'client' => $model,
            'activeItem' => 'general',
        ])
        ?>
    </div>

    <div class="col-md-10">
        <?php
            $body = $this->render('_form', [
                'model' => $model,
                'clientBankModel' => $clientBankModel,
                'filesModel' => $filesModel,
                'countryList' => $countryList,
                'languageList' => $languageList,
                'bankList' => $bankList,
                'valutaList' => $valutaList,
                'managerList' => $managerList,
                'clientGroupList' => $clientGroupList,
                'isAdmin' => $isAdmin,
                'isOwner' => $isOwner,                      
                'isModal' => $isModal,
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
                    'id' => "panel-client-data",
                ]
            );        
        ?>
    </div>
</div>