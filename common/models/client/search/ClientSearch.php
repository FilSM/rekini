<?php

namespace common\models\client\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

use common\models\client\Client;
use common\models\user\FSMUser;

/**
 * ClientSearch represents the model behind the search form about `common\models\Client`.
 */
class ClientSearch extends Client {

    public $create_time_range;
    public $abonent_name;
    public $parent_name;
    public $manager_name;
    public $manager_user_id;
    public $country_name;

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'legal_country_id', 'office_country_id', 'manager_id', 'language_id', 
                'vat_payer', 'deleted'/*, 'create_user_id', 'update_user_id'*/], 'integer'],
            [['it_is', 'client_type', 'status', 'name', 'reg_number', 'vat_number', 
                'invoice_email', 'abonent_name', 'parent_name', 'manager_name', 'manager_user_id', 
                'create_time_range', 'country_name', 'country_id', /*'create_time', 'update_time'*/ ], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
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
    public function search($params) {
        $baseTableName = $this->tableName();
        $this->clearDefaultValues();
        
        $query = Client::find();

        $query->select = [
            $baseTableName . '.*',
            'abonent_name' => 'abonent.name',
            'parent_name' => 'parent.name',
            'manager_name' => 'manager.name',
            'manager_user_id' => 'manager.user_id',
            'country_name' => 'country.name',
        ];
        
        $query->leftJoin(['abonent' => 'abonent'], 'abonent.id = '.$baseTableName.'.abonent_id');
        $query->leftJoin(['parent' => 'client'], 'parent.id = '.$baseTableName.'.parent_id');
        $query->leftJoin(['manager' => 'profile'], 'manager.id = '.$baseTableName.'.manager_id');
        $query->leftJoin(['country' => 'location_country'], 'country.id = '.$baseTableName.'.legal_country_id');
        
        if(!isset($params['sort'])){
            //$query->addOrderBy('id desc');
            $query->addOrderBy('name');
        }
        
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

        $withoutTypes = [];
        if(!FSMUser::getIsPortalAdmin()){
            $withoutTypes[] = Client::CLIENT_IT_IS_OWNER;
        }
        $itIsList = $this->clientItIsList;
        foreach ($withoutTypes as $type) {
            unset($itIsList[$type]);
        }

        $query->andFilterWhere([
            $baseTableName . '.id' => $this->id,
            $baseTableName . '.abonent_id' => $this->abonent_id,
            $baseTableName . '.parent_id' => $this->parent_id,
            $baseTableName . '.manager_id' => $this->manager_id,
            $baseTableName . '.language_id' => $this->language_id,
            $baseTableName . '.legal_country_id' => $this->legal_country_id,
            $baseTableName . '.office_country_id' => $this->office_country_id,
            $baseTableName . '.deleted' => $this->deleted,
            $baseTableName . '.vat_payer' => $this->vat_payer,
            $baseTableName . '.it_is' => !empty($this->it_is) ? $this->it_is : array_keys($itIsList),
        ]);

        $query
            ->andFilterWhere(['like', $baseTableName . '.client_type', $this->client_type])
            ->andFilterWhere(['like', $baseTableName . '.status', $this->status])
            ->andFilterWhere(['like', $baseTableName . '.name', $this->name])
            ->andFilterWhere(['like', $baseTableName . '.invoice_email', $this->invoice_email])
            ->andFilterWhere(['like', 'abonent.name', $this->abonent_name])
            ->andFilterWhere(['like', 'parent.name', $this->parent_name])
            ->andFilterWhere(['like', 'manager.name', $this->manager_name])
        ;
        
        if(isset($params['our_clients'])){
            if($params['our_clients']){
                $query->andWhere(['not', [$baseTableName . '.client_group_id' => null]]);
            }else{
                $query->andWhere(['is', $baseTableName . '.client_group_id', null]);
            }
        }
        
        if(!empty($this->create_time_range) && strpos($this->create_time_range, '-') !== false) { 
            list($start_date, $end_date) = explode(' - ', $this->create_time_range); 
            $query->andFilterWhere(['between', $baseTableName . '.create_time', $start_date, $end_date.' 23:59:59']);
        }

        //return $query->all();
        return $dataProvider;
    }

}
