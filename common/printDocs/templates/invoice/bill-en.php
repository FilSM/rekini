<?php

namespace common\printDocs\template\invoice;

use Yii;
use yii\helpers\ArrayHelper;
use NumberFormatter;

use common\components\FilsmPdf\FilsmPdf;

class BillPDF extends FilsmPdf {
    
    private $agreement;
    private $invoice;
    private $firstClient;
    private $secondClient;
    private $firstClientBank;
    private $secondClientBank;
    private $firstClientPerson;
    private $secondClientPerson;
    private $firstClientAddress;
    private $secondClientAddress;
    private $billProducts;

    public function Header() {
        if($this->numpages > 1){
            parent::Header();
            switch ($this->invoice->doc_type){
                default :
                case 'avans':
                    $title = 'Proforma';
                    break;
                case 'bill':
                case 'cr_bill':
                    $title = 'Invoice';
                    break;
                case 'invoice':
                    $title = 'Waybill';
                    break;
            }
            $this->SetTextColorArray($this->headerFontColorArr);
            $this->Cell(0, 0, $title.' No. '.$this->invoice->doc_number, 0, 1, 'R');
            $this->Ln();
            
            if($this->page == 2){
                $this->setPage(1);
                $this->setFooter();
                $this->setPage(2);
            }
        }
    }
    
    public function Footer() {
        if($this->numpages > 1){
            parent::Footer();
        }
    }

    public function buildOutput(array $margins = null) {
        $margins = ArrayHelper::merge($margins, [
            'top' => 10,
            'left' => 20,
            'footer' => 10,
            'right' => 20
        ]);
        parent::buildOutput($margins);

        $this->agreement = $this->printData['agreement'];
        $this->invoice = $this->printData['invoice'];
        $this->firstClient = $this->printData['firstClient'];
        $this->secondClient = $this->printData['secondClient'];
        $this->firstClientBank = $this->printData['firstClientBank'];
        $this->secondClientBank = $this->printData['secondClientBank'];
        $this->firstClientPerson = $this->printData['firstClientPerson'];
        $this->secondClientPerson = $this->printData['secondClientPerson'];
        $this->firstClientAddress = $this->printData['firstClientAddress'];
        $this->secondClientAddress = $this->printData['secondClientAddress'];
        $this->billProducts = $this->printData['billProducts'];

        $this->AddPage();
        $margins = $this->getMargins();
        $pWidth = $this->w - $margins['right'];

        switch ($this->invoice->doc_type){
            default :
            case 'avans':
                $title = 'Proforma';
                break;
            case 'bill':
            case 'cr_bill':
                $title = 'Invoice';
                break;
            case 'invoice':
                $title = 'Waybill';
                break;
        }
        $this->SetFont('freesans', 'B', 20);
        $this->Cell(0, 0, $title.' No. '.$this->invoice->doc_number, 0, 1, 'L');
        $this->SetFont('freesans', '', 12);
        $this->Cell(0, 0, (!empty($this->invoice->doc_date) ? date('F jS, Y', strtotime($this->invoice->doc_date)) : null), 0, 1, 'L');

        $this->SetFont('freesans', 'I', 10);
        $this->Cell(40, 0, 'Payment date:');
        $this->Cell(45, 0, (!empty($this->invoice->doc_date) ? 'due '.date('jS F, Y', strtotime($this->invoice->pay_date)) : null), 0, 1, 'R');
        $this->Cell(40, 0, 'Service period:');
        $this->Cell(45, 0, (!empty($this->invoice->services_period) ? $this->invoice->services_period : ''), 0, 1, 'R');
        
        $pTop = $this->GetY();
        
        $logoModel = $this->firstClient->logo;
        if(!empty($logoModel)){
            $logoPath = $logoModel->uploadedFilePath;
            if (!empty($logoPath) && file_exists($logoPath)) {
                $this->Image($logoPath, $pWidth - 50, $margins['top'], 50, 30, '', '', '', false, 300, 'R', false, false, 0, true);
                //$this->Image($logoPath, $margins['top'], $pWidth - 100, 40, 40, '', '', 'T', false);
                $this->SetY(42);
                $pTop = 42;
            }            
        }else{
            //$pTop = $margins['top'];
        }
        $this->Line($margins['left'], $pTop, $pWidth, $pTop, ['width' => 0.2]);
        $this->Line($margins['left'], $pTop + 1, $pWidth, $pTop + 1, ['width' => 0.5]);

        $this->Ln(5);
        $yClient = $this->GetY();
        
        $this->SetFont('freesans', 'U', 10);
        $this->Cell(85, 0, (isset($this->agreement->firstClientRole) ? $this->agreement->firstClientRole->name.':' : ''), 0, 1, 'L');
        $this->Ln(5);
        
        $this->SetFont('freesans', 'B', 10);
        $this->Cell(85, 0, $this->firstClient->name, 0, 1, 'L');
        
        $this->SetFont('freesans', '', 10);
        $this->Cell(85, 0, 'Registration number: '.$this->firstClient->reg_number, 0, 1, 'L');
        $this->Cell(85, 0, 'Address:', 0, 1, 'L');
        $this->MultiCell(85, 0, $this->firstClientAddress, 0, 'L', false, 1);
        $this->Cell(85, 0, 'VAT No.: '.$this->firstClient->vat_number, 0, 1, 'L');
        $this->Ln(5);

        $this->SetFont('freesans', 'B', 10);
        $this->Cell(85, 0, $this->firstClientBank->bank->name, 0, 1, 'L');
        $this->Cell(85, 0, 'S.W.I.F.T. '.$this->firstClientBank->bank->swift, 0, 1, 'L');
        $this->Cell(85, 0, 'IBAN: '.$this->firstClientBank->account, 0, 1, 'L');
        
        //---------------------------------------------------------------------------------------
        
        $secondClientX = $margins['left'] + 90;
        $this->SetXY($secondClientX, $yClient);
        
        $this->SetFont('freesans', 'U', 10);
        $this->Cell(85, 0, (isset($this->agreement->secondClientRole) ? $this->agreement->secondClientRole->name.':' : ''), 0, 1, 'L');
        $this->Ln(5);
        
        $this->SetFont('freesans', 'B', 10);
        $this->SetX($secondClientX);
        $this->Cell(85, 0, $this->secondClient->name, 0, 1, 'L');
        
        $this->SetFont('freesans', '', 10);
        $this->SetX($secondClientX);
        $this->Cell(85, 0, 'Registration number: '.$this->secondClient->reg_number, 0, 1, 'L');
        $this->SetX($secondClientX);
        $this->Cell(85, 0, 'Address:', 0, 1, 'L');
        //$this->SetX($secondClientX);
        $this->MultiCell(85, 0, $this->secondClientAddress, 0, 'L', false, 1, $secondClientX);
        $this->SetX($secondClientX);
        $this->Cell(85, 0, 'VAT No.: '.$this->secondClient->vat_number, 0, 1, 'L');
        $this->Ln(5);

        //$this->SetFont('freesans', 'B', 10);
        $this->SetX($secondClientX);
        $this->Cell(85, 0, $this->secondClientBank->bank->name, 0, 1, 'L');
        $this->SetX($secondClientX);
        $this->Cell(85, 0, 'S.W.I.F.T. '.$this->secondClientBank->bank->swift, 0, 1, 'L');
        $this->SetX($secondClientX);
        $this->Cell(85, 0, 'IBAN: '.$this->secondClientBank->account, 0, 1, 'L');
        
        $this->Ln(10);
        
        //---------------------------------------------------------------------------------------
        $this->SetFont('freesans', '', 10);

        $invoiceSumma = Yii::$app->formatter->asDecimal(abs((float)$this->invoice->summa), 2, [NumberFormatter::GROUPING_SEPARATOR_SYMBOL => ' ']);
        $invoiceVat = Yii::$app->formatter->asDecimal(abs((float)$this->invoice->vat), 2, [NumberFormatter::GROUPING_SEPARATOR_SYMBOL => ' ']);
        $invoiceTotal = Yii::$app->formatter->asDecimal(abs((float)$this->invoice->total), 2, [NumberFormatter::GROUPING_SEPARATOR_SYMBOL => ' ']);
        $avansText = '';
        if(empty($this->printData['avansSumma'])){
            $sumWords = $this->getSummaToWords($invoiceTotal);
        }else{
            $total = abs((float)($invoiceTotal - $this->printData['avansSumma']));
            $sumWords = $this->getSummaToWords($total);
            $avansText = 
                sprintf(
                    'The invoice was issued on the basis of Proforma %1$s.<br/>'.
                    'Was received prepayment %2$s EUR.<br/>'.
                    'Amount to be paid %3$s EUR.<br/>', 
                    $this->printData['avansNumberList'],
                    Yii::$app->formatter->asDecimal($this->printData['avansSumma'], 2, [NumberFormatter::GROUPING_SEPARATOR_SYMBOL => ' ']),
                    Yii::$app->formatter->asDecimal($total, 2, [NumberFormatter::GROUPING_SEPARATOR_SYMBOL => ' '])
                );
        }
        
        $productRows = '';
        $reversTotal = 0;
        $hasRevers = false;
        if(!empty($this->billProducts)){
            foreach ($this->billProducts as $product) {
                $productName = $product->productName;
                if($this->invoice->according_contract && (count($this->billProducts) == 1)){
                    $productName .= '<br/>Agreement No. '.$this->agreement->number;
                }
                $productRows .= '<tr>';
                $productRows .= '<td style="border: 1px solid black;">'.$productName.'</td>';
                $productRows .= '<td style="border: 1px solid black; text-align: center;">'.(empty($product->measure_id) ? $product->product->measure->name : $product->measure->name).'</td>';
                $productRows .= '<td style="border: 1px solid black; text-align: center;">'.$product->amount.'</td>';
                $productRows .= '<td style="border: 1px solid black; text-align: right;">'.$product->price.'</td>';
                $productRows .= '<td style="border: 1px solid black; text-align: right;">'.($product->revers ? Yii::t('bill', 'revers') : $product->vat).'</td>';
                $productRows .= '<td style="border: 1px solid black; text-align: right;">'.$product->total.'</td>';
                $productRows .= '</tr>';
                
                $hasRevers = $hasRevers || $product->revers;
                $reversTotal += ($product->revers ? $product->summa_vat : 0);
            }
            if($this->invoice->according_contract && (count($this->billProducts) > 1)){
                $productRows .= '<tr>';
                $productRows .= '<td colspan="5" style="border: 1px solid black; text-align: right;">'.\Yii::t('bill', 'Agreement No.').' '.$this->agreement->number.'</td>';
                $productRows .= '<td style="border: 1px solid black;"></td>';
                $productRows .= '</tr>';
            }            
        }
        
        $reversRow = ($hasRevers || !empty($reversTotal) ? 
            '<tr>
                <td colspan="5" style="border: 1px solid white; font-style: italic;">*'.Yii::t('bill', 'VAT reverse charge intra community supply').(!empty($reversTotal) ? ': '. number_format($reversTotal, 2): '').'</td>
            </tr>' : ''
        );
            
        $tableHTML = 
        '<table border="0" cellspacing="0" cellpadding="4">
            <tr style="font-weight: bold; text-align: center; ">
                <th style="width: 45%; border: 1px solid black;">Service</th>
                <th style="width: 10%; border: 1px solid black;">Measure</th>
                <th style="width: 10%; border: 1px solid black;">Amount</th>
                <th style="width: 12%; border: 1px solid black;">Price</th>
                <th style="width: 8%; border: 1px solid black;">VAT %</th>
                <th style="width: 15%; border: 1px solid black;">Summa, EUR</th>
            </tr>'.
            $productRows.
            '<tr style="font-weight: bold; text-align: right;">
                <td colspan="5" style="border-left: 1px solid black; border-right: 1px solid black;">Summa:</td>
                <td style="border: 1px solid black;">'.$invoiceSumma.'</td>
            </tr>
            <tr style="font-weight: bold; text-align: right;">
                <td colspan="5" style="border-left: 1px solid black; border-right: 1px solid black;">VAT:</td>
                <td style="border: 1px solid black;">'.(empty($invoiceVat) && !empty($reversTotal) ? $invoiceVat : '---').'</td>
            </tr>'.
            '<tr style="font-weight: bold; text-align: right;">
                <td colspan="5" style="border-left: 1px solid black; border-right: 1px solid black;">Total:</td>
                <td style="border: 1px solid black;">'.$invoiceTotal.'</td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid black;">'.
                    '<span style="font-weight: bold;">'.
                        $avansText.
                        'Amount in words:'.
                    '</span>'.
                    '<br/>'.
                    $sumWords.
                '</td>
            </tr>'.
            $reversRow.
        '</table>';
        $this->writeHTML($tableHTML, true, false, true, false, '');
        
        if($this->invoice->doc_type == 'invoice'){
            $this->checkPageBreak(30);
            
            $this->SetFont('freesans', 'B', 10);
            $this->Cell(85, 0, $this->invoice->getAttributeLabel('loading_address'), 0, 0, 'L');
            $this->SetX($secondClientX);
            $this->Cell(85, 0, $this->invoice->getAttributeLabel('unloading_address'), 0, 1, 'L');
            
            $yRow = $this->GetY();
            $this->SetFont('freesans', '', 10);
            $this->MultiCell(85, 0, $this->invoice->loading_address, 0, 'L', false, 0);
            $this->Ln();
            $yCol1 = $this->GetY();
            $this->SetY($yRow);
            //$this->SetX($secondClientX);
            $this->MultiCell(85, 0, $this->invoice->unloading_address, 0, 'L', false, 1, $secondClientX);
            $yCol2 = $this->GetY();
            if($yCol1 > $yCol2){
                $this->SetY($yCol1);
            }
            
            $this->SetFont('freesans', 'B', 10);
            $this->Cell(85, 0, $this->invoice->getAttributeLabel('carrier'), 0, 0, 'L');
            $this->SetX($secondClientX);
            $this->Cell(85, 0, $this->invoice->getAttributeLabel('transport'), 0, 1, 'L');
            
            $this->SetFont('freesans', '', 10);
            $this->Cell(85, 0, $this->invoice->carrier, 0, 0, 'L');
            $this->SetX($secondClientX);
            $this->Cell(85, 0, $this->invoice->transport, 0, 1, 'L');
            
            $this->Ln();
        }
        
        $this->checkPageBreak(30);
        
        $this->SetFont('freesans', '', 10);
        $this->Cell(85, 0, (isset($this->firstClientRole) ? $this->firstClientRole->name.':' : ''), 0, 0, 'L');
        $this->SetX($secondClientX);
        $this->Cell(85, 0, (isset($this->secondClientRole) ? $this->secondClientRole->name.':' : ''), 0, 1, 'L');
        $this->Ln(15);
        $this->Line($margins['left'], $this->GetY(), 80 + $margins['left'], $this->GetY(), ['width' => 0.2]);
        $this->Line($secondClientX, $this->GetY(), $pWidth, $this->GetY(), ['width' => 0.2]);
        
        $this->SetFont('freesans', 'I', 10);
        $yClient = $this->GetY();
        $this->Cell(85, 0, (!empty($this->firstClientPerson->id) ? 
                $this->firstClientPerson->first_name . ' ' . $this->firstClientPerson->last_name : ''), 
            0, 1, 'C');
        $this->Cell(85, 0, (!empty($this->firstClientPerson->id) && !empty($this->firstClientPerson->position_id) ? 
                $this->firstClientPerson->position->name : ''), 
            0, 1, 'C');
        
        $this->SetXY($secondClientX, $yClient);
        $this->Cell(85, 0, (!empty($this->secondClientPerson->id) ? 
                $this->secondClientPerson->first_name . ' ' . $this->secondClientPerson->last_name : ''), 
            0, 1, 'C');
        $this->SetX($secondClientX);
        $this->Cell(85, 0, (!empty($this->secondClientPerson->id) && !empty($this->secondClientPerson->position_id) ? 
                $this->secondClientPerson->position->name : ''), 
            0, 1, 'C');
        
        /*
        $template = dirname(__FILE__) . '/invoice.pdf';
        $pageCount = $this->setSourceFile($template);
        $tplIdx = $this->importPage(1, '/MediaBox');
        $this->useTemplate($tplIdx);
         * 
         */

    }

}