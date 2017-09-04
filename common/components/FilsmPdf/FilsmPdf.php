<?php

namespace common\components\FilsmPdf;

use Yii;

class FilsmPdf extends \FPDI {

    private $last_page_flag = false;
    
    protected $footerWithContacts = false;
    protected $headerWithLogo = false;
    protected $headerFontColorArr = [153,153,153];
    protected $printData = array();
    
    private function checkPageFooter($y) {
        return $this->checkPageBreak(0, $y);
    }

    private function _footer() {
        if ($this->last_page_flag) {
            /*
             * 
             * 
             */
        }
        $this->SetFontSize(6);
        
        $this->Cell(60, 0, 'Eventus Corporate Finance, SIA', 'T', 0, 'L');
        $this->Cell(60, 0, 'Reg.Nr. 40103625012', 0, 0, 'L');
        $this->Cell(60, 0, 'VAT Nr. LV40103625012', 0, 0, 'L');
        
        $this->Cell(60, 0, 'MEINL BANK', 'T', 1, 'R');
        $this->Cell(60, 0, 'SWIFT: MEINATWW', 0, 1, 'R');
        $this->Cell(60, 0, 'IBAN: AT421924000000578294', 0, 1, 'R');
        
        $this->Cell(60, 0, 'Legal address: 202, Georgiou Gennadiou - 10, Limassol, 3041, Cyprus', 0, 0, 'L');
        $this->Cell(60, 0, '', 0, 1, 'R');
        
        $this->Cell(60, 0, 'Actual address: 58a Bauskas str., 5th floor, LV-1004, Riga, Latvia', 0, 0, 'L');
        $this->Cell(60, 0, '', 0, 1, 'R');
        
        $this->Cell(70, 0, 'Tel. +371 671 033 31', 'T', 0, 'C');
        $this->Cell(70, 0, 'info@eventus.lv', 0, 0, 'C');
        $this->Cell(70, 0, 'www.eventus.lv', 0, 0, 'C');
        $this->Cell(70, 0, '', 0, 0, 'C');
        $this->Cell(70, 0, '', 0, 0, 'C');
    }
    
    private function _header() {
        //Logo
        $image_file = K_PATH_IMAGES . 'logo-print.png';
        $this->Image($image_file, 154);
    }
    
    protected function getSummaToWords($sum = null, $locale = ''){
        if(!$sum){
            return '';
        }
        if(empty($locale)){
            $locale = Yii::$app->language;
        }
        list($sumInt, $sumFloor) = preg_split("/[.,]/", (string)$sum);
        $oldLng = Yii::$app->language;
        Yii::$app->language = $locale;
        $sumWords = Yii::$app->formatter->asSpellout($sumInt);
        $sumWords = ucfirst($sumWords).
            Yii::t('common', '{count, plural, =0{ euros} =1{ euro} other{ euros}}', ['count' => $sumInt]).', '.
            (!empty($sumFloor) ? $sumFloor : '00').
            Yii::t('common', '{count, plural, =0{ cents} =1{ cent} other{ cents}}', ['count' => $sumFloor]);
        Yii::$app->language = $oldLng;
        return $sumWords;
    }
    
    public function __construct($data) {
        parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->printData = $data;
    }

    public function Close() {
        $this->last_page_flag = true;
        parent::Close();
    }

    public function Header() {
        parent::Header();
        if($this->headerWithLogo){
            $this->_header();
        }
    }

    public function Footer() {
        if($this->footerWithContacts){
            $this->_footer();
        }

        $this->SetFontSize(10);
        parent::Footer();
        //$this->Ln(1);
        //$this->Cell(0, 0, 'Lapa Nr.'.$this->getPage(), 0, 1, 'C');
    }

    public function buildOutput(array $margins = null) {
        // set default header data
        $this->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, '', '', $this->headerFontColorArr, $this->headerFontColorArr);
        $this->setFooterData($this->headerFontColorArr, $this->headerFontColorArr);

        // set header and footer fonts
        $this->setHeaderFont(Array('freesans', 'b', PDF_FONT_SIZE_MAIN));
        $this->setFooterFont(Array('freesans', '', PDF_FONT_SIZE_DATA));
        
        $this->SetFontSize(10);

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $this->SetMargins(
                (isset($margins['left']) ? $margins['left'] : 10), 
                (isset($margins['top']) ? $margins['top'] : 20), 
                (isset($margins['right']) ? $margins['right'] : PDF_MARGIN_RIGHT),
                true
        );
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin((isset($margins['footer']) ? $margins['footer'] : 20));

        // set auto page breaks
        $this->SetAutoPageBreak(true, (isset($margins['footer']) ? $margins['footer'] : PDF_MARGIN_FOOTER));

        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set cell padding
        $this->setCellPaddings(3, 1, 3, 1);
    }

}