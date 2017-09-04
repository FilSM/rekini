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

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/**
 * @var yii\web\View              $this
 * @var dektrium\user\models\User $user
 */
$isModal = !empty($isModal);

if (empty($profile) || empty($profile->id)):
    $this->title = Yii::t('user', 'Create a user account');
    $this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Users'), 'url' => ['index']];
    $this->params['breadcrumbs'][] = $this->title;
endif;
?>
<div class="panel panel-default">
    <div class="panel-heading">
        <?= Html::encode($this->title) ?>
    </div>
    <div class="panel-body">
        <div class="alert alert-info">
            <?= Yii::t('user', 'Credentials will be sent to the user by email') ?>.
            <?= Yii::t('user', 'A password will be generated automatically if not provided') ?>.
        </div>
        <?php if($isModal) : Pjax::begin(Yii::$app->params['PjaxModalOptions']); endif; ?>
        <?php $form = ActiveForm::begin([
            'type' => ActiveForm::TYPE_HORIZONTAL,
            //'enableAjaxValidation'   => true,
            'enableClientValidation' => false,
            'id' => 'user-create-form',
            'formConfig' => [
                'labelSpan' => 3,
            ],
            'fieldConfig' => [
                //'template' => "{label}\n<div class=\"col-lg-9\">{input}</div>\n<div class=\"col-sm-offset-3 col-lg-9\">\n{hint}\n{error}</div>",
                //'labelOptions' => ['class' => 'col-lg-3 control-label'],
                'showHints' => true,
            ],
            'options' => [
                'data-pjax' => isset($isModal),
            ],
        ]); ?> 

        <?= $this->render('_user', ['form' => $form, 'user' => $user]) ?>        

        <div class="form-group clearfix double-line-top">
            <div class="col-lg-offset-3 col-lg-9" style="text-align: right;">
                <?= \common\models\mainclass\FSMBaseModel::getSaveButton(); ?>
                <?= \common\models\mainclass\FSMBaseModel::getCancelButton(); ?>
            </div>            
        </div>
        
        <?php ActiveForm::end(); ?>
        <?php if($isModal) : Pjax::end(); endif; ?>
    </div>
</div>
