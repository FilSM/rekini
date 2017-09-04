<?php

namespace common\models\address\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\address\Country;

/**
 * CountrySearch represents the model behind the search form about `common\models\address\Country`.
 */
class CountrySearch extends Country {

    public function rules() {
        return [
            [['id'], 'integer'],
            [['name', 'short_name', 'currency'], 'safe'],
        ];
    }

    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params) {
        $this->clearDefaultValues();
        
        $query = Country::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            $this->tableName() . '.id' => $this->id,
        ]);

        $query->andFilterWhere(['like', $this->tableName() . '.name', $this->name])
                ->andFilterWhere(['like', $this->tableName() . '.short_name', $this->short_name])
                ->andFilterWhere(['like', $this->tableName() . '.currency', $this->currency]);

        return $dataProvider;
    }

}
