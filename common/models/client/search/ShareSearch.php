<?php

namespace common\models\client\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\client\Share;

/**
 * ShareSearch represents the model behind the search form of `common\models\client\Share`.
 */
class ShareSearch extends Share
{
    public $client_name;
    public $shareholder_name;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'deleted', 'client_id', 'shareholder_id'], 'integer'],
            [['term_from', 'term_till', 'client_name', 'shareholder_name'], 'safe'],
            [['share'], 'number'],
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
    
        $query = Share::find();

        $query->select = [
            $baseTableName . '.*',
            'client_name' => 'client.name',
            'shareholder_name' => 'shareclient.name',
        ];
        
        $query->leftJoin(['client' => 'client'], 'client.id = '.$baseTableName.'.client_id');
        $query->leftJoin(['shareclient' => 'client'], 'shareclient.id = '.$baseTableName.'.shareholder_id');
        
        if(!isset($params['sort'])){
            //$query->addOrderBy('id desc');
            $query->addOrderBy('client_name');
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
            //$baseTableName.'.client_id' => $this->client_id,
            $baseTableName.'.shareholder_id' => $this->client_id,
            $baseTableName.'.term_from' => $this->term_from,
            $baseTableName.'.term_till' => $this->term_till,
            $baseTableName.'.share' => $this->share,
        ]);

        $query->andFilterWhere(['like', 'client.name', $this->client_name])
            ->andFilterWhere(['like', 'shareclient.name', $this->shareholder_name]);
        
        return $dataProvider;
    }
}