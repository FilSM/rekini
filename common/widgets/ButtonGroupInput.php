<?php

namespace common\widgets;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\bootstrap\Button;
use yii\bootstrap\BootstrapAsset;

class ButtonGroupInput extends \kartik\widgets\InputWidget {

    private $hiddenInputName = '';
    /**
     * @var array list of buttons. Each array element represents a single button
     * which can be specified as a string or an array of the following structure:
     *
     * - label: string, required, the button label.
     * - options: array, optional, the HTML attributes of the button.
     */
    public $buttons = [];

    /**
     * @var boolean whether to HTML-encode the button labels.
     */
    public $encodeLabels = true;

    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function init() {
        parent::init();
        Html::addCssClass($this->options, 'btn-group');
    }

    /**
     * Renders the widget.
     */
    public function run() {
        $hidden = '';
        if ($this->hasModel()) {
            if (!array_key_exists('unselect', $this->options)) {
                $this->options['unselect'] = '';
            }
            if (!array_key_exists('id', $this->options)) {
                $this->options['id'] = Html::getInputId($this->model, $this->attribute);
            }
            $this->value = Html::getAttributeValue($this->model, $this->attribute);
            $this->hiddenInputName = isset($this->options['name']) ? $this->options['name'] : Html::getInputName($this->model, $this->attribute);
            $hidden = Html::hiddenInput($this->hiddenInputName, $this->value, $this->options['unselect']);
        }

        if (empty($this->buttons)) {
            $this->buttons = [];
            foreach ($this->data as $item => $value) {
                $this->buttons[] = [
                    'label' => !empty($this->options['translate']) && isset($this->options['translate'][$value]) ? $this->options['translate'][$value] : $value,
                    'options' => [
                        'class' => 'btn-default'.($this->value == $item ? ' active' : ''),
                        'type' => 'button',
                        'value' => $item,
                    ],
                ];
            }
        }

        unset($this->options['unselect']);

        $buttons = $this->renderButtons();
        unset($this->options['translate']);
        
        echo $hidden . Html::tag('div', $buttons, $this->options);
        
        $this->registerClientScript();
        BootstrapAsset::register($this->getView());
    }

    /**
     * Generates the buttons that compound the group as specified on [[buttons]].
     * @return string the rendering result.
     */
    protected function renderButtons() {
        $buttons = [];
        foreach ($this->buttons as $button) {
            if (is_array($button)) {
                $label = ArrayHelper::getValue($button, 'label');
                $options = ArrayHelper::getValue($button, 'options');
                $buttons[] = Button::widget([
                            'label' => $label,
                            'options' => $options,
                            'encodeLabel' => $this->encodeLabels,
                            'view' => $this->getView()
                ]);
            } else {
                $buttons[] = $button;
            }
        }

        return implode(PHP_EOL, $buttons);
    }

    public function registerClientScript() {
        $js = '';
        $view = $this->getView();
        $id = $this->options['id'];
        $js .= 'jQuery("#' . $id . ' [type=button]").click(function(){'
                    . 'var value = jQuery(this).val(); '
                    . 'var isActive = jQuery(this).hasClass("active");'
                    . 'jQuery("#' . $id . ' [type=button]").removeClass("active"); '
                    . 'if(!isActive){'
                    . ' jQuery("form [name=\''.$this->hiddenInputName.'\']").val(value); '
                    . ' jQuery(this).addClass("active"); '
                    . '}'
                    . (!empty($this->options['canBeNull']) ? ' else {jQuery("form [name=\''.$this->hiddenInputName.'\']").val(null);}' : '')
                . '});';
        //$js .= 'jQuery("#' . $id . ' [type=button]").click(function(){var value = jQuery(this).val(); jQuery("form [name=\''.$this->hiddenInputName.'\']").val(value); alert(value);});';
        //EnumInputAsset::register($view);
        $view->registerJs($js);
    }

}
