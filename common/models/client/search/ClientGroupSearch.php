<?php

namespace common\models\client\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\client\ClientGroup;

/**
 * ClientGroupSearch represents the model behind the search form of `common\models\client\ClientGroup`.
 */
class ClientGroupSearch extends ClientGroup
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'enabled'], 'integer'],
            [['name'], 'safe'],
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
    
        $query = ClientGroup::find();

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
            $baseTableName.'.enabled' => $this->enabled,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.name', $this->name]);

        return $dataProvider;
    }
}