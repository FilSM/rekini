<?php

namespace common\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use common\widgets\google\GoogleUIWidget;

class GeocodeAutocompleteInput extends GoogleUIWidget {

    private $geoInputId = '';
    public $pluginOptions = [];    
    /**
     * Renders the widget.
     */
    public function run() {
        parent::run();
        
        if (empty($this->options['class'])) {
            Html::addCssClass($this->options, "form-control");
        }           
        $input = $this->getInput('textInput');
        unset($this->options['placeholder'], $this->options['maxlength']);
        
        $this->geoInputId = $this->options['id'];
        $this->options['id'] = array_key_exists('id', $this->options) ? $this->options['id'] : 'geocoder_canvas_'.uniqid();
        Html::addCssClass($this->options, "geocoder-control");
        if(array_key_exists('detailInputs', $this->pluginOptions)){
            $this->options['detailInputs'] = $this->pluginOptions['detailInputs'];
        }else{
            $this->options['detailInputs'] = null;            
        }
        
        //$div = Html::tag('div', '', $this->options);
        //echo $input.$div;
        echo $input;
        
        $this->registerClientScript();
    }

    public function registerClientScript() {
        $view = $this->getView();
        $view->registerJsFile("https://maps.googleapis.com/maps/api/js?key={$this->api_key}&libraries=places&language=LV");

        $params['id'] = $this->options['id'];
        $params['addressInput'] = $this->geoInputId;
        $params['detailInputs'] = $this->options['detailInputs'];
        $params = json_encode($params);
        $js = "$('#{$this->options['id']}').geoCoder({$params});";
        $view->registerJs($js);
    }

}
