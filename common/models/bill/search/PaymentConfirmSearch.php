<?php

namespace common\models\bill\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\bill\PaymentConfirm;

/**
 * PaymentConfirmSearch represents the model behind the search form of `common\models\bill\PaymentConfirm`.
 */
class PaymentConfirmSearch extends PaymentConfirm
{
    public $file_name_xml;
    public $file_name_pdf;
    public $user_name;
    public $bank_name;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'bank_id', 'client_id', 'uploaded_file_id', 'uploaded_pdf_file_id', 
                'action_user_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['client_name', 'client_reg_number', 'name', 'start_date', 'end_date', 
                'pay_date', 'status', 'comment', 'action_time', 
                'file_name_xml', 'file_name_pdf', 'user_name', 'bank_name', 
                'create_time', 'update_time'], 'safe'],
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
    
        $query = PaymentConfirm::find();
        
        $query->select = [
            $baseTableName . '.*',
            'user_name' => 'profile.name',
            'file_name_xml' => 'files_xml.filename',
            'file_name_pdf' => 'files_pdf.filename',
            'bank_name' => 'bank.name',
        ];
        $query->leftJoin(['bank' => 'bank'], 'bank.id = '.$baseTableName.'.bank_id');
        $query->leftJoin(['files_xml' => 'files'], 'files_xml.id = '.$baseTableName.'.uploaded_file_id');
        $query->leftJoin(['files_pdf' => 'files'], 'files_pdf.id = '.$baseTableName.'.uploaded_pdf_file_id');
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
            $baseTableName.'.client_id' => $this->client_id,
            $baseTableName.'.start_date' => $this->start_date,
            $baseTableName.'.end_date' => $this->end_date,
            $baseTableName.'.pay_date' => $this->pay_date,
            $baseTableName.'.uploaded_file_id' => $this->uploaded_file_id,
            $baseTableName.'.uploaded_pdf_file_id' => $this->uploaded_pdf_file_id,
            $baseTableName.'.action_time' => $this->action_time,
            $baseTableName.'.action_user_id' => $this->action_user_id,
            $baseTableName.'.create_time' => $this->create_time,
            $baseTableName.'.create_user_id' => $this->create_user_id,
            $baseTableName.'.update_time' => $this->update_time,
            $baseTableName.'.update_user_id' => $this->update_user_id,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.client_name', $this->client_name])
            ->andFilterWhere(['like', $baseTableName.'.client_reg_number', $this->client_reg_number])
            ->andFilterWhere(['like', $baseTableName.'.name', $this->name])
            ->andFilterWhere(['like', $baseTableName.'.status', $this->status])
            ->andFilterWhere(['like', $baseTableName.'.comment', $this->comment])
                
            ->andFilterWhere(['like', 'files_xml.filename', $this->file_name_xml])    
            ->andFilterWhere(['like', 'files_pdf.filename', $this->file_name_pdf]);

        return $dataProvider;
    }
}