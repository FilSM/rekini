<?php

use kartik\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\client\ClientBank */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Create a new '.$model->modelTitle(1, false));
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="client-bank-create">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>