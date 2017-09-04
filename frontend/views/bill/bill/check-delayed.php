<?php

//use Yii;

use kartik\helpers\Html;

$this->title = Yii::t('bill', 'Check delayed invoices');
?>
<div class="bill-check-delayed">
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <div style="padding-right: 15px; padding-left: 15px; font-size: 1.5em;">                     
        <p>
            <?= Yii::t('bill', 'There {n, plural, '.
                '=0{are not found delayed invoices} '.
                '=1{is found <span style="font-size: 1.5em; color: red;">one</span> invoice} '.
                'other{are was found <span style="font-size: 1.5em; color: red;">#</span> invoices}'.
            '}!', ['n' => $billCount]);; ?>
        </p>
        <?php if($billCount) : ?>
        <p>
            <?= Yii::t('bill', 'Status was be changed to "Delayed"');; ?>
        </p>
        <?php endif; ?>
    </div>
    
</div>