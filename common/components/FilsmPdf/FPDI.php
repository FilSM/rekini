<?php

namespace common\components\FilsmPdf;

use Yii;
use yii\base\Component;

/* *
 *  Component to load TCPDF Libraries  *
 *                                     */

class FPDI extends Component {

    public function __construct() {
        require_once(dirname(__FILE__) . '/tcpdf/tcpdf.php');
        require_once(dirname(__FILE__) . '/tcpdf/fpdi.php');
    }

}
