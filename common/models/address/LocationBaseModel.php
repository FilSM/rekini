<?php

namespace common\models\address;

use Yii;
use yii\helpers\ArrayHelper;

class LocationBaseModel extends \common\models\mainclass\FSMBaseModel {

    public function getIdByGMapData($data, $byField = 'name') {
        if(empty($data[$byField])){
            return null;
        }
        
        $one = $this->findOne([$byField => $data[$byField]]);
        if(!$one){
            $this->setAttributes($data);
            if (!$this->save()) {
                $message = \Yii::t('location', $this->modelTitle().' not inserted due to validation error.');
                Yii::$app->getSession()->setFlash('error', $message);
                Yii::error($message, __METHOD__);
                return [
                    'address_state' => 'error',
                    'message' => $message,
                ];
            }
            return $this->id;
        }else{
            return $one->id;
        }
    } 
    
}
