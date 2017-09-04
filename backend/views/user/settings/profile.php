<?php
/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace common\models;

use Yii;
use yii\widgets\MaskedInput;
use yii\helpers\Url;
use yii\web\JsExpression;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;

use common\widgets\EnumInput;
//use common\widgets\GeocodeInput;
//use common\widgets\GMapInput;
use common\widgets\AutocompleteGMapInput;
use common\models\client\Client;

/**
 * @var yii\web\View $this
 * @var yii\widgets\ActiveForm $form
 * @var dektrium\user\models\Profile $profile
 */
$this->title = Yii::t('user', 'Profile settings');
if(Yii::$app->user->can('showBackend')){
    $this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Users'), 'url' => ['/user/admin/index']];
}
$this->params['breadcrumbs'][] = $this->title;
$username = empty($model->name) ? Html::encode($model->user->username) : Html::encode($model->name);
?>

<div class="row">

    <div class="col-md-2">
        <?= $this->render('_menu',[
            'profile' => $model,
            'activeItem' => 'profile',
            'client' => isset($client) ? $client : null,
            'isAdmin' => $isAdmin,
            'isOwner' => $isOwner,
        ]) ?>
    </div>

    <div class="col-md-10">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <?= Html::encode($this->title) ?>
            </div>
            <div class="panel-body">
                
                <?php $form = ActiveForm::begin([
                    'type' => ActiveForm::TYPE_HORIZONTAL,
                    'id' => 'profile-form',
                    'formConfig' => [
                        'labelSpan' => 3,
                    ],
                    'fieldConfig' => [
                        //'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-sm-offset-3 col-lg-9\">\n{hint}\n{error}</div>",
                        //'labelOptions' => ['class' => 'col-lg-3 control-label'],
                        'showHints' => true,
                    ],
                    'enableAjaxValidation' => true,
                    'enableClientValidation' => false,
                    'validateOnBlur' => false,
                ]); ?>                

                <?php
                    if(Yii::$app->user->can('showBackend')){
                        
                        echo $form->field($model, 'client_id', [])->widget(Select2::classname(), [
                            'initValueText' => !empty($model->client) ? $model->client->name : '',
                            'options' => [
                                'placeholder' => '...',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'minimumInputLength' => 3,
                                'ajax' => [
                                    'url' => Url::to(['/client/ajax-name-list']),
                                    'dataType' => 'json',
                                    'data' => new JsExpression('function(params) { return {q:params.term}; }')
                                ],
                                'escapeMarkup' => new JsExpression('function(markup) { return markup; }'),
                                'templateResult' => new JsExpression('function(data) { return data.text; }'),
                                'templateSelection' => new JsExpression('function(data) { return data.text; }'),       
                            ],
                        ]);
                        
                    }else{
                        echo Html::activeHiddenInput($model, 'client_id');
                        if(!empty($model->client_id)){
                            $myCompany = Html::a($client->name, ['/client/view', 'id' => $model->client_id], ['target' => '_blank']);
                            echo $form->field($client, 'id', [
                                'staticValue' => $myCompany,
                            ])->staticInput([
                                'class' => 'form-control',
                            ])->label(Yii::t('fsmuser', 'My company'));
                        }
                    }
                ?>
                
                <?= $form->field($model, 'name')->textInput([
                    'enableAjaxValidation' => true,
                    'maxlength' => 255,
                ]) ?>
                
                <?= $form->field($model, 'language_id')->widget(Select2::classname(), [
                    'data' => $languageList, 
                    'options' => [
                        'placeholder' => '...',
                    ],
                ]); ?>
                
                <?= $form->field($model, 'phone')->widget(MaskedInput::classname(), [
                    'clientOptions' => [
                        'greedy' => false,
                    ],
                    'mask' => '(+9{1,3}) 9{8,10}',
                    'options' => [
                        'class' => 'form-control',
                        'placeholder' => Yii::t('common', 'Enter as') . ' (+999) 9999999999...',
                    ],
                ]);
                ?>
                
                <?= $form->field($model, 'bio')->textarea() ?>

                <?= $form->field($model, 'gravatar_email')->widget(MaskedInput::classname(), [
                    'clientOptions' => [
                        'alias' => 'email',
                    ],
                    ])->hint(\yii\helpers\Html::a(Yii::t('user', 'Change your avatar at Gravatar.com'), 'http://gravatar.com')) ?>
                
                <?php /*
                  <?= $form->field($model, 'public_email')->widget(MaskedInput::classname(), [
                  'clientOptions' => [
                  'alias' => 'email',
                  ],
                  ]);
                  ?>

                  <?= $form->field($model, 'website')->widget(MaskedInput::classname(), [
                  'clientOptions' => [
                  'alias' => 'url',
                  ],
                  ]);
                  ?>
                 * 
                 */ ?>

                <?php if(!empty($model->id) && \common\models\user\FSMUser::getIsPortalAdmin()){
                    echo $form->field($model, 'deleted', [
                        ])->widget(SwitchInput::classname(), [
                        'pluginOptions' => [
                            'onText' => Yii::t('common', 'Yes'),
                            'offText' => Yii::t('common', 'No'),
                        ],
                    ]); 
                }?>

                <div class="form-group clearfix double-line-top">
                    <div class="col-lg-offset-9 col-md-3" style="text-align: right;">
                        <?= Html::submitButton(Yii::t('user', 'Save'), ['class' => 'btn btn-block btn-success']) ?><br>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
