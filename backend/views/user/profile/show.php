<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use kartik\helpers\Html;

use common\models\user\FSMUser;
use common\models\client\Client;

/**
 * @var \yii\web\View $this
 * @var \dektrium\user\models\Profile $profile
 */

$this->title = empty($profile->name) ? Html::encode($profile->user->username) : Html::encode($profile->name);
if(Yii::$app->user->can('showBackend')){
    $this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Users'), 'url' => ['/user/admin/index']];
}
$this->params['breadcrumbs'][] = $this->title;

$url = ['/user/settings', 'id' => (isset($_GET['id']) ? $_GET['id'] : null)];
?>
<div class="row">
    <div class="col-xs-12 col-md-12">
        <div class="row">
            <div class="col-md-6 col-md-2">
                <?= Html::img($profile->getAvatarUrl(230), [
                    'class' => 'img-rounded img-responsive',
                    'alt'   => $profile->user->username,
                    'style' => 'margin: 0 auto;',
                ]) ?>
                <?php if($isOwner || $itIsMyProfile) : ?>    
                <br/>
                <?= Html::a(Yii::t('user', 'Profile settings'), $url, [
                        'class' => 'btn btn-success btn-block',
                    ]);
                ?>
                <?php endif; ?>
            </div>
            <div class="col-md-6 col-md-10">
                <h4><?= $this->title ?></h4>
                <ul style="padding: 0; list-style: none outside none;">
                    <?php if (!empty($profile->client)): ?>
                        <li><?= Html::icon('home', ['class' => 'text-muted']);?> <?= Html::encode($profile->client->name) ?></li>
                    <?php endif; ?>
                    <?php if (!empty($profile->location)): ?>
                        <li><?= Html::icon('map-marker', ['class' => 'text-muted']);?> <?= Html::encode($profile->location) ?></li>
                    <?php endif; ?>
                    <?php /* if (!empty($profile->website)): ?>
                        <li><?= Html::icon('globe', ['class' => 'text-muted']);?> <?= Html::a(Html::encode($profile->website), Html::encode($profile->website)) ?></li>
                    <?php endif; */ ?>
                    <?php if (!empty($profile->user->email)): ?>
                        <li><?= Html::icon('envelope', ['class' => 'text-muted']);?> <?= Html::a(Html::encode($profile->user->email), 'mailto://' . Html::encode($profile->user->email)) ?></li>
                    <?php endif; ?>
                    <li><?= Html::icon('time', ['class' => 'text-muted']);?> <?= Yii::t('user', 'Joined on {0, date}', $profile->user->created_at) ?></li>
                </ul>
                <?php if (!empty($profile->bio)): ?>
                    <p><?= Html::encode($profile->bio) ?></p>
                <?php endif; ?>
                    
            </div>
        </div>
    </div>
</div>
