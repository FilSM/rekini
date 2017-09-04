<?php

namespace backend\controllers;

use common\controllers\AdminListController;

/**
 * LanguageController implements the CRUD actions for Language model.
 */
class LanguageController extends AdminListController {

    /**
     * Initializes the controller.
     */
    public function init() {
        parent::init();
        $this->defaultModel = 'common\models\Language';
        $this->defaultSearchModel = 'common\models\search\LanguageSearch';   
    }
   
}
