<?php

use kartik\helpers\Html;
use kartik\detail\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\client\Shareholder */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])){
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $model->modelTitle() .': '. $model->shareholder->name;
//}
$this->params['breadcrumbs'][] = ['label' => $clientModel->modelTitle(2), 'url' => ['client/index']];
$this->params['breadcrumbs'][] = ['label' => $clientModel->name, 'url' => ['client/view', 'id' => $clientModel->id]];
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index', 'client_id' => $clientModel->id]];
$this->params['breadcrumbs'][] = $this->title;

$period = '';
if(!empty($model->term_from) && empty($model->term_till)){
    $period = Yii::t('common', 'From').' '.$model->term_from;
}elseif(!empty($model->term_from) && !empty($model->term_till)){
    $period = $model->term_from.' / '.$model->term_from;
}elseif(empty($model->term_from) && !empty($model->term_till)){
    $period = Yii::t('common', 'Till').' '.$model->term_till;
}
?>
<div class="shareholder-view">
    <div class="col-md-2">
        <?= $this->render('@frontend/views/client/client/_menu', [
            'client' => $clientModel,
            'activeItem' => 'our-shareholders',
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
                    [
                        'attribute' => 'shareholder_id',
                        'value' => !empty($model->shareholder_id) ? Html::a($model->shareholder->name, ['/client/view', 'id' => $model->shareholder_id], ['target' => '_blank']) : null,
                        'format' => 'raw',
                    ],                      
                    [
                        'label' => Yii::t('common', 'Period'),
                        'value' => $period,
                    ],            
                    'share',
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
                    'id' => "panel-shareholder-data",
                ]
            );        
        ?>
    </div>

</div>