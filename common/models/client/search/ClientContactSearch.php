<?php

namespace common\models\client\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\client\ClientContact;

/**
 * ClientContactSearch represents the model behind the search form of `common\models\client\ClientContact`.
 */
class ClientContactSearch extends ClientContact
{
    public $position_name;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'deleted', 'client_id', 'position_id', 'can_sign', 'top_manager'], 'integer'],
            [['first_name', 'last_name', 'phone', 'email', 'position_name'], 'safe'],
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
    
        $query = ClientContact::find();

        $query->select = [
            $baseTableName . '.*',
            'position_name' => 'position.name',
        ];
        
        $query->leftJoin(['position' => 'person_position'], 'position.id = '.$baseTableName.'.position_id');
        
        if(!isset($params['sort'])){
            //$query->addOrderBy('id desc');
            $query->addOrderBy('last_name');
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
            $baseTableName.'.deleted' => $this->deleted,
            $baseTableName.'.client_id' => $this->client_id,
            $baseTableName.'.position_id' => $this->position_id,
            $baseTableName.'.can_sign' => $this->can_sign,
            $baseTableName.'.top_manager' => $this->top_manager,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.first_name', $this->first_name])
            ->andFilterWhere(['like', $baseTableName.'.last_name', $this->last_name])
            ->andFilterWhere(['like', $baseTableName.'.phone', $this->phone])
            ->andFilterWhere(['like', $baseTableName.'.email', $this->email])
            ->andFilterWhere(['like', 'position.name', $this->position_name]);

        return $dataProvider;
    }
}