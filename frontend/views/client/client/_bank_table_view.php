<?php

use kartik\helpers\Html;

use common\models\client\ClientBank
?>

<?php 
    ob_start();
    ob_implicit_flush(false);
?>

<?= $this->render('@frontend/views/client/client-bank/index', [
    'dataProvider' => $dataProvider,
    'searchModel' => $searchModel,
    'clientModel' => $clientModel,
    'isAdmin' => $isAdmin,
]);?> 

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