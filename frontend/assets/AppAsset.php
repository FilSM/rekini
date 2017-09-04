<?php

namespace frontend\assets;

use yii\helpers\ArrayHelper;
use yii\helpers\Url;

class AppAsset extends \kartik\widgets\AssetBundle {

    /**
     * @inheritdoc
     */
    public function init() {
        //require_once('frontend/assets/js/globalJSVars.php');

        $this->setSourcePath('@webroot/frontend/assets');
        $this->setupAssets('css', [
            'css/site',
            //'css/frontend',
        ]);
        $this->setupAssets('js', [
            'js/filJSFrontend',
        ]);

        $this->depends = ArrayHelper::merge(
            $this->depends, [
                'common\assets\CommonAsset',
            ]
        );

        if (YII_ENV_DEV) {
            $this->publishOptions['forceCopy'] = true;
        }
        
        parent::init();
    }

}
