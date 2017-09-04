<?php

namespace common\models\bill\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\bill\BillPayment;

/**
 * BillPaymentSearch represents the model behind the search form of `common\models\bill\BillPayment`.
 */
class BillPaymentSearch extends BillPayment
{
    public $bill_number;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'history_bill_id', 'payment_order_id', 'bill_id'], 'integer'],
            [['bill_number'], 'safe'],
            [['summa'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $baseTableName = $this->tableName();
        $this->clearDefaultValues();
    
        $query = BillPayment::find();

        $query->select = [
            $baseTableName . '.*',
            'bill_number' => 'bill.doc_number',
        ];
        
        $query->leftJoin(['bill' => 'bill'], 'bill.id = '.$baseTableName.'.bill_id');

        if(!isset($params['sort'])){
            $query->addOrderBy($baseTableName.'.id desc');
        }
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => empty($params['payment_order_id']) ? 20 : 0,
            ],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        if($this->hasAttribute('version')){
            $this->__unset('version');
        }

        // grid filtering conditions
        $query->andFilterWhere([
            $baseTableName.'.id' => $this->id,
            $baseTableName.'.history_bill_id' => $this->history_bill_id,
            $baseTableName.'.payment_order_id' => $this->payment_order_id,
            $baseTableName.'.bill_id' => $this->bill_id,
            $baseTableName.'.summa' => $this->summa,
        ]);
        
        $query->andFilterWhere(['like', 'bill.doc_number', $this->bill_number]);

        return $dataProvider;
    }
}