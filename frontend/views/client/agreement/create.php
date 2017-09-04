<?php

use kartik\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\client\Agreement */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Create a new '.$model->modelTitle(1, false));
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="agreement-create">

    <?php if(!$isModal): ?>
    <?= Html::pageHeader(Html::encode($this->title));?>
    <?php endif; ?>

    <?= $this->render('_form', [
        'model' => $model,
        'filesModel' => $filesModel,
        'projectList' => $projectList,
        'valutaList' => $valutaList,
        'clientList' => $clientList,
        'clientRoleList' => $clientRoleList,
        'isAdmin' => $isAdmin,
        'isModal' => $isModal,
    ]) ?>

</div>