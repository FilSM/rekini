<?php

namespace common\models\client\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\client\ClientBankBalance;

/**
 * ClientBankBalanceSearch represents the model behind the search form of `common\models\client\ClientBankBalance`.
 */
class ClientBankBalanceSearch extends ClientBankBalance
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'payment_confirm_id', 'account_id', 'uploaded_file_id'], 'integer'],
            [['start_date', 'end_date', 'currency'], 'safe'],
            [['balance'], 'number'],
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
    
        $query = ClientBankBalance::find();

        if(!isset($params['sort'])){
            $query->addOrderBy('id desc');
        }
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            $baseTableName.'.account_id' => $this->account_id,
            $baseTableName.'.start_date' => $this->start_date,
            $baseTableName.'.end_date' => $this->end_date,
            $baseTableName.'.uploaded_file_id' => $this->uploaded_file_id,
            $baseTableName.'.balance' => $this->balance,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.currency', $this->currency]);

        return $dataProvider;
    }
}