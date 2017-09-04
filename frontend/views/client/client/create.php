<?php

use kartik\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Client */

if(!$isModal){
    //if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
        //$this->title = Yii::$app->params['brandLabel'];
    //}else{
        $this->title = (empty($registerAction) ? Yii::t($model->tableName(), 'Create a new '.$model->modelTitle(1, false)) : Yii::t($model->tableName(), 'Register a new client'));
    //}
    if(empty($registerAction)){
        $this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
    }
    $this->params['breadcrumbs'][] = $this->title;
}
?>
<div class="client-create">

    <?php if(!$isModal): ?>
    <?= Html::pageHeader(Html::encode($this->title));?>
    <?php endif; ?>
    
    <?= $this->render('_form', [
        'model' => $model,
        'clientBankModel' => $clientBankModel,
        'filesModel' => $filesModel,
        'countryList' => $countryList,
        'languageList' => $languageList,
        'bankList' => $bankList,
        'valutaList' => $valutaList,
        'managerList' => $managerList,
        'clientGroupList' => $clientGroupList,
        'isAdmin' => $isAdmin,
        'isOwner' => $isOwner,
        'isModal' => $isModal,
        'registerAction' => !empty($registerAction),
    ]) ?>

</div>