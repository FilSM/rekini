<?php

namespace common\assets;

use kartik\base\AssetBundle;

class ButtonDeleteAsset extends AssetBundle {

    public function init() {
        $this->setSourcePath('@common/assets');
        $this->setupAssets('js', ['js/fsmDeleteAction']);
        $this->setupAssets('css', ['css/fsm-delete-action']);
        
        if (YII_ENV_DEV) {
            $this->publishOptions['forceCopy'] = true;
        }
        
        parent::init();
    }

}
