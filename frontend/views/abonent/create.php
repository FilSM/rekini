<?php

use kartik\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\abonent\Abonent */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Create a new '.$model->modelTitle(1, false));
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="abonent-create">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    

    <?= $this->render('_form', [
        'model' => $model,
        'clientModel' => $clientModel,
        'clientBankModel' => $clientBankModel,
        'filesModel' => $filesModel,
        'countryList' => $countryList,
        'languageList' => $languageList,
        'bankList' => $bankList,
        'valutaList' => $valutaList,
        'managerList' => $managerList,
        'isAdmin' => $isAdmin,
        'isOwner' => $isOwner,
    ]) ?>

</div>