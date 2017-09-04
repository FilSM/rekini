<?php

namespace common\widgets\google;

use yii\web\View;

class GoogleUIAsset extends \kartik\widgets\AssetBundle {

    public function init() {
        $this->setSourcePath('@common/widgets/google/assets');
        $this->setupAssets('js', [
            'js/geoPosition',
            'js/google.control.autocompletemap',
            'js/google.control.geocoder',
            /*
            'js/google.control.map',
             * 
             */
        ]);
        $this->setupAssets('css', [
            'css/gmap.control',
        ]);
        
        //$this->jsOptions['position'] = View::POS_BEGIN;
        
        if (YII_ENV_DEV) {
            $this->publishOptions['forceCopy'] = true;
        }
        
        parent::init();
    }

}
