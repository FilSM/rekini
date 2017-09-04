<?php

namespace common\models\address\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\address\Address;

/**
 * AddressSearch represents the model behind the search form about `common\models\address\Address`.
 */
class AddressSearch extends Address {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'client_id', 'country_id', 'region_id', 'city_id', 'district_id', 
                'deleted', 'create_user_id', 'update_user_id'], 'integer'],
            [['address_type', 'company_name', 'customer_address', 'contact_person', 'contact_phone', 'contact_email',
            'apartment_number', 'street_number', 'route', 'district', 'political', 'sublocality_level_1', 'sublocality', 'locality', 
            'administrative_area_level_1', 'country', 'postal_code', 'formated_address', 'create_time', 'update_time'], 'safe'],
            [['latitude', 'longitude'], 'number'],
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
        
        $query = Address::find();

        if (!isset($params['sort'])) {
            $query->addOrderBy('id desc');
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
            $baseTableName . '.id' => $this->id,
            $baseTableName . '.address_type' => $this->address_type,
            $baseTableName . '.client_id' => $this->client_id,
            $baseTableName . '.country_id' => $this->country_id,
            $baseTableName . '.region_id' => $this->region_id,
            $baseTableName . '.city_id' => $this->city_id,
            $baseTableName . '.district_id' => $this->district_id,
            $baseTableName . '.latitude' => $this->latitude,
            $baseTableName . '.longitude' => $this->longitude,
            $baseTableName . '.deleted' => $this->deleted,
            /*
              $baseTableName.'.create_time' => $this->create_time,
              $baseTableName.'.create_user_id' => $this->create_user_id,
              $baseTableName.'.update_time' => $this->update_time,
              $baseTableName.'.update_user_id' => $this->update_user_id,
             * 
             */
        ]);

        $query
            ->andFilterWhere(['like', $baseTableName . '.company_name', $this->company_name])
            ->andFilterWhere(['like', $baseTableName . '.customer_address', $this->customer_address])
            ->andFilterWhere(['like', $baseTableName . '.contact_person', $this->contact_person])
            ->andFilterWhere(['like', $baseTableName . '.contact_phone', $this->contact_phone])
            ->andFilterWhere(['like', $baseTableName . '.contact_email', $this->contact_email])
            ->andFilterWhere(['like', $baseTableName . '.apartment_number', $this->apartment_number])
            ->andFilterWhere(['like', $baseTableName . '.street_number', $this->street_number])
            ->andFilterWhere(['like', $baseTableName . '.route', $this->route])
            ->andFilterWhere(['like', $baseTableName . '.sublocality_level_1', $this->sublocality_level_1])
            ->andFilterWhere(['like', $baseTableName . '.sublocality', $this->sublocality])
            ->andFilterWhere(['like', $baseTableName . '.locality', $this->locality])
            ->andFilterWhere(['like', $baseTableName . '.administrative_area_level_1', $this->administrative_area_level_1])
            ->andFilterWhere(['like', $baseTableName . '.country', $this->country])
            ->andFilterWhere(['like', $baseTableName . '.postal_code', $this->postal_code])
            ->andFilterWhere(['like', $baseTableName . '.formated_address', $this->formated_address]);

        return $dataProvider;
    }

}
