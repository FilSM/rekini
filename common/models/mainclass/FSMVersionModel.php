<?php

namespace common\models\mainclass;

use yii\helpers\ArrayHelper;

use common\behaviors\SAModelVersioning;

class FSMVersionModel extends FSMCreateUpdateModel {

    protected $_nonVersionFields = [];
    
    public function attributes($withExternal = true) { 
        // add related fields to searchable attributes
        if($withExternal){
            return array_merge(parent::attributes(), $this->getNonVersionFields()); 
        }else{
            return parent::attributes($withExternal); 
        }        
    }
    
    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors = ArrayHelper::merge(
            $behaviors,
            [
                'modelVersioning' => array(
                    'class' => SAModelVersioning::className(),
                ),
            ]
        );
        return $behaviors;
    }
      
    public function getNonVersionFields() {
        $fields = $this->getExternalFields();
        $fields = ArrayHelper::merge(
            $fields,
            (array)$this->_nonVersionFields
        );
        return $fields;       
    }
  
}
