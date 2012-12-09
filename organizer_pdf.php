<?php
// This file is part of Moodle - http://moodle.org/
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

require_once('../../lib/pdflib.php');

define('HEADER_SEPARATOR_WIDTH', 8);

class organizer_pdf extends pdf {
    private $coursename;
    private $organizername;
    private $headerfooter;

    public function __construct($headerfooter = true, $orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true,
            $encoding = 'UTF-8') {
        parent::__construct($orientation, $unit, $format, $unicode, $encoding);
        $this->headerfooter = $headerfooter;
    }

    public function Header($coursename = '', $organizername = '') {
        if ($this->headerfooter) {
            $date = userdate(time(), get_string('fulldatetimetemplate', 'organizer'));

            $this->SetFont('helvetica', 'B', 8);
            $this->Cell($this->GetStringWidth(get_string('course') . ": "), 10, get_string('course') . ": ", 0, false,
                    'L', 0, '', 0, false, 'M', 'M');
            $this->SetFont('', '', 8);
            $this->Cell($this->GetStringWidth($this->coursename) + HEADER_SEPARATOR_WIDTH, 10, $this->coursename, 0,
                    false, 'L', 0, '', 0, false, 'M', 'M');
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell($this->GetStringWidth(get_string('modulename', 'organizer') . ": "), 10,
                    get_string('modulename', 'organizer') . ": ", 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->SetFont('', '', 8);
            $this->Cell($this->GetStringWidth($this->organizername) + HEADER_SEPARATOR_WIDTH, 10, $this->organizername,
                    0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->SetFont('helvetica', 'B', 8);
            $this->Cell($this->GetStringWidth(get_string('date') . ": "), 10, get_string('date') . ": ", 0, false, 'L',
                    0, '', 0, false, 'M', 'M');
            $this->SetFont('', '', 8);
            $this->Cell($this->GetStringWidth($date) + HEADER_SEPARATOR_WIDTH, 10, $date, 0, false, 'L', 0, '', 0,
                    false, 'M', 'M');
        }
    }

    public function setHeaderData($coursename = '', $organizername = 0, $ht = '', $hs = '', $tc = array(0, 0, 0), $lc = array(0, 0, 0)) {
        $this->coursename = $coursename;
        $this->organizername = $organizername;
    }

    public function Footer() {
        if ($this->headerfooter) {
            $this->SetY(-15);
            $this->SetFont('helvetica', 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0,
                    '', 0, false, 'T', 'M');
        }
    }
}
