<?php

use kartik\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\bill\Bill */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = Yii::t($model->tableName(), 'Update a '.$model->billDocTypeList[$model->doc_type]).': #' . $model->doc_number;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->doc_number, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('common', 'Update');
?>
<div class="bill-update">

    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <?= $this->render('_form', [
        'model' => $model,
        'agreementModel' => $agreementModel,
        'firstClientModel' => $firstClientModel,
        'secondClientModel' => $secondClientModel,
        'projectList' => $projectList,
        'clientRoleList' => $clientRoleList,
        'valutaList' => $valutaList,
        'managerList' => $managerList,
        'billProductModel' => $billProductModel,
        'productList' => $productList,
        'measureList' => $measureList,
        'languageList' => $languageList,
        'isAdmin' => $isAdmin,
        'hideDocType' => !empty($hideDocType),
    ]) ?>

</div>