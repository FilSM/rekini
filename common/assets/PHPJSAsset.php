<?php

namespace common\assets;

class PHPJSAsset extends \kartik\widgets\AssetBundle {

    public function init() {
        $this->setSourcePath('@common/assets/php_js');
        $this->setupAssets('js', [
            'datetime/date',
            'datetime/strtotime',
            'math/round',
            //'strings/implode',
            'strings/explode',
            'strings/str_pad',
            'var/empty',
        ]);
        
        if (YII_ENV_DEV) {
            $this->publishOptions['forceCopy'] = true;
        }
        
        parent::init();
    }

}
