<?php

namespace common\models\address\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\address\City;

/**
 * CitySearch represents the model behind the search form about `common\models\address\City`.
 */
class CitySearch extends City {

    public function rules() {
        return [
            [['id', 'country_id', 'region_id'], 'integer'],
            [['name'], 'safe'],
        ];
    }

    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params) {
        $this->clearDefaultValues();
        
        $query = City::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->joinWith(['country', 'region']);
        $query->select = [
            $this->tableName() . '.*',
            'country_name' => 'location_country.name',
            'region_name' => 'location_region.name',
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
            $this->tableName() . '.region_id' => $this->region_id,
        ]);

        $query->andFilterWhere(['like', $this->tableName() . '.name', $this->name]);

        return $dataProvider;
    }

}
