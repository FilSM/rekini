<?php

namespace common\models\client;

use Yii;
use kartik\widgets\SideNav;

use common\models\ObjectBase;
use common\models\estate\EstateFlat;
use common\models\estate\EstateHouse;
use common\models\estate\EstateCommerc;
use common\models\estate\EstateLand;
use common\models\request\RequestBase;
use common\models\request\RequestEstateFlat;
use common\models\request\RequestEstateHouse;
use common\models\request\RequestEstateCommerc;
use common\models\request\RequestEstateLand;

use common\models\client\Client;
use common\models\client\ClientContact;
use common\models\client\ClientManager;
use common\models\user\FSMUser;

$profile = $client->profile;
$user = $profile ? $profile->user : null;

$items = [
    ['label' => Yii::t('common', 'Summary'), 
        'url' => ['/client/view', 'id' => $client->id],
        'icon' => 'list-alt',
        'active' => (isset($activeItem) && ($activeItem == 'general')),
    ],
    ['label' => ClientContact::modelTitle(2), 
        'url' => ['/client-contact/index', 'client_id' => $client->id],
        'icon' => 'phone-alt',
        'active' => (isset($activeItem) && ($activeItem == ClientContact::tableName())),
    ],
    ['label' => ClientManager::modelTitle(2), 
        'url' => ['/client-manager/index', 'client_id' => $client->id],
        'icon' => 'briefcase',
        'active' => (isset($activeItem) && ($activeItem == 'client-manager')),
    ],
    ['label' => RegDoc::modelTitle(2), 
        'url' => ['/reg-doc/index', 'client_id' => $client->id],
        'icon' => 'file',
        'active' => (isset($activeItem) && ($activeItem == 'reg-doc')),
    ],
    ['label' => Share::modelTitle(2), 
        'items' => [
            ['label' => Yii::t('client', 'Our shares'), 
                'url' => ['/share/index', 'client_id' => $client->id],
                //'icon' => 'paste',
                'active' => (isset($activeItem) && ($activeItem == 'our-shares')),    
            ],
            ['label' => Yii::t('client', 'Our shareholders'), 
                'url' => ['/shareholder/index', 'client_id' => $client->id],
                //'icon' => 'paste',
                'active' => (isset($activeItem) && ($activeItem == 'our-shareholders')),    
            ],
        ],        
        'icon' => 'euro',
    ],
    ['label' => Yii::t('client', 'Staff'), 
        'url' => ['/client/staff', 'id' => $client->id],
        'icon' => 'user',
        'active' => (isset($activeItem) && ($activeItem == 'staff')),
        'visible' => $client->it_is == Client::CLIENT_IT_IS_OWNER,
    ],
/*    
    ['label' => Yii::t('client', 'My profile'), 
        'url' => ['/user/profile/show', 'id' => ($user ? $user->id : null)],
        'template'=> '<a href="{url}" target="_blank">{icon}{label}</a>',
        'icon' => 'user',
        'linkOptions' => [
            'target' => '_blank', 
        ],                                
        'visible' => $client->client_type == Client::CLIENT_TYPE_PHYSICAL,
    ],    
 * 
 */
    /*
    ['label' => Contact::modelTitle(2), 
        'url' => ['/contact/index', 'client_id' => $client->id],
        'icon' => 'phone-alt',
        'active' => (isset($activeItem) && ($activeItem == Contact::tableName())),
    ],
    ['label' => Agreement::modelTitle(2), 
        'url' => ['/agreement/index', 'client_id' => $client->id],
        'icon' => 'briefcase',
        'active' => (isset($activeItem) && ($activeItem == Agreement::tableName())),
    ],
     * 
     */
];

switch ($client->status) {
    case Client::CLIENT_STATUS_ACTIVE:
        $headerClass = 'client-status-active';
        break;
    case Client::CLIENT_STATUS_POTENTIAL:
        $headerClass = 'client-status-potential';
        break;
    case Client::CLIENT_STATUS_ARHIVED:
    default: 
        $headerClass = 'client-status-arhived';
        break;
}
if($client->deleted){
    $headerClass = 'client-status-deleted';
}
?>

<?= SideNav::widget([
    'items' => $items,
    'heading' => $client->name,
    'headingOptions' => [
        'class' => $headerClass,
    ],
    'containerOptions' => [
        'id' => 'client-navbar',
    ],
]);
?>
