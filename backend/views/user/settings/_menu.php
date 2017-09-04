<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
use yii\helpers\Url;

use kartik\widgets\SideNav;

use common\models\client\Client;

$user = isset($profile->user) ? $profile->user : Yii::$app->user->identity;
$networksVisible = count(Yii::$app->authClientCollection->clients) > 0;

$items = [
    ['label' => Yii::t('user', 'Profile'), 
        'url' => empty($profile->id) ? ['/user/settings/profile'] : ['/user/settings/profile', 'id' => $user->id],
        'icon' => 'user',
        'active' => (isset($activeItem) && ($activeItem == 'profile')),
    ],
    ['label' => Yii::t('fsmuser', 'My company'), 
        'url' => ['/client/view', 'id' => $profile->client_id, 'profile_id' => $profile->id],
        'template'=> '<a href="{url}" target="_blank">{icon}{label}</a>',
        'icon' => 'briefcase',
        'active' => (isset($activeItem) && ($activeItem == Client::tableName())),
        'visible' => !empty($profile->client_id),
    ],
    ['label' => Yii::t('user', 'Account'),  
        'url' => empty($profile->id) ? ['/user/settings/account'] : ['/user/settings/account', 'id' => $user->id],
        'icon' => 'lock',
        //'visible' => Yii::$app->user->can('showBackend'),
    ],
    ['label' => Yii::t('user', 'Networks'), 
        'url' => empty($profile->id) ? ['/user/settings/networks'] : ['/user/settings/networks', 'id' => $user->id],
        'icon' => 'comment',
        'visible' => $networksVisible,
    ],
];
?>

<?= SideNav::widget([
    'items' => $items,
    'heading' => 
        "<img src='http://gravatar.com/avatar/{$user->profile->gravatar_id}>?s=24' class='img-rounded' alt='{$user->username}'/> ".(!empty($profile->name) ? $profile->name : $user->username)
]);
?>
