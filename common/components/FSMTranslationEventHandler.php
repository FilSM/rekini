<?php

namespace common\components;

use Yii;
use yii\i18n\MissingTranslationEvent;
use yii\db\Query;

use lajax\translatemanager\models\LanguageSource;

class FSMTranslationEventHandler {

    public static function handleMissingTranslation(MissingTranslationEvent $event) {
        /*
        if($event->language == $event->sender->sourceLanguage){
            $event->translatedMessage = $event->message;
            return;
        }
         * 
         */
        
        static $command = null;
        static $query = null;
        
        if(!isset($query)){
            $query = new Query;
        }        
        $query
            ->select('*')
            ->where([
                'category' => $event->category,
                'message' => $event->message,
            ])
            ->from(LanguageSource::tableName());
        $select = $query->createCommand();
        $data = $select->queryAll();
        if(empty($data)){
            if(!isset($command)){
                $db = Yii::$app->db;
                $command = $db->createCommand();
            }
            $command->insert(LanguageSource::tableName(), [
                'category' => $event->category,
                'message' => $event->message,
                'from_variable' => 1,
            ])->execute();
        }

        $event->translatedMessage = "@MISSING: {$event->category}.{$event->message} FOR LANGUAGE {$event->language} @";
    }

}
