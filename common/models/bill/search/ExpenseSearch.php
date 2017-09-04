<?php

namespace common\models\bill\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\bill\Expense;

/**
 * ExpenseSearch represents the model behind the search form of `common\models\bill\Expense`.
 */
class ExpenseSearch extends Expense
{
    public $abonent_name;
    public $project_name;
    public $first_client_name;
    public $second_client_name;
    public $expense_type_name;
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'expense_type_id', 'abonent_id', 'project_id', 'first_client_id', 
                'second_client_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['doc_number', 'doc_date', 'comment', 'create_time', 'update_time',
                'abonent_name', 'project_name', 'first_client_name', 'second_client_name',
                'expense_type_name', 'valuta_id'], 'safe'],
            [['summa', 'vat', 'total'], 'number'],
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
    
        $query = Expense::find();

        $query->select = [
            $baseTableName . '.*',
            'abonent_name' => 'abonent.name',
            'project_name' => 'project.name',
            'first_client_name' => 'first_client.name',
            'second_client_name' => 'second_client.name',
            'expense_type_name' => 'expense_type.name',
        ];
        
        $query->leftJoin(['abonent' => 'abonent'], 'abonent.id = '.$baseTableName.'.abonent_id');
        $query->leftJoin(['project' => 'project'], 'project.id = '.$baseTableName.'.project_id');
        $query->leftJoin(['first_client' => 'client'], 'first_client.id = '.$baseTableName.'.first_client_id');
        $query->leftJoin(['second_client' => 'client'], 'second_client.id = '.$baseTableName.'.second_client_id');
        $query->leftJoin(['expense_type' => 'expense_type'], 'expense_type.id = '.$baseTableName.'.expense_type_id');
        
        if(!isset($params['sort'])){
            //$query->addOrderBy('id desc');
            $query->addOrderBy('doc_date desc, doc_number desc');
        }

        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $docDateArr = !empty($params['doc_date_from_till']) ? $params['doc_date_from_till'] : null;
        $direction = !empty($params['direction']) ? $params['direction'] : null;
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
            $baseTableName.'.expense_type_id' => $this->expense_type_id,
            $baseTableName.'.abonent_id' => $this->abonent_id,
            $baseTableName.'.project_id' => $this->project_id,
            $baseTableName.'.doc_date' => !empty($this->doc_date) ? date('Y-m-d', strtotime($this->doc_date)) : null,
            $baseTableName.'.first_client_id' => $this->first_client_id,
            $baseTableName.'.second_client_id' => $this->second_client_id,
            $baseTableName.'.summa' => $this->summa,
            $baseTableName.'.vat' => $this->vat,
            $baseTableName.'.total' => $this->total,
            $baseTableName.'.valuta_id' => $this->valuta_id,
            $baseTableName.'.create_time' => $this->create_time,
            $baseTableName.'.create_user_id' => $this->create_user_id,
            $baseTableName.'.update_time' => $this->update_time,
            $baseTableName.'.update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.doc_number', $this->doc_number])
            ->andFilterWhere(['like', $baseTableName.'.comment', $this->comment])
                
            ->andFilterWhere(['like', 'abonent.name', $this->abonent_name])
            ->andFilterWhere(['like', 'project.name', $this->project_name]);
                
        if(!empty($direction)){
            if($direction == 'in'){
                $query->andWhere(['not', ['first_client.client_group_id' => null]]);
            }elseif($direction == 'in'){
                $query->andWhere(['not', ['second_client.client_group_id' => null]]);
            }
        }
        
        if(!empty($docDateArr) !== false) { 
            $query->andFilterWhere(['between', $baseTableName . '.doc_date', $docDateArr[0], $docDateArr[1].' 23:59:59']);
        }
        
        return $dataProvider;
    }
}