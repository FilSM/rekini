<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

echo "<?php\n";
?>

use kartik\helpers\Html;


/* @var $this yii\web\View */
/* @var $model <?= ltrim($generator->modelClass, '\\') ?> */

$this->title = Yii::t($model->tableName(), 'Create a new '.$model->modelTitle(1, false));
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="<?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-create">

    <?= "<?= " ?>Html::pageHeader(Html::encode($this->title)); ?>
    

    <?= "<?= " ?>$this->render('_form', [
        'model' => $model,
<?php if($generator->hasLanguageIdField) : ?>
        'languageList' => $languageList,
<?php endif; ?>
    ]) ?>

</div>