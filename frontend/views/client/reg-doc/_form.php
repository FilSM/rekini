<?php
namespace common\models;

use Yii;
use yii\helpers\Url;
use yii\widgets\MaskedInput;

use kartik\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\SwitchInput;
use kartik\widgets\Select2;
use kartik\widgets\DatePicker;
use kartik\widgets\FileInput;

/* @var $this yii\web\View */
/* @var $model common\models\client\RegDoc */
/* @var $form yii\widgets\ActiveForm */
$isModal = !empty($isModal) ? 1 : 0;
?>

<div class="reg-doc-form">

    <?php $form = ActiveForm::begin([
        'type' => ActiveForm::TYPE_HORIZONTAL,
        'id' => $model->tableName().'-form',
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,       
    ]); ?>

    <?php if(!empty($model->client_id)){
            echo Html::activeHiddenInput($model, 'client_id');
        }else{
            echo $form->field($model, 'client_id')->textInput();
        }
    ?>

    <?= $form->field($model, 'reg_doc_type_id')->widget(Select2::classname(), [
            'data' => $regDocTypeList,
            'options' => [
                'id' => 'reg-doc-type-id',
                'placeholder' => '...',
            ],   
            'pluginOptions' => [
                'allowClear' => true,
            ],            
            'addon' => [
                'prepend' => $model::getModalButtonContent([
                    'formId' => $form->id,
                    'controller' => 'reg-doc-type',
                    'isModal' => $isModal,
                ]),
            ],        
        ]); 
    ?>
    
    <?= $form->field($model, 'doc_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'doc_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]); 
    ?>

    <?= $form->field($model, 'expiration_date')->widget(DatePicker::classname(), [
            'pluginOptions' => \yii\helpers\ArrayHelper::merge(Yii::$app->params['DatePickerPluginOptions'], ['language' => \Yii::$app->language]),
        ]); 
    ?>

    <?= $form->field($model, 'placement')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'notification_days')->widget(MaskedInput::classname(), [
        'clientOptions' => [
            'greedy' => false,
        ],
        'mask' => '9{1,3}',
        /*
        'options' => [
            'class' => 'form-control',
            'placeholder' => Yii::t('common', 'Enter as') . ' (+999) 9999999999...',
        ],
         * 
         */
    ]);
    ?>
    <?php
    if(!empty($filesModel->uploadedFileUrl)){
        $preview = [$filesModel->uploadedFileUrl];
        if(strpos($filesModel->filemime, 'image') !== false){
            $previewType = 'image';
        }elseif(strpos($filesModel->filemime, 'pdf') !== false){
            $previewType = 'pdf';
        }else{
            $previewType = 'object';
        }
    }else{
        $preview = [];
        $previewType = null;
    }
    echo $form->field($filesModel, 'filename')->widget(FileInput::classname(), [
        'language' =>  strtolower(substr(Yii::$app->language, 0, 2)),
        'sortThumbs' => false,
        /*
        'options' => [
            'multiple' => false,
        ],
         * 
         */
        'pluginOptions' => [
            'allowedFileExtensions' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            'maxFileSize' => 20000,
            'showRemove' => false,
            'showUpload' => false,
            //'showPreview' => ($previewType != 'object'),
            'initialPreview' => $preview,
            'initialPreviewShowDelete' => false,
            'initialPreviewAsData' => true,
            'initialPreviewConfig' => [
                [
                    'type' => $previewType,
                    'size' => $filesModel->filesize, 
                    'caption' => $filesModel->filename, 
                    //'url' => "$urlD", 
                    //'key' => 101,
                ],
            ],
            //'overwriteInitial' => false, 
        ],
    ])->label(Yii::t('files', 'Attachment')); ?>
    
    <?= $form->field($model, 'comment')->textarea(['rows' => 6]) ?>

    <div class="form-group">
        <div class="col-lg-offset-2 col-md-10" style="text-align: right;">
            <?= $model->SubmitButton; ?>
            <?= $model->CancelButton; ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>