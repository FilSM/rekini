<?php
namespace common\models;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\MaskedInput;
use yii\widgets\Pjax;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;
use kartik\widgets\DepDrop;
use kartik\widgets\DatePicker;
use kartik\widgets\FileInput;
use kartik\checkbox\CheckboxX;

use common\widgets\EnumInput;
use common\widgets\GeocodeAutocompleteInput;

/* @var $this yii\web\View */
/* @var $model common\models\client\Agreement */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal);
?>

<div class="payment-confirm-upload-form">
    <?php if($isModal) : Pjax::begin(Yii::$app->params['PjaxModalOptions']); endif; ?>
    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => $model->tableName().'-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
        'options' => [
            'data-pjax' => $isModal,
        ],         
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
    
    <?php if(empty($model->id)) :
        echo $form->field($filesXMLModel, 'filename', [
            'options' => [
                'class' => 'required form-group',
            ],
        ])->widget(FileInput::classname(), [
            'language' =>  strtolower(substr(Yii::$app->language, 0, 2)),
            'sortThumbs' => false,
            'options' => [
                'id' => 'xml-file',
            ],
            'pluginOptions' => [
                'allowedFileExtensions' => ['xml'],
                'maxFileSize' => 20000,
                'showRemove' => false,
                'showUpload' => false,
            ],
        ])->label(Yii::t('bill', 'XML filename to import')); 
   
        echo $form->field($filesPDFModel, 'filename', [
            'options' => [
                'class' => 'required form-group',
            ],
        ])->widget(FileInput::classname(), [
            'language' =>  strtolower(substr(Yii::$app->language, 0, 2)),
            'sortThumbs' => false,
            'options' => [
                'id' => 'pdf-file',
            ],
            'pluginOptions' => [
                'allowedFileExtensions' => ['pdf'],
                'maxFileSize' => 20000,
                'showRemove' => false,
                'showUpload' => false,
            ],
        ])->label(Yii::t('bill', 'PDF filename to import')); 

    endif; ?>
    
    <?php if(!empty($model->id) && !empty($isAdmin)){
        echo $form->field($model, 'status')->widget(EnumInput::classname(), [
            'type' => EnumInput::TYPE_RADIOBUTTON,
            'options' => [
                'translate' => $model->importStateList,
            ],
        ]); 
    } ?>
    
    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

    <div class="form-group <?php if($isModal) : echo 'modal-button-group'; endif; ?>">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SaveButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
    <?php if($isModal) : Pjax::end(); endif; ?>
</div>

<?php
/*
    echo GeocodeAutocompleteInput::widget([
        'name' => 'tmpGMAPInput',
        'options' => [
            'style' => 'display: none;',
        ],
    ]);
 * 
 */
?>

<?php
/*
$api_key = @Yii::$app->params['googleMapsApiKey'];
Yii::$app->getView()->registerJsFile("https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places&language=LV");
 * 
 */
?>