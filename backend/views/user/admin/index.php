<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\widgets\Pjax;
//use yii\jui\DatePicker;

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;
//use kartik\widgets\DatePicker;

use common\models\client\Client;

/**
 * @var View $this
 * @var ActiveDataProvider $dataProvider
 * @var UserSearch $searchModel
 */

$this->title = Yii::t('user', 'Manage users');
$this->params['breadcrumbs'][] = $this->title;
?>
<div id="user-index" class="page-index">
    
    <?= Html::pageHeader(Html::encode($this->title));?>
    <p>
        <?= Html::a(Yii::t('user', 'Create a user account'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= \common\components\FSMBtnDialog::button(Yii::t('common', 'Delete selected'), ['delete-selected'], [
            'model' => $searchModel,
            'grid' => 'grid-view',
            'id' => 'btn-dialog-selected',
            'class' => 'btn btn-danger',
            'confirm' => Yii::t('common', 'Are you sure you want to delete selected item(s)?'),
            'disabled' => true,
        ]); ?>
    </p>

<?php

$columns = [
    [
        'class' => 'yii\grid\CheckboxColumn',
    ],
    [
        'class' => '\kartik\grid\SerialColumn',
        'visible' => false,//!$isAdmin,
    ],
    [
        'attribute' => 'id',
        'hAlign' => 'center',
        //'visible' => $isAdmin,
    ],    
    [
        'attribute' => 'clientItIs',
        'headerOptions' => ['class'=>'td-mw-150'],
        'value' => function ($model) {
            return isset($model->profile, $model->profile->client) && !empty($model->profile->client->it_is) ? $model->profile->client->clientItIsList[$model->profile->client->it_is] : null;
        },
        'format'=>'raw',       
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => $itIsList,
        'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
        'filterInputOptions' => ['placeholder' => Yii::t('user', 'Any...')],
        'visible' => $isAdmin,
    ],     
    [
        'attribute' => 'client_id',
        'value' => function ($model, $index, $widget) {
            return !empty($client = $model->profile->client) ? Html::a($client->name, ['/client/view', 'id' => $model->profile->client_id]) : null;
        }, 
        'format' => 'raw', 
        'visible' => $isAdmin,
    ],
    [
        'attribute' => 'fullName',
        'value' => function ($model, $index, $widget) {
            return !empty($model->profile) ? Html::a($model->profile->name, ['/user/profile/show', 'id' => $model->id]) : null;
        },
        'format' => 'raw',
    ],        
    [
        'attribute' => 'phone',
        'value' => 'profile.phone',
    ],        
    [
        'attribute' => 'username',
        'value' => function ($model, $index, $widget) {
            return Html::a($model->username, ['/user/profile/show', 'id' => $model->id]);
        },
        'format' => 'raw',
    ],
    'email:email',
    [
        'attribute' => 'registration_ip',
        'value' => function ($model) {
            return $model->registration_ip == null
                ? '<span class="not-set">' . Yii::t('user', '(not set)') . '</span>'
                : $model->registration_ip;
        },
        'format' => 'html',
    ],
                /*
    [
        'attribute' => 'created_at',
        'width' => '200px',
        'value' => function ($model) {
            //if (extension_loaded('intl')) {
                //return Yii::t('user', '{0, date, MMMM dd, YYYY HH:mm}', [$model->created_at]);
            //} else {
                return date('Y-m-d G:i:s', $model->created_at);
            //}
        },
        'filter' => DatePicker::widget([
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]), 
    ],
    [
      'attribute' => 'last_login_at',
      'value' => function ($model) {
        if (!$model->last_login_at || $model->last_login_at == 0) {
            return Yii::t('user', 'Never');
        } else if (extension_loaded('intl')) {
            return Yii::t('user', '{0, date, MMMM dd, YYYY HH:mm}', [$model->last_login_at]);
        } else {
            return date('Y-m-d G:i:s', $model->last_login_at);
        }
      },
    ],                
                 * 
                 */
                /*
    [ 
        'attribute' => 'created_at',
        'value' => function ($model) { 
            return date('Y-m-d G:i', $model->created_at);
        }, 
        'headerOptions' => [ 
            'class' => 'td-mw-200', 
        ],
        'filter' => DateRangePicker::widget([ 
            'model' => $searchModel, 
            'attribute' => 'created_at',
            //'convertFormat' => true,
            'presetDropdown' => true,
            'pluginOptions' => [ 
                'locale' => [
                    'firstDay' => 1,
                    'format' => 'YYYY-MM-DD',
                ],
            ]
        ]), 
    ],     
                 * 
                 */
    [ 
        'attribute' => 'last_login_at',
        'value' => function ($model) { 
            if (!$model->last_login_at || $model->last_login_at == 0) {
                return Yii::t('user', 'Never');
            } else {
                return date('Y-m-d G:i', $model->last_login_at);
            }            
        }, 
        'headerOptions' => [ 
            'class' => 'td-mw-200', 
        ]/*,
        'filter' => DateRangePicker::widget([ 
            'model' => $searchModel, 
            'attribute' => 'last_login_at',
            //'convertFormat' => true,
            'presetDropdown' => true,
            'pluginOptions' => [ 
                'locale' => [
                    'firstDay' => 1,
                    'format' => 'YYYY-MM-DD',
                ],
            ]
        ])*/, 
    ],                
    [
        'attribute' => 'role',
        'width' => '200px',
        'value' => function ($model) {
            $userRoleList = $model->myRoleList;
            $roleList = $model->roleList;
            $result = [];
            foreach ($roleList as $key => $value) {
                if(in_array($key, $userRoleList)){
                    $result[] = $value;
                }
            }
            return implode(', ', $result);
        },
        'filterType' => GridView::FILTER_SELECT2,
        'filter' => $dataFilterRoles,
        'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
        'filterInputOptions' => ['placeholder' => '...'],
    ],
    [
        'header' => Yii::t('user', 'Confirmation'),
        'value' => function ($model) {
            if ($model->isConfirmed) {
                return '<div class="text-center"><span class="text-success">' . Yii::t('user', 'Confirmed') . '</span></div>';
            } else {
                return Html::a(Yii::t('user', 'Confirm'), ['confirm', 'id' => $model->id], [
                    'class' => 'btn btn-xs btn-success btn-block',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to confirm this user?'),
                ]);
            }
        },
        'format' => 'raw',
        'visible' => Yii::$app->getModule('user')->enableConfirmation,
    ],
    [
        'header' => Yii::t('user', 'Block status'),
        'value' => function ($model) {
            if ($model->isBlocked) {
                return Html::a(Yii::t('user', 'Unblock'), ['block', 'id' => $model->id], [
                    'class' => 'btn btn-xs btn-success btn-block',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to unblock this user?'),
                ]);
            } else {
                return Html::a(Yii::t('user', 'Block'), ['block', 'id' => $model->id], [
                    'class' => 'btn btn-xs btn-danger btn-block',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to block this user?'),
                ]);
            }
        },
        'format' => 'raw',
    ],
    [
        'class' => '\kartik\grid\ActionColumn',
        'template' => '{update} {delete}',
        'buttons' => [
            'update' => function ($url, $model) {
                return Html::a(Html::icon('pencil'), ['/user/settings', 'id' => $model->id], [
                    'class' => 'btn btn-xs btn-info',
                    'title' => Yii::t('kvgrid', 'Update'),
                ]);
            },
            'delete' => function ($url, $model) {
                return Html::a(Html::icon('trash'), $url, [
                    'class' => 'btn btn-xs btn-danger',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure to delete this user?'),
                    'title' => Yii::t('kvgrid', 'Delete'),
                ]);
            },
        ]
    ],

];
?>

<?= GridView::widget([
    'id' => 'grid-view',
    'dataProvider' => $dataProvider,
    'filterModel'  => $searchModel,
    'responsive' => false,
    'layout' => "{items}\n{pager}",
    //'layout' => "{pager}\n{items}\n{pager}",
    'columns' => $columns,
    'floatHeader' => true,
]); ?>
    
</div>
