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

require_once('../../config.php');
require_once('../../lib/pdflib.php');
require_once('lib.php');
require_once('custom_table_renderer.php');
require_once('../../lib/pdflib.php');

define('CELL_HEIGHT', 8);
define('MARGIN_LEFT', 10);
define('MARGIN_RIGHT', 10);
define('MARGIN_TOP', 10);
define('MARGIN_HEADER', 10);
define('MARGIN_FOOTER', 10);
define('SHRINK_TO_FIT', true);
define('FONT', 'freesans');
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

            $this->SetFont(FONT, 'B', 8);
            $this->Cell($this->GetStringWidth(get_string('course') . ": "), 10, get_string('course') . ": ", 0, false,
                    'L', 0, '', 0, false, 'M', 'M');
            $this->SetFont('', '', 8);
            $this->Cell($this->GetStringWidth($this->coursename) + HEADER_SEPARATOR_WIDTH, 10, $this->coursename, 0,
                    false, 'L', 0, '', 0, false, 'M', 'M');
            $this->SetFont(FONT, 'B', 8);
            $this->Cell($this->GetStringWidth(get_string('modulename', 'organizer') . ": "), 10,
                    get_string('modulename', 'organizer') . ": ", 0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->SetFont('', '', 8);
            $this->Cell($this->GetStringWidth($this->organizername) + HEADER_SEPARATOR_WIDTH, 10, $this->organizername,
                    0, false, 'L', 0, '', 0, false, 'M', 'M');
            $this->SetFont(FONT, 'B', 8);
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
            $this->SetFont(FONT, 'I', 8);
            $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0,
                    '', 0, false, 'T', 'M');
        }
    }
}

function display_printable_table($columns, $slots, $entriesperpage = false, $textsize = '10', $orientation = 'L',
        $headerfooter = true) {
    global $USER;

    list($cm, $course, $organizer, $context) = get_course_module_data();

    $pdf = new organizer_pdf($headerfooter);

    $pdf->SetCreator('TUWEL');
    $a = new stdClass();
    $a->firstname = $USER->firstname;
    $a->lastname = $USER->lastname;
    $pdf->SetAuthor(get_string('fullname_template', 'organizer', $a));
    $pdf->SetTitle(
            get_string('modulename', 'organizer') . " " . $organizer->name . " - "
                    . get_string('printout', 'organizer'));

    if ($headerfooter) {
        $pdf->setHeaderData("$course->idnumber {$course->fullname}", $organizer->name);

        $pdf->SetHeaderMargin(MARGIN_HEADER);
        $pdf->SetFooterMargin(MARGIN_FOOTER);
    }

    $pdf->setPageOrientation($orientation);

    $pdf->SetMargins(MARGIN_LEFT, MARGIN_TOP + MARGIN_HEADER, MARGIN_RIGHT, true);

    $columnwitdh = array('datetime' => 48, 'location' => 48, 'teacher' => 32, 'groupname' => 32, 'participant' => 32,
            'idnumber' => 24, 'attended' => 12, 'grade' => 18, 'feedback' => 64);

    $pagewidth = $pdf->getPageWidth();

    $tablewidth = MARGIN_LEFT + MARGIN_RIGHT;

    $newcols = array();
    foreach ($columns as $key => $column) {
        if ($column) {
            $newcols[] = $column;
            $tablewidth += $columnwitdh[$column];
        }
    }

    $columns = $newcols;

    $scale = $pagewidth / $tablewidth;

    $pageheight = $pdf->getPageHeight();

    $maxpossiblerows = round(($pageheight - (MARGIN_TOP + MARGIN_HEADER + MARGIN_FOOTER)) / CELL_HEIGHT) - 3;

    if ($entriesperpage && $entriesperpage < $maxpossiblerows) {
        $maxrows = $entriesperpage;
    } else {
        $maxrows = $maxpossiblerows;
    }

    $rowspan = 0;
    $numrows = $maxrows;
    $entries = fetch_table_entries($slots);

    foreach ($entries as $entry) {
        if ($rowspan == 0 && $numrows + $entry->rowspan > $maxrows) { // if a new page should be made, add a new header
            $pdf->AddPage();
            $pdf->SetFont(FONT, 'B');
            $pdf->setFontSize($textsize);
            foreach ($columns as $key => $column) {
                $pdf->MultiCell($columnwitdh[$column] * $scale, CELL_HEIGHT, get_string("th_$column", 'organizer'), 1,
                        'C', false, 0, '', '', true, 0, false, true, CELL_HEIGHT, 'M', SHRINK_TO_FIT);
            }
            $pdf->Ln();
            $numrows = 0;
        }

        $pdf->SetFont(FONT, '');
        $pdf->setFontSize($textsize);

        foreach ($columns as $key => $column) {
            if ($rowspan == 0) {
                $cellheight = $entry->rowspan * CELL_HEIGHT;
                switch ($column) {
                    case 'datetime':
                        $datetime = userdate($entry->starttime, get_string('fulldatetimetemplate', 'organizer')) . ' - '
                                . userdate($entry->starttime + $entry->duration, get_string('timetemplate', 'organizer'));
                        $pdf->MultiCell($columnwitdh[$column] * $scale, $cellheight, $datetime, 1, 'C', false, 0, '', '',
                                true, 0, false, true, $cellheight, 'M', SHRINK_TO_FIT);
                        break;
                    case 'location':
                        $location = $entry->location;
                        $pdf->MultiCell($columnwitdh[$column] * $scale, $cellheight, $location, 1, 'C', false, 0, '', '',
                                true, 0, false, true, $cellheight, 'M', SHRINK_TO_FIT);
                        break;
                    case 'teacher':
                        $a = new stdClass();
                        $a->firstname = $entry->teacherfirstname;
                        $a->lastname = $entry->teacherlastname;
                        $name = get_string('fullname_template', 'organizer', $a);
                        $pdf->MultiCell($columnwitdh[$column] * $scale, $cellheight, $name, 1, 'C', false, 0, '', '', true,
                                0, false, true, $cellheight, 'M', SHRINK_TO_FIT);
                        break;
                    case 'groupname':
                        $groupname = $entry->groupname ? $entry->groupname : ''; echo "$";
                        $pdf->MultiCell($columnwitdh[$column] * $scale, $cellheight, $groupname, 1, 'C', false, 0, '', '',
                                true, 0, false, true, $cellheight, 'M', SHRINK_TO_FIT); echo "$";
                        break;
                    default:
                        break;
                }
            }

            switch ($column) {
                case 'participant':
                    $a = new stdClass();
                    $a->firstname = $entry->firstname;
                    $a->lastname = $entry->lastname;
                    $name = get_string('fullname_template', 'organizer', $a);
                    $pdf->MultiCell($columnwitdh[$column] * $scale, CELL_HEIGHT, $name, 1, 'C', false, 0, '', '', true, 0,
                            false, true, CELL_HEIGHT, 'M', SHRINK_TO_FIT);
                    break;
                case 'idnumber':
                    $idnumber = (isset($entry->idnumber) && $entry->idnumber !== '') ? $entry->idnumber : '';
                    $pdf->MultiCell($columnwitdh[$column] * $scale, CELL_HEIGHT, $idnumber, 1, 'C', false, 0, '', '', true,
                            0, false, true, CELL_HEIGHT, 'M', SHRINK_TO_FIT);
                    break;
                case 'attended':
                    $attended = isset($entry->attended) ? ($entry->attended == 1 ? 'Yes' : 'No') : '';
                    $pdf->MultiCell($columnwitdh[$column] * $scale, CELL_HEIGHT, $attended, 1, 'C', false, 0, '', '', true,
                            0, false, true, CELL_HEIGHT, 'M', SHRINK_TO_FIT);
                    break;
                case 'grade':
                    $grade = isset($entry->grade) ? sprintf("%01.2f", $entry->grade) : '';
                    $pdf->MultiCell($columnwitdh[$column] * $scale, CELL_HEIGHT, $grade, 1, 'C', false, 0, '', '', true, 0,
                            false, true, CELL_HEIGHT, 'M', SHRINK_TO_FIT);
                    break;
                case 'feedback':
                    $feedback = isset($entry->feedback) && $entry->feedback !== '' ? $entry->feedback : '';
                    $pdf->MultiCell($columnwitdh[$column] * $scale, CELL_HEIGHT, $feedback, 1, 'L', false, 0, '', '', true,
                            0, false, true, CELL_HEIGHT, 'M', SHRINK_TO_FIT);
                    break;
                case 'datetime':
                case 'location':
                case 'teacher':
                case 'groupname':
                    if ($rowspan != 0) { // this one's invisible in order to maintain proper positions within respective row
                        $pdf->MultiCell($columnwitdh[$column] * $scale, CELL_HEIGHT, '', 0, 'C', false, 0, '', '', true, 0,
                                false, true, CELL_HEIGHT, 'M', SHRINK_TO_FIT);
                    }
                    break;
                default:
                    print_error("Unsupported column type: $column");
            }
        }
        // this one has 0 width to facilitate proper new line shift at the end of a table row
        $pdf->MultiCell(0, CELL_HEIGHT, '', 0, 'C', false, 0, '', '', true, 0, false, true, CELL_HEIGHT, 'M',
                SHRINK_TO_FIT);

        $pdf->Ln();
        $numrows++;

        $rowspan = ($rowspan + 1) % $entry->rowspan;
    }
    ob_clean();
    $pdf->Output(
            get_string('modulename', 'organizer') . " " . $organizer->name . " - "
                    . get_string('printout', 'organizer') . ".pdf", 'D');
}

function fetch_table_entries($slots) {
    global $DB;

    list($insql, $inparams) = $DB->get_in_or_equal($slots, SQL_PARAMS_NAMED);

    $params = array();
    $query = "SELECT CONCAT(s.id, COALESCE(a.id, 0)) AS mainid,
	    s.id AS slotid,
	    a.id,
        a.attended,
        a.grade,
        a.feedback,
        a.comments,
        s.starttime,
        s.duration,
        s.location,
        s.comments AS teachercomments,
        u.firstname,
        u.lastname,
        u.idnumber,
        u2.firstname AS teacherfirstname,
        u2.lastname AS teacherlastname,
        g.name AS groupname,
        CASE (SELECT COUNT(a2.slotid) FROM {organizer_slot_appointments} a2 WHERE a2.slotid = a.slotid)
			WHEN 0 THEN 1
			ELSE (SELECT COUNT(a2.slotid) FROM {organizer_slot_appointments} a2 WHERE a2.slotid = a.slotid)
		END AS rowspan

    FROM {organizer_slots} s
        LEFT JOIN {organizer_slot_appointments} a ON a.slotid = s.id
        LEFT JOIN {user} u ON a.userid = u.id
        LEFT JOIN {user} u2 ON s.teacherid = u2.id
        LEFT JOIN {groups} g ON a.groupid = g.id

    WHERE s.id $insql

    ORDER BY s.starttime ASC,
        u.lastname ASC,
        u.firstname ASC,
        teacherlastname ASC,
        teacherfirstname ASC";

    $params = array_merge($params, $inparams);
    return $DB->get_records_sql($query, $params);
}