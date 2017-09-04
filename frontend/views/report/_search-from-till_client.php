<?php
use yii\helpers\Url;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\DatePicker;
use kartik\widgets\Select2;

use common\models\bill\Bill;

/* @var $this yii\web\View */
/* @var $model common\models\bill\search\BillSearch */
/* @var $form yii\widgets\ActiveForm */
$dateFrom = isset($_GET['from']) ? date("d-M-Y", strtotime($_GET['from'])) : date('01-M-Y');
$dateTill = isset($_GET['till']) ? (!empty($_GET['till']) ? date("d-M-Y", strtotime($_GET['till'])) : date("t-M-Y")) : date("t-M-Y");
?>

<div class="bill-search">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_INLINE,
        'id' => 'bill-search',
        'action' => [$action],
        'method' => 'get',
    ]); ?>
    
    <div class="input-group" style="width: 500px; min-width: 300px;">
    <?= Select2::widget([
        'name' => 'client_id',
        'data' => $clientList,
        'value' => !empty($_GET['client_id']) ? $_GET['client_id'] : null,
        'options' => [
            'placeholder' => Yii::t('report', 'Select client ...'),
            'multiple' => true,
            //'style' => 'width: 200px;',
        ],
    ]); ?>
    </div>
    
    <div class="input-group" style="width: 500px; min-width: 300px;">
    <?= DatePicker::widget([
        'name' => 'from',
        'value' => $dateFrom,
        'name2' => 'till',
        'value2' => $dateTill,      
        'options' => ['placeholder' => Yii::t('common', 'Start date')],
        'options2' => ['placeholder' => Yii::t('common', 'End date')],
        'type' => DatePicker::TYPE_RANGE,
        'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
    ]); 
    ?>
    </div>
    
    <div class="form-group">
        <div style="text-align: right;">
            <?= Html::submitButton(Yii::t('common', 'Select'), ['class' => 'btn btn-primary']) ?>
            <?= Html::resetButton(Yii::t('common', 'Reset'), ['class' => 'btn btn-default']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
