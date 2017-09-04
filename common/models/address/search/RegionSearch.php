<?php

namespace common\models\address\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\address\Region;

/**
 * RegionSearch represents the model behind the search form about `common\models\address\Region`.
 */
class RegionSearch extends Region {

    public function rules() {
        return [
            [['id', 'country_id'], 'integer'],
            [['name'], 'safe'],
        ];
    }

    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params) {
        $this->clearDefaultValues();
        
        $query = Region::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->joinWith(['country']);
        $query->select = [
            $this->tableName() . '.*',
            'country_name' => 'location_country.name',
        ];

        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            $this->tableName() . '.id' => $this->id,
            $this->tableName() . '.country_id' => $this->country_id,
        ]);

        $query->andFilterWhere(['like', $this->tableName() . '.name', $this->name]);

        return $dataProvider;
    }

}
