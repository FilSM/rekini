<?php

namespace common\models\client\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\client\Agreement;

/**
 * AgreementSearch represents the model behind the search form of `common\models\client\Agreement`.
 */
class AgreementSearch extends Agreement
{
    public $create_time_range;
    public $first_client_name;
    public $second_client_name;
    public $third_client_name;
    public $project_name;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'version', 'deleted', 'abonent_id', 'first_client_id', 'second_client_id', 
                'third_client_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['number', 'signing_date', 'due_date', 'status', 'agreement_type', 
                'conclusion', 'comment', 'create_time', 'update_time', 
                'first_client_name', 'second_client_name', 'third_client_name', 
                'project_name', 'create_time_range'], 'safe'],
            [['summa', 'rate'], 'number'],
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
    
        $query = Agreement::find();

        $query->select = [
            $baseTableName . '.*',
            'first_client_name' => 'first_client.name',
            'second_client_name' => 'second_client.name',
            'third_client_name' => 'third_client.name',
            'project_name' => 'project.name',
        ];
        
        $query->leftJoin(['first_client' => 'client'], 'first_client.id = '.$baseTableName.'.first_client_id');
        $query->leftJoin(['second_client' => 'client'], 'second_client.id = '.$baseTableName.'.second_client_id');
        $query->leftJoin(['third_client' => 'client'], 'third_client.id = '.$baseTableName.'.third_client_id');
        $query->leftJoin(['project' => 'project'], 'project.id = '.$baseTableName.'.project_id');
        
        if(!isset($params['sort'])){
            //$query->addOrderBy('id desc');
            $query->addOrderBy('signing_date desc, number');
        }
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
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
            $baseTableName.'.version' => $this->version,
            $baseTableName.'.deleted' => $this->deleted,
            $baseTableName.'.abonent_id' => $this->abonent_id,
            $baseTableName.'.first_client_id' => $this->first_client_id,
            $baseTableName.'.second_client_id' => $this->second_client_id,
            $baseTableName.'.third_client_id' => $this->third_client_id,
            $baseTableName.'.signing_date' => $this->signing_date,
            $baseTableName.'.due_date' => $this->due_date,
            $baseTableName.'.summa' => $this->summa,
            $baseTableName.'.rate' => $this->rate,
            $baseTableName.'.create_time' => $this->create_time,
            $baseTableName.'.create_user_id' => $this->create_user_id,
            $baseTableName.'.update_time' => $this->update_time,
            $baseTableName.'.update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.number', $this->number])
            ->andFilterWhere(['like', $baseTableName.'.comment', $this->comment])
            ->andFilterWhere(['like', $baseTableName . '.agreement_type', $this->agreement_type])
            ->andFilterWhere(['like', $baseTableName . '.status', $this->status])
            ->andFilterWhere(['like', $baseTableName . '.conclusion', $this->conclusion])
            ->andFilterWhere(['like', 'first_client.name', $this->first_client_name])
            ->andFilterWhere(['like', 'second_client.name', $this->second_client_name])
            ->andFilterWhere(['like', 'third_client.name', $this->third_client_name])
            ->andFilterWhere(['like', 'project.name', $this->project_name]);

        return $dataProvider;
    }
}