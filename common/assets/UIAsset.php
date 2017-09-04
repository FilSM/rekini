<?php

namespace common\assets;

use kartik\base\AssetBundle;

class UIAsset extends AssetBundle {

    public function init() {
        $this->setSourcePath('@common/assets');
        $this->setupAssets('js', [
            'js/filJSCommon',
        ]);
        
        /*
        $this->setupAssets('css', [
            'css/gmap.control',
        ]);
         * 
         */
        $this->depends = [
            'common\assets\PHPJSAsset',
        ];
        
        if (YII_ENV_DEV) {
            $this->publishOptions['forceCopy'] = true;
        }
        
        parent::init();
    }

}
