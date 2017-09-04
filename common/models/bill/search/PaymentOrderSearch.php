<?php

namespace common\models\bill\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\bill\PaymentOrder;

/**
 * PaymentOrderSearch represents the model behind the search form of `common\models\bill\PaymentOrder`.
 */
class PaymentOrderSearch extends PaymentOrder
{
    public $file_name;
    public $user_name;
    public $bank_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'bank_id', 'file_id', 'action_user_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['number', 'name', 'status', 'comment', 'action_time', 'pay_date', 
                'create_time', 'update_time', 'file_name', 'user_name', 'bank_name'], 'safe'],
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
    
        $query = PaymentOrder::find();

        $query->select = [
            $baseTableName . '.*',
            'user_name' => 'profile.name',
            'file_name' => 'files.filename',
            'bank_name' => 'bank.name',
        ];
        $query->leftJoin(['bank' => 'bank'], 'bank.id = '.$baseTableName.'.bank_id');
        $query->leftJoin(['files' => 'files'], 'files.id = '.$baseTableName.'.file_id');
        $query->leftJoin(['profile' => 'profile'], 'profile.user_id = '.$baseTableName.'.action_user_id');
        
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
            $baseTableName.'.bank_id' => $this->bank_id,
            $baseTableName.'.file_id' => $this->file_id,
            $baseTableName.'.action_time' => $this->action_time,
            $baseTableName.'.action_user_id' => $this->action_user_id,
            $baseTableName.'.create_time' => $this->create_time,
            $baseTableName.'.create_user_id' => $this->create_user_id,
            $baseTableName.'.update_time' => $this->update_time,
            $baseTableName.'.update_user_id' => $this->update_user_id,
            $baseTableName.'.pay_date' => $this->pay_date,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.number', $this->number])
            ->andFilterWhere(['like', $baseTableName.'.name', $this->name])
            ->andFilterWhere(['like', $baseTableName.'.status', $this->status])
            ->andFilterWhere(['like', $baseTableName.'.comment', $this->comment])
                
            ->andFilterWhere(['like', 'files.filename', $this->file_name]);

        return $dataProvider;
    }
}