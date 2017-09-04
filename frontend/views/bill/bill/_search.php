<?php
use yii\helpers\Url;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;

use common\widgets\EnumInput;
use common\models\bill\Bill;

/* @var $this yii\web\View */
/* @var $model common\models\bill\search\BillSearch */
/* @var $form yii\widgets\ActiveForm */

$showSearchFrom = (
    !empty($model->project_id) ||
    !empty($model->agreement_id) ||
    !empty($model->first_client_id) ||
    !empty($model->doc_type) ||
    !empty($model->manager_id));

?>

<div class="bill-search" style="<?= $showSearchFrom ? '' : 'display: none'?>;">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_INLINE,
        'id' => 'bill-search',
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'project_id', ['options' => ['class' => 'form-group', 'style' => 'width: 250px;']])->widget(Select2::classname(), [
        'data' => $projectList,
        'options' => [
            //'id' => 'project-id',
            'placeholder' => $model->getAttributeLabel('project_id'),
            'multiple' => true,
        ],         
        'pluginOptions' => [
            'allowClear' => true,
        ],   
    ]); ?>    

    <?= $form->field($model, 'agreement_id', ['options' => ['class' => 'form-group', 'style' => 'width: 250px;']])->widget(Select2::classname(), [
        'data' => $agreementList,
        'options' => [
            'placeholder' => $model->getAttributeLabel('agreement_id'),
            'multiple' => true,
        ],         
        'pluginOptions' => [
            'allowClear' => true,
        ],        
    ]); ?>    

    <?php /*
    <?= $form->field($model, 'agreement_id', ['options' => ['class' => 'form-group', 'style' => 'width: 250px;']])->widget(DepDrop::classname(), [
        'type' => DepDrop::TYPE_SELECT2,
        //'data' => empty($model->agreement_id) ? null : [$model->agreement_id => $model->agreement->number],
        //'options' => ['placeholder' => '...'],
        'select2Options' => [
            'pluginOptions' => [
                'allowClear' => !$model->isAttributeRequired('agreement_id'),
            ],
        ],
        'pluginOptions' => [
            'depends' => ['project-id'],
            //'initialize' => true,            
            //'initDepends' => ['project-id'],
            'url' => Url::to(['/project/ajax-get-agreement-list']),
            'placeholder' => '...',
        ],
    ]); ?>
     * 
     */?>
    
    <?= $form->field($model, 'first_client_id', ['options' => ['class' => 'form-group', 'style' => 'width: 250px;']])->widget(Select2::classname(), [
        'data' => $clientList,
        'options' => [
            'placeholder' => Yii::t('agreement', 'First party'),
            'multiple' => true,
        ],         
        'pluginOptions' => [
            'allowClear' => true,
        ],        
    ]); ?>    

    <?= $form->field($model, 'doc_type', ['options' => ['class' => 'form-group', 'style' => 'width: 150px;']])->widget(EnumInput::classname(), [
            'type' => EnumInput::TYPE_SELECT2,
            'options' => [
                'translate' => $model->billDocTypeList,
                'placeholder' => $model->getAttributeLabel('doc_type'),
                'multiple' => true,
            ],
            'clientOptions' => [
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ],         
        ]); 
    ?>    

    <?= $form->field($model, 'manager_id', ['options' => ['class' => 'form-group', 'style' => 'width: 250px;']])->widget(Select2::classname(), [
        'data' => $managerList,
        'options' => [
            'placeholder' => $model->getAttributeLabel('manager_id'),
        ],         
        'pluginOptions' => [
            'allowClear' => true,
        ],        
    ]); ?>    
    
    <?php //echo  $form->field($model, 'first_client_id') ?>

    <?php //echo $form->field($model, 'second_client_id') ?>

    <?php //echo $form->field($model, 'parent_id') ?>

    <?php //echo $form->field($model, 'doc_number') ?>

    <?php //echo $form->field($model, 'doc_date') ?>

    <?php //echo $form->field($model, 'pay_date') ?>

    <?php //echo $form->field($model, 'status') ?>

    <?php //echo $form->field($model, 'pay_status') ?>

    <?php //echo $form->field($model, 'client_bank_id') ?>

    <?php //echo $form->field($model, 'second_client_bank_id') ?>

    <?php //echo $form->field($model, 'summa') ?>

    <?php //echo $form->field($model, 'vat') ?>

    <?php //echo $form->field($model, 'total') ?>

    <?php //echo $form->field($model, 'valuta_id') ?>

    <?php //echo $form->field($model, 'comment') ?>

    <?php //echo $form->field($model, 'create_time') ?>

    <?php //echo $form->field($model, 'create_user_id') ?>

    <?php //echo $form->field($model, 'update_time') ?>

    <?php //echo $form->field($model, 'update_user_id') ?>

    <div class="form-group">
        <div style="text-align: right;">
            <?= Html::submitButton(Yii::t('common', 'Search'), ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton(Yii::t('common', 'Reset'), ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>