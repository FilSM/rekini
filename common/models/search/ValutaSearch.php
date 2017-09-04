<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Valuta;

/**
 * ValutaSearch represents the model behind the search form about `common\models\Valuta`.
 */
class ValutaSearch extends Valuta
{
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'safe'],
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Valuta::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if(!isset($params['sort'])){
            $query->addOrderBy('id desc');
        }
        
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', $this->tableName().'.name', $this->name]);

        return $dataProvider;
    }
}