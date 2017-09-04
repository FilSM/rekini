<?php
use yii\helpers\Url;
//use yii\widgets\MaskedInput;

use kartik\helpers\Html;
use kartik\widgets\Select2;
use kartik\checkbox\CheckboxX;

use common\widgets\dynamicform\DynamicFormWidget;
use common\widgets\MaskedInput;
?>

<?php 
    ob_start();
    ob_implicit_flush(false);
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper',
    'widgetBody' => '.form-bill-payment-body',
    'widgetItem' => '.form-bill-payment-item',
    'min' => 1,
    'insertButton' => '.add-item',
    'deleteButton' => '.delete-item',
    'model' => $model[0],
    'formId' => $form->id,
    'formFields' => [
        'bill_number',
        'summa',
    ],
]); ?>

<table class="table table-bordered table-striped margin-b-none">
    <thead>
        <tr>
            <th class="required" style="width: 70%;"><?= $model[0]->getAttributeLabel('bill_number'); ?></th>
            <th style="width: 30%; text-align: right;"><?= $model[0]->getAttributeLabel('summa'); ?></th>
            <th style="width: 90px; text-align: center"><?= Yii::t('kvgrid', 'Actions'); ?></th>
        </tr>
    </thead>
    <tbody class="form-bill-payment-body">
        <?php foreach ($model as $index => $billPayment): ?>
            <tr class="form-bill-payment-item">
                <td>
                    <?= Html::activeHiddenInput($billPayment, "[{$index}]bill_id", ['value' => $billPayment->bill_id]); ?>
                    <?= $form->field($billPayment, "[{$index}]bill_number", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                        'staticValue' => (isset($billPayment->bill_id) ? $billPayment->bill_number : ''),
                    ])->staticInput()->label(false); ?>
                </td>
                <td>
                    <?= $form->field($billPayment, "[{$index}]summa", [
                        /*
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                         * 
                         */
                        'template' => '<div class="col-md-12">{input}</div>',
                    ])->widget(MaskedInput::classname(), [
                        'mask' => '9{1,10}[.9{1,3}]',
                        'options' => [
                            'class' => 'form-control number-field',
                            'style' => 'text-align: right;',
                        ],
                        'clientOptions' => ['textAlign' => "right"]
                    ])->label(false); ?>                    
                </td>
                <td class="text-center vcenter">
                    <button type="button" class="delete-item btn btn-danger btn-xs"><?= Html::icon('minus');?></button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php DynamicFormWidget::end(); ?>

<?php
    $body = ob_get_contents();
    ob_get_clean(); 

    $panelContent = [
        //'heading' => Yii::t('bill', 'Invoice list'),
        //'preBody' => '<div class="panel-body">',
        'body' => $body,
        //'postBody' => '</div>',
    ];
    echo Html::panel(
        $panelContent, 
        '', 
        [
            'id' => "panel-bill-payment-data",
        ]
    );
?>