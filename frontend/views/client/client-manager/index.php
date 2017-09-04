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
/* @var $searchModel common\models\client\search\ClientManagerSearch */
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
<div class="client-manager-index">

    <div class="col-md-2">
        <?= $this->render('@frontend/views/client/client/_menu', [
            'client' => $clientModel,
            'activeItem' => 'client-manager',
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
                    'first_name',
                    'last_name',
                    'phone',
                    'email:email',
                    'position_name',
                    [
                        'label' => Yii::t('common', 'Period'),
                        'headerOptions' => ['class'=>'td-mw-100'],
                        'value' => function ($model) {
                            if(empty($model->term_from) && empty($model->term_till)){
                                $period = '';
                            }elseif(!empty($model->term_from) && empty($model->term_till)){
                                $period = Yii::t('common', 'From').' '.date('d-M-Y', strtotime($model->term_from));
                            }elseif(!empty($model->term_from) && !empty($model->term_till)){
                                $period = date('d-M-Y', strtotime($model->term_from)).' / '.date('d-M-Y', strtotime($model->term_till));
                            }elseif(empty($model->term_from) && !empty($model->term_till)){
                                $period = Yii::t('common', 'Till').' '.date('d-M-Y', strtotime($model->term_till));
                            }
                            return $period;
                        },                         
                    ],            
                    'share',
                    [
                        'attribute' => "can_sign",                
                        'class' => '\kartik\grid\BooleanColumn',
                        'trueLabel' => 'Yes', 
                        'falseLabel' => 'No',
                        'headerOptions' => [
                            'class' => 'td-mw-100',
                        ],
                    ],
                    [
                        'attribute' => "deleted",                
                        'class' => '\kartik\grid\BooleanColumn',
                        'trueLabel' => 'Yes', 
                        'falseLabel' => 'No',
                        'headerOptions' => [
                            'class' => 'td-mw-100',
                        ],
                        'visible' => $isAdmin,
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
                    'id' => "panel-client-contact-data",
                ]
            );        
        ?>
    </div>
</div>