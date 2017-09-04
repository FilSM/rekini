<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Bank;

/**
 * BankSearch represents the model behind the search form about `common\models\Bank`.
 */
class BankSearch extends Bank
{
    public function rules()
    {
        return [
            [['id', 'enabled'], 'integer'],
            [['name', 'reg_number', 'swift', 'address'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Bank::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if(!isset($params['sort'])){
            $query->addOrderBy('name');
        }

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'enabled' => $this->enabled,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'swift', $this->reg_number])
            ->andFilterWhere(['like', 'swift', $this->swift])
            ->andFilterWhere(['like', 'address', $this->address]);

        return $dataProvider;
    }
}
