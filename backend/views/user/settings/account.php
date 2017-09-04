<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\password\PasswordInput;

use common\widgets\EnumInput;

/**
 * @var $this  yii\web\View
 * @var $form  yii\widgets\ActiveForm
 * @var $model common\models\user\FSMSettingsForm
 */

$this->title = Yii::t('user', 'Account settings');
if(Yii::$app->user->can('showBackend')){
    $this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Users'), 'url' => ['/user/admin/index']];
}
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <div class="col-md-2">
        <?= $this->render('_menu',[
            'profile' => $profile,
        ]) ?>
    </div>
    
    <div class="col-md-10">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <?= Html::encode($this->title) ?>
            </div>
            <div class="panel-body">
                
                <?php $form = ActiveForm::begin([
                    'id' => 'account-form',
                    'type' => ActiveForm::TYPE_HORIZONTAL,
                    'fieldConfig' => [
                        //'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-sm-offset-3 col-lg-9\">\n{hint}\n{error}</div>",
                        //'labelOptions' => ['class' => 'col-lg-3 control-label'],
                        'showHints' => true,
                    ],
                    'enableAjaxValidation'   => true,
                    'enableClientValidation' => false,
                ]); ?>
                
                <?php
                    if(Yii::$app->user->can('showBackend')){
                        echo $form->field($model, 'role')->widget(EnumInput::classname(), [
                            'type' => EnumInput::TYPE_SELECT2,
                            //'value' => ['anonymous', 'user'],
                            'data' => $roleList,
                            'options' => [
                                'placeholder' => '...',
                                'multiple' => true,
                            ],
                        ]); 
                    }
                ?>
                
                <?= $form->field($model, 'email') ?>

                <?= $form->field($model, 'username') ?>

                <?= $form->field($model, 'new_password')->widget(PasswordInput::classname()) ?>

                <hr/>
                
                <?php if($model->scenario != 'admin_update'): 
                    echo $form->field($model, 'current_password')->passwordInput();
                 endif; ?>                

                <div class="form-group clearfix">
                    <div class="col-lg-offset-9 col-md-3" style="text-align: right;">
                        <?= Html::submitButton(Yii::t('user', 'Save'), ['class' => 'btn btn-block btn-success']) ?><br>
                    </div>
                </div>
                
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>
