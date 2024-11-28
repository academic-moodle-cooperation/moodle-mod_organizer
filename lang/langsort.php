<?php
// This file is part of mod_organizer for Moodle - http://moodle.org/
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
 * langsort.php
 *
 * @package       mod_organizer
 * @author        Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @copyright     2022 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__, 4) .'/config.php');

$location = $CFG->dirroot . '/mod/organizer/lang/en/organizer.php';
$firstseparator = "defined('MOODLE_INTERNAL') || die();";
$lineseparator = "';";
$fieldseparator = " = '";
$inarr = [];
$outstr = "";
$linebreak = "\n";

$filecontent = file_get_contents($location);
$filecontent = str_replace('"', "'", $filecontent);
$arr = explode($firstseparator, $filecontent);
if (count($arr) != 2) {
    echo "No first separator found or empty file.";
    die();
}

$entries = explode($lineseparator, $arr[1]);

$entrybefore = "";
foreach ($entries as $entry) {
    $arr = explode($fieldseparator, $entry);
    if (count($arr) == 2) {
        $inarr[$arr[0]] = $arr[1];
    } else if ($entry == PHP_EOL) {
        continue;
    } else {
        echo "*".$entrybefore."*<br>";
        echo "*".$entry."*<br>";
    }
    $entrybefore = $entry;
}

ksort($inarr);

foreach ($inarr as $key => $value) {
    $outstr .= $key.$fieldseparator.$value.$lineseparator;
}

echo $outstr;

