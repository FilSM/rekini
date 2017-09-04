<?php

use yii\helpers\Url;

use kartik\helpers\Html;

use common\models\client\Client;

/* @var $this yii\web\View */
/* @var $model common\models\Client */

$this->title = $model->name.': '.Yii::t('client', 'Staff');
if(!empty($isOwner)){
    $this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['client/index']];
}
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['client/view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row client-view">

    <div class="col-md-2">
        <?=
        $this->render('_menu', [
            'client' => $model,
            'activeItem' => 'staff',
        ])
        ?>
    </div>

    <div class="col-md-10">
        <?php 
        
        $body = Html::button(Yii::t('fsmuser', 'Add user to the client company'), [
            'value' => Url::to('@web/user/admin/create'), 
            'class' => 'btn btn-success show-modal-button', 
            'id' => 'modal-add-user-to-company',
            'title' => '',
        ]).'<p></p>';                
        $body .= Html::panel(
            [
                //'heading' => Yii::t('client', 'Client staff'),
                'body' => Html::listGroup($userList),
            ], 
            'primary'
        );
        
        echo Html::panel(
            [
                'heading' => Yii::t('client', 'Client staff'),
                'preBody' => '<div class="panel-body"><div class="cargo-staff-index">',
                'body' => $body,
                'postBody' => '</div></div>',
            ], 
            'primary'
        );    
        ?>  
    </div>
</div>


