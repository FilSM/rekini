<?php

namespace common\models\bill\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\ActiveQuery;

use common\models\bill\Bill;

/**
 * BillSearch represents the model behind the search form of `common\models\bill\Bill`.
 */
class BillSearch extends Bill
{
    public $create_time_range;
    public $abonent_name;
    public $project_name;
    public $agreement_number;
    public $first_client_id;
    public $first_client_name;
    public $first_client_role_name;
    public $second_client_id;
    public $second_client_name;
    public $second_client_role_name;
    public $third_client_id;
    public $third_client_name;
    public $third_client_role_name;
    public $manager_name;
    public $manager_user_id;
    
    public $project_id;
    public $project_sales;
    public $project_purchases;
    public $project_profit;

    public $client_id;
    public $client_name;
    public $client_sales;
    public $client_vat_plus;
    public $client_purchases;
    public $client_vat_minus;
    public $client_vat_result;
    
    public $debtors_summa;
    public $creditors_summa;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'version', 'deleted', 'abonent_id',  'project_id', 'agreement_id', 
                'parent_id', 'delayed', 'first_client_bank_id', 'second_client_bank_id', 
                /*'valuta_id', */'manager_id', 'create_user_id', 'update_user_id'], 'integer'],
            [['doc_type', 'doc_number', 'doc_date', 'pay_date', 'paid_date', 'complete_date', 
                'status', 'pay_status', 'comment', 'create_time', 'update_time', 
                'create_time_range', 'abonent_name', 'project_name', 'agreement_number',
                'first_client_name', 'second_client_name', 'third_client_name', 'manager_name', 
                'manager_user_id', 'first_client_id', 'second_client_id', 'third_client_id', 
                'first_client_role_name', 'second_client_role_name', 'third_client_role_name', 
                'project_id', 'client_id', 'valuta_id'], 'safe'],
            [['summa', 'vat', 'debtors_summa', 'creditors_summa'], 'number'],
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
    
        $query = Bill::find();

        $query->select = [
            $baseTableName . '.*',
            'abonent_name' => 'abonent.name',
            'project_name' => 'project.name',
            'agreement_number' => 'agreement.number',
            'first_client_id' => 'If('.$baseTableName.'.doc_type != "cession", agreement.first_client_id, IF('.$baseTableName.'.cession_direction = "D", agreement.first_client_id, agreement.third_client_id))',
            'first_client_name' => 'If('.$baseTableName.'.doc_type != "cession", first_client.name, IF('.$baseTableName.'.cession_direction = "D", first_client.name, third_client.name))',
            'first_client_role_name' => 'If('.$baseTableName.'.doc_type != "cession", first_client_role.name, IF('.$baseTableName.'.cession_direction = "D", first_client_role.name, third_client_role.name))',
            'second_client_id' => 'If('.$baseTableName.'.doc_type != "cession", agreement.second_client_id, IF('.$baseTableName.'.cession_direction = "D", agreement.third_client_id, agreement.second_client_id))',
            'second_client_name' => 'If('.$baseTableName.'.doc_type != "cession", second_client.name, IF('.$baseTableName.'.cession_direction = "D", third_client.name, second_client.name))',
            'second_client_role_name' => 'If('.$baseTableName.'.doc_type != "cession", second_client_role.name, IF('.$baseTableName.'.cession_direction = "D", third_client_role.name, second_client_role.name))',
            'third_client_id' => 'agreement.third_client_id',
            'third_client_name' => 'third_client.name',
            'third_client_role_name' => 'third_client_role.name',
            'manager_name' => 'manager.name',
            'manager_user_id' => 'manager.user_id',
        ];
        
        $query->leftJoin(['abonent' => 'abonent'], 'abonent.id = '.$baseTableName.'.abonent_id');
        $query->leftJoin(['project' => 'project'], 'project.id = '.$baseTableName.'.project_id');
        $query->leftJoin(['agreement' => 'agreement'], 'agreement.id = '.$baseTableName.'.agreement_id');
        $query->leftJoin(['first_client' => 'client'], 'first_client.id = agreement.first_client_id');
        $query->leftJoin(['second_client' => 'client'], 'second_client.id = agreement.second_client_id');
        $query->leftJoin(['third_client' => 'client'], 'third_client.id = agreement.third_client_id');
        $query->leftJoin(['first_client_role' => 'client_role'], 'first_client_role.id = agreement.first_client_role_id');
        $query->leftJoin(['second_client_role' => 'client_role'], 'second_client_role.id = agreement.second_client_role_id');
        $query->leftJoin(['third_client_role' => 'client_role'], 'third_client_role.id = agreement.third_client_role_id');
        $query->leftJoin(['manager' => 'profile'], 'manager.id = '.$baseTableName.'.manager_id');
        
        if(!isset($params['sort'])){
            //$query->addOrderBy('id desc');
            $query->addOrderBy('doc_date desc, doc_number desc');
        }
        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        $statusArr = !empty($params['status']) ? $params['status'] : null;
        $payStatusArr = !empty($params['pay_status']) ? $params['pay_status'] : null;
        $docTypeArr = !empty($params['doc_type']) ? $params['doc_type'] : null;
        $projectIds = !empty($params['BillSearch']['project_id']) ? $params['BillSearch']['project_id'] : null;
        $agreementIds = !empty($params['BillSearch']['agreement_id']) ? $params['BillSearch']['agreement_id'] : null;
        $firstClientIds = !empty($params['BillSearch']['first_client_id']) ? $params['BillSearch']['first_client_id'] : null;
        $docDateArr = !empty($params['doc_date_from_till']) ? $params['doc_date_from_till'] : null;
        unset(
            $params['status'], 
            $params['pay_status'], 
            $params['doc_type'], 
            $params['BillSearch']['project_id'], 
            $params['BillSearch']['agreement_id'], 
            $params['BillSearch']['first_client_id']
        );
        
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        if($this->hasAttribute('version')){
            $this->__unset('version');
        }

        $this->project_id = !empty($projectIds) ? $projectIds : (!empty($params['project_id']) ? $params['project_id'] : null);
        $this->agreement_id = !empty($agreementIds) ? $agreementIds : null;
        $this->first_client_id = !empty($firstClientIds) ? $firstClientIds : null;
                
        // grid filtering conditions
        $query->andFilterWhere([
            $baseTableName.'.id' => $this->id,
            $baseTableName.'.version' => $this->version,
            $baseTableName.'.deleted' => $this->deleted,
            //$baseTableName.'.status' => $this->status,
            $baseTableName.'.delayed' => $this->delayed,
            $baseTableName.'.abonent_id' => $this->abonent_id,
            $baseTableName.'.project_id' => $this->project_id,
            $baseTableName.'.agreement_id' => $this->agreement_id,
            $baseTableName.'.parent_id' => $this->parent_id,
            $baseTableName.'.doc_date' => !empty($this->doc_date) ? date('Y-m-d', strtotime($this->doc_date)) : null,
            $baseTableName.'.pay_date' => !empty($this->pay_date) ? date('Y-m-d', strtotime($this->pay_date)) : null,
            $baseTableName.'.paid_date' => !empty($this->paid_date) ? date('Y-m-d', strtotime($this->paid_date)) : null,
            $baseTableName.'.complete_date' => !empty($this->complete_date) ? date('Y-m-d', strtotime($this->complete_date)) : null,
            $baseTableName.'.first_client_bank_id' => $this->first_client_bank_id,
            $baseTableName.'.second_client_bank_id' => $this->second_client_bank_id,
            $baseTableName.'.summa' => $this->summa,
            $baseTableName.'.vat' => $this->vat,
            $baseTableName.'.total' => $this->total,
            $baseTableName.'.valuta_id' => $this->valuta_id,
            $baseTableName.'.manager_id' => $this->manager_id,
            $baseTableName.'.create_time' => $this->create_time,
            $baseTableName.'.create_user_id' => $this->create_user_id,
            $baseTableName.'.update_time' => $this->update_time,
            $baseTableName.'.update_user_id' => $this->update_user_id,
            
            'agreement.first_client_id' => $this->first_client_id,
            'agreement.second_client_id' => $this->second_client_id,
        ]);

        $query->andFilterWhere(['like', $baseTableName.'.doc_number', $this->doc_number])
            ->andFilterWhere(['like', $baseTableName.'.comment', $this->comment])
                
            ->andFilterWhere(['like', 'abonent.name', $this->abonent_name])
            ->andFilterWhere(['like', 'project.name', $this->project_name])
            ->andFilterWhere(['like', 'agreement.number', $this->agreement_number])
            ->andFilterWhere(['like', 'If('.$baseTableName.'.doc_type != "cession", first_client.name, IF('.$baseTableName.'.cession_direction = "D", first_client.name, third_client.name))', $this->first_client_name])
            ->andFilterWhere(['like', 'If('.$baseTableName.'.doc_type != "cession", second_client.name, IF('.$baseTableName.'.cession_direction = "D", third_client.name, second_client.name))', $this->second_client_name])
            ->andFilterWhere(['like', 'manager.name', $this->manager_name]);

        if(!empty($this->status)){
            $query->andFilterWhere(['in', $baseTableName.'.status', $this->status]);
        }elseif(!empty($statusArr)){
            $query->andFilterWhere(['in', $baseTableName.'.status', $statusArr]);
        }
        
        if(!empty($this->doc_type)){
            $query->andFilterWhere(['in', $baseTableName.'.doc_type', $this->doc_type]);
        }elseif(!empty($docTypeArr)){
            $query->andFilterWhere(['in', $baseTableName.'.doc_type', $docTypeArr]);
        }
        
        if(isset($this->pay_status)){
            if($this->pay_status != 'delayed'){
                if(is_array($this->pay_status)){
                    $query->andFilterWhere(['in', $baseTableName.'.pay_status', $this->pay_status]);
                }else{
                    $query->andFilterWhere([$baseTableName.'.pay_status' => $this->pay_status]);
                }
            }else{
                $query->andFilterWhere([$baseTableName.'.delayed' => 1]);
            }
        }elseif(!empty($payStatusArr)){
            $query->andFilterWhere(['in', $baseTableName.'.pay_status', $payStatusArr]);
        }
                
        if(!empty($params['direction'])){
            if($params['direction'] == 'in'){
                $query->andWhere(['not', ['first_client.client_group_id' => null]]);
            }elseif($params['direction'] == 'out'){
                $query->andWhere(['not', ['second_client.client_group_id' => null]]);
            }
        }
        
        if(!empty($this->create_time_range) && strpos($this->create_time_range, '-') !== false) { 
            list($start_date, $end_date) = explode(' - ', $this->create_time_range); 
            $query->andFilterWhere(['between', $baseTableName . '.create_time', $start_date, $end_date.' 23:59:59']);
        }
        if(!empty($docDateArr) !== false) { 
            $query->andFilterWhere(['between', $baseTableName . '.doc_date', $docDateArr[0], $docDateArr[1].' 23:59:59']);
        }
        
        //return $query->all();
        return $dataProvider;
    }
    
    public function searchDelayed()
    {
        $baseTableName = $this->tableName();
        $this->clearDefaultValues();
        $query = $this->find();
        $query->andFilterWhere([
            $baseTableName.'.deleted' => 0,
            $baseTableName.'.delayed' => 0,
        ]);        
        $query->andFilterWhere(['<', $baseTableName.'.pay_date', date('Y-m-d')])
            ->andFilterWhere(['in', $baseTableName.'.pay_status', [Bill::BILL_PAY_STATUS_NOT, Bill::BILL_PAY_STATUS_PART]]);
        
        return $query->all();
    }
    
    public function searchEbitdaReport($params)
    {
        $this->clearDefaultValues();
        
        $start_date = !empty($_GET['from']) ? date('Y-m-d', strtotime($_GET['from'])) : '1970-01-01';
        $end_date = !empty($_GET['till']) ? date('Y-m-d', strtotime($_GET['till'])) : '2500-01-01';
        
        $query = new ActiveQuery(Bill::className());
        $query->select([
                'project_id' => 'project.id',
                'project_name' => 'project.name',
                'project_sales' => 'debet.total',
                'project_purchases' => '(IF(credit.total, credit.total, 0) + IF(expense.total, expense.total, 0))',
                'project_profit' => '(IF(debet.total, debet.total, 0) - IF(credit.total, credit.total, 0) - IF(expense.total, expense.total, 0))',
            ])
            ->from('project');
        
        $subBillQuery = (new Query())
            ->select([
                'project_id' => 'bill.project_id',
                'total' => 'SUM(total)',
            ])  
            ->from('bill')
            ->leftJoin(['a' => 'agreement'], 'a.id = bill.agreement_id')
            ->leftJoin(['c' => 'client'], 'c.id = a.first_client_id')
            ->where(['in', 'bill.status', ['signed', 'prepar_payment', 'payment', 'paid', 'complete']])
            ->andWhere(['not', ['c.client_group_id' => null]])
            ->andFilterWhere(['!=', 'bill.doc_type', Bill::BILL_DOC_TYPE_AVANS])                
            ->andFilterWhere(['between', 'bill.doc_date', $start_date, $end_date.' 23:59:59'])
            ->andFilterWhere(['!=', 'bill.deleted', 1])
            ->groupBy('bill.project_id');
        
        $query->leftJoin(['debet' => $subBillQuery], 'debet.project_id = project.id');

        $subBillQuery = (new Query())
            ->select([
                'project_id' => 'bill.project_id',
                'total' => 'SUM(total)',
            ])  
            ->from('bill')
            ->leftJoin(['a' => 'agreement'], 'a.id = bill.agreement_id')
            ->leftJoin(['c' => 'client'], 'c.id = a.second_client_id')
            ->where(['in', 'bill.status', ['signed', 'prepar_payment', 'payment', 'paid', 'complete']])
            ->andWhere(['not', ['c.client_group_id' => null]])
            ->andFilterWhere(['!=', 'bill.doc_type', Bill::BILL_DOC_TYPE_AVANS])                
            ->andFilterWhere(['between', 'bill.doc_date', $start_date, $end_date.' 23:59:59'])
            ->andFilterWhere(['!=', 'bill.deleted', 1])
            ->groupBy('bill.project_id');
        
        $query->leftJoin(['credit' => $subBillQuery], 'credit.project_id = project.id');
        
        $subExpenseQuery = (new Query())
            ->select([
                'project_id' => 'project_id',
                'total' => 'SUM(total)',
            ]) 
            ->from('expense')
            ->where(['between', 'doc_date', $start_date, $end_date.' 23:59:59']) 
            ->groupBy('project_id');
        
        $query->leftJoin(['expense' => $subExpenseQuery], 'expense.project_id = project.id');
        
        $query->where(['!=', 'project.deleted', 1]);
        $query->andWhere('(debet.total != 0) OR (credit.total != 0) OR (expense.total != 0)');
        
        /*
        SELECT 
            `project`.`id` AS `project_id`,
            `project`.`name` AS `project_name`,
            `debet`.`total` AS `project_sales`,
            (IF(credit.total, credit.total, 0) + IF(expense.total, expense.total, 0)) AS `project_purchases`,
            (IF(debet.total, debet.total, 0) - IF(credit.total, credit.total, 0) - IF(expense.total, expense.total, 0)) AS `project_profit`
        FROM
            `project`
        LEFT JOIN (
            SELECT 
                `bill`.`project_id` AS `project_id`, SUM(total) AS `total`
            FROM
                `bill`
                LEFT JOIN `agreement` `a` ON a.id = bill.agreement_id
                LEFT JOIN `client` `c` ON c.id = a.first_client_id
            WHERE
                (`bill`.`status` IN ('signed' , 'prepar_payment', 'payment', 'paid', 'complete'))
                AND (NOT (`c`.`client_group_id` IS NULL))
                AND (`bill`.`doc_type` != 'avans')
                AND (`bill`.`doc_date` BETWEEN '1970-01-01' AND '2500-01-01 23:59:59')
                AND (`bill`.`deleted` != 1)
            GROUP BY `bill`.`project_id`
        ) `debet` ON debet.project_id = project.id
        LEFT JOIN (
            SELECT 
                `bill`.`project_id` AS `project_id`, SUM(total) AS `total`
            FROM
                `bill`
                LEFT JOIN `agreement` `a` ON a.id = bill.agreement_id
                LEFT JOIN `client` `c` ON c.id = a.second_client_id
            WHERE
                (`bill`.`status` IN ('signed' , 'prepar_payment', 'payment', 'paid', 'complete'))
                AND (NOT (`c`.`client_group_id` IS NULL))
                AND (`bill`.`doc_type` != 'avans')
                AND (`bill`.`doc_date` BETWEEN '1970-01-01' AND '2500-01-01 23:59:59')
                AND (`bill`.`deleted` != 1)
            GROUP BY `bill`.`project_id`
        ) `credit` ON credit.project_id = project.id
        LEFT JOIN (
            SELECT 
                `project_id` AS `project_id`, SUM(total) AS `total`
            FROM
                `expense`
            WHERE
                `doc_date` BETWEEN '1970-01-01' AND '2500-01-01 23:59:59'
            GROUP BY `project_id`
        ) `expense` ON expense.project_id = project.id
        WHERE
            (`project`.`deleted` != 1)
            AND ((debet.total != 0) OR (credit.total != 0))
            AND (`project`.`id` = 26)
        ORDER BY `name`
         * 
         */
        
        if(!isset($params['sort'])){
            $query->addOrderBy('name');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        
        $projectIds = !empty($params['project_id']) ? $params['project_id'] : null;
        unset($params['project_id']);
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        $this->project_id = !empty($projectIds) ? $projectIds : null;
        
        $query->andFilterWhere([
            'project.id' => $this->project_id,
        ]);
        
        //return $query->all();
        return $dataProvider;
    }    
    
    public function searchVatReport($params)
    {
        $this->clearDefaultValues();
        
        $start_date = !empty($_GET['from']) ? date('Y-m-d', strtotime($_GET['from'])) : '1970-01-01';
        $end_date = !empty($_GET['till']) ? date('Y-m-d', strtotime($_GET['till'])) : '2500-01-01';
        
        $query = new ActiveQuery(Bill::className());
        $query->select([
            'client_id' => 'client.id',
            'client_name' => 'client.name',
            'client_sales' => 'debet.summa',
            'client_vat_plus' => 'debet.vat',
            'client_purchases' => 'credit.summa',
            'client_vat_minus' => 'credit.vat',
            'client_vat_result' => '(IF(debet.vat, debet.vat, 0) - IF(credit.vat, credit.vat, 0))',
        ]);
        $query->from('client');
        
        $subQuery = (new Query())
            ->select([
                'client_id' => 'first_client_id',
                'summa' => 'SUM(bill.summa)',
                'vat' => 'SUM(vat)',
            ])  
            ->from('bill')
            ->leftJoin(['a' => 'agreement'], 'a.id = bill.agreement_id')
            ->where(['in', 'bill.status', ['signed', 'prepar_payment', 'payment', 'paid', 'complete']])
            ->andFilterWhere(['!=', 'bill.doc_type', Bill::BILL_DOC_TYPE_AVANS])                
            ->andFilterWhere(['between', 'bill.doc_date', $start_date, $end_date.' 23:59:59'])
            ->andFilterWhere(['!=', 'bill.deleted', 1])                 
            ->groupBy('first_client_id');
        
        $query->leftJoin(['debet' => $subQuery], 'debet.client_id = client.id');
        
        $subQuery = (new Query())
            ->select([
                'client_id' => 'second_client_id',
                'summa' => 'SUM(bill.summa)',
                'vat' => 'SUM(vat)',
            ])  
            ->from('bill')
            ->leftJoin(['a' => 'agreement'], 'a.id = bill.agreement_id')
            ->where(['in', 'bill.status', ['signed', 'prepar_payment', 'payment', 'paid', 'complete']])
            ->andFilterWhere(['!=', 'bill.doc_type', Bill::BILL_DOC_TYPE_AVANS])                
            ->andFilterWhere(['between', 'bill.doc_date', $start_date, $end_date.' 23:59:59'])
            ->andFilterWhere(['!=', 'bill.deleted', 1])                   
            ->groupBy('second_client_id');
        
        $query->leftJoin(['credit' => $subQuery], 'credit.client_id = client.id');
        
        $query->where('(debet.vat IS NOT NULL OR credit.vat IS NOT NULL)');
        
        /*
        SELECT 
            `client`.`id` AS `client_id`,
            `client`.`name` AS `client_name`,
            `debet`.`summa` AS `client_sales`,
            `debet`.`vat` AS `client_vat_plus`,
            `credit`.`summa` AS `client_purchases`,
            `credit`.`vat` AS `client_vat_minus`,
            IF(debet.vat, debet.vat, 0) - IF(credit.vat, credit.vat, 0) AS `client_vat_result`
        FROM
            `client`
            LEFT JOIN (
                SELECT 
                    `first_client_id` AS `client_id`,
                    SUM(total) AS `summa`,
                    SUM(vat) AS `vat`
                FROM
                    `bill`
                    LEFT JOIN `agreement` `a` ON a.id = bill.agreement_id
                WHERE
                    (bill.status = 'complete')
                    AND (`bill`.`doc_type` != 'avans')
                    AND (`bill`.`complete_date` BETWEEN '2017-01-01' AND '2018-01-01')
                GROUP BY `first_client_id`
            ) `debet` ON debet.client_id = client.id
            LEFT JOIN (
                SELECT 
                    `second_client_id` AS `client_id`,
                    SUM(total) AS `summa`,
                    SUM(vat) AS `vat`
                FROM
                    `bill`
                    LEFT JOIN `agreement` `a` ON a.id = bill.agreement_id
                WHERE
                    (bill.status = 'complete')
                    AND (`bill`.`doc_type` != 'avans')
                    AND (`bill`.`complete_date` BETWEEN '2017-01-01' AND '2018-01-01')
                GROUP BY `second_client_id`
            ) `credit` ON credit.client_id = client.id
        WHERE
            (debet.vat IS NOT NULL OR credit.vat IS NOT NULL)
        ORDER BY `name`
         * 
         */
        
        if(!isset($params['sort'])){
            $query->addOrderBy('name');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        $query->andFilterWhere([
            'client.id' => $this->client_id,
        ]);
        
        //return $query->all();
        return $dataProvider;
    }    
    
    public function searchDebitorCreditorReport($params)
    {
        $this->clearDefaultValues();
        
        $start_date = !empty($_GET['from']) ? date('Y-m-d', strtotime($_GET['from'])) : '1970-01-01';
        $end_date = !empty($_GET['till']) ? date('Y-m-d', strtotime($_GET['till'])) : '2500-01-01';
        
        $query = new ActiveQuery(Bill::className());
        $query->select([
            'client_id' => 'client.id',
            'client_name' => 'client.name',
            'debtors_summa' => 'debet.summa',
            'creditors_summa' => 'credit.summa',
        ]);
        $query->from('client');
        
        $subQuery = (new Query())
            ->select([
                'client_id' => 'first_client_id',
                'summa' => 'SUM(total)',
            ])  
            ->from('bill')
            ->leftJoin(['a' => 'agreement'], 'a.id = bill.agreement_id')
            ->where(['in', 'bill.status', ['signed', 'prepar_payment', 'payment', 'paid', 'complete']])
            ->andFilterWhere(['!=', 'bill.doc_type', Bill::BILL_DOC_TYPE_AVANS])                
            ->andFilterWhere(['between', 'bill.doc_date', $start_date, $end_date.' 23:59:59'])
            ->andFilterWhere(['!=', 'bill.deleted', 1])
            ->groupBy('first_client_id');
        
        $query->leftJoin(['debet' => $subQuery], 'debet.client_id = client.id');
        
        $subQuery = (new Query())
            ->select([
                'client_id' => 'second_client_id',
                'summa' => 'SUM(total)',
            ])  
            ->from('bill')
            ->leftJoin(['a' => 'agreement'], 'a.id = bill.agreement_id')
            ->where(['in', 'bill.status', ['signed', 'prepar_payment', 'payment', 'paid', 'complete']])
            ->andFilterWhere(['!=', 'bill.doc_type', Bill::BILL_DOC_TYPE_AVANS])                
            ->andFilterWhere(['between', 'bill.doc_date', $start_date, $end_date.' 23:59:59'])                
            ->andFilterWhere(['!=', 'bill.deleted', 1])
            ->groupBy('second_client_id');
        
        $query->leftJoin(['credit' => $subQuery], 'credit.client_id = client.id');
        
        $query->where('(debet.summa IS NOT NULL OR credit.summa IS NOT NULL)')
            ->andFilterWhere(['!=', 'client.deleted', 1]);
        
        /*
        SELECT 
            `client`.`id` AS `client_id`,
            `client`.`name` AS `client_name`,
            `debet`.`summa` AS `client_sales`,
            `debet`.`vat` AS `client_vat_plus`,
            `credit`.`summa` AS `client_purchases`,
            `credit`.`vat` AS `client_vat_minus`,
            IF(debet.vat, debet.vat, 0) - IF(credit.vat, credit.vat, 0) AS `client_vat_result`
        FROM
            `client`
            LEFT JOIN (
                SELECT 
                    `first_client_id` AS `client_id`,
                    SUM(total) AS `summa`,
                    SUM(vat) AS `vat`
                FROM
                    `bill`
                    LEFT JOIN `agreement` `a` ON a.id = bill.agreement_id
                WHERE
                    (bill.status = 'complete')
                    AND (`bill`.`doc_type` != 'avans')
                    AND (`bill`.`complete_date` BETWEEN '2017-01-01' AND '2018-01-01')
                GROUP BY `first_client_id`
            ) `debet` ON debet.client_id = client.id
            LEFT JOIN (
                SELECT 
                    `second_client_id` AS `client_id`,
                    SUM(total) AS `summa`,
                    SUM(vat) AS `vat`
                FROM
                    `bill`
                    LEFT JOIN `agreement` `a` ON a.id = bill.agreement_id
                WHERE
                    (bill.status = 'complete')
                    AND (`bill`.`doc_type` != 'avans')
                    AND (`bill`.`complete_date` BETWEEN '2017-01-01' AND '2018-01-01')
                GROUP BY `second_client_id`
            ) `credit` ON credit.client_id = client.id
        WHERE
            (debet.vat IS NOT NULL OR credit.vat IS NOT NULL)
        ORDER BY `name`
         * 
         */
        
        if(!isset($params['sort'])){
            $query->addOrderBy('name');
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);
        
        $this->load($params);
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        $query->andFilterWhere([
            'client.id' => $this->client_id,
        ]);
        
        //return $query->all();
        return $dataProvider;
    }    
    
}