<?php

namespace common\models\user\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
 * UserSearch represents the model behind the search form about User.
 */
class FSMUserSearch extends \dektrium\user\models\UserSearch {

    /** @var string */
    public $client;
    public $client_id;
    public $role;
    public $fullName;
    public $phone;
    public $clientItIs;

    /** @inheritdoc */
    public function rules() {
        $rules = parent::rules();
        $rules = ArrayHelper::merge(
            $rules, 
            [
                [['fullName', 'client', 'client_id', 'phone', 'role', 'clientItIs'], 'safe'],
            ]
        );
        return $rules;
    }

    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params) {
        $query = $this->finder->getUserQuery();

        $query->select = [
            '{{%user}}.*',
            'fullName' => 'profile.name',
        ];        
        $query->leftJoin(['profile' => 'profile'], 'profile.user_id = {{%user}}.id');
        $query->leftJoin(['client' => 'client'], 'client.id = profile.client_id');
        $query->leftJoin(['auth_assignment' => 'auth_assignment'], 'auth_assignment.user_id = {{%user}}.id');
	
        if(!isset($params['sort'])){
            //$query->addOrderBy('id desc');
            $query->addOrderBy('fullName');
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        $query->andFilterWhere([
            'profile.client_id' => $this->client_id,
            'auth_assignment.item_name' => $this->role,
            'registration_ip' => $this->registration_ip,
        ]);
        
        $query->andFilterWhere(['like', 'username', $this->username]);
        $query->andFilterWhere(['like', 'email', $this->email]);
        $query->andFilterWhere(['like', 'profile.name', $this->fullName]);
        $query->andFilterWhere(['like', 'profile.phone', $this->phone]);
        //$query->andFilterWhere(['like', 'client.name', $this->client]);
	$query->andFilterWhere(['client.it_is' => $this->clientItIs]);

        if(!empty($this->created_at) && strpos($this->created_at, '-') !== false) { 
            list($start_date, $end_date) = explode(' - ', $this->created_at); 
            $query->andFilterWhere(['between', '{{%user}}.created_at', strtotime($start_date), strtotime($end_date.' 23:59:59')]);
        }
        
        if(!empty($this->last_login_at) && strpos($this->last_login_at, '-') !== false) { 
            list($start_date, $end_date) = explode(' - ', $this->last_login_at); 
            $query->andFilterWhere(['between', '{{%user}}.last_login_at', strtotime($start_date), strtotime($end_date.' 23:59:59')]);
        }
        
        return $dataProvider;
    }

}
