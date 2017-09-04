<?php

namespace common\models\abonent\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\abonent\Abonent;

/**
 * AbonentSearch represents the model behind the search form of `common\models\abonent\Abonent`.
 */
class AbonentSearch extends Abonent
{
    public $create_time_range;
    public $client_name;
    public $manager_name;
    public $manager_user_id;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'main_client_id', 'manager_id', 'deleted'], 'integer'],
            [['name', 'subscription_end_date', 'subscription_type',
                'client_name', 'manager_name', 'manager_user_id', 'create_time_range'], 'safe'],
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
    
        $query = Abonent::find();

        $query->select = [
            $baseTableName . '.*',
            'client_name' => 'client.name',
            'manager_name' => 'manager.name',
            'manager_user_id' => 'manager.user_id',
        ];
        
        $query->leftJoin(['client' => 'client'], 'client.id = '.$baseTableName.'.main_client_id');
        $query->leftJoin(['manager' => 'profile'], 'manager.id = '.$baseTableName.'.manager_id');

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
            $baseTableName.'.main_client_id' => $this->main_client_id,
            $baseTableName.'.subscription_end_date' => $this->subscription_end_date,
            $baseTableName.'.manager_id' => $this->manager_id,
            $baseTableName.'.deleted' => $this->deleted,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.name', $this->name])
            ->andFilterWhere(['like', $baseTableName.'.subscription_type', $this->subscription_type])
            ->andFilterWhere(['like', 'client.name', $this->client_name])
            ->andFilterWhere(['like', 'manager.name', $this->manager_name]);
        
        if(!empty($this->create_time_range) && strpos($this->create_time_range, '-') !== false) { 
            list($start_date, $end_date) = explode(' - ', $this->create_time_range); 
            $query->andFilterWhere(['between', $baseTableName . '.create_time', $start_date, $end_date.' 23:59:59']);
        }
        
        return $dataProvider;
    }
}