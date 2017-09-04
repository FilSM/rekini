<?php
use yii\helpers\Url;

use kartik\helpers\Html;
use kartik\widgets\Select2;

use common\widgets\dynamicform\DynamicFormWidget;

use common\models\client\ClientBank;

$isModal = !empty($isModal);
?>

<?php 
    ob_start();
    ob_implicit_flush(false);
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper',
    'widgetBody' => '.form-banks-body',
    'widgetItem' => '.form-banks-item',
    'min' => 1,
    'insertButton' => '.add-item',
    'deleteButton' => '.delete-item',
    'model' => $model[0],
    'formId' => $form->id,
    'formFields' => [
        'bank_name',
        'swift',
        'account',
        'name',
    ],
]); ?>

<table class="table table-bordered table-striped margin-b-none">
    <thead>
        <tr>
            <th class="required" style="width: 25%;"><?= $model[0]->getAttributeLabel('bank_name'); ?></th>
            <th style="width: 25%;"><?= $model[0]->getAttributeLabel('swift'); ?></th>
            <th style="width: 25%;"><?= $model[0]->getAttributeLabel('account'); ?></th>
            <th style="width: 25%;"><?= $model[0]->getAttributeLabel('name'); ?></th>
            <th style="width: 90px; text-align: center"><?= Yii::t('kvgrid', 'Actions'); ?></th>
        </tr>
    </thead>
    <tbody class="form-banks-body">
        <?php foreach ($model as $index => $clientBank): ?>
            <tr class="form-banks-item">
                <td>
                    <?= Html::activeHiddenInput($clientBank, "[{$index}]id"); ?>
                    <?= $form->field($clientBank, "[{$index}]bank_id", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                    ])->widget(Select2::classname(), [
                        'data' => $bankList, 
                        'options' => [
                            'placeholder' => '...',
                            'class' => 'bank-item-select',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                        'addon' => [
                            'prepend' => $clientBank->getModalButtonContent([
                                'formId' => $form->id,
                                'controller' => 'bank',
                                'isModal' => $isModal,
                            ]),
                        ],                          
                    ])->label(false); ?>
                </td>
                <td>
                    <?= $form->field($clientBank, "[{$index}]swift", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                    ])->label(false)->textInput([
                        'maxlength' => 20, 
                        'disabled' => true,
                        'value' => (!empty($clientBank->bank_id) ? $clientBank->bank->swift : ''),
                    ]); ?>
                </td>
                <td>
                    <?php echo $form->field($clientBank, "[{$index}]account", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                        'addon' => [
                            'prepend' => [
                                'content' => 
                                    Html::button(Html::icon('ok-sign'), [
                                        'data-index' => $index, 
                                        'class'=>'btn btn-default btn-iban-search',
                                        'value' => Url::to(["/client/ajax-check-iban"]),
                                        'title' => Yii::t('client', 'Check IBAN'),
                                    ]),
                                'asButton' => true, 
                            ],
                        ],
                    ])->textInput(['maxlength' => 31])->label(false); ?>
                </td>
                <td>
                    <?php echo $form->field($clientBank, "[{$index}]name", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                    ])->textInput(['maxlength' => 64])->label(false); ?>
                </td>
                <td class="text-center vcenter">
                    <button type="button" class="delete-item btn btn-danger btn-xs"><?= Html::icon('minus');?></button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5"><button type="button" class="add-item btn btn-success btn-sm"><?= Html::icon('plus');?> <?= Yii::t('client', 'Add account'); ?></button></td>
        </tr>
    </tfoot>
</table>

<?php DynamicFormWidget::end(); ?>

<?php
    $body = ob_get_contents();
    ob_get_clean(); 

    $panelContent = [
        'heading' => ClientBank::modelTitle(2),
        'preBody' => '<div class="panel-body">',
        'body' => $body,
        'postBody' => '</div>',
    ];
    echo Html::panel(
        $panelContent, 
        'default', 
        [
            'id' => "panel-bank-data",
        ]
    );
?>