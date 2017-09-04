<?php

namespace common\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Product;

/**
 * ProductSearch represents the model behind the search form of `common\models\Product`.
 */
class ProductSearch extends Product
{
    public $measure_name;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'measure_id'], 'integer'],
            [['article', 'name', 'description', 'measure_name'], 'safe'],
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
    
        $query = Product::find();

        $query->select = [
            $baseTableName . '.*',
            'measure_name' => 'measure.name',
        ];        
        
        $query->leftJoin(['measure' => 'measure'], 'measure.id = '.$baseTableName.'.measure_id');        
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
            $baseTableName.'.measure_id' => $this->measure_id,
        ]);

        $query
            ->andFilterWhere(['like', $baseTableName.'.article', $this->article])
            ->andFilterWhere(['like', $baseTableName.'.name', $this->name])
            ->andFilterWhere(['like', $baseTableName.'.description', $this->description])
            ->andFilterWhere(['like', 'measure.name', $this->measure_name]);

        return $dataProvider;
    }
}