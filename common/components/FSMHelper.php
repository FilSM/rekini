<?php

namespace common\components;

use Yii;
use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use kartik\helpers\Html;

class FSMHelper extends Object {
    
    static public function aButton($id, $options = []) {
        $options = ArrayHelper::merge(
            [
                'label' => '',
                'title' => '',
                'icon' => '',
                'controller' => '',
                'action' => '',
                'class' => 'success',
                'size' => '',
                'message' => '',
                'options' => [
                    'data-pjax' => 0,
                ],
            ], 
            $options
        );
        /*
        if(empty($options['message'])){
            $options['message'] = Yii::t('common', 'Are you sure you want to do this operation?');
        }
         * 
         */
        
        $url = !empty($options['action']) && ($options['action'] != '#') ?
            [(!empty($options['controller']) ? '/'.$options['controller'].'/' : '').$options['action'], 'id' => $id] :
            '#';
        $class = empty($options['dropdown']) ? 
            'btn btn-'.$options['class'].(!empty($options['size']) ? ' '.$options['size'] : '') :
            (!empty($options['class']) ? $options['class'] : '');
        $btnOptions =
            [
                'title' => (empty($options['label']) && !empty($options['title']) ? $options['title'] : null),
                'class' => $class,
                'data-method' => !empty($options['message']) ? 'post' : null,
                'data-confirm' => !empty($options['message']) ? $options['message'] : null,
            ];
        $btnOptions = empty($options['options']) ? $btnOptions :  ArrayHelper::merge($btnOptions, $options['options']);
        
        return Html::a(Html::icon($options['icon']).(!empty($options['label']) ? ' '.$options['label'] : ''), 
            $url, 
            $btnOptions
        );
    }
    
    static public function vButton($id, $options = []) {
        $options = ArrayHelper::merge(
            [
                'label' => '',
                'title' => '',
                'icon' => '',
                'controller' => '',
                'action' => '',
                'class' => 'success',
                'modal' => false,
                'size' => '',
                'message' => '',
                'options' => [
                    'data-pjax' => 0,
                ],
            ], 
            $options
        );
        
        $url = !empty($options['action']) && ($options['action'] != '#') ?
            [(!empty($options['controller']) ? '/'.$options['controller'].'/' : '').$options['action'], 'id' => $id] :
            '#';
        $class = empty($options['dropdown']) ? 
            'btn btn-'.$options['class'].(!empty($options['size']) ? ' '.$options['size'] : '') :
            (!empty($options['class']) ? $options['class'] : '');
        $btnOptions =
            [
                'title' => $options['title'],
                'class' => $class.(!empty($options['modal']) ? ' show-modal-button' : ''),
                'data-method' => !empty($options['message']) ? 'post' : null,
                'data-confirm' => !empty($options['message']) && empty($options['modal']) ? $options['message'] : null,
                'value' => !empty($options['modal']) ? Url::to($url) : null,
            ];
        $btnOptions = empty($options['options']) ? $btnOptions :  ArrayHelper::merge($btnOptions, $options['options']);
        
        return Html::button(Html::icon($options['icon']).(!empty($options['label']) ? ' '.$options['label'] : ''), 
            $btnOptions
        );
    }
    
    static public function aDropdown($id, $options = []) {
        $options['dropdown'] = true;
        return '<li>' . FSMHelper::aButton($id, $options) . '</li>' . PHP_EOL;
    }
    
    static public function vDropdown($id, $options = []) {
        $options = ArrayHelper::merge(
            [
                'label' => '',
                'title' => '',
                'icon' => '',
                'controller' => '',
                'action' => '',
                'class' => '',
                'message' => '',
                'options' => [
                    'data-pjax' => 0,
                ],
            ], 
            $options
        );
        /*
        if(empty($options['message'])){
            $options['message'] = Yii::t('common', 'Are you sure you want to do this operation?');
        }
         * 
         */
        
        $url = [(!empty($options['controller']) ? '/'.$options['controller'].'/' : '').$options['action'], 'id' => $id];
        $class = (!empty($options['class']) ? $options['class'] : '');
        $btnOptions =
            [
                'title' => $options['title'],
                'class' => $class.(!empty($options['modal']) ? ' show-modal-button' : ''),
                'data-method' => !empty($options['message']) ? 'post' : null,
                'data-confirm' => !empty($options['message']) && empty($options['modal']) ? $options['message'] : null,
                'value' => !empty($options['modal']) ? Url::to($url) : null,
            ];
        $btnOptions = empty($options['options']) ? $btnOptions :  ArrayHelper::merge($btnOptions, $options['options']);
        
        return '<li>' . 
                Html::a(Html::icon($options['icon']).(!empty($options['label']) ? ' '.$options['label'] : ''), 
            '#', 
            $btnOptions
        ) . '</li>' . PHP_EOL;
    }
    
    static function arrayRemove($arr, $item)
    {
        ArrayHelper::remove($arr, $item); 
        return $arr;
    }
    
}
