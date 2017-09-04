<?php

namespace backend\assets_b;

use yii\helpers\ArrayHelper;

class AppAsset extends \kartik\widgets\AssetBundle {
    
    /**
     * @inheritdoc
     */
    public function init() {
        //require_once('backend/assets_b/js/globalJSVars.php');

        $this->setSourcePath('@webroot/assets_b');
        $this->setupAssets('css', [
            'css/site',
            'css/backend',
        ]);
        $this->setupAssets('js', [
            //'js/filJSBackend',
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
