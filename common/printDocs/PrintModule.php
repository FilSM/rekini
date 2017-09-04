<?php

namespace common\printDocs;

use Yii;
use yii\helpers\Url;

use common\models\bill\Bill;
use common\printDocs\template\invoice\BillPDF;

class PrintModule {

    const INVOICES_DIR = '@webroot/print/invoice';
    
    private $docType = '';
    private $templateDir = '';
    private $printData = [];
    private $templateDirectory = '@common/printDocs/templates';
    
    public function __construct($data) {
        if ($data) {
            $this->docType = isset($data['doc-type']) ? $data['doc-type'] : null;
            $this->templateDir = isset($data['template-dir']) ? $data['template-dir'] : null;
            $this->printData = $data;
        }
    }

    public function printDoc() {
        Yii::$app->get('fpdi');

        switch ($this->docType) {
            case 'avans':
            case 'bill':
            case 'cr_bill':
            case 'invoice':
            case 'debt':
            case 'cession':
                return $this->prnInvoice();
                break;            
            default:
                return false;
                break;
        }
    }

    private function prnInvoice() {
        $data = $this->printData;

        $dir = Url::to($this->templateDirectory);
        $invoice = $data['invoice'];
        $language = empty($invoice->language_id) ? Bill::BILL_DEFAULT_PRINT_LANGUAGE : $invoice->language->language;
        $template = $dir . '/' . (isset($this->templateDir) ? $this->templateDir . '/' : '') .'bill-'.$language.'.php';

        if (file_exists($template)) {
            require_once($template);
            $pdf = new BillPDF($data);
            $pdf->buildOutput();
        } else {
            $txt = 'Can`t find the template file: ' . $template;
            //watchdog('zBravo', $txt, array(), WATCHDOG_ERROR);
            trigger_error($txt);
            return false;
        }

        if (!isset($pdf)) {
            //watchdog('zBravo', '$pdf not defined.', array(), WATCHDOG_ERROR);
            return false;
        }
        
        $uploads_dir = $this->checkDir($this::INVOICES_DIR, $this->printData['clientId']);
        if($uploads_dir) {
            $savedFilename = "$uploads_dir/invoice-{$data['doc-key']}.pdf";
            $pdf->Output($savedFilename, (isset($data['mode']) ? $data['mode'] : 'F'));
            unset($pdf);
            $result = $savedFilename;
        }else{
            unset($pdf);
            $result = false;
        }
        return $result;
    }

    static public function checkDir($dir, $id) {
        $dir = Url::to($dir).'/'.$id;
        if (!is_dir($dir)) {
            if(!mkdir($dir, 0777, true)){
                $txt = "Can't to create directory $dir";
                trigger_error($txt);
                return false;
            }
        }
        return $dir;
    }
}
