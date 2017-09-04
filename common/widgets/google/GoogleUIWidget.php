<?php

namespace common\widgets\google;

use Yii;

use \kartik\widgets\InputWidget;

class GoogleUIWidget extends InputWidget {

    public $api_key;

    public function init() {
        parent:: init();
        $this->api_key = @Yii::$app->params['googleMapsApiKey'];
        $this->registerAssets();
    }

    protected function registerAssets() {
        $view = $this->getView();
        GoogleUIAsset::register($view);
    }

}
