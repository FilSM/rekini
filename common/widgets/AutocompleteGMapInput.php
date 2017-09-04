<?php

namespace common\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use common\widgets\google\GoogleUIWidget;

class AutocompleteGMapInput extends GoogleUIWidget {

    private $geoInputId = '';
    private $center;
    private $lat = 56.94839;
    private $lng = 24.107874;
    private $zoom;
    
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
        $this->zoom = array_key_exists('zoom', $this->pluginOptions) ? $this->pluginOptions['zoom'] : 15;
        $this->lat = $this->center && isset($this->center['lat']) ? $this->center['lat'] : (isset($this->model) && !empty($this->model->id) ? $this->model->latitude : $this->lat);
        $this->lng = $this->center && isset($this->center['lng']) ? $this->center['lng'] : (isset($this->model) && !empty($this->model->id) ? $this->model->longitude : $this->lng);
        $this->center = array_key_exists('center', $this->pluginOptions) && !empty($this->pluginOptions['center']['lat']) ? 
            $this->pluginOptions['center'] : 
            ['lat' => $this->lat, 'lng' => $this->lng];
        
        $this->options['id'] = array_key_exists('id', $this->pluginOptions) ? $this->pluginOptions['id'] : 'map_canvas_'.uniqid();
        Html::addCssClass($this->options, "gmap-control");
        
        if(array_key_exists('visible', $this->pluginOptions) && !$this->pluginOptions['visible']){
            Html::addCssClass($this->options, "hidden");
        }     
        if(array_key_exists('extContainer', $this->pluginOptions)){
            $this->options['extContainer'] = $this->pluginOptions['extContainer'];
        }else{
            $this->options['extContainer'] = null;
        }
        
        if(array_key_exists('controlBtn', $this->pluginOptions)){
            $this->options['controlBtn'] = $this->pluginOptions['controlBtn'];
        }else{
            $this->options['controlBtn'] = null;
            Html::removeCssClass($this->options, "hidden");
        }
        
        if(array_key_exists('myLocationBtn', $this->pluginOptions)){
            $this->options['myLocationBtn'] = $this->pluginOptions['myLocationBtn'];
        }else{
            $this->options['myLocationBtn'] = null;
        }
        
        if(array_key_exists('detailInputs', $this->pluginOptions)){
            $this->options['detailInputs'] = $this->pluginOptions['detailInputs'];
        }else{
            $this->options['detailInputs'] = null;            
        }
        
        $map = Html::tag('div', '', $this->options);
        
        echo $input.$map;
        
        $this->registerClientScript();
    }

    public function registerClientScript() {
        $view = $this->getView();
        $view->registerJsFile("https://maps.googleapis.com/maps/api/js?key={$this->api_key}&libraries=places&language=LV");

        $params['center'] =  $this->center;
        $params['zoom'] = $this->zoom;
        $params['id'] = $this->options['id'];
        $params['addressInput'] = $this->geoInputId;
        $params['extContainer'] = $this->options['extContainer'];
        $params['controlBtn'] = $this->options['controlBtn'];
        $params['myLocationBtn'] = $this->options['myLocationBtn'];
        $params['detailInputs'] = $this->options['detailInputs'];
        
        $params = json_encode($params);
        
        $js = "$('#{$this->options['id']}').autoGMap({$params});";
        $view->registerJs($js);
    }

}
