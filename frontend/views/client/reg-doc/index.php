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
/* @var $searchModel common\models\client\search\ClientContactSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = ['label' => $clientModel->modelTitle(2), 'url' => ['client/index']];
$this->params['breadcrumbs'][] = ['label' => $clientModel->name, 'url' => ['client/view', 'id' => $clientModel->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-reg-doc-index">

    <div class="col-md-2">
        <?= $this->render('@frontend/views/client/client/_menu', [
            'client' => $clientModel,
            'activeItem' => $searchModel->tableName(),
        ])
        ?>
    </div>
    
    <div class="col-md-10">

        <?php /*= Html::pageHeader(Html::encode($this->title)); */?>

        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <?php 
            $body = '<p>'.
                    Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create', 'client_id' => $clientModel->id], ['class' => 'btn btn-success']).
                    '</p>';
            $body .= GridView::widget([
                'responsive' => false,
                //'striped' => false,
                'hover' => true,
                'dataProvider' => $dataProvider,
                //'filterModel' => $searchModel,
                'floatHeader' => true,
                'columns' => [
                //['class' => '\kartik\grid\SerialColumn'],

                    [
                        'attribute' => 'id',
                        'width' => '75px',
                        'hAlign' => 'center',
                    ],
                    //'client_id',
                    [
                        'attribute' => 'reg_doc_type_name',
                        'value' => function ($model) {
                            return !empty($model->reg_doc_type_id) ? $model->reg_doc_type_name : null;
                        },     
                    ],
                    'doc_number',
                    'doc_date',
                    'expiration_date',
                    'placement',
                    'notification_days',
                    [
                        'attribute' => 'file_name',
                        'value' => function ($model) {
                            return isset($model->uploaded_file_id) ? Html::a($model->attachment->filename, $model->attachment->fileurl, ['target' => '_blank']) : null;
                        },          
                        'format' => 'raw',
                    ],                                  
                    'comment:ntext',
                    [
                        'class' => '\common\components\FSMActionColumn',
                        'headerOptions' => ['class'=>'td-mw-125'],
                        'dropdown' => true,
                        //'width' => '150px',
                        'template' => '{view} {update} {delete}',
                        'linkedObj' => [
                            ['fieldName' => 'client_id', 'id' => (!empty($clientModel->id) ? $clientModel->id : null)],
                        ],
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
                    'id' => "panel-client-reg-doc-data",
                ]
            );        
        ?>
    </div>
</div>