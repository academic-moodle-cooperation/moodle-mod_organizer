<?php
// This file is part of mtablepdf for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * mtablepdf.php
 *
 * @package       mtablepdf
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

require_once($CFG->libdir . '/pdflib.php');

/**
 * @author Andreas Windbichler
 * @version 25.06.2015
 *
 */
class MTablePDF extends pdf{
    const PORTRAIT = 'P';
    const LANDSCAPE = 'L';

    const FONTSIZE_SMALL = 8;
    const FONTSIZE_MEDIUM = 10;
    const FONTSIZE_LARGE = 12;

    const OUTPUT_FORMAT_PDF = 0;
    const OUTPUT_FORMAT_XLSX = 1;
    const OUTPUT_FORMAT_XLS = 2;
    const OUTPUT_FORMAT_ODS = 3;
    const OUTPUT_FORMAT_CSV_COMMA = 4;
    const OUTPUT_FORMAT_CSV_TAB = 5;

    private $outputformat = self::OUTPUT_FORMAT_PDF;

    private $orientation = self::PORTRAIT;
    private $rowsperpage = 0;
    private $fontsize = self::FONTSIZE_MEDIUM;
    private $showheaderfooter = true;

    private $columnwidths = array();
    private $titles = null;
    private $columnformat;
    private $headerformat = array('title' => array(), 'desc' => array());

    private $data = array();

    public function __construct($orientation, $columnwidths) {
        parent::__construct($orientation);

        // Set default configuration.
        $this->SetCreator('TUWEL');
        $this->SetMargins(10, 20, 10, true);
        $this->setHeaderMargin(7);
        $this->SetFont('freesans', '');
        $this->columnwidths = $columnwidths;

        $this->orientation = $orientation;
        $this->columnformat = array();
        for ($i = 0; $i < count($columnwidths); $i++) {
            $this->columnformat[] = array();
            $this->columnformat[$i][] = array("fill" => 0, "align" => "L");
            $this->columnformat[$i][] = array("fill" => 1, "align" => "L");
        }
    }

    public function setcolumnformat($columnformat) {
        if (count($columnformat) != count($this->columnwidths)) {
            print_error("Columnformat (" . count($columnformat) . ") count doesnt match " .
                "column count (" . count($this->columnwidths) . ")");
        }

        $this->columnformat = array_merge($this->columnformat, $columnformat);
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
    public function setheadertext($title1, $desc1, $title2, $desc2, $title3, $desc3,
            $title4, $desc4, $title5, $desc5, $title6, $desc6) {
        $this->header = array($title1, $desc1, $title2, $desc2, $title3, $desc3,
                $title4, $desc4, $title5, $desc5, $title6, $desc6);
    }

    public function header() {
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
     * If showheaderfooter is selected
     * Displays the number and total number of pages in the footer
     */
    public function footer() {
        if ($this->showheaderfooter) {
            // Set font.
            $this->SetFont('', '');

            // Position at 15 mm from bottom.
            $this->SetY(-15);

            // Page number.
            $this->Cell(0, 10, $this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
        }
    }

    /**
     * Sets the titles for the columns in the pdf
     * @param String $titles
     */
    public function settitles($titles) {
        if (count($titles) != count($this->columnwidths)) {
            print_error("Error: Title count doesnt match column count");
            exit();
        }

        $this->titles = $titles;
    }

    /**
     * $orientation 'P' = Portrait, 'L' = Landscape
     * @param Char $orientation
     * @return true if ok
     */
    public function setorientation($orientation) {
        if ($orientation == 'P' || $orientation == 'L') {
            $this->orientation = $orientation;
            return true;
        }

        return false;
    }

    public function setoutputformat($format) {
        $this->outputformat = $format;
    }

    /**
     * Defines how many rows are printed on each page
     * @param int $i > 0
     * @return true if ok
     */
    public function setrowsperpage($rowsperpage) {
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
    public function addrow($row) {
        if (count($row) != count($this->columnwidths)) {
            print_error("number of columns from row (" . count($row) . ") doenst match " .
                "the number defined (" . count($this->columnwidths) . ")");
            return false;
        }

        $fastmode = false;
        foreach ($row as $r) {
            if (!is_null($r) && !is_array($r)) {
                $fastmode = true;
            }
        }

        if ($fastmode) {
            // Fast mode.
            $tmp = array();

            foreach ($row as $idx => $value) {
                if (is_array($value)) {
                    print_error("Error: if you want to add a row using the fast mode, you cannot pass me an array");
                }

                $tmp[] = array("rowspan" => 0, "data" => $value);
            }

            $row = $tmp;
        } else {
            foreach ($row as $idx => $value) {
                if (!is_array($value)) {
                    $row[$idx] = array("rowspan" => 0, "data" => $value);
                } else if (!isset($value["data"])) {
                    print_error("Error: you need to set a value for [\"data\"]");
                    exit();
                } else {
                    if (!isset($value["rowspan"])) {
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
    public function setfontsize($fontsize, $out=true) {
        if ($fontsize <= self::FONTSIZE_SMALL) {
            $fontsize = self::FONTSIZE_SMALL;
        } else if ($fontsize > self::FONTSIZE_SMALL && $fontsize < self::FONTSIZE_LARGE) {
            $fontsize = self::FONTSIZE_MEDIUM;
        } else if ($fontsize >= self::FONTSIZE_LARGE) {
            $fontsize = self::FONTSIZE_LARGE;
        }

        $this->fontsize = $fontsize;

        parent::SetFontSize($fontsize, $out);
    }

    /**
     * Define if the header and footer should be printed
     * @param unknown $showheaderfooter
     */
    public function showheaderfooter($showheaderfooter) {
        $this->showheaderfooter = $showheaderfooter;
    }

    /*
     * Generate the file.
     * */
    public function generate($filename = '') {

        if ($filename == '') {
            $filename = userdate(time());
        }

        $filename = clean_filename($filename);

        switch($this->outputformat) {
            case self::OUTPUT_FORMAT_XLS:
                $this->get_xls($filename);
                break;
            case self::OUTPUT_FORMAT_XLSX:
                $this->get_xlsx($filename);
                break;
            case self::OUTPUT_FORMAT_ODS:
                $this->get_ods($filename);
                break;
            case self::OUTPUT_FORMAT_CSV_COMMA:
                $this->get_csv($filename, ';');
                break;
            case self::OUTPUT_FORMAT_CSV_TAB:
                $this->get_csv($filename);
                break;
            default:
                $this->get_pdf($filename);
        }
    }

    /**
     * Generate pdf
     */
    private function get_pdf($filename) {
        $pdf = $this;

        // Add a page.
        $pdf->setDrawColor(0);
        $pdf->AddPage();

        // Calcuate column widths.
        $sumfix = 0;
        $sumrelativ = 0;

        $rowspans = array();
        $allfixed = true;
        $sum = 0;

        foreach ($this->columnwidths as $idx => $width) {
            $rowspans[] = 0;

            $sum += $width['value'];

            if ($width["mode"] == "Fixed") {
                $sumfix += $width['value'];
            } else if ($width["mode"] == "Relativ") {
                $sumrelativ += $width['value'];
                $allfixed = false;
            } else {
                print_error("ERROR: unvalid columnwidth format");
                exit();
            }
        }

        $w = array();
        foreach ($this->columnwidths as $idx => $width) {
            if ($allfixed) {
                $w[$idx] = round(
                        ($pdf->getPageWidth() - 20) / $sum * $width['value']);
            } else if ($width["mode"] == "Fixed") {
                $w[$idx] = $width['value'];
            } else {
                $w[$idx] = round(
                        ($pdf->getPageWidth() - 20 - $sumfix) / $sumrelativ * $width['value']);
            }
        }

        // Print table header.
        if (isset($this->theadMargins['top'])) {
            // Restore the original top-margin.
            $this->tMargin = $this->theadMargins['top'];
            $this->pagedim[$this->page]['tm'] = $this->tMargin;
            $this->y = $this->tMargin;
        }

        $header = $this->titles;

        if (!empty($header)) {
            // Set margins.
            $prevlmargin = $this->lMargin;
            $prevrmargin = $this->rMargin;
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

        // Color and font restoration.
        $pdf->SetFillColor(0xe8, 0xe8, 0xe8);
        $pdf->SetTextColor(0);
        $pdf->SetFont('');

        // Data.
        $fill = 0;

        $rowheights = array();

        // Calculate line heights for not rowspanned fields.
        foreach ($this->data as $rownum => $row) {
            $maxnumlines = 1;

            foreach ($row as $key => $value) {
                if ($value['rowspan'] == 0 && !is_null($value['data'])) {
                    $this->data[$rownum][$key]['numlines'] = $this->getNumLines($value['data'], $w[$key]);
                    $maxnumlines = max($maxnumlines, $this->data[$rownum][$key]['numlines']);
                }
            }

            $rowheights[$rownum] = $maxnumlines;
        }

        // Add heights to rows for fields wich are rowspanned but still need more space.
        foreach ($this->data as $rownum => $row) {
            foreach ($row as $key => $value) {
                if ($value['rowspan'] != 0 && !is_null($value['data'])) {
                    $lineheight = $this->getNumLines($value['data'], $w[$key]);

                    $lines = 0;
                    for ($i = $rownum; $i <= $rownum + $value['rowspan']; $i++) {
                        $lines += $rowheights[$i];
                    }

                    if ($lineheight > $lines) {
                        $rowheights[$rownum] += $lineheight - $lines - 1;
                    }
                }
            }
        }

        $cellsize = $pdf->FontSizePt / 2;
        $fullrows = 0;

        // Calculate space on pages.
        $fsize = ceil($this->getFontSize());
        if ($fsize == 3) {
            $fsize = self::FONTSIZE_SMALL;
        } else if ($fsize == 4) {
            $fsize = self::FONTSIZE_MEDIUM;
        } else if ($fsize == 5) {
            $fsize = self::FONTSIZE_LARGE;
        }

        $spaceonpage = array();

        if ($this->fontsize == self::FONTSIZE_SMALL) {
            if ($this->orientation == self::PORTRAIT) {
                $spaceonpage[0] = 62;
                $spaceonpage[1] = 64;
            } else {
                $spaceonpage[0] = 40;
                $spaceonpage[1] = 42;
            }

        } else if ($this->fontsize == self::FONTSIZE_MEDIUM) {
            if ($this->orientation == self::PORTRAIT) {
                $spaceonpage[0] = 49;
                $spaceonpage[1] = 51;
            } else {
                $spaceonpage[0] = 32;
                $spaceonpage[1] = 33;
            }

        } else if ($this->fontsize == self::FONTSIZE_LARGE) {
            if ($this->orientation == self::PORTRAIT) {
                $spaceonpage[0] = 41;
                $spaceonpage[1] = 42;
            } else {
                $spaceonpage[0] = 27;
                $spaceonpage[1] = 28;
            }
        } else {
            print_error("an unexpected error occured. Please report this to your administrator.");
            exit();
        }

        $forcebreakonnextpage = false;

        // Now the generating of the page.
        foreach ($this->data as $rownum => $row) {
            $spanned = 0;
            $dontbreak = false;
            foreach ($row as $key => $value) {
                if ($value['rowspan'] > $spanned) {
                    $spanned = $value['rowspan'];
                }

                if (is_null($value['data'])) {
                    $dontbreak = true;
                }
            }

            $fullrows += $rowheights[$rownum];

            $spannedheight = 0;
            for ($i = $rownum + 1; $i < $rownum + $spanned; $i++) {
                $spannedheight = $rowheights[$i];
            }

            if ($this->getPage() == 1) {
                $spaceleft = $spaceonpage[0];
            } else {
                $spaceleft = $spaceonpage[1];
            }

            if ($forcebreakonnextpage) {
                // Break because there had to be allready a break but we couldnt.
                if (!$dontbreak) {
                    $pdf->addPage();
                    $fullrows = $rowheights[$rownum];
                    $forcebreakonnextpage = false;

                }
                // Break because of fixed rows per page.
            } else if ($this->rowsperpage && $this->rowsperpage > 0 && $rownum != 0 && $rownum % $this->rowsperpage == 0) {
                if (!$dontbreak) {
                    $pdf->addPage();
                    $fullrows = $rowheights[$rownum];
                } else {
                    $forcebreakonnextpage = true;
                }
                // Break because there is no more space on current page.
            } else if ($this->rowsperpage && $this->rowsperpage > 0 && $fullrows + $spannedheight > $spaceleft) {
                if (!$dontbreak) {
                    $pdf->addPage();
                    $fullrows = $rowheights[$rownum];
                } else {
                    $forcebreakonnextpage = true;
                }
                // Make optimal page breaks.
            } else {
                if ($fullrows + $spannedheight > $spaceleft) {
                    if (!$dontbreak) {
                        $pdf->addPage();
                        $fullrows = $rowheights[$rownum];
                    } else {
                        $forcebreakonnextpage = true;
                    }
                }
            }

            if ($rownum == count($this->data) - 1) {
                $bottomborder = 'B';
            } else {
                $bottomborder = '';
            }

            $debug = false;

            foreach ($row as $key => $value) {
                $cf = $this->columnformat[$key];
                $cf = $cf[$rownum % count($cf)];

                if (!is_null($value['data'])) {
                    $bottomborder = 'TB';
                    if ($value['rowspan'] > 0) {
                        $rowspans[$key] = $value['rowspan'];
                    }

                    $numlines = 0;
                    for ($i = $rownum; $i <= $rownum + $value['rowspan']; $i++) {
                        $numlines += $rowheights[$i];
                    }

                    if ($debug) {
                        $debuginfo = $spanned . '/' . $value['rowspan'] . '/' . $numlines . '/';
                        $value['data'] = $debuginfo . substr($value['data'], 0, strlen($value['data']) - (strlen($debuginfo)));
                    }

                    $pdf->MultiCell($w[$key], $numlines * $cellsize, $value['data'], 'LR'.$bottomborder,
                            $cf['align'], $cf['fill'], 0, '', '', true, '0');

                } else if ($rowspans[$key] > 0) {
                    if ($debug) {
                        $value['data'] = $value['rowspan'] . "/_";
                    }

                    $numlines = $rowheights[$rownum];

                    $pdf->Cell($w[$key], $numlines * $cellsize, $value['data'], 'LR', 0, false, 0, '', '', true, '0');
                    $rowspans[$key] = $rowspans[$key] - 1;
                }
            }
            $pdf->Ln();
            $fill = !$fill;
        }

        if ($filename != '') {
            if (substr($filename, strlen($filename) - 4) != ".pdf") {
                $filename .= '.pdf';
            }

            $filename = clean_filename($filename);
            $pdf->Output($filename, 'D');
        } else {
            $pdf->Output();
        }
    }

    /**
     * fills workbook (either XLS or ODS) with data
     *
     * @param MoodleExcelWorkbook $workbook workbook to put data into
     */
    public function fill_workbook(&$workbook) {
        global $DB;

        $time = time();
        $time = userdate($time);
        $worksheet = $workbook->add_worksheet($time);

        $headlineprop = array('size' => 12,
            'bold' => 1,
            'bottom' => 1,
            'align' => 'center',
            'v_align' => 'vcenter');
        $headlineformat = $workbook->add_format($headlineprop);
        $headlineformat->set_left(1);
        $headlinefirst = $workbook->add_format($headlineprop);
        unset($headlineprop['bottom']);
        if (!empty($this->headerformat['title'])) {
            $hdrleft = $workbook->add_format($this->headerformat['title']);
        } else {
            $hdrleft = $workbook->add_format($headlineprop);
            $hdrleft->set_align('right');
        }
        unset($headlineprop['bold']);
        if (!empty($this->headerformat['desc'])) {
            $hdrright = $workbook->add_format($this->headerformat['desc']);
        } else {
            $hdrright = $workbook->add_format($headlineprop);
            $hdrright->set_align('left');
        }

        $textprop = array('size' => 10,
            'align' => 'left',
            'v_align' => 'vcenter');
        $text = $workbook->add_format($textprop);
        $text->set_left(1);
        $textfirst = $workbook->add_format($textprop);

        $line = 0;

        // Write header.
        for ($i = 0; $i < count($this->header); $i += 2) {
            $worksheet->write_string($line, 0, $this->header[$i], $hdrleft);
            $worksheet->write_string($line, 1, $this->header[$i + 1], $hdrright);
            $line++;
        }
        $line++;

        // Table header.
        $i = 0;
        $first = true;
        foreach ($this->titles as $key => $header) {
            if ($first) {
                $worksheet->write_string($line, $i, $header, $headlinefirst);
                $first = false;
            } else {
                $worksheet->write_string($line, $i, $header, $headlineformat);
                $first = false;
            }
            $i++;
        }

        // Data.
        $prev = $this->data[0];
        foreach ($this->data as $row) {
            $first = true;
            $line++;
            $i = 0;
            foreach ($row as $idx => $cell) {
                if (is_null($cell['data'])) {
                    $cell['data'] = $prev[$idx]['data'];
                }

                if (array_key_exists('format', $cell)) {
                    $worksheet->write_string($line, $i, $cell['data'], $workbook->add_format($cell['format']));
                } else {
                    if ($first) {
                        $worksheet->write_string($line, $i, $cell['data'], $textfirst);
                        $first = false;
                    } else {
                        $worksheet->write_string($line, $i, $cell['data'], $text);
                    }
                }

                $prev[$idx] = $cell;
                $i++;
            }
        }
    }

    public function set_headerformat($headertitleformat, $headerdescformat) {
             $this->headerformat['title'] = $headertitleformat;
             $this->headerformat['desc'] = $headerdescformat;
    }

    public function get_xls($filename) {
        global $CFG;

        require_once($CFG->libdir . "/excellib.class.php");

        $workbook = new MoodleExcelWorkbook("-", 'excel5');

        $this->fill_workbook($workbook);

        $workbook->send($filename.'.xls');
        $workbook->close();
    }

    public function get_xlsx($filename) {
        global $CFG;

        require_once($CFG->libdir . "/excellib.class.php");

        $workbook = new MoodleExcelWorkbook("-", 'Excel2007');

        $this->fill_workbook($workbook);

        $workbook->send($filename);
        $workbook->close();
    }

    public function get_ods($filename) {
        global $CFG;

        require_once($CFG->libdir . "/odslib.class.php");

        $workbook = new MoodleODSWorkbook("-");

        $this->fill_workbook($workbook);

        $workbook->send($filename.'.ods');
        $workbook->close();
    }

    public function get_csv($filename, $sep = "\t") {
        $lines = array();

        // Course information.
        for ($i = 0; $i < count($this->header); $i += 2) {
            $lines[] = $this->header[$i] . $sep . $this->header[$i + 1];
        }

        // Table header.
        $lines[] = join($sep, $this->titles);

        $prev = $this->data[0];

        // Data.
        foreach ($this->data as $row) {
            $r = array();
            foreach ($row as $idx => $cell) {
                if (is_null($cell['data'])) {
                    $cell['data'] = $prev[$idx]['data'];
                }

                $r[] = $cell['data'];
                $prev[$idx] = $cell;
            }

            $lines[] = join($sep, $r);
        }

        $filecontent = implode("\n", $lines);

        if ($filename != '') {
            if (substr($filename, strlen($filename) - 4) != ".csv") {
                $filename .= '.csv';
            }

            $filename = clean_filename($filename);
        }

        ob_clean();
        header('Content-Type: text/plain');
        header('Content-Length: ' . strlen($filecontent));
        header('Content-Disposition: attachment; filename="'.$filename.'"; filename*="'.
                rawurlencode($filename));
                header('Content-Transfer-Encoding: binary');
                header('Content-Encoding: utf-8');
                echo $filecontent;die();
    }
}