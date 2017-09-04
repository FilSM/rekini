<?php

/* @var $this yii\web\View */

use kartik\helpers\Html;

$this->title = 'About';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <?= Html::pageHeader(Html::encode($this->title));?>

    <p>This is the About page. You may modify the following file to customize its content:</p>

    <code><?= __FILE__ ?></code>
    
    <p>
        <?php
        /*
        echo "PHP: " . PHP_VERSION . '</br>';
        echo "ICU: " . INTL_ICU_VERSION . '</br>';
        //echo "ICU Data: " . INTL_ICU_DATA_VERSION . '</br>';
        
        phpinfo();
         * 
         */
        ?>
    </p>
</div>
