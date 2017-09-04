<?php
namespace common\models;

use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;
use yii\widgets\Pjax;

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

use common\models\bill\PaymentConfirm;

/* @var $this yii\web\View */
/* @var $searchModel common\models\bill\search\PaymentConfirmSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-confirm-index">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>
    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => '\kartik\grid\SerialColumn'],
            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
            ],
            'name',
            [
                'attribute' => 'bank_id',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->bank_id) ? Html::a($model->bank_name, ['/bank/view', 'id' => $model->bank_id], ['target' => '_blank', 'data-pjax' => 0,]) : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $bankList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'format' => 'raw',
            ], 
            [
                'attribute' => 'client_id',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->client_id) ? Html::a($model->client_name, ['/client/view', 'id' => $model->client_id], ['target' => '_blank', 'data-pjax' => 0,]) : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $clientList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'format' => 'raw',
            ],                         
            'client_reg_number',
            [
                //'attribute' => 'file_name_xml',
                'label' => Yii::t('common', 'Attachments'),
                'mergeHeader' => true,
                'headerOptions' => ['style' => 'text-align: center;'],
                'contentOptions' => ['style' => 'white-space: nowrap;'],
                'value' => function ($model) {
                    $result = [];
                    $fileName = isset($model->uploaded_file_id) ? 
                        '<span style="font-weight: bold;">XML: </span>'.Html::a($model->file_name_xml, $model->uploadedFile->fileurl, ['target' => '_blank', 'data-pjax' => 0,]) : null;
                    if($fileName){
                        $result[] = $fileName;
                    }
                    $fileName = isset($model->uploaded_pdf_file_id) ? 
                        '<span style="font-weight: bold;">PDF: </span>'.Html::a($model->file_name_pdf, $model->uploadedPdfFile->fileurl, ['target' => '_blank', 'data-pjax' => 0,]) : null;
                    if($fileName){
                        $result[] = $fileName;
                    }
                    return implode('<br/>', $result);
                },          
                'format' => 'raw',
            ],            
            [
                'attribute' => 'start_date',
                'label' => Yii::t('bill', 'Start-End dates'),
                'hAlign' => 'center',
                'width' => '100px',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->start_date) ? date('d-M-Y', strtotime($model->start_date)).(isset($model->end_date) ? '<br/>'.date('d-M-Y', strtotime($model->end_date)) : '') : null;
                },
                'format' => 'raw',
            ],  
            [
                'attribute' => 'pay_date',
                'hAlign' => 'center',
                'width' => '100px',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->pay_date) ? date('d-M-Y', strtotime($model->pay_date)) : null;
                },                         
            ],                          
            [
                'attribute' => 'status',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->status) ? $model->importStateList[$model->status] : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $searchModel->importStateList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
            ],
            [
                'attribute' => 'action_time',
                'hAlign' => 'center',
                'width' => '100px',
                'headerOptions' => ['class'=>'td-mw-100'],
                'value' => function ($model) {
                    return isset($model->action_time) ? date('d-M-Y H:i', strtotime($model->action_time)) : null;
                },                         
            ],            
            [
                'attribute' => 'action_user_id',
                'headerOptions' => ['class'=>'td-mw-150'],
                'value' => function ($model) {
                    return isset($model->action_user_id) ? Html::a($model->user_name, ['/user/profile/show', 'id' => $model->action_user_id], ['target' => '_blank', 'data-pjax' => 0,]) : null;
                },                         
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => $userList,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => '...'],
                'format' => 'raw',
            ],
            //'comment:ntext',
            // 'create_time',
            // 'create_user_id',
            // 'update_time',
            // 'update_user_id',

            [
                'class' => '\common\components\FSMActionColumn',
                'headerOptions' => ['class'=>'td-mw-125'],
                'dropdown' => true,
                'dropdownDefaultBtn' => 'import',
                'template' => '{import} {view} {update} {delete}',
                'buttons' => [
                    'import' => function (array $params) { 
                        return PaymentConfirm::getButtonImport($params);
                    },
                ]                
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>