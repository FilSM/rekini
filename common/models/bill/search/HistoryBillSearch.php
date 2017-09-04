<?php

namespace common\models\bill\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\bill\HistoryBill;

/**
 * HistoryBillSearch represents the model behind the search form of `common\models\bill\HistoryBill`.
 */
class HistoryBillSearch extends HistoryBill
{
    public $bill_number;
    public $user_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'bill_id', 'action_id', 'create_user_id'], 'integer'],
            [['comment', 'create_time', 'bill_number', 'user_name'], 'safe'],
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
    
        $query = HistoryBill::find();

        $query->select = [
            $baseTableName . '.*',
            'bill_number' => 'bill.doc_number',
            'user_name' => 'profile.name',
        ];
        
        $query->leftJoin(['bill' => 'bill'], 'bill.id = '.$baseTableName.'.bill_id');
        $query->leftJoin(['profile' => 'profile'], 'profile.user_id = '.$baseTableName.'.create_user_id');

        if(!isset($params['sort'])){
            $query->addOrderBy('create_time desc');
        }
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => empty($params['bill_id']) ? 20 : 10,
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
            $baseTableName.'.bill_id' => $this->bill_id,
            $baseTableName.'.action_id' => $this->action_id,
            $baseTableName.'.create_time' => $this->create_time,
            $baseTableName.'.create_user_id' => $this->create_user_id,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.comment', $this->comment])
                
            ->andFilterWhere(['like', 'bill.doc_number', $this->bill_number]);

        return $dataProvider;
    }
}