<?php

namespace common\models\client;

use Yii;

/**
 * This is the model class for table "shareholder".
 *
 * @property integer $id
 * @property integer $deleted
 * @property integer $client_id
 * @property integer $shareholder_id
 * @property string $term_from
 * @property string $term_till
 * @property string $share
 *
 * @property Client $client
 * @property Client $shareholder
 */
class Share extends Shareholder
{
    /**
     * @inheritdoc
     */
    public static function modelTitle($n = 1, $translate = true) {
        return parent::label('client', 'Share|Shares', $n, $translate);
    }  
    
    /** @inheritdoc */
    public function beforeValidate()
    {
        if(!empty($this->client_id) && !empty($this->shareholder_id) && ($this->client_id == $this->shareholder_id)){
            $this->addError('client_id', Yii::t('bill', 'The same clients are selected!'));
            return false;
        }else{
            return parent::beforeValidate();
        }
    }    
}