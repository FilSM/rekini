<?php

use kartik\widgets\SideNav;

$items = [
    ['label' => Yii::t('client', 'All clients'), 
        'url' => ['/client'],
        'icon' => 'list-alt',
        'active' => (isset($activeItem) && ($activeItem == 'all_client')),
    ],
    ['label' => Yii::t('client', 'My clients'), 
        'url' => ['/client', 'manager_id' => $profileId],
        'icon' => 'list-alt',
        'active' => (isset($activeItem) && ($activeItem == 'my_clients')),
    ],
    ['label' => Yii::t('client', 'Our clients'), 
        'url' => ['/client', 'our_clients' => true],
        'icon' => 'list-alt',
        'active' => (isset($activeItem) && ($activeItem == 'our_clients')),
    ],
    ['label' => Yii::t('client', 'External clients'), 
        'url' => ['/client', 'our_clients' => false],
        'icon' => 'list-alt',
        'active' => (isset($activeItem) && ($activeItem == 'ext_clients')),
    ],
    /*
    ['label' => Yii::t('abonent', 'Abonent list'), 
        'url' => ['/abonent'],
        'icon' => 'list-alt',
        'active' => (isset($activeItem) && ($activeItem == 'abonent_list')),
    ],   
     * 
     */ 
];

?>

<?= SideNav::widget([
    'items' => $items,
    //'heading' => $client->name,
    'headingOptions' => [
        //'class' => $headerClass,
    ],
    'containerOptions' => [
        'id' => 'client-v-navbar',
    ],
]);
?>
