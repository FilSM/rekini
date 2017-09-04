<?php

use kartik\helpers\Html;
use kartik\detail\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\bill\PaymentConfirm */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])){
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $model->modelTitle() .' // '. $model->name;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-confirm-view">

    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <div style="padding-right: 15px; padding-left: 15px;">                     
        <div class='col-xs-6' style="padding: 0;">
            <?= Html::a(Yii::t('common', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= \common\components\FSMBtnDialog::button(Yii::t('common', 'Delete'), ['delete', 'id' => $model->id], [
                'id' => 'btn-dialog-selected',
                'class' => 'btn btn-danger',
            ]); ?>                
        </div>

        <div class='col-xs-offset-6' style="padding: 0; text-align: right;">
            <?= $model->getOptionsButtons('', true);?>
        </div>
    </div>
    <p></p>

    <div class='col-md-12'>
        <?php 
            $attributes = [
                [
                    'attribute' => 'id',
                    'visible' => $isAdmin,
                ], 
                'name',
                [
                    'attribute' => 'bank_id',
                    'value' => isset($model->bank_id) ? Html::a($model->bank->name, ['/bank/view/', 'id' => $model->bank_id], ['target' => '_blank']) : null,
                    'format'=>'raw',
                ],                
                [
                    'attribute' => 'client_name',
                    'value' => isset($model->client_id) ? Html::a($model->client_name, ['/client/view/', 'id' => $model->client_id], ['target' => '_blank']) : $model->client_name,
                    'format'=>'raw',
                ],                
                'client_reg_number',
                [
                    'attribute' => 'start_date',
                    'label' => Yii::t('bill', 'Start-End dates'),
                    'value' => isset($model->start_date) ? date('d-M-Y', strtotime($model->start_date)).(isset($model->end_date) ? ' // '.date('d-M-Y', strtotime($model->end_date)) : '') : null,
                ],                
                [
                    'attribute' => 'pay_date',
                    'value' => isset($model->pay_date) ? date('d-M-Y', strtotime($model->pay_date)) : null,
                ],                
                [
                    'attribute' => 'status',
                    'value' => isset($model->status) ? $model->importStateList[$model->status] : null,
                ],                
                [
                    'attribute' => 'action_time',
                    'value' => isset($model->action_time) ? date('d-M-Y H:i:s', strtotime($model->action_time)) : null,
                ],                
                [
                    'attribute' => 'action_user_id',
                    'value' => !empty($model->action_user_id) ? Html::a($model->actionUserProfile->name, ['/user/'.$model->actionUserProfile->id], ['target' => '_blank']) : null,
                    'format'=>'raw',
                ],                
                [
                    'attribute' => 'uploaded_file_id',
                    'value' => isset($model->uploaded_file_id) ? Html::a($model->uploadedFile->filename, $model->uploadedFile->fileurl, ['target' => '_blank']) : null,
                    'format'=>'raw',
                ],                
                [
                    'attribute' => 'uploaded_pdf_file_id',
                    'value' => isset($model->uploaded_pdf_file_id) ? Html::a($model->uploadedPdfFile->filename, $model->uploadedPdfFile->fileurl, ['target' => '_blank']) : null,
                    'format'=>'raw',
                ],                
                'comment:ntext',
            ]; 
            
            echo DetailView::widget([
                'model' => $model,
                'attributes' => $attributes,
            ]) 
        ?>
    </div>
    
    <div class='col-md-12'>
        <?= $this->render('_bill_confirm_table_view', [
                'searchModel' => $billConfirmModel,
                'dataProvider' => $billConfirmDataProvider,
                'linkedModel' => $model,
            ]); 
        ?>
    </div>

</div>