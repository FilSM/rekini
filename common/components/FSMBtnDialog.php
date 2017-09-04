<?php

namespace common\components;

use Yii;
use yii\base\Object;
//use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;

use kartik\grid\ActionColumnAsset;

class FSMBtnDialog extends Object {
    
    static public function button($label, $url, $options) {
        //return '';
        $model = isset($options['model']) ? $options['model'] : null;
        $grid = isset($options['grid']) ? $options['grid'] : null;
        $confirm = isset($options['confirm']) ? $options['confirm'] : null;
        $dialogSettings = isset($options['DialogSettings']) ? $options['DialogSettings'] : null;
        ArrayHelper::remove($options, 'model');
        ArrayHelper::remove($options, 'grid');
        ArrayHelper::remove($options, 'confirm');
        ArrayHelper::remove($options, 'DialogSettings');
        
        $defaults = ['title' => Yii::t('common', 'Delete'), 'data-pjax' => 'false'];
        $pjax = false;
        $pjaxContainer = '';
        $css = $grid ? $grid . '-btn-dialog-selected' : 'btn-dialog-selected';
        $options = array_replace_recursive($defaults, $options);
        Html::addCssClass($options, $css);
        $view = Yii::$app->getView();
        $delOpts = Json::encode([
            'grid' => $grid,
            'css' => $css,
            'pjax' => $pjax,
            'pjaxContainer' => $pjaxContainer,
            'lib' => ArrayHelper::getValue($dialogSettings, 'libName', 'krajeeDialog'),
            'msg' => !empty($confirm) ? $confirm : 
                (isset($model) ? 
                    Yii::t('common', 'Are you sure to delete this ').$model->modelTitle().'?' : 
                    Yii::t('common', 'Are you sure to delete this item?')
                ),
        ]);
        ActionColumnAsset::register($view);
        $js = "fsmActionDialog({$delOpts});";
        $view->registerJs($js);
        //$this->initPjax($js);
        return Html::a($label, $url, $options)."<span class='img-loader' style='display: none;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>";
    }

}
