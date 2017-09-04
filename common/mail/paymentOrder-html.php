<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $user common\models\User */
?>
<div class="payment-order-mail-view" style="font-family: 'Tahoma'; font-size: 12px; color: #434343">
    <br/>
    <strong>Welcome to!</strong><br/><br/>    
    <?= $content; ?>
    <br/>
    <br/>
    Best regards,<br/><br/>
    <?= $contacts; ?>
    <br/>
    <?php if (!empty($logoImageFileName)) : ?>
    <div class="mail-logo">
        <img src="<?= $message->embed($logoImageFileName); ?>">
    </div>
    <?php endif; ?>
</div>
