<?php

namespace common\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use kartik\widgets\Select2;
use common\widgets\ButtonGroupInput;

class EnumInput extends \kartik\widgets\InputWidget {

    const TYPE_DEFAULT = 1;
    const TYPE_SELECT2 = 2;    
    const TYPE_RADIO = 3;
    const TYPE_RADIOBUTTON = 4;
    const TYPE_CHECKBOX = 5;
    const TYPE_CHECKBOXBUTTON = 6;

    public $type = self::TYPE_DEFAULT;
    public $data = '';
    public $without = [];
    public $clientOptions = [];
    public $translateList = [];


    /**
     * @var \yii\web\View instance
     */
    private $_view;
    
    /**
     * Initializes the widget.
     *
     */
    public function init() {
        if (empty($this->options['class']) && in_array($this->type,[self::TYPE_DEFAULT, self::TYPE_RADIO, self::TYPE_CHECKBOX])) {
            Html::addCssClass($this->options, "form-control");
            //$this->options['class'] = 'form-control';
        }        
        parent::init();
        if ($this->type !== self::TYPE_SELECT2 && !empty($this->options['placeholder'])) {
            $this->data = ['' => $this->options['placeholder']] + $this->data;
        }
        if ($this->type === self::TYPE_SELECT2){
            if(!empty($this->options['placeholder']) || !empty($this->clientOptions['options']['placeholder'])) {
                $this->pluginOptions['placeholder'] = '';
            } elseif (!empty($this->pluginOptions['placeholder'])) {
                $this->options['placeholder'] = $this->pluginOptions['placeholder'];
                $this->pluginOptions['placeholder'] = '';
            }
        }
        $this->translateList = !empty($this->options['translate']) ? $this->options['translate'] : [];
        unset($this->options['translate']);
        
        $this->data = $this->enumItems($this->data);
        
        $this->_view = $this->getView();
        if ($this->type === self::TYPE_SELECT2) {

            if ($this->hasModel()) {
                echo Select2::widget([
                        'model' => $this->model,
                        'attribute' => $this->attribute,
                        'data' => $this->data,
                        'options' => $this->options
                    ] + $this->clientOptions);
            } else {
                echo Select2::widget([
                        'name' => $this->name,
                        'value' => $this->value,
                        'data' => $this->data,
                        'options' => $this->options
                    ] + $this->clientOptions);
            }

            $id = '$("#' . $this->options['id'] . '")';
            $text = ArrayHelper::getValue($this->pluginOptions, 'loadingText', 'Loading ...');
            $this->_view->registerJs("{$id}.on('depdrop:beforeChange',function(e,i,v){{$id}.select2('data',{text: '{$text}'});});");
            $this->_view->registerJs("{$id}.on('depdrop:change',function(e,i,v,c){{$id}.select2('val',{$id}.val());});");
            
        } elseif ($this->type === self::TYPE_RADIOBUTTON) {
            
            if ($this->hasModel()) {
                echo ButtonGroupInput::widget([
                        'model' => $this->model,
                        'attribute' => $this->attribute,
                        'data' => $this->data,
                        'options' => $this->options,
                    ] + $this->clientOptions);
            } else {
                echo ButtonGroupInput::widget([
                        'name' => $this->name,
                        'value' => $this->value,
                        'options' => $this->options,
                    ] + $this->clientOptions);
            }
            
        } elseif ($this->type === self::TYPE_CHECKBOX) {

            $containerOptions = [];
            Html::addCssClass($containerOptions, "checkbox");
            Html::addCssStyle($containerOptions, "display: inline; padding-right: 10px;");
            
            $this->options += [
                'itemOptions' => [
                    'container' => $containerOptions,
                ],
            ];
            echo $this->getInput('checkboxList', true);
            
        } elseif ($this->type === self::TYPE_RADIO) {

            $containerOptions = [];
            Html::addCssClass($containerOptions, "radio");
            Html::addCssStyle($containerOptions, "display: inline; padding-right: 10px;");
            
            $this->options += [
                'itemOptions' => [
                    'container' => $containerOptions,
                ],
            ];
            echo $this->getInput('radioList', true);
            
        } else {
            echo $this->getInput('dropdownList', true);
        }        
    }

    private function enumItems($data) {
        $keyIsValue = empty($data);
        if(empty($data)){
            preg_match('/\((.*)\)/', $this->model->tableSchema->columns[$this->attribute]->dbType, $matches);
            $data = explode(',', $matches[1]);
        }
        $values = [];
        foreach ($data as $key => $value) {
            $value = str_replace("'", null, $value);
            $enumKey = $keyIsValue ? $value : $key;
            if(!empty($this->without) && in_array($enumKey, $this->without)){
                continue;
            }
            if(!empty($this->translateList)){
                if(isset($this->options['onlyTransleted']) && $this->options['onlyTransleted']){
                    if(isset($this->translateList[$value])){
                        $values[$enumKey] = translateList[$value];
                    }
                }else{
                    $values[$enumKey] = isset($this->translateList[$value]) ? $this->translateList[$value] : $value;
                }
            }else{
                $values[$enumKey] = $value;
            }
        }

        return $values;
    }

}
