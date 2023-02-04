<?php

require_once(dirname(__FILE__, 4) .'/config.php');

$location = $CFG->dirroot . '/mod/organizer/lang/en/organizer.php';
$firstseparator = "defined('MOODLE_INTERNAL') || die();";
$lineseparator = ";";
$fieldseparator = " = '";
$inarr = array();
$outstr = "";
$linebreak = '\n';

$filecontent = file_get_contents($location);
$filecontent = str_replace('"', "'", $filecontent);
$arr = explode($firstseparator, $filecontent);
if (count($arr) != 2) {
    echo "No first separator found or empty file.";
    die();
}
$outstr = $arr[0].$linebreak.$firstseparator.$linebreak;
$entries = explode($lineseparator, $arr[1]);

foreach($entries as $entry) {
    $arr = explode($fieldseparator, $entry);
    if (count($arr) == 2) {
        $inarr[$arr[0]] = $arr[1];
    } else {
        echo "*".$entry."*<br>";
    }
}

ksort($inarr);

$i = 0;
foreach ($inarr as $key => $value) {
    echo '#'.$key.$fieldseparator.$value.'#<br>';
    if ($i++ == 20) {
        break;
    }
}
