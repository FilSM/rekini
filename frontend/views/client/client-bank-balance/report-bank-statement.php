<?php
namespace common\models;

use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel common\models\client\search\ClientBankBalanceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle().': '.$searchModel->account->account;
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-bank-balance-index">

    <?= Html::pageHeader(
        Html::tag('small', Yii::t('client', 'Client').': '.
            Html::a($searchModel->account->client->name, ['/client/view', 'id' => $searchModel->account->client_id], ['target' => '_blank'])
        ).'<br/>'.
        Html::tag('small', Yii::t('bill', 'Bank').': '.
            Html::a($searchModel->account->bank->name, ['/bank/view', 'id' => $searchModel->account->bank_id], ['target' => '_blank'])
        ).'<br/>'.
        Html::tag('small', Html::encode(Yii::t('client', 'Account').': '.$searchModel->account->account))
    ); ?>
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => '\kartik\grid\SerialColumn',
                'visible' => !$isAdmin,
            ],
            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
                'visible' => $isAdmin,
            ],
            [
                'attribute' => 'payment_confirm_id',
                'value' => function ($model) {
                    return !empty($model->payment_confirm_id) ? Html::a($model->paymentConfirm->name, ['/payment-confirm/view', 'id' => $model->payment_confirm_id], ['target' => '_blank']) : null;
                }, 
                'format' => 'raw',
            ],                      
            [
                'attribute' => 'uploaded_file_id',
                'value' => function ($model) {
                    return isset($model->uploaded_file_id) ? Html::a($model->uploadedFile->filename, $model->uploadedFile->fileurl, ['target' => '_blank', 'data-pjax' => 0]) : null;
                },          
                'format' => 'raw',
            ],
            [
                'attribute' => 'start_date',
                'width' => '100px',
                'value' => function ($model) {
                    return isset($model->start_date) ? date('d-M-Y', strtotime($model->start_date)) : null;
                },
            ],                          
            [
                'attribute' => 'end_date',
                'width' => '100px',
                'value' => function ($model) {
                    return isset($model->end_date) ? date('d-M-Y', strtotime($model->end_date)) : null;
                },
            ], 
            [
                'attribute' => 'balance',
                'width' => '150px',
                'hAlign' => 'right',
                'value' => function ($model) {
                    return isset($model->balance) ? $model->balance : null;
                    //return isset($model->balance) ? number_format($model->balance, 2).' '.$model->currency : null;
                },
                'format' => ['decimal', 2],
            ],                          
            [
                'attribute' => 'currency',
                'width' => '100px',
                'value' => function ($model) {
                    return isset($model->currency) ? $model->currency : null;
                },
            ], 

            [
                'class' => '\common\components\FSMActionColumn',
                'headerOptions' => ['class'=>'td-mw-125'],
                'visible' => $isAdmin,
            ],          
        ],
    ]); ?>
</div>