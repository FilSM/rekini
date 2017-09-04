<?php

namespace common\assets;

//use yii\web\AssetBundle;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class CommonAsset extends \kartik\widgets\AssetBundle {
    
    /*
      public $css = [
      // your css files here
      ];
      public $js = [
      // your js files here
      ];
     * 
     */
    /*
    public $depends = [
        'common\assets\PHPJSAsset',
    ];
     * 
     */

    /**
     * @inheritdoc
     */
    public function init() {
        require_once('globalJSVars.php');

        $this->setSourcePath('@common/assets');
        $this->setupAssets('css', [
            'css/common',
            //'css/grid',
        ]);
        $this->setupAssets('js', [
            'js/filJSCommon',
        ]);
        
        $this->depends = ArrayHelper::merge(
            $this->depends,
            [
                'yii\web\YiiAsset',
                'yii\bootstrap\BootstrapAsset',
                'common\assets\PHPJSAsset',
            ]
        );
        
        if (YII_ENV_DEV) {
            $this->publishOptions['forceCopy'] = true;
        }
        
        parent::init();
    }

}
