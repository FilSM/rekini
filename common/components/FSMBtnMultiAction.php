<?php

namespace common\components;

use Yii;
use yii\base\Object;
//use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;

use kartik\helpers\Html;
use kartik\grid\ActionColumnAsset;

class FSMBtnMultiAction extends Object {
    
    static public function aButton($label, $url, $attributes, $options) {
        //return '';
        $model = isset($attributes['model']) ? $attributes['model'] : null;
        $grid = isset($attributes['grid']) ? $attributes['grid'] : null;
        $confirm = isset($attributes['confirm']) ? $attributes['confirm'] : null;
        $dialogSettings = isset($attributes['DialogSettings']) ? $attributes['DialogSettings'] : null;
        
        $defaults = ['title' => Yii::t('common', 'Action')];
        $css = $grid ? $grid . '-btn-multi-select' : 'btn-multi-select';
        $options = array_replace_recursive($defaults, $options);
        Html::addCssClass($options, $css);
        $view = Yii::$app->getView();
        $actionOpts = Json::encode([
            'grid' => $grid,
            'css' => $css,
            'lib' => ArrayHelper::getValue($dialogSettings, 'libName', 'krajeeDialog'),
            'msg' => !empty($confirm) ? $confirm : Yii::t('common', 'Are you sure to do this action?'),
            'aButton' => true,
        ]);
        ActionColumnAsset::register($view);
        $js = "fsmMultiAction({$actionOpts});";
        $view->registerJs($js);
        return Html::a(
            (!empty($attributes['icon']) ? Html::icon($attributes['icon']).'&nbsp;':'').
                $label."<span class='img-loader' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>", 
            $url, 
            $options
        );
    }

    static public function vButton($label, $url, $attributes, $options) {
        $model = isset($attributes['model']) ? $attributes['model'] : null;
        $grid = isset($attributes['grid']) ? $attributes['grid'] : null;
        $confirm = isset($attributes['confirm']) ? $attributes['confirm'] : null;
        $dialogSettings = isset($attributes['DialogSettings']) ? $attributes['DialogSettings'] : null;
        
        $css = $grid ? $grid . '-btn-multi-select' : 'btn-multi-select';
        Html::addCssClass($options, $css);
        $view = Yii::$app->getView();
        $actionOpts = Json::encode([
            'grid' => $grid,
            'css' => $css,
            'lib' => ArrayHelper::getValue($dialogSettings, 'libName', 'krajeeDialog'),
            'msg' => !empty($confirm) ? $confirm : null,
            'vButton' => true,
        ]);
        ActionColumnAsset::register($view);
        $js = "fsmMultiAction({$actionOpts});";
        $view->registerJs($js);
        
        $btnOptions =
            [
                'value' => !empty($model) ? Url::to($url) : null,
                'title' => (!empty($options['title']) ? $options['title'] : $label),
            ];
        $btnOptions = empty($options) ? $btnOptions : ArrayHelper::merge($btnOptions, $options);  
        Html::addCssClass($btnOptions, "btn-multi-select");
        return Html::button((!empty($attributes['icon']) ? Html::icon($attributes['icon']) . '&nbsp;' : '') . $label, 
            $btnOptions
        );
    }

}
