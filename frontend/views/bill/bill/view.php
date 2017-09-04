<?php

use yii\widgets\Pjax;

use kartik\helpers\Html;
use kartik\detail\DetailView;

use common\components\FSMHelper;
use common\models\bill\Bill;

/* @var $this yii\web\View */
/* @var $model common\models\bill\Bill */

//if (YII_ENV_DEV && isset(Yii::$app->params['brandLabel'])){
    //$this->title = Yii::$app->params['brandLabel'];
//}else{
    $this->title = $model->billDocTypeList[$model->doc_type] .' #'. $model->doc_number;
//}
$this->params['breadcrumbs'][] = ['label' => $model->modelTitle(2), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bill-view">
    
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= Html::pageHeader(Html::encode($this->title)); ?>

    <div style="padding-right: 15px; padding-left: 15px;">                     
        <div class='col-xs-6' style="padding: 0;">
            <?= Html::a(Yii::t('common', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
            <?= \common\components\FSMBtnDialog::button(Yii::t('common', 'Delete'), ['delete', 'id' => $model->id], [
                'id' => 'btn-dialog-selected',
                'class' => 'btn btn-danger',
            ]); ?>         
        </div>

        <div class='col-xs-offset-6' style="padding: 0; text-align: right;">
            <?= $model->getProgressButtons('', true);?>
            <?= $model->getOptionsButtons();?>
            <?php if(in_array($model->doc_type, [
                    Bill::BILL_DOC_TYPE_BILL,
                    Bill::BILL_DOC_TYPE_INVOICE,
                    Bill::BILL_DOC_TYPE_CRBILL,
                    Bill::BILL_DOC_TYPE_CESSION,
                ])) : 
                    echo FSMHelper::aButton($model->id, [
                    'label' => Yii::t('common', 'Print'),
                    'icon' => 'print',
                    //'controller' => 'bill',
                    'action' => 'view-pdf',
                    'options' => [
                        'target' => '_blank',
                    ],
                ]);
            endif;?>
        </div>
    </div>
    <p></p>

    <div class='col-md-12'>
        <?php 
            $attributes = [
                [
                    'attribute' => 'id',
                    'visible' => $isAdmin,
                ], 
                [
                    'attribute' => 'doc_type',
                    'value' => isset($model->doc_type) ? $model->billDocTypeList[$model->doc_type] : null,
                ], 
                [
                    'attribute' => 'doc_number',
                    'valueColOptions' => ['style' => 'font-weight: bold; font-size: 150%;'],
                    'format'=>'raw',
                ],                         
                [
                    'attribute' => 'doc_date',
                    'value' => isset($model->doc_date) && ($model->doc_date == date('Y-m-d')) ? Yii::t('common', 'Today') : $model->doc_date,
                    'valueColOptions' => ['style' => 'font-weight: bold; font-size: 150%;'],
                    'format'=>'raw',
                ],                         
                [
                    'attribute' => 'status',
                    'value' => isset($model->status) ? Html::badge($model->billStatusList[$model->status], ['class' => $model->statusBackgroundColor.' status-badge']) : null,
                    'format'=>'raw',
                ],                 
                [
                    'attribute' => 'loading_address',
                    'visible' => !empty($model->loading_address),
                ], 
                [
                    'attribute' => 'unloading_address',
                    'visible' => !empty($model->unloading_address),
                ], 
                [
                    'attribute' => 'carrier',
                    'visible' => !empty($model->carrier),
                ], 
                [
                    'attribute' => 'transport',
                    'visible' => !empty($model->transport),
                ], 
            ]
        ?>
        <?php
            $panelContent = [
                'heading' => Yii::t('common', 'Common data'),
                'preBody' => '<div class="list-group-item">',
                'body' => DetailView::widget([
                    'model' => $model,
                    'attributes' => $attributes,
                ]),
                'postBody' => '</div>',
            ];                        
            echo Html::panel(
                $panelContent, 
                'primary', 
                [
                    'id' => "common-data",
                ]
            );                        
        ?>   
    </div>
        
    <div class='col-md-12'>
        <div class='col-md-6' style="padding-left: 0;">
            <?php 
                $attributes = [
                    [
                        'label' => Yii::t('client', 'Full name'),
                        'value' => !empty($firstClientModel->id) ? 
                            (!empty($agreementModel->first_client_role_id) ? $model->firstClientRole->name.': ' : '') . 
                            Html::a($firstClientModel->name, ['/client/view', 'id' => $firstClientModel->id], ['target' => '_blank']) : null,
                        'format'=>'raw',
                        'valueColOptions' => ['style' => 'font-weight: bold; font-size: 150%;'],
                    ],
                    [
                        'label' => Yii::t('client', 'Reg.number'),
                        'value' => !empty($firstClientModel->id) ? $firstClientModel->reg_number : null,
                    ],
                    [
                        'label' => Yii::t('client', 'VAT number'),
                        'value' => !empty($firstClientModel->id) ? $firstClientModel->vat_number : null,
                    ],
                    [
                        'label' => Yii::t('client', 'Legal address'),
                        'value' => !empty($firstClientModel->id) ? $firstClientModel->legal_address : null,
                    ],
                    [
                        'label' => Yii::t('bill', 'Bank account'),
                        'attribute' => 'first_client_bank_id',
                        'value' => !empty($model->first_client_bank_id) ? 
                            $model->firstClientBank->bank->name . ' | ' . $model->firstClientBank->account . (!empty($model->firstClientBank->name) ? ' ( '.$model->firstClientBank->name . ' )' : '') :
                            null,
                    ],
                ]
            ?>
            <?php
                $panelContent = [
                    'heading' => Yii::t('bill', 'First party data'),
                    'preBody' => '<div class="list-group-item">',
                    'body' => DetailView::widget([
                        'model' => $model,
                        'attributes' => $attributes,
                    ]),
                    'postBody' => '</div>',
                ];                        
                echo Html::panel(
                    $panelContent, 
                    'success', 
                    [
                        'id' => "first-party-data",
                    ]
                );                        
            ?>   
        </div>
        
        <div class='col-md-6' style="padding-right: 0;">
            <?php 
                $attributes = [
                    [
                        'label' => Yii::t('client', 'Full name'),
                        'value' => !empty($secondClientModel->id) ? 
                            (!empty($agreementModel->second_client_role_id) ? $model->secondClientRole->name.': ' : '') . 
                            Html::a($secondClientModel->name, ['/client/view', 'id' => $secondClientModel->id], ['target' => '_blank']) : null,
                        'format'=>'raw',
                        'valueColOptions' => ['style' => 'font-weight: bold; font-size: 150%;'],
                    ],
                    [
                        'label' => Yii::t('client', 'Reg.number'),
                        'value' => !empty($secondClientModel->id) ? $secondClientModel->reg_number : null,
                    ],
                    [
                        'label' => Yii::t('client', 'VAT number'),
                        'value' => !empty($secondClientModel->id) ? $secondClientModel->vat_number : null,
                    ],
                    [
                        'label' => Yii::t('client', 'Legal address'),
                        'value' => !empty($secondClientModel->id) ? $secondClientModel->legal_address : null,
                    ],
                                    [
                        'attribute' => 'second_client_bank_id',
                        'label' => Yii::t('bill', 'Bank account'),
                        'value' => !empty($model->second_client_bank_id) ? 
                            $model->secondClientBank->bank->name . ' | ' . $model->secondClientBank->account . (!empty($model->secondClientBank->name) ? ' ( '.$model->secondClientBank->name . ' )' : '') :
                            null,
                    ],
                ]
            ?>
            <?php
                $panelContent = [
                    'heading' => Yii::t('bill', 'Second party data'),
                    'preBody' => '<div class="list-group-item">',
                    'body' => DetailView::widget([
                        'model' => $model,
                        'attributes' => $attributes,
                    ]),
                    'postBody' => '</div>',
                ];                        
                echo Html::panel(
                    $panelContent, 
                    'success', 
                    [
                        'id' => "second-party-data",
                    ]
                );                        
            ?>   
        </div>
    </div>
    
    <div class='col-md-12'>
        <div class='col-md-6' style="padding-left: 0;">
            <?php 
                $firstDate = new DateTime(date('Y-m-d'));
                $secondDate = new DateTime($model->pay_date);
                $dateDiff = date_diff($firstDate, $secondDate, true)->days;

                $firstDate = $firstDate->format('Y-m-d');
                $secondDate = $secondDate->format('Y-m-d');
                if($firstDate > $secondDate){
                    //$dateDiff = -1 * $dateDiff;
                    $class = 'badge-danger';
                    $dateDiffTxt = Yii::t('bill', 'delayed {count, plural, =1{one day} other{# days}}', ['count' => $dateDiff]);
                }elseif($dateDiff >= 0){
                    $class = 'badge-success';
                    $dateDiffTxt = Yii::t('bill', 'after {count, plural, =1{one day} other{# days}}', ['count' => $dateDiff]);
                }

                $payClass = $model->delayed ? 'badge-danger' : $model->payStatusBackgroundColor;

                $attributes = [
                    [
                        'attribute' => 'pay_date',
                        'value' => isset($model->pay_date) && ($model->pay_date == date('Y-m-d')) ? 
                            Html::badge(Yii::t('common', 'Today'), ['class' => $class]) : 
                            $model->pay_date .(!in_array($model->pay_status, [Bill::BILL_PAY_STATUS_FULL, Bill::BILL_PAY_STATUS_OVER]) ? '&nbsp;&nbsp;&nbsp;' . Html::badge($dateDiffTxt, ['class' => $class]) : ''),
                        'format' => 'raw',
                    ],     
                    [
                        'attribute' => 'services_period',
                        'visible' => !empty($model->services_period),
                    ],                         
                    [
                        'attribute' => 'pay_status',
                        'value' => 
                            isset($model->pay_status) ? Html::badge($model->billPayStatusList[$model->pay_status], ['class' => $payClass.' pay-status-badge']).
                            ($model->pay_status == Bill::BILL_PAY_STATUS_PART ? 
                                '<br/><span style="padding-top: 5px;">'.Yii::t('bill', 'Paid').': '.number_format($model->paymentsSumma, 2).'</span>'.
                                '<br/><span style="padding-top: 5px; font-weight: bold;">'.Yii::t('bill', 'Debt').': '.number_format($model->paymentsSummaLess, 2).'</span>' :
                                ($model->pay_status == Bill::BILL_PAY_STATUS_OVER ? 
                                        '<br/><span style="padding-top: 5px;">'.number_format($model->paymentsSummaGrate, 2) .'</span>': 
                                    '')
                            ): null,
                        'format' => 'raw',
                    ],     
                    //'delayed:boolean',
                    [
                        'attribute' => 'summa',
                    ],                         
                    [
                        'attribute' => 'vat',
                    ],     
                    [
                        'attribute' => 'total',
                        'value' => isset($model->valuta_id) ? $model->total . ' ' . $model->valuta->name : $model->total,
                    ],                         
                ]
            ?>
            <?php
                $panelContent = [
                    'heading' => Yii::t('bill', 'Payment data'),
                    'preBody' => '<div class="list-group-item">',
                    'body' => DetailView::widget([
                        'model' => $model,
                        'attributes' => $attributes,
                    ]),
                    'postBody' => '</div>',
                ];                        
                echo Html::panel(
                    $panelContent, 
                    'success', 
                    [
                        'id' => "payment-data",
                    ]
                );                        
            ?>   
        </div>
        <div class='col-md-6' style="padding-right: 0;">
            <?php 
                $attributes = [
                    [
                        'attribute' => 'abonent_id',
                        'value' => !empty($model->abonent_id) ? Html::a($model->abonent->name, ['/abonent/view', 'id' => $model->abonent_id], ['target' => '_blank']) : null,
                        'format' => 'raw',
                        'visible' => $isAdmin && !empty($model->abonent_id),
                    ],            
                    [
                        'attribute' => 'project_id',
                        'value' => !$model->project_id ? null : Html::a($model->project->name, ['project/view', 'id' => $model->project_id], ['target' => '_blank']),
                        'format'=>'raw',
                    ], 
                    [
                        'attribute' => 'agreement_id',
                        'value' => !$model->agreement_id ? null : Html::a($model->agreement->number, ['agreement/view', 'id' => $model->agreement_id], ['target' => '_blank']),
                        'format'=>'raw',
                    ], 
                    [
                        'attribute' => 'parent_id',
                        'value' => !empty($model->parent_id) ? Html::a($model->parent->doc_number, ['/bill/view', 'id' => $model->parent_id], ['target' => '_blank']) : null,
                        'format' => 'raw',
                        'visible' => !empty($model->parent_id),
                    ],            
                    [
                        'attribute' => 'child_id',
                        'value' => !empty($model->child_id) ? Html::a($model->child->doc_number, ['/bill/view', 'id' => $model->child_id], ['target' => '_blank']) : null,
                        'format' => 'raw',
                        'visible' => !empty($model->child_id),
                    ],        
                    /*
                    [
                        'attribute' => 'valuta_id',
                        'value' => isset($model->valuta_id) ? $model->valuta->name : null,
                    ],    
                     * 
                     */                     
                    [
                        'attribute' => 'manager_id',
                        'value' => isset($model->manager_id, $model->author) ? Html::a($model->author->name, ['/user/profile/show', 'id' => (isset($model->author->user) ? $model->author->user->id : null)], ['target' => '_blank']) : null,
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'language_id',
                        'value' => !empty($model->language_id) ? $model->language->name : null,
                    ],
                    'comment:ntext',
                    [
                        'attribute' => 'deleted',
                        'format' => 'boolean',
                        'visible' => $isAdmin,
                    ],
                ]
            ?>
            <?php
                $panelContent = [
                    'heading' => Yii::t('common', 'Other data'),
                    'preBody' => '<div class="list-group-item">',
                    'body' => DetailView::widget([
                        'model' => $model,
                        'attributes' => $attributes,
                    ]),
                    'postBody' => '</div>',
                ];                        
                echo Html::panel(
                    $panelContent, 
                    'success', 
                    [
                        'id' => "other-data",
                    ]
                );                        
            ?>   
        </div>
    </div>
    
    <div class='col-md-12'>
        <?= $this->render('_product_table_view', [
                'model' => $billProductModel,
                'invoiceModel' => $model,
                'bill' => $model,
            ]); 
        ?>
    </div>
        
    <div class='col-md-12'>
        <?= $this->render('_history_table_view', [
                'searchModel' => $historyModel,
                'dataProvider' => $historyDataProvider,
            ]); 
        ?>
    </div>
    
</div>