<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\gii\crud;

use yii\db\Schema;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;

class Generator extends \yii\gii\generators\crud\Generator {
    
    public $isCommentRow = false;
    
    public $hasLanguageIdField = false;

    public function getName() {
        return 'FSM-CRUD Generator';
    }

    public function getDescription() {
        return 'My crud generator. The same as a native, but with small change of parent functions';
    }

    public function generate() {
        $columns = $this->getColumnNames();
        $this->hasLanguageIdField = !empty($columns) && (array_search('language_id', $columns) !== false);
        return parent::generate();
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

    /**
     * Generates code for active field
     * @param string $attribute
     * @return string
     */
    public function generateActiveField($attribute) {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false || !isset($tableSchema->columns[$attribute])) {
            if (preg_match('/^(password|pass|passwd|passcode)$/i', $attribute)) {
                return "\$form->field(\$model, '$attribute')->passwordInput()";
            } else {
                return "\$form->field(\$model, '$attribute')";
            }
        }
        $column = $tableSchema->columns[$attribute];
        if (($column->phpType === 'boolean') || ($column->dbType === 'tinyint(1)')) {
            return  "\$form->field(\$model, '$attribute')->widget(SwitchInput::classname(), [\n" .
                    "           'pluginOptions' => [\n" .
                    "               'onText' => " . $this->generateAppString('Yes') . ",\n" .
                    "               'offText' => " . $this->generateAppString('No') . ",\n" .
                    "           ],\n" .
                    "       ])";
        } elseif ($column->type === 'text') {
            return "\$form->field(\$model, '$attribute')->textarea(['rows' => 6])";
        } else {
            if($column->name == 'language_id') {
                return  "\$form->field(\$model, 'language_id')->widget(Select2::classname(), [\n" .
                        "            'data' => \$languageList,\n" . 
                        "            'options' => [\n" .
                        "                'placeholder' => '...',\n" .
                        "            ],\n" .
                        "        ]);";
            } elseif (preg_match('/^(password|pass|passwd|passcode)$/i', $column->name)) {
                $input = 'passwordInput';
            } else {
                $input = 'textInput';
            }
            if (is_array($column->enumValues) && count($column->enumValues) > 0) {
                $dropDownOptions = [];
                foreach ($column->enumValues as $enumValue) {
                    $dropDownOptions[$enumValue] = Inflector::humanize($enumValue);
                }
                return "\$form->field(\$model, '$attribute')->dropDownList("
                    . preg_replace("/\n\s*/", ' ', VarDumper::export($dropDownOptions)).", ['prompt' => ''])";
            } elseif ($column->phpType !== 'string' || $column->size === null) {
                return "\$form->field(\$model, '$attribute')->$input()";
            } else {
                return "\$form->field(\$model, '$attribute')->$input(['maxlength' => true])";
            }
        }
    }

    /**
     * Generates code for active search field
     * @param string $attribute
     * @return string
     */
    public function generateActiveSearchField($attribute) {
        $comment = $this->isCommentRow ? '// ' : '';
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')";
        }
        $column = $tableSchema->columns[$attribute];
        if (($column->phpType === 'boolean') || ($column->dbType === 'tinyint(1)')) {
            return  "\$form->field(\$model, '$attribute')->widget(SwitchInput::classname(), [\n" .
                    $comment."            'pluginOptions' => [\n" .
                    $comment."                'onText' => " . $this->generateAppString('Yes') . ",\n" .
                    $comment."                'offText' => " . $this->generateAppString('No') . ",\n" .
                    $comment."            ],\n" .
                    $comment."        ])";
        } elseif($column->name == 'language_id') {
            return  "\$form->field(\$model, 'language_id')->widget(Select2::classname(), [\n" .
                    $comment."            'data' => \$languageList,\n" . 
                    $comment."            'options' => [\n" .
                    $comment."                'placeholder' => '...',\n" .
                    $comment."            ],\n" .
                    $comment."        ]);";
        } else {
            return "\$form->field(\$model, '$attribute')";
        }
    }

    /**
     * Generates search conditions
     * @return array
     */
    public function generateSearchConditions() {
        $columns = [];
        if (($table = $this->getTableSchema()) === false) {
            $class = $this->modelClass;
            /* @var $model \yii\base\Model */
            $model = new $class();
            foreach ($model->attributes() as $attribute) {
                $columns[$attribute] = 'unknown';
            }
        } else {
            foreach ($table->columns as $column) {
                $columns[$column->name] = $column->type;
            }
        }

        $likeConditions = [];
        $hashConditions = [];
        foreach ($columns as $column => $type) {
            switch ($type) {
                case Schema::TYPE_SMALLINT:
                case Schema::TYPE_INTEGER:
                case Schema::TYPE_BIGINT:
                case Schema::TYPE_BOOLEAN:
                case Schema::TYPE_FLOAT:
                case Schema::TYPE_DECIMAL:
                case Schema::TYPE_MONEY:
                case Schema::TYPE_DATE:
                case Schema::TYPE_TIME:
                case Schema::TYPE_DATETIME:
                case Schema::TYPE_TIMESTAMP:
                    $hashConditions[] = "\$baseTableName.'.{$column}' => \$this->{$column},";
                    break;
                default:
                    $likeConditions[] = "->andFilterWhere(['like', \$baseTableName.'.{$column}', \$this->{$column}])";
                    break;
            }
        }

        $conditions = [];
        if (!empty($hashConditions)) {
            $conditions[] = "\$query->andFilterWhere([\n"
                    . str_repeat(' ', 12) . implode(PHP_EOL . str_repeat(' ', 12), $hashConditions)
                    . PHP_EOL . str_repeat(' ', 8) . "]);\n";
        }
        if (!empty($likeConditions)) {
            $conditions[] = "\$query" . implode(PHP_EOL . str_repeat(' ', 12), $likeConditions) . ";\n";
        }

        return $conditions;
    }

}