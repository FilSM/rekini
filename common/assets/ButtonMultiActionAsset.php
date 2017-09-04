<?php

namespace common\assets;

use kartik\base\AssetBundle;

class ButtonMultiActionAsset extends AssetBundle {

    public function init() {
        $this->setSourcePath('@common/assets');
        $this->setupAssets('js', ['js/fsmMultiAction']);
        //$this->setupAssets('css', ['css/fsm-delete-action']);
        
        if (YII_ENV_DEV) {
            $this->publishOptions['forceCopy'] = true;
        }
        
        parent::init();
    }

}
