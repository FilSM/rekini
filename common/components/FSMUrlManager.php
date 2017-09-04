<?php

namespace common\components;

use codemix\localeurls\UrlManager as BaseUrlManager;
use lajax\translatemanager\models\Language as TLanguage;

class FSMUrlManager extends BaseUrlManager {

    /**
     * @inheritdoc
     */
    public function init() {
        if(empty($this->languages)){
            $langArr = TLanguage::getLanguages(true, true);
            $this->languages = [];
            if(!empty($langArr)){
                foreach ($langArr as $lang) {
                    $this->languages[$lang['language']] = $lang['language_id'];
                }
            }else{
                $this->languages = ['en' => 'en-US', 'lv' => 'lv-LV', 'ru' => 'ru-RU'];
            }            
        }
        parent::init();
    }

}
