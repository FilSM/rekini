<?php

namespace common\models\client\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\client\RegDoc;

/**
 * RegDocSearch represents the model behind the search form of `common\models\client\RegDoc`.
 */
class RegDocSearch extends RegDoc
{
    public $reg_doc_type_name;
    public $file_name;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'client_id', 'reg_doc_type_id', 'notification_days'], 'integer'],
            [['doc_number', 'doc_date', 'expiration_date', 'placement', 'comment',
                'file_name', 'reg_doc_type_name'], 'safe'],
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
    
        $query = RegDoc::find();
        
        $query->select = [
            $baseTableName . '.*',
            'reg_doc_type_name' => 'reg_doc_type.name',
            'file_name' => 'files.filename',
        ];
        
        $query->leftJoin(['reg_doc_type' => 'reg_doc_type'], 'reg_doc_type.id = '.$baseTableName.'.reg_doc_type_id');
        $query->leftJoin(['files' => 'files'], 'files.id = '.$baseTableName.'.uploaded_file_id');
        
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
            $baseTableName.'.client_id' => $this->client_id,
            $baseTableName.'.reg_doc_type_id' => $this->reg_doc_type_id,
            $baseTableName.'.doc_date' => $this->doc_date,
            $baseTableName.'.expiration_date' => $this->expiration_date,
            $baseTableName.'.notification_days' => $this->notification_days,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.doc_number', $this->doc_number])
            ->andFilterWhere(['like', $baseTableName.'.placement', $this->placement])
            ->andFilterWhere(['like', $baseTableName.'.comment', $this->comment])
                
            ->andFilterWhere(['like', 'reg_doc_type.name', $this->reg_doc_type_name])
            ->andFilterWhere(['like', 'files.filename', $this->file_name]);

        return $dataProvider;
    }
}