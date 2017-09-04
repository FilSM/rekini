<?php

namespace backend\controllers;

use common\controllers\AdminListController;

/**
 * ValutaController implements the CRUD actions for Valuta model.
 */
class ValutaController extends AdminListController {

    /**
     * Initializes the controller.
     */
    public function init() {
        parent::init();
        $this->defaultModel = 'common\models\Valuta';
        $this->defaultSearchModel = 'common\models\search\ValutaSearch';   
    }   

}