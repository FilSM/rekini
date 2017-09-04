<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\client\ClientRole */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Update a '.$model->modelTitle(1, false)) . ': ' . $model->name;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('common', 'Update');
?>
<div class="client-role-update">

    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>