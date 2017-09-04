<?php

namespace common\models\bill\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\bill\BillConfirm;

/**
 * BillConfirmSearch represents the model behind the search form of `common\models\bill\BillConfirm`.
 */
class BillConfirmSearch extends BillConfirm
{
    public $bill_number;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'payment_confirm_id', 'history_bill_id', 'bill_payment_id', 
                'bill_id', 'second_client_id'], 'integer'],
            [['first_client_account', 'second_client_name', 'second_client_reg_number', 
                'second_client_account', 'doc_date', 'doc_number', 'bank_ref', 'direction', 
                'currency', 'comment', 'bill_number'], 'safe'],
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
    
        $query = BillConfirm::find();
        
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
                'pageSize' => empty($params['payment_confirm_id']) ? 20 : 0,
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
            $baseTableName.'.payment_confirm_id' => $this->payment_confirm_id,
            $baseTableName.'.history_bill_id' => $this->history_bill_id,
            $baseTableName.'.bill_payment_id' => $this->bill_payment_id,
            $baseTableName.'.bill_id' => $this->bill_id,
            $baseTableName.'.second_client_id' => $this->second_client_id,
            $baseTableName.'.bank_ref' => $this->bank_ref,
            $baseTableName.'.doc_date' => $this->doc_date,
            $baseTableName.'.summa' => $this->summa,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.first_client_account', $this->first_client_account])
            ->andFilterWhere(['like', $baseTableName.'.second_client_name', $this->second_client_name])
            ->andFilterWhere(['like', $baseTableName.'.second_client_reg_number', $this->second_client_reg_number])
            ->andFilterWhere(['like', $baseTableName.'.second_client_account', $this->second_client_account])
            ->andFilterWhere(['like', $baseTableName.'.doc_number', $this->doc_number])
            ->andFilterWhere(['like', $baseTableName.'.direction', $this->direction])
            ->andFilterWhere(['like', $baseTableName.'.currency', $this->currency])
            ->andFilterWhere(['like', $baseTableName.'.comment', $this->comment])
        
            ->andFilterWhere(['like', 'bill.doc_number', $this->bill_number]);

        return $dataProvider;
    }
}