<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();

echo "<?php\n";
?>

use kartik\helpers\Html;
use kartik\detail\DetailView;

/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = $model->modelTitle() .' #'. $model->id;
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-view">

    <?= "<?= " ?>Html::pageHeader(Html::encode($this->title)); ?>

    <p>
        <?= "<?= " ?>Html::a(<?= $generator->generateAppString('Update') ?>, ['update', <?= $urlParams ?>], ['class' => 'btn btn-primary']) ?>
        <?= "<?= " ?>\common\components\FSMBtnDialog::button(Yii::t('common', 'Delete'), ['delete', <?= $urlParams ?>], [
            'id' => 'btn-dialog-selected',
            'class' => 'btn btn-danger',
        ]); ?>            
    </p>

    <?= "<?= " ?>DetailView::widget([
        'model' => $model,
        'attributes' => [
<?php
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo "            '" . $name . "',\n";
    }
} else {
    foreach ($generator->getTableSchema()->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if(!in_array(strtoupper($column->name), array('ENABLED', 'DELETED', 'LANGUAGE_ID'))){
            echo "            '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }elseif(strtoupper($column->name) == 'LANGUAGE_ID'){
            echo "            [
                'attribute' => 'language_id',
                'label' => \$model->language->modelTitle(),
                'value' => \$model->language !== null ?  Html::encode(\$model->language->name) : null,
            ],\n";
        }elseif(in_array(strtoupper($column->name), array('ENABLED', 'DELETED'))){
            echo "            '", strtolower($column->name), ":boolean',\n";
        }
    }
}
?>
        ],
    ]) ?>

</div>