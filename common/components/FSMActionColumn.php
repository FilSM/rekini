<?php

namespace common\components;

use Yii;
//use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\bootstrap\ButtonGroup;
use yii\bootstrap\ButtonDropdown;

use kartik\grid\ActionColumnAsset;
use kartik\helpers\Html;

class FSMActionColumn extends \kartik\grid\ActionColumn {
    
    public $linkedObj = null;
    public $dropdownDefaultBtn = '';
    public $isDropdownActionColumn = false;

    public function init() {
        parent::init();
    }

    /**
     * Render default action buttons
     *
     * @return string
     */
    protected function initDefaultButtons() {
        if($this->isDropdownActionColumn){
            return '';
        }
        $linkedObj = isset($this->linkedObj) ? $this->linkedObj : null;
        if(is_array($linkedObj)){
            $arr = [];
            foreach ($linkedObj as $obj) {
                if(!empty($obj['fieldName']) && !empty($obj['id'])){
                    $arr[] = "{$obj['fieldName']}={$obj['id']}";
                }
            }
            $linkedObjParam = "&".  implode("&", $arr);
        }else{
            $linkedObjParam = (isset($linkedObj) ? "&{$linkedObj['fieldName']}={$linkedObj['id']}" : '');
        }
            
        if (!$this->_isDropdown) {
            if (!isset($this->buttons['view'])) {
                $this->buttons['view'] = function ($url, $model, $key) use ($linkedObjParam) {
                    $options = ArrayHelper::merge(['class' => 'btn btn-xs btn-info'], $this->viewOptions);
                    if(empty($options['noTarget'])){
                        $options['target'] = '_blank';
                    }
                    $title = Yii::t('kvgrid', 'View');
                    $icon = Html::icon('eye-open');
                    $label = ArrayHelper::remove($options, 'label', $icon);
                    $options = array_replace_recursive(['title' => $title, 'data-pjax' => '0'], $options);
                    return Html::a($label, $url.$linkedObjParam, $options);
                };
            }
            if (!isset($this->buttons['update'])) {
                $this->buttons['update'] = function ($url, $model, $key) use ($linkedObjParam) {
                    $options = ArrayHelper::merge(['class' => 'btn btn-xs btn-primary'], $this->updateOptions);
                    $title = Yii::t('kvgrid', 'Update');
                    $icon = Html::icon('pencil');
                    $label = ArrayHelper::remove($options, 'label', $icon);
                    $options = array_replace_recursive(['title' => $title, 'data-pjax' => '0'], $options);
                    return Html::a($label, $url.$linkedObjParam, $options);
                };
            }
            if (!isset($this->buttons['delete'])) {
                $this->buttons['delete'] = function ($url, $model, $key) use ($linkedObjParam) {
                    $options = ArrayHelper::merge(['class' => 'btn btn-xs btn-danger'], $this->deleteOptions);
                    $title = Yii::t('kvgrid', 'Delete');
                    $icon = Html::icon('trash');
                    $label = ArrayHelper::remove($options, 'label', $icon);
                    $defaults = ['title' => $title, 'data-pjax' => 'false'];
                    $pjax = $this->grid->pjax ? true : false;
                    $pjaxContainer = $pjax ? $this->grid->pjaxSettings['options']['id'] : '';
                    if ($pjax) {
                        $defaults['data-pjax-container'] = $pjaxContainer;
                    }
                    $options = array_replace_recursive($defaults, $options);
                    $css = $this->grid->options['id'] . '-action-del';
                    Html::addCssClass($options, $css);
                    $view = $this->grid->getView();
                    $delOpts = Json::encode([
                        'css' => $css,
                        'pjax' => $pjax,
                        'pjaxContainer' => $pjaxContainer,
                        'lib' => ArrayHelper::getValue($this->grid->krajeeDialogSettings, 'libName', 'krajeeDialog'),
                        'msg' => Yii::t('kvgrid', 'Are you sure to delete this '.$model->modelTitle().'?')
                    ]);
                    ActionColumnAsset::register($view);
                    $js = "fsmActionDialog({$delOpts});";
                    $view->registerJs($js);
                    $this->initPjax($js);
                    return Html::a($label, $url.$linkedObjParam, $options);
                };
            }
        }else{
            if (!isset($this->buttons['view'])) {
                $this->buttons['view'] = function (array $params) use ($linkedObjParam) {
                    extract($params);
                    $isDropdown = $this->_isDropdown && !$isBtn;
                    $options = $this->viewOptions;
                    if(!$isDropdown){
                        Html::addCssClass($options, 'btn btn-xs btn-info');
                    }
                    if(empty($options['noTarget'])){
                        $options['target'] = '_blank';
                    }
                    $title = Yii::t('kvgrid', 'View');
                    $icon = Html::icon('eye-open');
                    $label = ArrayHelper::remove($options, 'label', ($isDropdown ? $icon . ' ' . $title : $icon));
                    $options = array_replace_recursive(['title' => $title, 'data-pjax' => '0'], $options);
                    if ($isDropdown) {
                        $options['tabindex'] = '-1';
                        return '<li>' . Html::a($label, $url.$linkedObjParam, $options) . '</li>' . PHP_EOL;
                    } else {
                        return Html::a($label, $url.$linkedObjParam, $options);
                    }
                };
            }
            if (!isset($this->buttons['update'])) {
                $this->buttons['update'] = function (array $params) use ($linkedObjParam) {
                    extract($params);
                    $isDropdown = $this->_isDropdown && !$isBtn;
                    $options = $this->updateOptions;
                    if(!$isDropdown){
                        Html::addCssClass($options, 'btn btn-xs btn-primary');
                    }
                    $title = Yii::t('kvgrid', 'Update');
                    $icon = Html::icon('pencil');
                    $label = ArrayHelper::remove($options, 'label', ($isDropdown ? $icon . ' ' . $title : $icon));
                    $options = array_replace_recursive(['title' => $title, 'data-pjax' => '0'], $options);
                    if ($isDropdown) {
                        $options['tabindex'] = '-1';
                        return '<li>' . Html::a($label, $url.$linkedObjParam, $options) . '</li>' . PHP_EOL;
                    } else {
                        return Html::a($label, $url.$linkedObjParam, $options);
                    }
                };
            }
            if (!isset($this->buttons['delete'])) {
                $this->buttons['delete'] = function (array $params) use ($linkedObjParam) {
                    extract($params);
                    $isDropdown = $this->_isDropdown && !$isBtn;
                    $options = $this->deleteOptions;
                    if(!$isDropdown){
                        Html::addCssClass($options, 'btn btn-xs btn-danger');
                    }
                    $title = Yii::t('kvgrid', 'Delete');
                    $icon = Html::icon('trash');
                    $label = ArrayHelper::remove($options, 'label', ($isDropdown ? $icon . ' ' . $title : $icon));
                    $defaults = ['title' => $title, 'data-pjax' => 'false'];
                    $pjax = $this->grid->pjax ? true : false;
                    $pjaxContainer = $pjax ? $this->grid->pjaxSettings['options']['id'] : '';
                    if ($pjax) {
                        $defaults['data-pjax-container'] = $pjaxContainer;
                    }
                    $options = array_replace_recursive($defaults, $options);
                    $css = $this->grid->options['id'] . '-action-del';
                    Html::addCssClass($options, $css);
                    $view = $this->grid->getView();
                    $delOpts = Json::encode([
                        'css' => $css,
                        'pjax' => $pjax,
                        'pjaxContainer' => $pjaxContainer,
                        'lib' => ArrayHelper::getValue($this->grid->krajeeDialogSettings, 'libName', 'krajeeDialog'),
                        'msg' => Yii::t('kvgrid', 'Are you sure to delete this '.$model->modelTitle().'?')
                    ]);
                    ActionColumnAsset::register($view);
                    $js = "fsmActionDialog({$delOpts});";
                    $view->registerJs($js);
                    $this->initPjax($js);
                    if ($isDropdown) {
                        $options['tabindex'] = '-1';
                        return '<li>' . Html::a($label, $url.$linkedObjParam, $options) . '</li>' . PHP_EOL;
                    } else {
                        return Html::a($label, $url.$linkedObjParam, $options);
                    }
                };
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function renderDataCellContent($model, $key, $index)
    {
        if (!$this->_isDropdown) {
            return parent::renderDataCellContent($model, $key, $index);
        }else{
            $template = preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) {
                return $matches[1];
            }, $this->template);
            $btnArr = explode(' ', $template);
            
            $firstBtn = null;
            if($defaultBtn = (!empty($this->dropdownDefaultBtn) ? $this->dropdownDefaultBtn : null)){
                $url = $this->createUrl($defaultBtn, $model, $key, $index);
                $firstBtn = call_user_func($this->buttons[$defaultBtn], ['url' => $url, 'model' => $model, 'key' => $key, 'isBtn' => true]);
            }
            if($defaultBtn && (count($btnArr) > 0) && (($btnKey = array_search($defaultBtn, $btnArr)) !== false)){
                unset($btnArr[$btnKey]);
            }
            while (!$firstBtn && (count($btnArr) > 0)) {
                $defaultBtn = array_shift($btnArr);
                if(!empty($defaultBtn)){
                    $url = $this->createUrl($defaultBtn, $model, $key, $index);
                    $firstBtn = call_user_func($this->buttons[$defaultBtn], ['url' => $url, 'model' => $model, 'key' => $key, 'isBtn' => true]);
                }
            }

            $content = preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
                $name = $matches[1];

                if (isset($this->visibleButtons[$name])) {
                    $isVisible = $this->visibleButtons[$name] instanceof \Closure
                        ? call_user_func($this->visibleButtons[$name], ['url' => $url, 'model' => $model, 'key' => $key, 'isBtn' => false])
                        : $this->visibleButtons[$name];
                } else {
                    $isVisible = true;
                }

                if ($isVisible && isset($this->buttons[$name])) {
                    $url = $this->createUrl($name, $model, $key, $index);
                    return call_user_func($this->buttons[$name], ['url' => $url, 'model' => $model, 'key' => $key, 'isBtn' => false]);
                } else {
                    return '';
                }
            }, $this->template);
            
            if (!empty(trim($content))) {
                $options = $this->dropdownButton;
                $label = ArrayHelper::remove($options, 'label', Yii::t('kvgrid', 'Actions'));
                $caret = ArrayHelper::remove($options, 'caret', ' <span class="caret"></span>');
                $options = array_replace_recursive($options, ['type' => 'button', 'data-toggle' => 'dropdown']);
                Html::addCssClass($options, 'dropdown-toggle');
                $button = Html::button($label . $caret, $options);
                Html::addCssClass($this->dropdownMenu, 'dropdown-menu');
                $countBtn = substr_count($content, '</button>');
                $countLink = substr_count($content, '</a>');
                if(($countBtn > 1) || ($countLink > 1)){
                    $dropdown = $button . PHP_EOL . Html::tag('ul', $content, $this->dropdownMenu);
                }else{
                    $dropdown = '';
                }
                Html::addCssClass($this->dropdownOptions, 'dropdown');
                
                $buttonGroup = ButtonGroup::widget([
                    'options'=>['class'=>'btn-group-sm'],
                    'buttons' => [
                        $firstBtn,
                        $dropdown,
                    ],
                ]);
                return Html::tag('div', $buttonGroup, $this->dropdownOptions);
            }
            return $content;
        }
    }
}
