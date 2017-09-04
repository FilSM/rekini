<?php

namespace frontend\assets;

class ClientUIAsset extends \kartik\widgets\AssetBundle {

    public function init() {
        $this->setSourcePath('@frontend/views/client/client/assets');
        $this->setupAssets('js', [
            'js/filJSClient',
        ]);
        
        /*
        $this->setupAssets('css', [
            'css/gmap.control',
        ]);
         * 
         */
        $this->depends = [
            'common\assets\UIAsset',
        ];
        
        if (YII_ENV_DEV) {
            $this->publishOptions['forceCopy'] = true;
        }
        
        parent::init();
    }

}
