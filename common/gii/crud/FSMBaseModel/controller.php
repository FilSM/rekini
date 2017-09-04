<?php
/**
 * This is the template for generating a CRUD controller class file.
 */

use yii\db\ActiveRecordInterface;
use yii\helpers\StringHelper;

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\crud\Generator */

$parentControllerArr = ['FilSMController', 'AdminListController'];
$baseControllerClass = StringHelper::basename($generator->baseControllerClass);
$controllerClass = StringHelper::basename($generator->controllerClass);
$modelClass = StringHelper::basename($generator->modelClass);
$searchModelClass = StringHelper::basename($generator->searchModelClass);
if ($modelClass === $searchModelClass) {
    $searchModelAlias = $searchModelClass . 'Search';
}

/* @var $class ActiveRecordInterface */
$class = $generator->modelClass;
$pks = $class::primaryKey();
$urlParams = $generator->generateUrlParams();
$actionParams = $generator->generateActionParams();
$actionParamComments = $generator->generateActionParamComments();

echo "<?php\n";
?>

namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

<?php if(!in_array($baseControllerClass, $parentControllerArr)): ?>
use Yii;
use yii\base\Exception;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

use <?= ltrim($generator->modelClass, '\\') ?>;
<?php if (!empty($generator->searchModelClass)): ?>
use <?= ltrim($generator->searchModelClass, '\\') . (isset($searchModelAlias) ? " as $searchModelAlias" : "") ?>;
<?php else: ?>
use yii\data\ActiveDataProvider;
<?php endif; ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
<?php else: ?>
use <?= ltrim($generator->baseControllerClass, '\\') ?>;
<?php endif; ?>

/**
 * <?= $controllerClass ?> implements the CRUD actions for <?= $modelClass ?> model.
 */
class <?= $controllerClass ?> extends <?= $baseControllerClass; ?><?= PHP_EOL ?>
{
<?php if(in_array($baseControllerClass, $parentControllerArr)): ?>

    /**
     * Initializes the controller.
     */
    public function init()
    {
        parent::init();
        $this->defaultModel = '<?= ltrim($generator->modelClass, '\\') ?>';
        $this->defaultSearchModel = '<?= ltrim($generator->searchModelClass, '\\') ?>';
    }

}<?php return false; ?><?php endif; ?>

<?php if(!in_array($baseControllerClass, $parentControllerArr)): ?>
    public function behaviors() 
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($action) 
    {
        if($action->id == 'delete'){
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
<?php endif; ?>

<?php if(!in_array($baseControllerClass, $parentControllerArr)): ?>
    /**
     * Lists all <?= $modelClass ?> models.
     * @return mixed
     */
    public function actionIndex() 
    {
<?php if (!empty($generator->searchModelClass)): ?>
        $searchModel = new <?= isset($searchModelAlias) ? $searchModelAlias : $searchModelClass ?>;
        $params = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($params);
<?php if($generator->hasLanguageIdField) : ?>
        $dataFilterLanguage = \common\models\Language::getEnabledLanguageList();
<?php endif; ?>
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
<?php if($generator->hasLanguageIdField) : ?>
            'dataFilterLanguage' => $dataFilterLanguage,
<?php endif; ?>
        ]);
<?php else: ?>
        $dataProvider = new ActiveDataProvider([
            'query' => <?= $modelClass ?>::find(),
        ]);

<?php if($generator->hasLanguageIdField) : ?>
        $dataFilterLanguage = \common\models\Language::getEnabledLanguageList();
<?php endif; ?>
        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
<?php if($generator->hasLanguageIdField) : ?>
            'dataFilterLanguage' => $dataFilterLanguage,
<?php endif; ?>
        ]);
<?php endif; ?>
    }
<?php endif; ?>

<?php if(!in_array($baseControllerClass, $parentControllerArr)): ?>
    /**
     * Displays a single <?= $modelClass ?> model.
     * <?= implode("\n     * ", $actionParamComments) . PHP_EOL ?>
     * @return mixed
     */
    public function actionView(<?= $actionParams ?>) 
    {
        return $this->render('view', [
            'model' => $this->findModel(<?= $actionParams ?>),
        ]);
    }
<?php endif; ?>

<?php if(!in_array($baseControllerClass, $parentControllerArr)): ?>
    /**
     * Creates a new <?= $modelClass ?> model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() 
    {
        $model = new <?= $modelClass ?>();
<?php if($generator->hasLanguageIdField) : ?>
        $languageList = \common\models\Language::getEnabledLanguageList();
<?php endif; ?>

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', <?= $urlParams ?>]);
        } else {
            return $this->render('create', [
                'model' => $model,
<?php if($generator->hasLanguageIdField) : ?>
                'languageList' => $languageList,
<?php endif; ?>
            ]);
        }
    }
<?php endif; ?>

<?php if(!in_array($baseControllerClass, $parentControllerArr)): ?>
    /**
     * Updates an existing <?= $modelClass ?> model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * <?= implode("\n     * ", $actionParamComments) . PHP_EOL ?>
     * @return mixed
     */
    public function actionUpdate(<?= $actionParams ?>) 
    {
        $model = $this->findModel(<?= $actionParams ?>);
<?php if($generator->hasLanguageIdField) : ?>
        $languageList = \common\models\Language::getEnabledLanguageList();
<?php endif; ?>

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', <?= $urlParams ?>]);
        } else {
            return $this->render('update', [
                'model' => $model,
<?php if($generator->hasLanguageIdField) : ?>
                'languageList' => $languageList,
<?php endif; ?>
            ]);
        }
    }
<?php endif; ?>

<?php if(!in_array($baseControllerClass, $parentControllerArr)): ?>
    /**
     * Deletes an existing <?= $modelClass ?> model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * <?= implode("\n     * ", $actionParamComments) . PHP_EOL ?>
     * @return mixed
     */
    public function actionDelete(<?= $actionParams ?>) 
    {
        $this->findModel(<?= $actionParams ?>)->delete();

        return $this->redirect(['index']);
    }
<?php endif; ?>

<?php if(!in_array($baseControllerClass, $parentControllerArr)): ?>
    /**
     * Finds the <?= $modelClass ?> model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * <?= implode("\n     * ", $actionParamComments) . PHP_EOL ?>
     * @return <?=                   $modelClass ?> the loaded model
     * @throws Exception if the model cannot be found
     */
    protected function findModel(<?= $actionParams ?>) 
    {
<?php
if (count($pks) === 1) {
    $condition = '$id';
} else {
    $condition = [];
    foreach ($pks as $pk) {
        $condition[] = "'$pk' => \$$pk";
    }
    $condition = '[' . implode(', ', $condition) . ']';
}
?>
        if (($model = <?= $modelClass ?>::findOne(<?= $condition ?>)) !== null) {
            return $model;
        } else {
            throw new Exception('The requested page does not exist.');
        }
    }
<?php endif; ?>    
}