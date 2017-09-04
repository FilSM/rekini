<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    //'language' => 'ru-RU',
    'language' => 'en-US',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        
        'urlManager' => [
            'class' => 'common\components\FSMUrlManager',
            //'languages' => ['en-US', 'en', 'lv', 'ru'],
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableLanguagePersistence' => false,
            'enableLanguageDetection' => false,
        ],
	
        'authManager' => [
            'class' => /*'yii\rbac\PhpManager', //*/ /*'yii\rbac\DbManager'//*/ 'common\components\rbac\FilSMDbManager',
            //'defaultRoles' => ['user'],
        ],
        
        'i18n' => [
            'translations' => [
                '*' => [
                    //'class' => 'yii\i18n\DbMessageSource',
                    'class' => 'common\components\FSMDbMessageSource',
                    'db' => 'db',
                    'sourceLanguage' => 'en-US',
                    //'basePath' => '@common/messages/bank',
                    'sourceMessageTable' => '{{%language_source}}',
                    'messageTable' => '{{%language_translate}}',
                    'cachingDuration' => 86400,
                    //'enableCaching' => true,  
                    'ignoredCategories' => ['language', 'javascript', 'model'], // these categories won’t be included in the language database.                    
                    'on missingTranslation' => ['common\components\FSMTranslationEventHandler', 'handleMissingTranslation']
                ],
            ]
        ],

        'view' => [
            'theme' => [
                'pathMap' => [
                    '@dektrium/user/views' => '@backend/views/user'
                ],
            ],
        ],
	        
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
    ],
    
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            //'enableUnconfirmedLogin ' => true,
            'enableFlashMessages' => true,
            'enableRegistration' => false,
            'confirmWithin' => 21600,
            'cost' => 12,
            //'admins' => ['FilSM'],
            'modelMap' => [
                'User' => 'common\models\user\FSMUser',
                'Profile' => 'common\models\user\FSMProfile',
                'LoginForm' => 'common\models\user\FSMLoginForm',
                'SettingsForm' => 'common\models\user\FSMSettingsForm',
                'RegistrationForm' => 'common\models\user\FSMRegistrationForm',
                'ResendForm' => 'common\models\user\FSMResendForm',
            ],
            'controllerMap' => [
                'admin' => 'backend\controllers\user\AdminController',
                'settings' => 'backend\controllers\user\SettingsController',
                'security' => 'backend\controllers\user\SecurityController',
                'profile' => 'backend\controllers\user\ProfileController',
                //'register' => 'backend\controllers\user\RegistrationController',
            ],
        ],
        'admin' => [
            'class' => 'mdm\admin\Module',
            'layout' => 'top-menu',
        ],
        'gridview' =>  [
            'class' => '\kartik\grid\Module'
        ],
        'translatemanager' => [
            'class' => 'lajax\translatemanager\Module',
            /*
            'root' => '@app',               // The root directory of the project scan.
            'layout' => 'language',         // Name of the used layout. If using own layout use 'null'.
             * 
             */ 
            'allowedIPs' => ['*'],  // IP addresses from which the translation interface is accessible.
            //'allowedIPs' => ['127.0.0.1', '::1', '185.8.62.116'],  // IP addresses from which the translation interface is accessible.
            /*
            'roles' => ['@'],               // For setting access levels to the translating interface.
            'tmpDir' => '@runtime',         // Writable directory for the client-side temporary language files. 
                                            // IMPORTANT: must be identical for all applications (the AssetsManager serves the JavaScript files containing language elements from this directory).
            'phpTranslators' => ['::t'],    // list of the php function for translating messages.
            'jsTranslators' => ['lajax.t'], // list of the js function for translating messages.
            'patterns' => ['*.js', '*.php'],// list of file extensions that contain language elements.
             * 
             */ 
            'ignoredCategories' => ['yii', 'user', 
                'language', 'javascript', 'model',
                'kvgrid', 'kvdrp', 'kvdetail', 'kvdialog', 'kvenum', 'kvbase', 
                'kvpwdstrength', 'kvcolor', 'kvdate', 'kvdtime', 'kvselect', 
                'fileinput', 'rbac-admin'], // these categories won’t be included in the language database.
            'ignoredItems' => ['config'],   // these files will not be processed.
            'languageTable' => '{{%language}}',
            'tables' => [                   // Properties of individual tables
                [
                    'connection' => 'db',   // connection identifier
                    'table' => 'measure', // table name
                    'columns' => ['name', 'description'] //names of multilingual fields
                ],
                [
                    'connection' => 'db',   // connection identifier
                    'table' => 'product', // table name
                    'columns' => ['name', 'description'] //names of multilingual fields
                ],                
            ],
        ],
    ],
    'as access' => [
        'class' => 'mdm\admin\components\AccessControl',
        'allowActions' => [
            '*', // add or remove allowed actions to this list
            //'admin/*', // add or remove allowed actions to this list
        ]
    ],
    'controllerMap' => [
        'address' => [
            'class' => 'backend\controllers\address\AddressController',
            'viewPath' => '@backend/views/address/address',
        ],
        'country' => [
            'class' => 'backend\controllers\address\CountryController',
            'viewPath' => '@backend/views/address/country',
        ],
        'region' => [
            'class' => 'backend\controllers\address\RegionController',
            'viewPath' => '@backend/views/address/region',
        ],
        'city' => [
            'class' => 'backend\controllers\address\CityController',
            'viewPath' => '@backend/views/address/city',
        ],
        'district' => [
            'class' => 'backend\controllers\address\DistrictController',
            'viewPath' => '@backend/views/address/district',
        ],
        'bank' => [
            'class' => 'backend\controllers\BankController',
            'viewPath' => '@backend/views/bank',
        ],          
        'client-role' => [
            'class' => 'backend\controllers\ClientRoleController',
            'viewPath' => '@backend/views/client-role',
        ],      
        'person-position' => [
            'class' => 'backend\controllers\PersonPositionController',
            'viewPath' => '@backend/views/person-position',
        ],      
        'client' => [
            'class' => 'frontend\controllers\client\ClientController',
            'viewPath' => '@frontend/views/client/client',
        ],      
        'client-group' => [
            'class' => 'backend\controllers\ClientGroupController',
            'viewPath' => '@backend/views/client-group',
        ],      
        'client-contact' => [
            'class' => 'frontend\controllers\client\ClientContactController',
            'viewPath' => '@frontend/views/client/client-contact',
        ],      
        'client-manager' => [
            'class' => 'frontend\controllers\client\ClientManagerController',
            'viewPath' => '@frontend/views/client/client-manager',
        ],      
        'client-bank' => [
            'class' => 'frontend\controllers\client\ClientBankController',
            'viewPath' => '@frontend/views/client/client-bank',
        ],      
        'client-bank-balance' => [
            'class' => 'frontend\controllers\client\ClientBankBalanceController',
            'viewPath' => '@frontend/views/client/client-bank-balance',
        ],      
        'share' => [
            'class' => 'frontend\controllers\client\ShareController',
            'viewPath' => '@frontend/views/client/share',
        ],      
        'shareholder' => [
            'class' => 'frontend\controllers\client\ShareholderController',
            'viewPath' => '@frontend/views/client/shareholder',
        ],      
        'abonent' => [
            'class' => 'frontend\controllers\abonent\AbonentController',
            'viewPath' => '@frontend/views/abonent',
        ],          
        'agreement' => [
            'class' => 'frontend\controllers\client\AgreementController',
            'viewPath' => '@frontend/views/client/agreement',
        ],          
        'project' => [
            'class' => 'frontend\controllers\client\ProjectController',
            'viewPath' => '@frontend/views/client/project',
        ],          
        'product' => [
            'class' => 'backend\controllers\ProductController',
            'viewPath' => '@backend/views/product',
        ],          
        'measure' => [
            'class' => 'backend\controllers\MeasureController',
            'viewPath' => '@backend/views/measure',
        ],          
        'bill' => [
            'class' => 'frontend\controllers\bill\BillController',
            'viewPath' => '@frontend/views/bill/bill',
        ],          
        'history-bill' => [
            'class' => 'frontend\controllers\bill\HistoryBillController',
            'viewPath' => '@frontend/views/bill/history-bill',
        ],          
        'payment-order' => [
            'class' => 'frontend\controllers\bill\PaymentOrderController',
            'viewPath' => '@frontend/views/bill/payment-order',
        ],          
        'payment-confirm' => [
            'class' => 'frontend\controllers\bill\PaymentConfirmController',
            'viewPath' => '@frontend/views/bill/payment-confirm',
        ],          
        'bill-payment' => [
            'class' => 'frontend\controllers\bill\BillPaymentController',
            'viewPath' => '@frontend/views/bill/bill-payment',
        ],          
        'bill-confirm' => [
            'class' => 'frontend\controllers\bill\BillConfirmController',
            'viewPath' => '@frontend/views/bill/bill-confirm',
        ],          
        'reg-doc-type' => [
            'class' => 'backend\controllers\RegDocTypeController',
            'viewPath' => '@backend/views/reg-doc-type',
        ],          
        'reg-doc' => [
            'class' => 'frontend\controllers\client\RegDocController',
            'viewPath' => '@frontend/views/client/reg-doc',
        ],          
        'expense-type' => [
            'class' => 'backend\controllers\ExpenseTypeController',
            'viewPath' => '@backend/views/expense-type',
        ],          
        'expense' => [
            'class' => 'frontend\controllers\bill\ExpenseController',
            'viewPath' => '@frontend/views/bill/expense',
        ],          
        'base-report' => [
            'class' => 'frontend\controllers\report\BaseReportController',
            'viewPath' => '@frontend/views/report',
        ],          
    ],
];
