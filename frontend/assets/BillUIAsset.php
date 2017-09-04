<?php

namespace frontend\assets;

class BillUIAsset extends \kartik\widgets\AssetBundle {

    public function init() {
        $this->setSourcePath('@frontend/views/bill/assets');
        $this->setupAssets('js', [
            'js/filJSBill',
        ]);
        
        $this->setupAssets('css', [
            'css/bill',
        ]);

        $this->depends = [
            'common\assets\UIAsset',
        ];
        
        if (YII_ENV_DEV) {
            $this->publishOptions['forceCopy'] = true;
        }
        
        parent::init();
    }

}
