<?php
use yii\helpers\Url;
//use yii\widgets\MaskedInput;

use kartik\helpers\Html;
use kartik\widgets\Select2;
use kartik\checkbox\CheckboxX;

use common\widgets\dynamicform\DynamicFormWidget;
use common\widgets\MaskedInput;
use common\models\bill\BillProduct;
?>

<?php 
    ob_start();
    ob_implicit_flush(false);
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_wrapper',
    'widgetBody' => '.form-products-body',
    'widgetItem' => '.form-products-item',
    'min' => 1,
    'insertButton' => '.add-item',
    'deleteButton' => '.delete-item',
    'model' => $model[0],
    'formId' => $form->id,
    'formFields' => [
        'product_name',
        'measure_id',
        'amount',
        'price',
        'vat',
        'revers',
        'summa',
        'summa_vat',
        'total',
    ],
]); ?>

<table class="table table-bordered table-striped margin-b-none">
    <thead>
        <tr>
            <th class="required" style="width: 30%;"><?= $model[0]->getAttributeLabel('product_name'); ?></th>
            <th style="width: 11%; text-align: center;"><?= $model[0]->getAttributeLabel('measure_id'); ?></th>
            <th style="width: 9%; text-align: right;"><?= $model[0]->getAttributeLabel('amount'); ?></th>
            <th style="width: 9%; text-align: right;"><?= $model[0]->getAttributeLabel('price'); ?></th>
            <th style="width: 9%; text-align: right;"><?= $model[0]->getAttributeLabel('vat'); ?></th>
            <th style="width: 5%; text-align: center;"><?= $model[0]->getAttributeLabel('revers'); ?></th>
            <th style="width: 9%; text-align: right;"><?= $model[0]->getAttributeLabel('summa'); ?></th>
            <th style="width: 9%; text-align: right;"><?= $model[0]->getAttributeLabel('summa_vat'); ?></th>
            <th style="width: 9%; text-align: right;"><?= $model[0]->getAttributeLabel('total'); ?></th>
            <th style="width: 90px; text-align: center"><?= Yii::t('kvgrid', 'Actions'); ?></th>
        </tr>
    </thead>
    <tbody class="form-products-body">
        <?php foreach ($model as $index => $billProduct): ?>
            <tr class="form-products-item">
                <td>
                    <?= Html::activeHiddenInput($billProduct, "[{$index}]id"); ?>
                    <?= $form->field($billProduct, "[{$index}]product_id", [    
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                        'options' => [
                            'class' => 'form-group',
                            'style' => 'display: none;',
                        ],
                    ])->widget(Select2::classname(), [
                        'data' => $productList, 
                        'options' => [
                            'placeholder' => '...',
                            'class' => 'product-item-select',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                        'addon' => [
                            'prepend' => $billProduct->getModalButtonContent([
                                'formId' => $form->id,
                                'controller' => 'product',
                                'isModal' => $isModal,
                            ]),
                        ],                          
                    ])->label(false); ?>
                    
                    <?= $form->field($billProduct, "[{$index}]product_name", [    
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                        'options' => [
                            'class' => 'form-group',
                            'style' => 'display: none; margin-top: 0px;',
                        ],
                    ])->textInput([
                        'class' => 'product-item-input', 
                    ])->label(false); ?>
                </td>
                <td>
                    <?= $form->field($billProduct, "[{$index}]measure_id", [    
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                        'options' => [
                            'class' => 'form-group',
                            'style' => 'text-align: center;',
                        ],
                    ])->widget(Select2::classname(), [
                        'data' => $measureList, 
                        'disabled' => true,
                        'options' => [
                            'placeholder' => '...',
                            'class' => 'measure-item-select',
                            'value' => (!empty($billProduct->measure_id) ? $billProduct->measure_id : (isset($billProduct->product) ? $billProduct->product->measure_id : null)),
                        ],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                        'addon' => [
                            'prepend' => $billProduct->getModalButtonContent([
                                'formId' => $form->id,
                                'controller' => 'measure',
                                'isModal' => $isModal,
                                /*
                                'options' => [
                                    'disabled' => true,
                                    'style' => 'display: none;',
                                ],
                                 * 
                                 */
                            ]),
                        ],                          
                    ])->label(false); ?>
                </td>
                <td>
                    <?= $form->field($billProduct, "[{$index}]amount", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                    ])->widget(MaskedInput::classname(), [
                        'mask' => '9{1,10}[.9{1,3}]',
                        'options' => [
                            'class' => 'form-control number-field bill-product-amount',
                            'style' => 'text-align: right;',
                        ],
                        'clientOptions' => ['textAlign' => "right"]
                    ])->label(false); ?>
                </td>
                <td>
                    <?= $form->field($billProduct, "[{$index}]price", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                    ])->widget(MaskedInput::classname(), [
                        'mask' => '9{1,10}[.9{1,2}]',
                        'options' => [
                            'class' => 'form-control number-field bill-product-price',
                            'style' => 'text-align: right;',
                        ],
                        'clientOptions' => ['textAlign' => "right"]
                    ])->label(false); ?>
                </td>
                <td>
                    <?= $form->field($billProduct, "[{$index}]vat", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                    ])->widget(MaskedInput::classname(), [
                        'mask' => '9{1,10}[.9{1,2}]',
                        'options' => [
                            'class' => 'form-control number-field bill-product-vat',
                            'style' => 'text-align: right;',
                        ],
                        'clientOptions' => ['textAlign' => "right"]
                    ])->label(false); ?>
                </td>
                <td style='text-align: center;'>
                    <?= $form->field($billProduct, "[{$index}]revers", [
                        'template' => '{input}{label}{error}{hint}',
                        'options' => [
                            'class' => 'vat-revers',
                        ],
                        //'labelOptions' => ['class' => 'cbx-label']
                    ])->widget(CheckboxX::classname(), [
                        //'autoLabel'=>false,
                        'pluginOptions' => ['threeState' => false],
                    ])->label(false); ?>
                </td>
                <td>
                    <?= $form->field($billProduct, "[{$index}]summa", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                    ])->textInput([
                        'class' => 'bill-product-summa', 
                        'readonly' => true, 
                        'style' => 'text-align: right;',
                        'value' => (!empty($billProduct->product_id) || !empty($billProduct->product_name) ? $billProduct->summa : ''),
                    ])->label(false); ?>
                </td>
                <td>
                    <?= $form->field($billProduct, "[{$index}]summa_vat", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                    ])->textInput([
                        'class' => 'bill-product-summa_vat', 
                        'readonly' => true, 
                        'style' => 'text-align: right;',
                        'value' => (!empty($billProduct->product_id) || !empty($billProduct->product_name) ? $billProduct->summa_vat : ''),
                    ])->label(false); ?>
                </td>
                <td>
                    <?= $form->field($billProduct, "[{$index}]total", [
                        'horizontalCssClasses' => [
                            'wrapper' => 'col-md-12',
                        ],
                    ])->textInput([
                        'class' => 'bill-product-total', 
                        'readonly' => true, 
                        'style' => 'text-align: right;',
                        'value' => (!empty($billProduct->product_id) || !empty($billProduct->product_name) ? $billProduct->total : ''),
                    ])->label(false); ?>
                </td>
                <td class="text-center vcenter">
                    <button type="button" class="delete-item btn btn-danger btn-xs"><?= Html::icon('minus');?></button>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="10"><button type="button" class="add-item btn btn-success btn-sm"><?= Html::icon('plus');?> <?= Yii::t('bill', 'Add product'); ?></button></td>
        </tr>
    </tfoot>
</table>

<?php DynamicFormWidget::end(); ?>

<?php
    $body = ob_get_contents();
    ob_get_clean(); 

    $panelContent = [
        'heading' => BillProduct::modelTitle(2),
        'preBody' => '<div class="panel-body">',
        'body' => $body,
        'postBody' => '</div>',
    ];
    echo Html::panel(
        $panelContent, 
        'default', 
        [
            'id' => "panel-product-data",
        ]
    );
?>