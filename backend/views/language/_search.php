<?php

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;

/**
 * @var yii\web\View $this
 * @var common\models\search\LanguageSearch $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="language-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'language') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'native') ?>

    <?= $form->field($model, 'enabled')->widget(SwitchInput::classname(), [
        'pluginOptions' => [
            'onText' => Yii::t('common', 'Yes'),
            'offText' => Yii::t('common', 'No'),
        ],
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('common', 'Search'), ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton(Yii::t('common', 'Reset'), ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
