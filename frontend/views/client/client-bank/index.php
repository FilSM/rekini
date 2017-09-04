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
/* @var $searchModel common\models\client\search\ClientBankSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = $this->title;

$isAdmin = !empty($isAdmin);
?>
<div class="client-bank-index">

    <?php if(empty($clientModel->id)) : ?>
    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>
    <?php endif; ?>
    
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => empty($clientModel->id) ? $searchModel : null,
        'columns' => [
            [
                'class' => '\kartik\grid\SerialColumn',
                'visible' => !empty($clientModel->id),
            ],
            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
                'visible' => empty($clientModel->id),
            ],
            [
                'attribute' => 'client_id',
                'value' => function ($model) {
                    return !empty($model->client_id) ? Html::a($model->client_name, ['/client/view', 'id' => $model->client_id], ['target' => '_blank']) : null;
                }, 
                'format' => 'raw',
                'visible' => empty($clientModel->id),
            ],              
            [
                'attribute' => 'bank_id',
                'value' => function ($model) {
                    return !empty($model->bank_id) ? Html::a($model->bank_name, ['/bank/view', 'id' => $model->bank_id], ['target' => '_blank']) : null;
                }, 
                'format' => 'raw',
            ],              
            'account',
            'name',
            [
                'attribute' => 'uploaded_file_id',
                'value' => function ($model) {
                    return isset($model->uploaded_file_id) ? Html::a($model->uploadedFile->filename, $model->uploadedFile->fileurl, ['target' => '_blank']) : null;
                },          
                'format' => 'raw',
            ],                          
            [
                'attribute' => 'balance',
                'value' => function ($model) {
                    return isset($model->balance) ? $model->balance.''.$model->currency : null;
                },          
            ],                          
            
            [
                'attribute' => "deleted",                
                'class' => '\kartik\grid\BooleanColumn',
                'trueLabel' => 'Yes', 
                'falseLabel' => 'No',
                'width' => '100px',
                'visible' => $isAdmin && empty($clientModel->id),
            ],

            [
                'class' => '\common\components\FSMActionColumn',
                'headerOptions' => ['class'=>'td-mw-125'],
                'dropdown' => true,
                'viewOptions' => ['noTarget' => true],
                //'width' => '150px',
                'template' => '{view} {update} {delete}',
                'linkedObj' => [
                    ['fieldName' => 'client_id', 'id' => (!empty($clientModel->id) ? $clientModel->id : null)],
                ],
                'visible' => empty($clientModel->id),
            ],   
        ],
    ]); ?>
</div>