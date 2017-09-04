<?php

namespace frontend\assets;

class AgreementUIAsset extends \kartik\widgets\AssetBundle {

    public function init() {
        $this->setSourcePath('@frontend/views/client/agreement/assets');
        $this->setupAssets('js', [
            'js/filJSAgreement',
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
