<?php

use kartik\helpers\Html;

use common\models\bill\BillProduct;
?>

<?php 
    ob_start();
    ob_implicit_flush(false);
?>

<table class="table table-bordered table-striped margin-b-none">
    <thead>
        <tr>
            <th style="text-align: center;">#</th>
            <th style="width: 20%;"><?= $model[0]->getAttributeLabel('product_name'); ?></th>
            <th style="width: 11%; text-align: center;"><?= $model[0]->getAttributeLabel('measure_id'); ?></th>
            <th style="width: 11%; text-align: right;"><?= $model[0]->getAttributeLabel('amount'); ?></th>
            <th style="width: 11%; text-align: right;"><?= $model[0]->getAttributeLabel('price'); ?></th>
            <th style="width: 11%; text-align: right;"><?= $model[0]->getAttributeLabel('vat'); ?></th>
            <th style="width: 10%; text-align: center;"><?= $model[0]->getAttributeLabel('revers'); ?></th>
            <th style="width: 11%; text-align: right;"><?= $model[0]->getAttributeLabel('summa'); ?></th>
            <th style="width: 11%; text-align: right;"><?= $model[0]->getAttributeLabel('summa_vat'); ?></th>
            <th style="width: 11%; text-align: right;"><?= $model[0]->getAttributeLabel('total'); ?></th>
        </tr>
    </thead>
    <tbody class="form-products-body">
        <?php foreach ($model as $index => $billProduct): 
            if(empty($billProduct->id)){
                continue;
            }
            ?>
            <tr class="form-products-item">
                <td style="text-align: center;"><?= $index + 1; ?></td>
                <td>
                    <?= $billProduct->productName; ?>
                </td>
                <td style="text-align: center;">
                    <?= $billProduct->measureName; ?>
                </td>
                <td style="text-align: right;">
                    <?= $billProduct->amount; ?>
                </td>
                <td style="text-align: right;">
                    <?= $billProduct->price; ?>
                </td>
                <td style="text-align: right;">
                    <?= $billProduct->vat; ?>
                </td>
                <td style="text-align: center;">
                    <?= $billProduct->revers ? Yii::t('common', 'Yes') : ''; ?>
                </td>
                <td style="text-align: right;">
                    <?= $billProduct->summa; ?>
                </td>
                <td style="text-align: right;">
                    <?= $billProduct->summa_vat; ?>
                </td>
                <td style="text-align: right;">
                    <?= $billProduct->total; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr class="form-products-summa">
            <td colspan="8"></td>
            <td style="font-weight: bold; font-size: 150%;"><?= $bill->getAttributeLabel('summa'); ?></td>
            <td style="text-align: right; font-weight: bold; font-size: 150%;"><?= $bill->summa; ?></td>
        </tr>            
        <tr class="form-products-vat">
            <td colspan="8"></td>
            <td style="font-weight: bold; font-size: 150%;"><?= $bill->getAttributeLabel('vat'); ?></td>
            <td style="text-align: right; font-weight: bold; font-size: 150%;"><?= $bill->vat; ?></td>
        </tr>            
        <tr class="form-products-total">
            <td colspan="8"></td>
            <td style="font-weight: bold; font-size: 150%;"><?= $bill->getAttributeLabel('total').(isset($bill->valuta_id) ? ', '.$bill->valuta->name : ''); ?></td>
            <td style="text-align: right; font-weight: bold; font-size: 150%;"><?= $bill->total; ?></td>
        </tr>            
    </tbody>
</table>

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
        'success', 
        [
            'id' => "panel-product-data",
        ]
    );
?>