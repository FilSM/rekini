<?php

/* @var $this \yii\web\View */
/* @var $content string */

namespace common\models;

use Yii;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\bootstrap\Modal;
use yii\widgets\Breadcrumbs;
use yii\jui\Draggable;

//use kartik\nav\NavX;
use kartik\widgets\AlertBlock;
use kartik\helpers\Html;

use common\components\FSMNavX;
use common\models\client\Client;
use common\models\client\Project;
use common\models\client\Agreement;
use frontend\assets\AppAsset;

AppAsset::register($this);
$this->registerLinkTag(['rel' => 'shortcut icon', 'type' => 'image/png', 'href' => '../favicon.png?v='. time()]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body id="frontend-body">
    <?php $this->beginBody() ?>
    <div class="wrap">
        <?php
            NavBar::begin(
            [
                'brandLabel' => Yii::$app->params['brandLabel'],
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-inverse navbar-fixed-top',
                    //'class' => 'navbar-inverse',
                    'style' => 'z-index: 1002;'
                ],
            ]);
            
            $menuLeftItems = [
                ['label' => Yii::t('yii', 'Home'), 
                    'url' => ['/site/index'],
                    'visible' => Yii::$app->user->isGuest,
                ],
                
                ['label' => \common\models\bill\Bill::modelTitle(2), 
                    'url' => ['/bill'],
                    'items' => [
                        [
                            'label' => Yii::t('bill', 'Add new invoice'), 
                            'url' => ['/bill/create'],
                        ],
                        '<li class="divider"></li>',
                        [
                            'label' => Yii::t('bill', 'Export / Import'), 
                            'items' => [
                                [
                                    'label' => Yii::t('bill', 'Prepared payments'), 
                                    'url' => ['/payment-order/index'],
                                ],
                                [
                                    'label' => Yii::t('bill', 'Payment confirmations'), 
                                    'url' => ['/payment-confirm/index'],
                                ],
                            ]
                        ],
                        [
                            'label' => Yii::t('bill', 'All invoices'), 
                            'url' => ['/bill'],
                        ],
                    ],
                    'visible' => !Yii::$app->user->isGuest,
                ],
                ['label' => \common\models\bill\Expense::modelTitle(2), 
                    'url' => ['/expense'],
                    'items' => [
                        [
                            'label' => Yii::t('bill', 'Add new expense'), 
                            'url' => ['/expense/create'],
                        ],
                        '<li class="divider"></li>',
                        [
                            'label' => Yii::t('bill', 'All expenses'), 
                            'url' => ['/expense'],
                        ],
                    ],
                    'visible' => !Yii::$app->user->isGuest,
                ],
                                
                ['label' => \common\models\client\Client::modelTitle(2), 
                    'url' => ['/client'],
                    'items' => [
                        [
                            'label' => Yii::t('client', 'Add new client'), 
                            'url' => ['/client/create'],
                        ],
                        '<li class="divider"></li>',
                        [
                            'label' => Yii::t('client', 'All clients'), 
                            'url' => ['/client'],
                        ],
                        [
                            'label' => Yii::t('client', 'My clients'), 
                            'url' => ['/client', 'manager_id' => Yii::$app->user->identity ? (Yii::$app->user->identity->profile ? Yii::$app->user->identity->profile->id : null) : null],
                        ],                        
                        [
                            'label' => Yii::t('client', 'Our clients'), 
                            'url' => ['/client', 'our_clients' => true],
                        ],                        
                        [
                            'label' => Yii::t('client', 'External clients'), 
                            'url' => ['/client', 'our_clients' => false],
                        ],    
                        /*
                        '<li class="divider"></li>',
                        [
                            'label' => Yii::t('abonent', 'Abonent list'), 
                            'url' => ['/abonent'],
                        ],      
                         * 
                         */                  
                    ],
                    'visible' => !Yii::$app->user->isGuest,
                ],
                
                ['label' => Project::modelTitle(2), 
                    'url' => ['/project'],
                    'items' => [
                        [
                            'label' => Yii::t('client', 'Add new project'), 
                            'url' => ['/project/create'],
                        ],
                        '<li class="divider"></li>',
                        [
                            'label' => Yii::t('client', 'All projects'), 
                            'url' => ['/project'],
                        ],
                    ],
                    'visible' => !Yii::$app->user->isGuest,
                ],
                
                ['label' => Agreement::modelTitle(2), 
                    'url' => ['/agreement'],
                    'items' => [
                        [
                            'label' => Yii::t('client', 'Add new agreement'), 
                            'url' => ['/agreement/create'],
                        ],
                        '<li class="divider"></li>',
                        [
                            'label' => Yii::t('client', 'All agreements'), 
                            'url' => ['/agreement'],
                        ],
                    ],
                    'visible' => !Yii::$app->user->isGuest,
                ],
                
                ['label' => Yii::t('report', 'Reports'), 
                    'items' => [
                        [
                            'label' => Yii::t('report', 'Debtors/Creditors'), 
                            'url' => ['/base-report/debitor-creditor'],
                        ],
                        [
                            'label' => Yii::t('report', 'EBITDA'), 
                            'url' => ['/base-report/ebitda'],
                        ],
                        [
                            'label' => Yii::t('report', 'VAT Report'), 
                            'url' => ['/base-report/vat'],
                        ],
                        [
                            'label' => Yii::t('report', 'Bank statements'), 
                            'url' => ['/client-bank/report'],
                        ],
                    ],
                    'visible' => !Yii::$app->user->isGuest,
                ],
                
                ['label' => Yii::t('common', 'About us'), 
                    'url' => ['/site/about'],
                    'visible' => Yii::$app->user->isGuest,
                ],
                ['label' => Yii::t('common', 'Contacts'), 
                    'url' => ['/site/contact'],
                    'visible' => Yii::$app->user->isGuest,
                ],
            ];

            $menuRightItems = [
                ['label' => '', 
                    'url' => ['/backend'], 
                    'linkOptions' => [
                        'title' => Yii::t('common', 'Admin'), 
                        'class' => 'glyphicon glyphicon-cog',
                        'style' => 'font-size: 1.7em;',
                    ],
                    'visible' => Yii::$app->user->can('showBackend'),
                    //'visible' => !Yii::$app->user->isGuest,
                ], 
                ['label' => '', 
                    'url' => ['/user/security/login'], 
                    'linkOptions' => [
                        'title' => Yii::t('common', 'Login'), 
                        'class' => 'glyphicon glyphicon-log-in',
                    ],
                    'visible' => Yii::$app->user->isGuest,
                ],
            ];
            
            if(!Yii::$app->user->isGuest){
                $user = Yii::$app->user->identity;
                $gravatar_id = $user->profile ? $user->profile->gravatar_id : null;
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
                    ['label' => $gravatar_id ? "<img src='http://gravatar.com/avatar/{$gravatar_id}>?s=28' alt='{$user->username}'/> ".($user->profile ? $user->profile->name : '') : '',
                        'items' => [
                            [
                                'label' => Yii::t('user', 'Profile'), 
                                'url' => ['/user/profile/show', 'id' => (isset($user) ? $user->id : null)],
                            ],
                            [
                                'label' => Yii::t('fsmuser', 'My company'), 
                                'url' => ['/client/view', 'id' => (isset($user, $user->profile, $user->profile->client_id) ? $user->profile->client_id : null)],
                                'linkOptions' => [
                                    'target' => '_blank', 
                                ],                                
                                'visible' => (isset($user, $user->profile, $user->profile->client) ? true : false)
                            ],
                            '<li class="divider"></li>',
                            [
                                'label' => Html::icon('log-out').'&nbsp;'.Yii::t('common', 'Log Out'),
                                'url' => ['/user/security/logout'],
                                'linkOptions' => [
                                    //'title' => Yii::t('common', 'Log Out'), 
                                    //'class' => 'glyphicon glyphicon-log-out',
                                    'data-method' => 'post'
                                ],
                            ],
                        ],
                        'linkOptions' => $linkOptions,
                    ]
                ]);
            } 
            
            echo FSMNavX::widget([
                'options' => ['class' => 'navbar-nav navbar-left'],
                'items' => $menuLeftItems,
                'activateParents' => true,
                'encodeLabels' => false
            ]);
            
            echo FSMNavX::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => $menuRightItems,
                'activateParents' => true,
                'encodeLabels' => false
            ]);            
            
            NavBar::end();
        ?>

        <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]); ?>
            
        <?= AlertBlock::widget([
                'delay' => 0,
                //'delay' => 5000,
            ]); 
        ?>  

        <?php /*echo Alert::widget();*/ ?>
        <?= $content ?>
        </div>
    </div>

    <footer class="footer" style="padding-top: 0;">
        <div class="container">
        <p class="pull-left" style="margin: 0 5px 0 -15px;">
            <a href="https://www.vultr.com/?ref=7183965" target="_blank"><img src="https://www.vultr.com/media/banner_2.png" height="59"></a>
        </p>
        <p class="pull-left" style="padding-top: 20px;">&copy; Eventus SIA <?= date('Y') ?></p>
        <p class="pull-right" style="padding-top: 20px;"><?= Yii::powered() ?></p>
        <p class="pull-right" style="padding-top: 20px; margin-right: 5px;"> <?= Yii::t('app', 'Designer'); ?> <a href="mailto:philip.smelov@gmail.com"><span class="glyphicon glyphicon-envelope"></span> Philip Smelov</a></p>
        </div>
    </footer>
    
    <?php
        Draggable::begin();
        Draggable::end();
        
        Modal::begin([
            'header' => '<span id="modalHeaderTitle"></span>',
            'headerOptions' => [
                'id' => 'modalHeader',
                'class' => 'modal-header type-primary bootstrap-dialog-draggable',
            ],
            'id' => 'modal-window',
            'size' => 'modal-lg',
            //keeps from closing modal with esc key or by clicking out of the modal.
            // user must click cancel or X to close
            'clientOptions' => ['backdrop' => 'static', 'keyboard' => FALSE],
            'closeButton' => ['id' => 'close-button'],
            'options' => [
                'tabindex' => false // important for Select2 to work properly
            ],            
        ]);
        
        $imgPath = Url::to('@web/common/assets/images', true);
        echo '<div id="modalContent"><div style="text-align:center"><img src="'.$imgPath.'/loader.gif" alt="Loading..."/></div></div>';
        
        Modal::end();
    ?>
    
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
