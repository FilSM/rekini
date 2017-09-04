<?php
namespace common\models;

use Yii;
//use yii\helpers\Html;
use yii\helpers\ArrayHelper;
//use yii\grid\GridView;

use kartik\helpers\Html;
use kartik\grid\GridView;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel common\models\bill\search\BillConfirmSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])) {
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $searchModel->modelTitle(2);
//}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bill-confirm-index">

    <?= Html::pageHeader(Html::encode($this->title)); ?>
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Html::icon('plus').'&nbsp;'.$searchModel->modelTitle(), ['create'], ['class' => 'btn btn-success']); ?>
    </p>
    <?= GridView::widget([
        'responsive' => false,
        //'striped' => false,
        'hover' => true,
        'floatHeader' => true,
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            //['class' => '\kartik\grid\SerialColumn'],
            [
                'attribute' => 'id',
                'width' => '75px',
                'hAlign' => 'center',
            ],
            'payment_confirm_id',
            'history_bill_id',
            'bill_payment_id',
            'bill_id',
            'first_client_account',
            'second_client_name',
            'second_client_reg_number',
            'second_client_account',
            // 'second_client_id',
            // 'doc_date',
            // 'doc_number',
            // 'direction',
            // 'summa',
            // 'currency',
            // 'comment:ntext',

            ['class' => '\kartik\grid\ActionColumn'],
        ],
    ]); ?>
</div>