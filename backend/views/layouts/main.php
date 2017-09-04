<?php

/* @var $this \yii\web\View */
/* @var $content string */

namespace common\models;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Modal;

use kartik\nav\NavX;
use kartik\widgets\AlertBlock;
//use kartik\dialog\Dialog;

use backend\assets_b\AppAsset;
use common\widgets\languageSelection\FSMLanguageSelection;
use common\models\user\FSMUser;

/* @var $this \yii\web\View */
/* @var $content string */

AppAsset::register($this);

?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
    <head>
    	<meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= Html::csrfMetaTags() ?>
        <title><?= Yii::$app->params['brandLabel']; ?></title>
        <!--title><?= Html::encode($this->title) ?></title-->
        <?php $this->head() ?>
    </head>
    <body id="backend-body">
        <?php $this->beginBody() ?>
        <div class="wrap">
            <?php
            NavBar::begin([
                'brandLabel' => Yii::$app->params['brandLabel'],
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-default navbar-fixed-top',
                    'style' => 'z-index: 1002;'
                ],
            ]);
            
            $menuLeftItems = [
                /*
                FSMLanguageSelection::widget([
                    //'language' => ['lv', 'en-us', 'ru'],
                    //'languageParam' => 'language',
                    //'container' => 'div', // li for navbar, div for sidebar or footer example
                    //'classContainer' =>  'btn btn-default dropdown-toggle' // btn btn-default dropdown-toggle
                ]),
                 * 
                 */
                ['label' => Yii::t('admin', 'Admin'),
                    'visible' => !Yii::$app->user->isGuest,
                    'items' => [
                        ['label' => Yii::t('admin', 'Main lists'), 
                            'items' => [
                                ['label' => \common\models\Language::modelTitle(2), 
                                    'items' => [
                                        ['label' => Yii::t('languages', 'Communication language'), 'url' => ['/language']],
                                        ['label' => Yii::t('languages', 'Translation'), 
                                            'items' => [
                                                ['label' => Yii::t('language', 'Language'), 
                                                    'items' => [
                                                        ['label' => Yii::t('language', 'List of languages'), 'url' => ['/translatemanager/language/list']],
                                                        ['label' => Yii::t('language', 'Create'), 'url' => ['/translatemanager/language/create']],
                                                    ]
                                                ],
                                                ['label' => Yii::t('language', 'Scan'), 'url' => ['/translatemanager/language/scan']],
                                                ['label' => Yii::t('language', 'Optimize'), 'url' => ['/translatemanager/language/optimizer']],
                                            ],
                                        ],
                                    ],
                                ],
                                //['label' => Yii::t('languages', 'Communication languages'), 'url' => ['/language']],
                                ['label' => Bank::modelTitle(2), 'url' => ['/bank']],
                                ['label' => Valuta::modelTitle(2), 'url' => ['/valuta']],
                                ['label' => Measure::modelTitle(2), 'url' => ['/measure']],
                                ['label' => Product::modelTitle(2), 'url' => ['/product']],
                                ['label' => client\ClientGroup::modelTitle(2), 'url' => ['/client-group']],
                                ['label' => client\ClientRole::modelTitle(2), 'url' => ['/client-role']],
                                ['label' => client\PersonPosition::modelTitle(2), 'url' => ['/person-position']],
                                ['label' => client\RegDocType::modelTitle(2), 'url' => ['/reg-doc-type']],
                                ['label' => bill\ExpenseType::modelTitle(2), 'url' => ['/expense-type']],
                                ['label' => Yii::t('common', 'Locations'), 
                                    'items' => [
                                        ['label' => \common\models\address\Country::modelTitle(2), 'url' => ['/country']],
                                        ['label' => \common\models\address\Region::modelTitle(2), 'url' => ['/region']],
                                        ['label' => \common\models\address\City::modelTitle(2), 'url' => ['/city']],
                                        ['label' => \common\models\address\District::modelTitle(2), 'url' => ['/district']],
                                        '<li class="divider"></li>',
                                        ['label' => \common\models\address\Address::modelTitle(2), 'url' => ['/address']],
                                    ],
                                ],
                                ['label' => '<li class="divider"></li>', 
                                    'visible' => !Yii::$app->user->isGuest && FSMUser::getIsPortalAdmin(),
                                ],
                                ['label' => Yii::t('admin', 'RBAC'), 
                                    'items' => [
                                        ['label' => Yii::t('admin', 'Assignments'), 'url' => ['/admin/assignment']],
                                        ['label' => Yii::t('admin', 'Roles'), 'url' => ['/admin/role']],
                                        ['label' => Yii::t('admin', 'Permissions'), 'url' => ['/admin/permission']],
                                        ['label' => Yii::t('admin', 'Routes'), 'url' => ['/admin/route']],
                                        ['label' => Yii::t('admin', 'Rules'), 'url' => ['/admin/rule']],
                                        ['label' => Yii::t('admin', 'Menu'), 'url' => ['/admin/menu']],
                                    ],
                                    'visible' => !Yii::$app->user->isGuest && FSMUser::getIsPortalAdmin(),
                                ],
                            ],
                        ],
                        ['label' => Yii::t('action', 'History'), 
                            'items' => [
                                ['label' => Yii::t('bill', 'Invoices history'), 'url' => ['/history-bill/index']],
                            ],
                        ],
                        ['label' => Yii::t('bill', 'Payments'), 
                            'items' => [
                                ['label' => Yii::t('bill', 'Check delayed invoices'), 'url' => ['/bill/check-delayed', 'isCron' => false]],
                            ],
                        ],
                        '<li class="divider"></li>',
                        ['label' => Yii::t('user', 'Users'), 'url' => ['/user/admin/index']],
                    ],
                ],
            ];
            
            $menuRightItems = [
                ['label' => '',
                    'url' => \yii\helpers\Url::to('@web').'/..',
                    //'url' => Yii::$app->user->isGuest ? ['../../'] : ['../'],
                    'linkOptions' => [
                        'title' => Yii::t('common', 'Application'),
                        'class' => 'glyphicon glyphicon-cog',
                        'style' => 'font-size: 1.7em;',
                    ],
                    //'visible' => !Yii::$app->user->isGuest,
                ],
                ['label' => '',
                    'url' => ['/user/login'],
                    'linkOptions' => [
                        'title' => Yii::t('common', 'Login'),
                        'class' => 'glyphicon glyphicon-off'
                    ],
                    'visible' => Yii::$app->user->isGuest,
                ],                
            ];
            if(!Yii::$app->user->isGuest){
                $user = Yii::$app->user->identity;
                $gravatar_id = $user->profile->gravatar_id;
                if(!$gravatar_id){
                    $linkOptions = [
                        'title' => Yii::t('user', 'Profile') . ' (' . Yii::$app->user->identity->username . ')', 
                        'class' => 'glyphicon glyphicon-user',
                        'style' => 'font-size: 1.7em;'
                    ];
                }else{
                    $linkOptions = [
                        //'title' => Yii::t('user', 'Profile') . ' (' . Yii::$app->user->identity->username . ')', 
                        'class' => 'gravatar',
                        //'style' => 'font-size: 1.5em;'
                    ];                    
                }
                $menuRightItems = ArrayHelper::merge($menuRightItems, [
                    ['label' => $gravatar_id ? "<img src='http://gravatar.com/avatar/{$gravatar_id}>?s=28' alt='{$user->username}'/> {$user->profile->name}" : '',
                        'items' => [
                            [
                                'label' => 'Profile', 
                                //'label' => Yii::t('user', 'Profile'), 
                                'url' => ['/user/profile/index'],
                            ],
                            [
                                'label' => Yii::t('fsmuser', 'My company'), 
                                'url' => ['/client/view', 'id' => (isset($user, $user->profile, $user->profile->client_id) ? $user->profile->client_id : null)],
                                'visible' => (isset($user, $user->profile, $user->profile->client) ? true : false)
                            ],
                            '<li class="divider"></li>',
                            ['label' => '',
                                'url' => ['/user/security/logout'],
                                'linkOptions' => [
                                    'title' => Yii::t('common', 'Log Out'), 
                                    'class' => 'glyphicon glyphicon-off',
                                    'data-method' => 'post'
                                ],
                            ],
                        ],
                        'linkOptions' => $linkOptions,
                    ]
                ]);
            }            
            
            echo NavX::widget([
                'options' => ['class' => 'navbar-nav navbar-left'],
                'items' => $menuLeftItems,
                'activateParents' => true,
                'encodeLabels' => false
            ]);
            
            echo NavX::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuRightItems,
                'activateParents' => true,
                'encodeLabels' => false
            ]);
                        
            NavBar::end();
            ?>

            <div class="container container-type-<?= strtolower($this->context->id)?>">
                <?= Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ])
                ?>
                <?= AlertBlock::widget([
                    'delay' => 0,
                    //'delay' => 5000,
                    'useSessionFlash' => true
                ]);
                ?>            
                <?= $content ?>
            </div>
        </div>

        <footer class="footer">
            <div class="container">
                <p class="pull-left">&copy; <?= Yii::$app->params['brandLabel'].' '.date('Y') ?></p>
                <p class="pull-right"><?= Yii::powered() ?></p>
            </div>
        </footer>
    
        <?php
            Modal::begin([
                'header' => '<span id="modalHeaderTitle"></span>',
                'headerOptions' => ['id' => 'modalHeader'],
                'id' => 'modal-window',
                'size' => 'modal-lg',
                //keeps from closing modal with esc key or by clicking out of the modal.
                // user must click cancel or X to close
                'clientOptions' => [
                    'backdrop' => 'static', 
                    'keyboard' => false,
                ],
                //'closeButton' => ['id' => 'close-button'],
            ]);

            $imgPath = Url::to('@web/../common/assets/images', true);
            echo '<div id="modalContent"><div style="text-align:center"><img src="'.$imgPath.'/loader.gif" alt="Loading..."/></div></div>';

            Modal::end();
        ?>
        
        <?php $this->endBody() ?>
    </body>
</html>
<?php $this->endPage() ?>
