<?php

use yii\helpers\Url;

use kartik\helpers\Html;
use kartik\detail\DetailView;

use common\models\user\FSMUser;
use common\models\client\Client;

/* @var $this yii\web\View */
/* @var $model common\models\Client */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $model->modelTitle().': '.$model->name;
//}
if(!empty($isOwner)){
    $this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="row client-view">

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
            ob_start();
            ob_implicit_flush(false);
        ?>
        <div class='col-md-12'>
            <?= Html::a(Yii::t('common', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= \common\components\FSMBtnDialog::button(Yii::t('common', 'Delete'), ['delete', 'id' => $model->id], [
                'id' => 'btn-dialog-selected',
                'class' => 'btn btn-danger',
            ]); ?> 
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
                        'attribute' => 'client_group_id',
                        'value' => !empty($model->client_group_id) ? $model->clientGroup->name : null,
                    ],
                    [
                        'attribute' => 'name',
                        'label' => isset($model->client_type) && ($model->client_type == Client::CLIENT_TYPE_LEGAL) ? $model->getAttributeLabel('name') : Yii::t('client', 'Firstname, Lastname'),
                    ],
                    [
                        'attribute' => 'reg_number',
                        'label' => isset($model->client_type) && ($model->client_type == Client::CLIENT_TYPE_LEGAL) ? $model->getAttributeLabel('reg_number') : Yii::t('client', 'Personal code'),
                    ],
                    [
                        'attribute' => 'vat_number',
                        'visible' => !empty($model->vat_payer),
                    ],
                    [
                        'attribute' => 'tax',
                        'visible' => !empty($model->vat_payer),
                    ],
                    [
                        'attribute' => 'legal_address',
                        'label' => isset($model->client_type) && ($model->client_type == Client::CLIENT_TYPE_LEGAL) ? $model->getAttributeLabel('legal_address') : Yii::t('client', 'Registration address'),
                    ],                            
                    [
                        'attribute' => 'office_address',
                        'label' => isset($model->client_type) && ($model->client_type == Client::CLIENT_TYPE_LEGAL) ? $model->getAttributeLabel('office_address') : Yii::t('client', 'Home address'),
                        'visible' => !empty($model->office_address) && ($model->legal_address != $model->office_address),
                    ], 
                    /*
                    [
                        'attribute' => 'invoice_email',
                        'format' => 'email',
                        'visible' => ($model->client_type == Client::CLIENT_TYPE_LEGAL),
                    ],
                     * 
                     */
                    [
                        'attribute' => 'language_id',
                        'value' => !empty($model->language_id) ? $model->language->name : null,
                    ],
                    [
                        'attribute' => 'manager_id',
                        'value' => isset($model->manager_id, $model->manager) ? Html::a($model->manager->name, ['/user/profile/show', 'id' => (isset($model->manager->user) ? $model->manager->user->id : null)], ['target' => '_blank']) : null,
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'abonent_id',
                        'value' => !empty($model->abonent_id) ? Html::a($model->abonent->name, ['/abonent/view', 'id' => $model->abonent_id], ['target' => '_blank']) : null,
                        'format' => 'raw',
                        'visible' => $isAdmin && !empty($model->abonent_id),
                    ],
                    [
                        'attribute' => 'parent_id',
                        'value' => !empty($model->parent_id) ? Html::a($model->parent->name, ['/client/view', 'id' => $model->parent_id], ['target' => '_blank']) : null,
                        'format' => 'raw',
                        'visible' => !empty($model->parent_id),
                    ],
                    [
                        'attribute' => 'client_type',
                        'value' => isset($model->client_type) ? $model->clientTypeList[$model->client_type] : null,
                    ],
                    [
                        'attribute' => 'it_is',
                        'value' => isset($model->it_is) ? $model->clientItIsList[$model->it_is] : null,
                        'visible' => $isAdmin,
                    ],
                    [
                        'attribute' => 'status',
                        'value' => isset($model->status) ? $model->clientStatusList[$model->status] : null,
                    ],
                    'comment:ntext',
                    [
                        'attribute' => 'uploaded_file_id',
                        'value' => !empty($logoPath) ? Html::img($logoPath, ['width' => '100px']) : null,
                        'format' => 'html',    
                        'visible' => !empty($logoPath),
                    ],                            
                    [
                        'attribute' => 'deleted',
                        'format' => 'boolean',
                        'visible' => $isAdmin,
                    ],
                    //'create_time',
                    //'create_user_id',
                    //'update_time',
                    //'update_user_id',
                ],
            ]) ?>

            <?= $this->render('_bank_table_view', [
                'dataProvider' => $clientBankdataProvider,
                'searchModel' => $clientBankSearchModel,
                'clientModel' => $model,
                'isAdmin' => $isAdmin,
            ]) ?>                    
        </div>
        
        <?php
            $body = ob_get_contents();
            ob_get_clean(); 

            $panelContent = [
                'heading' => Yii::t('client', 'About client'),
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


