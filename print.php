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

define('ORGANIZER_CELL_HEIGHT', 8);
define('ORGANIZER_MARGIN_LEFT', 10);
define('ORGANIZER_MARGIN_RIGHT', 10);
define('ORGANIZER_MARGIN_TOP', 10);
define('ORGANIZER_MARGIN_HEADER', 10);
define('ORGANIZER_MARGIN_FOOTER', 10);
define('ORGANIZER_SHRINK_TO_FIT', true);
define('ORGANIZER_FONT', 'freesans');
define('ORGANIZER_HEADER_SEPARATOR_WIDTH', 8);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/../../lib/pdflib.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/mtablepdf.php');
require_once(dirname(__FILE__) . '/custom_table_renderer.php');

function organizer_display_printable_table($timeavailable, $timedue, $columns, $slots, $entriesperpage = false, $textsize = '10', $orientation = 'L',
        $headerfooter = true, $filename = '') {
    global $USER;

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

    $columnwitdh = array();
    $titles = array();
    $columnformats = array();
    
    $tsort = isset($_SESSION['organizer_tsort']) ? $_SESSION['organizer_tsort'] : "";
    if($tsort != ""){
    	$order = "ASC";
    
    	if(substr($tsort, strlen($tsort) - strlen("DESC")) == "DESC"){
    		$tsort = substr($tsort,0, strlen($tsort) - strlen("DESC"));
    		$order = "DESC";
    	}
    }
	$colorder = array();
    $dosort = false;
    $i = 0;
    foreach ($columns as $column) {
    	if($column != ""){	
	        $titles[] = get_string("th_$column", 'organizer');
	        
	        if($tsort == $column){
	        	$dosort = true;
	        }
	        
	        $colorder[$column] = $i++;
	        
	        switch ($column) {
	            case 'datetime':
	                $columnwitdh[] = array('value' => 64, 'mode' => 'Relativ');
	                $columnformats[] = array('fill' => 0, 'align' => 'C');
	                break;
	            case 'location':
	                $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
	                $columnformats[] = array('fill' => 0, 'align' => 'C');
	                break;
	            case 'teacher':
	                $columnwitdh[] = array('value' => 32, 'mode' => 'Relativ');
	                $columnformats[] = array('fill' => 0, 'align' => 'C');
	                break;
	            case 'groupname':
	                $columnwitdh[] = array('value' => 32, 'mode' => 'Relativ');
	                $columnformats[] = array('fill' => 0, 'align' => 'C');
	                break;
	            case 'participant':
	                $columnwitdh[] = array('value' => 32, 'mode' => 'Relativ');
	                $columnformats[] = array('fill' => 0, 'align' => 'C');
	                break;
	            case 'idnumber':
	                $columnwitdh[] = array('value' => 24, 'mode' => 'Relativ');
	                $columnformats[] = array('fill' => 0, 'align' => 'C');
	                break;
	            case 'attended':
	                $columnwitdh[] = array('value' => 12, 'mode' => 'Relativ');
	                $columnformats[] = array('fill' => 0, 'align' => 'C');
	                break;
	            case 'grade':
	                $columnwitdh[] = array('value' => 18, 'mode' => 'Relativ');
	                $columnformats[] = array('fill' => 0, 'align' => 'C');
	                break;
	            case 'feedback':
	                $columnwitdh[] = array('value' => 64, 'mode' => 'Relativ');
	                $columnformats[] = array('fill' => 1, 'align' => 'L');
	                break;
	        }
    	}
    }
    
    
    switch($tsort){
    	case "datetime";		$sort = "starttime"; break;
    	case "location":		$sort = "s.location"; break;
    	case "teacher":			$sort = "teacherfirstname"; break;
    	case "participant":		$sort = "u.lastname"; break;
    	case "idnumber":		$sort = "u.idnumber"; break;
    	case "attended":		$sort = "a.attended"; break;
    	case "grade":			$sort = "a.grade"; break;
    	case "feedback":		$sort = "a.feedback"; break;
    	default:				$sort = NULL;
    }
    
    if(!isset($order)){
    	$order = "";
    }else if($order != "DESC" && $order != "ASC"){
    	$order = "";
    }
    
    if($dosort){
    	$dosort = $sort . ' ' . $order;
    }else{
    	$dosort = "";
    }
    
    if($timedue == NULL){
    	$duetitle = "";
    	$due = "";
    }else{
    	$duetitle = get_string('duedate', 'organizer').':';
    	$due = userdate($timedue);
    }

    $mpdftable = new MTablePDF($orientation, $columnwitdh);
    $mpdftable->SetTitle(get_string('modulename', 'organizer') . " " . $organizer->name . " - " . get_string('printout', 'organizer'));
    $mpdftable->setRowsperPage($entriesperpage);
    $mpdftable->ShowHeaderFooter($headerfooter);
    $mpdftable->SetFontSize($textsize);
    $mpdftable->setHeaderText(get_string('course') . ':', "{$course->idnumber} {$course->fullname}", get_string('availablefrom', 'organizer').':', userdate($timeavailable), get_string('date') . ':', userdate(time()),
    							get_string('modulename', 'organizer') . ':', $organizer->name, $duetitle, $due, '', '');
    $mpdftable->setTitles($titles);
    $mpdftable->setColumnFormat($columnformats);
    $entries = fetch_table_entries($slots, $dosort);
    $rowspan = 0;
    foreach ($entries as $entry) {
        $row = array();
        if ($rowspan == 0) {
            $rowspan = $entry->rowspan;
        }
        foreach ($columns as $column) {
            switch ($column) {
            // these columns may have rowspan
            case 'datetime':
            	if($rowspan != $entry->rowspan){
            		$row[] = null;
            	}else{
	                $datetime = userdate($entry->starttime, get_string('fulldatetimetemplate', 'organizer')) . ' - ' . userdate($entry->starttime + $entry->duration, get_string('timetemplate', 'organizer'));
	                $row[] = array('data' => $datetime, 'rowspan' => $rowspan - 1);
            	}
                break;
            case 'location':
            	if($rowspan != $entry->rowspan){
            		$row[] = null;
            	}else{
                	$row[] = array('data' => $entry->location, 'rowspan' => $rowspan - 1);
            	}
                break;
            case 'teacher':
            	if($rowspan != $entry->rowspan){
            		$row[] = null;
            	}else{
	                $a = new stdClass();
	                $a->firstname = $entry->teacherfirstname;
	                $a->lastname = $entry->teacherlastname;
	                $name = get_string('fullname_template', 'organizer', $a);
	                $row[] = array('data' => $name, 'rowspan' => $rowspan - 1);
            	}
                break;
            case 'groupname':
            	if($rowspan != $entry->rowspan){
            		$row[] = null;
            	}else{
	                $groupname = isset($entry->groupname) ? $entry->groupname : '';
	                $row[] = array('data' => $groupname, 'rowspan' => $rowspan - 1);
            	}
                break;
            // these columns cannot have rowspan
            case 'participant':
                $a = new stdClass();
                $a->firstname = $entry->firstname;
                $a->lastname = $entry->lastname;
                $name = get_string('fullname_template', 'organizer', $a);
                $row[] = array('data' => $name, 'rowspan' => 0);
                break;
            case 'idnumber':
                $idnumber = (isset($entry->idnumber) && $entry->idnumber !== '') ? $entry->idnumber : '';
                $row[] = array('data' => $idnumber, 'rowspan' => 0);
                break;
            case 'attended':
                $attended = isset($entry->attended) ? ($entry->attended == 1 ? get_string('yes') : get_string('no')) : '';
                $row[] = array('data' => $attended, 'rowspan' => 0);
                break;
            case 'grade':
                $grade = isset($entry->grade) ? sprintf("%01.2f", $entry->grade) : '';
                $row[] = array('data' => $grade, 'rowspan' => 0);
                break;
            case 'feedback':
                $feedback = isset($entry->feedback) && $entry->feedback !== '' ? $entry->feedback : '';
                $row[] = array('data' => $feedback, 'rowspan' => 0);
                break;
            }
        }

        $mpdftable->addRow($row);
        $rowspan--;
    }

    $mpdftable->generate($filename);
}

function fetch_table_entries($slots,$orderby="") {
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
	";

    
    if($orderby == " " || $orderby == ""){
    	$query .=     "ORDER BY s.starttime ASC,
        u.lastname ASC,
        u.firstname ASC,
        teacherlastname ASC,
        teacherfirstname ASC";
    }else{
    	$query .= "ORDER BY " . $orderby;
    }
    
    

    $params = array_merge($params, $inparams);
    return $DB->get_records_sql($query, $params);
}