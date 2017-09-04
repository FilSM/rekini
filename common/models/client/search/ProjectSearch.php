<?php

namespace common\models\client\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\client\Project;

/**
 * ProjectSearch represents the model behind the search form of `common\models\client\Project`.
 */
class ProjectSearch extends Project
{
    public $create_time_range;
    public $country_name;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'version', 'deleted', 'country_id', 'vat_taxable',
                'create_user_id', 'update_user_id'], 'integer'],
            [['name', 'comment', 'create_time', 'update_time', 'country_name', 'address', 'create_time_range'], 'safe'],
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
    
        $query = Project::find();

        $query->select = [
            $baseTableName . '.*',
            'country_name' => 'country.name',
        ];
        
        $query->leftJoin(['country' => 'location_country'], 'country.id = '.$baseTableName.'.country_id');
        
        if(!isset($params['sort'])){
            //$query->addOrderBy('id desc');
            $query->addOrderBy('name');
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
            $baseTableName.'.version' => $this->version,
            $baseTableName.'.deleted' => $this->deleted,
            $baseTableName.'.country_id' => $this->country_id,
            $baseTableName.'.vat_taxable' => $this->vat_taxable,
            $baseTableName.'.create_time' => $this->create_time,
            $baseTableName.'.create_user_id' => $this->create_user_id,
            $baseTableName.'.update_time' => $this->update_time,
            $baseTableName.'.update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.name', $this->name])
            ->andFilterWhere(['like', $baseTableName.'.comment', $this->comment])
            ->andFilterWhere(['like', $baseTableName.'.address', $this->address])
            ->andFilterWhere(['like', 'country.name', $this->country_name]);
        
        if(!empty($this->create_time_range) && strpos($this->create_time_range, '-') !== false) { 
            list($start_date, $end_date) = explode(' - ', $this->create_time_range); 
            $query->andFilterWhere(['between', $baseTableName . '.create_time', $start_date, $end_date.' 23:59:59']);
        }

        return $dataProvider;
    }
}