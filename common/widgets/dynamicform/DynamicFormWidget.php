<?php

namespace common\widgets\dynamicform;

/*
use Yii;
use yii\helpers\Json;
use yii\helpers\Html;
use yii\base\InvalidConfigException;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelector;
 * 
 */

class DynamicFormWidget extends \wbraganca\dynamicform\DynamicFormWidget
{
    //private $_hashVar; 
    
    /**
     * Registers the needed assets.
     *
     * @param View $view The View object
     */
    public function registerAssets($view)
    {
        parent::registerAssets($view);
        DynamicFormAsset::register($view);
    }

}
