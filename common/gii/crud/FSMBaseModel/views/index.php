<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>
namespace common\models;

use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use <?= $generator->indexWidgetType === 'grid' ? "yii\\grid\\GridView" : "yii\\widgets\\ListView" ?>;
<?= $generator->enablePjax ? 'use yii\widgets\Pjax;' : '' ?>

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
<?= !empty($generator->searchModelClass) ? "/* @var \$searchModel " . ltrim($generator->searchModelClass, '\\') . " */\n" : '' ?>
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $searchModel->modelTitle(2);
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">

    <?= "<?= " ?>Html::pageHeader(Html::encode($this->title)); ?>
    
<?php if(!empty($generator->searchModelClass)): ?>
<?= "    <?php " . ($generator->indexWidgetType === 'grid' ? "// " : "") ?>echo $this->render('_search', ['model' => $searchModel]); ?>
<?php endif; ?>

    <p>
        <?= "<?= " ?>Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>
<?= $generator->enablePjax ? "    <?php Pjax::begin(); ?>\n" : '' ?>
<?php if ($generator->indexWidgetType === 'grid'): ?>
    <?= "<?= " ?>GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
        'dataProvider' => $dataProvider,
        <?= !empty($generator->searchModelClass) ? "'filterModel' => \$searchModel,\n        'columns' => [\n" : "'columns' => [\n"; ?>
            //['class' => '\kartik\grid\SerialColumn'],
<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        echo "            ", (++$count < 10 ? "'" : "// '"), $name . "',\n";
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $isCommentRows = (++$count >= 10);
        $format = $generator->generateColumnFormat($column);
        if(!in_array(strtoupper($column->name), array('ID', 'ENABLED', 'DELETED', 'LANGUAGE_ID'))){
            echo "            ", ($isCommentRows ? "// '" : "'"), $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }elseif(strtoupper($column->name) == 'ID'){
            
            echo "            ", ($isCommentRows ? "/* " : ""), "[
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
            ],", ($isCommentRows ? " */" : ""), PHP_EOL;
        }elseif(in_array(strtoupper($column->name), array('ENABLED', 'DELETED'))){
            echo "            ", ($isCommentRows ? "/* " : ""), "[
                'attribute' => ", '"', strtolower($column->name), '"', ",                
                'class' => '\kartik\grid\BooleanColumn',
                'trueLabel' => 'Yes', 
                'falseLabel' => 'No',
                'width' => '100px',
            ],", ($isCommentRows ? " */" : ""), PHP_EOL;
        }elseif(strtoupper($column->name) == 'LANGUAGE_ID'){
            echo "            ", ($isCommentRows ? "/* " : ""), "[
                'attribute' => 'language_id',
                'value' => function (\$model) {
                    return \$model->language->name;
                },
                'filterType' => GridView::FILTER_SELECT2,
                'filter' => \$dataFilterLanguage,
                'filterWidgetOptions'=>['pluginOptions' => ['allowClear' => true],],
                'filterInputOptions' => ['placeholder' => Yii::t('common', 'Any...').' '.\common\models\Language::modelTitle()],
            ],", ($isCommentRows ? " */" : ""), PHP_EOL;
        }
    }
}
?>

            ['class' => '\kartik\grid\ActionColumn'],
        ],
    ]); ?>
<?php else: ?>
    <?= "<?= " ?>ListView::widget([
        'dataProvider' => $dataProvider,
        'itemOptions' => ['class' => 'item'],
        'itemView' => function ($model, $key, $index, $widget) {
            return Html::a(Html::encode($model-><?= $nameAttribute ?>), ['view', <?= $urlParams ?>]);
        },
    ]) ?>
<?php endif; ?>
<?= $generator->enablePjax ? "    <?php Pjax::end(); ?>\n" : '' ?>
</div>