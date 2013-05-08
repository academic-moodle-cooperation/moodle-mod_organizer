<?php
// This plugin is for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
//
// this file contains all the functions that aren't needed by core moodle
// but start becoming required once we're actually inside the assignment module.

require_once($CFG->dirroot . "/config.php");

require_once($CFG->libdir . '/pdflib.php');

/**
 * @author Andreas Windbichler
 * @version 21.03.2012:1
 *
 */
class MTablePDF extends pdf{
    const portrait = 'P';
    const landscape = 'L';

    const fontsize_small = 8;
    const fontsize_medium = 10;
    const fontsize_large = 12;

    private $orientation = MTablePDF::portrait;
    private $rowsperpage = 0;
    private $fontsize = MTablePDF::fontsize_medium;
    private $showheaderfooter = true;

    private $columnwidths = array();
    private $titles = NULL;
    private $columnformat;

    private $data = array();

    public function __construct($orientation,$columnwidths){
        parent::__construct($orientation);

        // Set default configuration.
        $this->SetCreator('TUWEL');
        $this->SetMargins(10, 20, 10);
        $this->SetFont('freesans', '');

        $this->columnwidths = $columnwidths;

        $this->columnformat = array();
        for($i=0;$i<count($columnwidths);$i++){
            $this->columnformat[] = array();
            $this->columnformat[$i][] = array("fill"=>0,"align"=>"L");
            $this->columnformat[$i][] = array("fill"=>1,"align"=>"L");
        }
    }

    public function setColumnFormat($columnformat){
        if(count($columnformat) != count($this->columnwidths)){
            echo "Error: Columnformat (" . count($columnformat) . ") count doesnt match column count (" . count($this->columnwidths) . ")";
            exit();
        }

        $this->columnformat = array_merge($this->columnformat,$columnformat);
    }

    /**
     * Set the texts for the header of the pdf
     * @param unknown $title1
     * @param unknown $desc1
     * @param unknown $title2
     * @param unknown $desc2
     * @param unknown $title3
     * @param unknown $desc3
     * @param unknown $title4
     * @param unknown $desc4
     * @param unknown $title5
     * @param unknown $desc5
     * @param unknown $title6
     * @param unknown $desc6
     */
    public function setHeaderText($title1,$desc1,$title2,$desc2,$title3,$desc3,
            $title4,$desc4,$title5,$desc5,$title6,$desc6){
        $this->header = array($title1,$desc1,$title2,$desc2,$title3,$desc3,
                $title4,$desc4,$title5,$desc5,$title6,$desc6);
    }

    public function Header() {
        // Set font.
        $this->SetFont('', '');
        // Title.

        $header = $this->header;

        if ($this->showheaderfooter) {

            $pagewidth = $this->getPageWidth();
            $scale = $pagewidth / 200;
            $oldfontsize = $this->getFontSize();
            $this->setFontSize('12');

            // First row.
            $border = 0;
            $height = 4;
            $this->SetFont('', 'B');
            $this->Cell(/*8*/15 * $scale, $height, $header[0], $border, false, 'L', 0, '', 1, false/*, 'M', 'M'*/);
            $this->SetFont('', '');
            $this->Cell(31 * $scale, $height, $header[1], $border, false, 'R', 0, '', 1, false/*, 'M', 'M'*/);
            $this->Cell(/*8*/15 * $scale, $height, "", $border, false, 'C', 0, '', 1, false/*, 'M', 'M'*/);

            $this->SetFont('', 'B');
            $this->Cell(21 * $scale, $height, $header[2], $border, false, 'L', 0, '', 1, false/*, '1', '0'*/);
            $this->SetFont('', '');

            $this->SetFont('', '');
            $this->Cell(41 * $scale, $height, $header[3], $border, false, 'R', 0, '', 1, false/*, '1', '0'*/);
            $this->Cell(/*8*/15 * $scale, $height, "", $border, false, 'C', 0, '', 1, false/*, '1', '0'*/);

            $this->SetFont('', 'B');
            $this->Cell(15 * $scale, $height, $header[4], $border, false, 'L', 0, '', 1, false/*, '1', '0'*/);
            $this->SetFont('', '');
            $this->Cell(31 * $scale, $height, $header[5], $border, false, 'R', 0, '', 1, false/*, '1', '0'*/);

            $this->Ln();

            // Second row.
            $height = 4;

            $this->SetFont('', 'B');
            $this->Cell(/*8*/15 * $scale, $height, $header[6], $border, false, 'L', 0, '', 1, false);

            $this->SetFont('', '');
            $this->Cell(31 * $scale, $height, $header[7], $border, false, 'R', 0, '', 1, false/*, '1', '0'*/);
            $this->Cell(/*8*/15 * $scale, $height, "", $border, false, 'C', 0, '', 1, false/*, '1', '0'*/);

            $this->SetFont('', 'B');
            $this->Cell(21 * $scale, $height, $header[8], $border, false, 'L', 0, '', 1, false/*, '1', '0'*/);
            $this->SetFont('', '');

            $this->SetFont('', '');
            $this->Cell(41 * $scale, $height, $header[9], $border, false, 'R', 0, '', 1, false/*, '1', '0'*/);

            $this->Cell(/*8*/15 * $scale, $height, "", $border, false, 'C', 0, '', 1, false/*, '1', '0'*/);

            $this->SetFont('', 'B');
            $this->Cell(15 * $scale, $height, $header[10], $border, false, 'L', 0, '', 1, false/*, '1', '0'*/);
            $this->SetFont('', '');
            $this->Cell(31 * $scale, $height, $header[11], $border, false, 'R', 0, '', 1, false/*, '1', '0'*/);

            $this->Ln();
            $this->SetFontSize($oldfontsize);
        }
    }

    /**
     * Sets the titles for the columns in the pdf
     * @param String $titles
     */
    public function setTitles($titles){
        if(count($titles) != count($this->columnwidths)){
            echo "Error: Title count doesnt match column count";
            exit();
        }

        $this->titles = $titles;
    }

    /**
     * $orientation 'P' = Portrait, 'L' = Landscape
     * @param Char $orientation
     * @return true if ok
     */
    public function setOrientation($orientation){
        if($orientation == 'P' || $orientation == 'L'){
            $this->orientation = $orientation;
            return true;
        }

        return false;
    }
    /**
    * Defines how many rows are printed on each page
    * @param int $i > 0
    * @return true if ok
    */
    public function setRowsperPage($rowsperpage){
        if (is_number($rowsperpage) && $rowsperpage > 0) {
            $this->rowsperpage = $rowsperpage;
            return true;
        }

        return false;
    }

    /**
     * Adds a row to the pdf
     * @param array $row
     * @return boolean
     */
    public function addRow($row){
        if(count($row) != count($this->columnwidths)){

            var_dump($row);
            echo "Error: number of columns from row (" . count($row) . ") doenst match the number defined (" . count($this->columnwidths) . ")";
            return false;
        }

        $fastmode = false;
        foreach($row as $r){
            if(!is_null($r) && !is_array($r)){
                $fastmode = true;
            }
        }

        if($fastmode){
            //fast mode
            $tmp = array();

            foreach($row as $idx => $value){
                if(is_array($value)){
                    echo "Error: if you want to add a row using the fast mode, you cannot pass me an array";
                    exit();
                }

                $tmp[] = array("rowspan"=>0,"data"=>$value);
            }

            $row = $tmp;
        }else{
            foreach($row as $idx => $value){
                if(!is_array($value)){
                    $row[$idx] = array("rowspan"=>0,"data"=> $value);
                }else if(!isset($value["data"])){
                    echo "Error: you need to set a value for [\"data\"]";
                    exit();
                }else{
                    if(!isset($value["rowspan"])){
                        $row[$idx]["rowspan"] = 0;
                    }
                }
            }
        }

        $this->data[] = $row;

        return true;
    }

    /**
     * Sets the font size
     * @param unknown $fontsize
     * @param string $out
     */
    public function SetFontSize($fontsize, $out=true) {
        parent::SetFontSize($fontsize, $out);
    }

    /**
     * Define if the header and footer should be printed
     * @param unknown $showheaderfooter
     */
    public function ShowHeaderFooter($showheaderfooter){
        $this->showheaderfooter = $showheaderfooter;
    }

    /**
     * Generate the pdf
     */
    public function generate(){
        $pdf = $this;

        // Add a page.
        $pdf->setDrawColor(0);
        $pdf->AddPage();

        // calcuate column widths
        $sum_fix = 0;
        $sum_relativ = 0;

        $rowspans = array();
        $allfixed = true;
        $sum = 0;

        foreach($this->columnwidths as $idx => $width){
            $rowspans[] = 0;

            $sum += $width['value'];

            if($width["mode"]=="Fixed"){
                $sum_fix += $width['value'];
            }else if($width["mode"] == "Relativ"){
                $sum_relativ += $width['value'];
                $allfixed = false;
            }else{
                echo "ERROR: unvalid columnwidth format";
                var_dump($width);
                exit();
            }
        }

        $w = array();
        foreach($this->columnwidths as $idx => $width){
            if($allfixed){
                $w[$idx] = round(
                        ($pdf->getPageWidth()-20)/$sum*$width['value']);
            }else if($width["mode"] == "Fixed"){
                $w[$idx] = $width['value'];
            }else{
                $w[$idx] = round(
                        ($pdf->getPageWidth()-20-$sum_fix)/$sum_relativ*$width['value']);
            }
        }

        { // print table header
            if (isset($this->theadMargins['top'])) {
                // Restore the original top-margin.
                $this->tMargin = $this->theadMargins['top'];
                $this->pagedim[$this->page]['tm'] = $this->tMargin;
                $this->y = $this->tMargin;
            }

            $header = $this->titles;

            if (!empty($header)) {
                // Set margins.
                $prev_lMargin = $this->lMargin;
                $prev_rMargin = $this->rMargin;
                $this->lMargin = $this->pagedim[$this->page]['olm'];
                $this->rMargin = $this->pagedim[$this->page]['orm'];

                // Colors, line width and bold font.
                $this->SetFillColor(0xc0, 0xc0, 0xc0);
                $this->SetTextColor(0);
                $this->setDrawColor(0);
                $this->SetLineWidth(0.3);
                $this->SetFont('', 'B');
                // Header.

                foreach ($header as $key => $value) {
                    if (!isset($this->align[$key])) {
                        $this->align[$key] = 'C';
                    }
                    $this->Cell($w[$key], 7, $value, 1, 0, $this->align[$key], 1, null, '1', 0);
                }
                $this->Ln();
            }
            // Set new top margin to skip the table headers.
            if (!isset($this->theadMargins['top'])) {
                $this->theadMargins['top'] = $this->tMargin;
            }
            $this->tMargin = $this->y;
            $this->pagedim[$this->page]['tm'] = $this->tMargin;
            $this->lasth = 0;

            // Color and font restoration.
            $this->SetFillColor(0xe8, 0xe8, 0xe8);
            $this->SetTextColor(0);
            $this->SetFont('');
        }

        // Color and font restoration.
        $pdf->SetFillColor(0xe8, 0xe8, 0xe8);
        $pdf->SetTextColor(0);
        $pdf->SetFont('');

        // Data.
        $fill = 0;

        foreach ($this->data as $rownum => $row) {

            if($this->rowsperpage && $rownum != 0 && $rownum % $this->rowsperpage == 0){
                $pdf->addPage();
            }

            if ($rownum == count($this->data)-1) {
                $bottomborder = 'B';
            } else {
                $bottomborder = '';
            }

            $numlines = 1;
            $colspan = 0;
            foreach ($row as $key => $value) {
                $width = 0;
                for($i=$key;$i<=$key+$colspan;$i++){
                    $width+=$w[$i];
                }

                $numlines = max($numlines, $this->getNumLines($value['data'], $width));
            }

            foreach ($row as $key => $value) {

                $cf = $this->columnformat[$key];
                $cf = $cf[$rownum % count($cf)];

                $colspan = 0;
                $width = 0;
                for($i=$key;$i<=$key+$colspan;$i++){
                    $width+=$w[$i];
                    $row[$i]['rowspan'] = $row[$key]['rowspan']+1;
                }

                $colspan = 0;
                $width = 0;
                for($i=$key;$i<=$key+$colspan;$i++){
                    $width+=$w[$i];
                }

                if(!is_null($value['data'])){
                    $bottomborder = 'B';
                    if($value['rowspan'] > 0){
                        $rowspans[$key] = $value['rowspan'];
                    }

                    $cellsize = $pdf->FontSizePt/2;

                    $pdf->MultiCell($width, max(($value['rowspan']+1),$numlines) * $cellsize, $value['data'], 'LR'.$bottomborder, $cf['align'], $cf['fill'], 0, '', '', true, '0');
                }else{
                    if($rowspans[$key] > 0){
                        $pdf->MultiCell($width, max(($value['rowspan']+1),$numlines) * $cellsize, "", 'LR'.$bottomborder, 0, $cf['align'], $cf['fill'], 0, '', '', true, '0');
                        $rowspans[$key] = $rowspans[$key]-1;
                    }
                }
            }
            $pdf->Ln();
            $fill=!$fill;
        }

        $pdf->Output();
    }
}

/*
$cellwidth = array();
$cellwidth[] = array("mode"=>'Fixed', "value"=>"20");
$cellwidth[] = array("mode"=>'Fixed', "value"=>"40");
$cellwidth[] = array("mode"=>'Fixed', "value"=>"25");
$cellwidth[] = array("mode"=>'Fixed', "value"=>"25");
$cellwidth[] = array("mode"=>'Relativ', "value"=>"25");
$cellwidth[] = array("mode"=>'Relativ', "value"=>"75");


$pdf = new MTablePDF(MTablePDF::portrait, $cellwidth);
//orientation: MTablePDF::portrait or MTablePDF::landscape

$columnformat = array();
$columnformat[] = array();
$columnformat[0][] = array("align"=>"C");
$columnformat[] = array();
$columnformat[1][] = array("align"=>"C");
$columnformat[] = array();
$columnformat[2][] = array("align"=>"C");
$columnformat[] = array();
$columnformat[3][] = array("align"=>"C");
$columnformat[] = array();
$columnformat[4][] = array("align"=>"L");
$columnformat[] = array();
$columnformat[5][] = array("align"=>"R","fill"=>true);
$columnformat[5][] = array("align"=>"C","fill"=>false);
$columnformat[5][] = array("align"=>"L","fill"=>false);

$pdf->setColumnFormat($columnformat);

$pdf->setHeaderText("title1","desc1","title2","desc2","title3","desc3",
        "title4","desc4","title5","desc5","title6","desc6");

// default is true (so no need for that)
$pdf->ShowHeaderFooter(true);

//  |--------------------------------------------------|
//  | $title1 $desc1   $title2 $desc2   $title3 $desc3 |
//  | $title4 $desc4   $title5 $desc5   $title6 $desc6 |
//  |--------------------------------------------------|

$titles = array();
$titles[] = "First name/ Surename";
$titles[] = "Student ID";
$titles[] = "Last modified (Submission)";
$titles[] = "Last modified (Grade)";
$titles[] = "Grade";
$titles[] = "Comment";

$pdf->setTitles($titles);

// Full mode: for advanced configuration
$row = array();
$row[] = array("rowspan"=>0, "data"=>"1026000");
$row[] = array("rowspan"=>0, "data"=>"Max Mustermann");
$row[] = array("rowspan"=>0, "data"=>"01.01.2013");
$row[] = array("rowspan"=>0, "data"=>"01.01.2013");
$row[] = array("rowspan"=>0, "data"=>"50/100");
$row[] = array("rowspan"=>0, "data"=>"Lorem Ipsum");

$pdf->addRow($row);

// only need to change data if everything else stays the same
$row[0]['data'] = "1026001";
$row[1]['data'] = "Michaela Musterfrau";
$row[2]['data'] = "01.01.2013";
$row[3]['data'] = "01.01.2013";
$row[4]['data'] = "50/100";
$row[5]['data'] = "Dolor sit ammet!";

$pdf->addRow($row);

// Fast mode: rowspan:0, values used as data
$row = array("1226475","Max Mustermann","21.01.2013","21.01.2013","100/100","Woha");
$pdf->addRow($row);


// only need to change data if everything else stays the same
$row = array();
$row[] = array("rowspan"=>0, "data"=>"1026000");
$row[] = array("rowspan"=>0, "data"=>"Max Mustermann");
$row[] = array("rowspan"=>0, "data"=>"01.01.2013");
$row[] = array("rowspan"=>0, "data"=>"01.01.2013");
$row[] = array("rowspan"=>0, "data"=>"50/100");
$row[] = array("rowspan"=>0, "data"=>"Lorem Ipsum");

$pdf->addRow($row);

// Rowspan example
$row = array();
$row[] = array("rowspan"=>1, "data"=>"1026000");
$row[] = array("rowspan"=>1, "data"=>"Max Mustermann");
$row[] = array("rowspan"=>0, "data"=>"01.01.2013");
$row[] = array("rowspan"=>0, "data"=>"01.01.2013");
$row[] = array("rowspan"=>0, "data"=>"50/100");
$row[] = array("rowspan"=>0, "data"=>"Lorem Ipsum");

$pdf->addRow($row);

$row[0] = null;
$row[1] = null;

$pdf->addRow($row);

$row = array();
$row[] = array("rowspan"=>2, "data"=>"1026000");
$row[] = array("rowspan"=>2, "data"=>"Max Mustermann");
$row[] = array("rowspan"=>0, "data"=>"01.01.2013");
$row[] = array("rowspan"=>0, "data"=>"01.01.2013");
$row[] = array("rowspan"=>0, "data"=>"50/100");
$row[] = array("rowspan"=>0, "data"=>"Lorem Ipsum");

$pdf->addRow($row);

$row[0] = null;
$row[1] = null;

$pdf->addRow($row);
$pdf->addRow($row);

$row = array();
$row[] = array("rowspan"=>2, "data"=>"1026000");
$row[] = array("rowspan"=>2, "data"=>"Max Mustermann");
$row[] = array("rowspan"=>0, "data"=>"01.01.2013");
$row[] = array("rowspan"=>0, "data"=>"01.01.2013");
$row[] = array("rowspan"=>0, "data"=>"50/100");
$row[] = array("rowspan"=>0, "data"=>"Lorem Ipsum");

$pdf->addRow($row);

$row[0] = null;
$row[1] = null;

$pdf->addRow($row);
$pdf->addRow($row);

$pdf->generate();
exit();
*/