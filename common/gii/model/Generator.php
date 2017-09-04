<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\gii\model;

class Generator extends \yii\gii\generators\model\Generator {

    public function getName() {
        return 'FSM-Model Generator';
    }

    public function getDescription() {
        return 'My model generator. The same as a native, but with small change of parent functions';
    }

    public function generateAppString($string = '', $placeholders = []) {
        $string = addslashes($string);
        if ($this->enableI18N) {
            // If there are placeholders, use them
            if (!empty($placeholders)) {
                $search = ['array (', ')'];
                $replace = ['[', ']'];
                $ph = ', ' . str_replace($search, $replace, var_export($placeholders, true));
            } else {
                $ph = '';
            }
            $str = "Yii::t('common', '" . $string . "'" . $ph . ")";
        } else {
            // No I18N, replace placeholders by real words, if any
            if (!empty($placeholders)) {
                $phKeys = array_map(function($word) {
                    return '{' . $word . '}';
                }, array_keys($placeholders));
                $phValues = array_values($placeholders);
                $str = "'" . str_replace($phKeys, $phValues, $string) . "'";
            } else {
                // No placeholders, just the given string
                $str = "'" . $string . "'";
            }
        }
        return $str;
    }

}
