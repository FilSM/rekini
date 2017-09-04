<?php

use kartik\helpers\Html;
use kartik\detail\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\bill\BillConfirm */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])){
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $model->modelTitle() .' #'. $model->id;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bill-confirm-view">

    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <p>
        <?= Html::a(Yii::t('common', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= \common\components\FSMBtnDialog::button(Yii::t('common', 'Delete'), ['delete', 'id' => $model->id], [
            'id' => 'btn-dialog-selected',
            'class' => 'btn btn-danger',
        ]); ?>            
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'payment_confirm_id',
            'history_bill_id',
            'bill_payment_id',
            'bill_id',
            'first_client_account',
            'second_client_name',
            'second_client_reg_number',
            'second_client_account',
            'second_client_id',
            'doc_date',
            'doc_number',
            'direction',
            'summa',
            'currency',
            'comment:ntext',
        ],
    ]) ?>

</div>