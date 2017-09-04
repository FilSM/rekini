<?php

namespace common\models\client\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\client\ClientBank;

/**
 * ClientBankSearch represents the model behind the search form of `common\models\client\ClientBank`.
 */
class ClientBankSearch extends ClientBank
{
    public $client_name;
    public $bank_name;
    public $swift;
    public $home_page;
    public $file_name_xml;
    public $file_name_pdf;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'deleted', /*'client_id', 'bank_id',*/ 'uploaded_file_id'], 'integer'],
            [['client_id', 'bank_id', 'account', 'name', 'currency', 'bank_name', 
                'swift', 'client_name', 'home_page', 'start_date', 'end_date',
                'file_name_xml', 'file_name_pdf'], 'safe'],
            [['balance'], 'number'],
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
    
        $query = ClientBank::find();

        $query->select = [
            $baseTableName . '.*',
            'client_name' => 'client.name',
            'bank_name' => 'bank.name',
            'swift' => 'bank.swift',
            'home_page' => 'bank.home_page',
            'file_name_xml' => 'files_xml.filename',
            'file_name_pdf' => 'files_pdf.filename',            
        ];
        
        $query->leftJoin(['bank' => 'bank'], 'bank.id = '.$baseTableName.'.bank_id');
        $query->leftJoin(['client' => 'client'], 'client.id = '.$baseTableName.'.client_id');
        $query->leftJoin(['files_xml' => 'files'], 'files_xml.id = '.$baseTableName.'.uploaded_file_id');
        $query->leftJoin(['files_pdf' => 'files'], 'files_pdf.id = '.$baseTableName.'.uploaded_pdf_file_id');        
        
        if(!isset($params['sort'])){
            $query->addOrderBy('client_name, bank_name');
        }
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => empty($params['client_id']) ? 10 : 10,
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
            $baseTableName.'.deleted' => $this->deleted,
            $baseTableName.'.client_id' => $this->client_id,
            $baseTableName.'.bank_id' => $this->bank_id,
            $baseTableName.'.uploaded_file_id' => $this->uploaded_file_id,
            $baseTableName.'.balance' => $this->balance,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.account', $this->account])
            ->andFilterWhere(['like', $baseTableName.'.name', $this->name])
            ->andFilterWhere(['like', $baseTableName.'.currency', $this->currency])
            ->andFilterWhere(['like', $baseTableName.'.start_date', $this->start_date])
            ->andFilterWhere(['like', $baseTableName.'.end_date', $this->end_date])
            ->andFilterWhere(['like', $baseTableName.'.currency', $this->currency])
                
            ->andFilterWhere(['like', 'client.name', $this->client_name])
            ->andFilterWhere(['like', 'bank.name', $this->bank_name])
            ->andFilterWhere(['like', 'bank.swift', $this->swift])
            ->andFilterWhere(['like', 'files_xml.filename', $this->file_name_xml])    
            ->andFilterWhere(['like', 'files_pdf.filename', $this->file_name_pdf]);

        return $dataProvider;
    }
}