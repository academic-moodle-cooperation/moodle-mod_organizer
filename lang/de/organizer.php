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

/**
 * English strings for organizer
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod_organizer
 * @copyright 2010 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Terminplaner';
$string['modulenameplural'] = 'Terminplaner';
$string['modulename_help'] = 'Terminplaner ermöglichen es den Lehrenden Termine bzw. Zeitfenster für Studierende bereitzustellen.';
$string['organizername'] = 'Name';
$string['organizer'] = 'Terminplaner';
$string['pluginadministration'] = 'Terminplaner Administration';
$string['pluginname'] = 'Terminplaner';
$string['isgrouporganizer'] = 'Gruppentermine';
$string['isgrouporganizer_help'] = "Ankreuzen um den Terminplaner im Gruppenmodus zu verwenden. Statt einzelner Benutzer/innen können sich Gruppen für Termine anmelden. Wenn nicht angekreuzt ist es trotzdem möglich mehrere Benutzer/innen zu einem einzelnen Termin zuzulassen.";
$string['appointmentdatetime'] = 'Datum & Zeit';
$string['multipleappointmentstartdate'] = 'Startdatum';
$string['multipleappointmentenddate'] = 'Enddatum';
$string['appointmentcomments'] = 'Kommentare';
$string['appointmentcomments_help'] = 'Zusätzliche Informationen zum Termin können hier ergänzt werden.';
$string['duration'] = 'Dauer';
$string['duration_help'] = 'Bestimmt die Dauer der Termine. Alle festgelegten Zeitfenster werden in Slots der hier definierten Dauer aufgeteilt. Überbleibende Zeit wird nicht verwendet (d.h ein 40 Minuten langes Zeitfenster und eine 15 minütige Dauer resultiert in 2 verfügbare Slots und 10 Minuten extra, die nicht verfügbar sind).';
$string['location'] = 'Ort';
$string['location_help'] = 'Name des Ortes wo die Termine stattfinden';
$string['introeditor_error'] = 'Eine Beschreibung des Terminplaners muss vorhanden sein!';
$string['timeavailable'] = 'Verfügbar ab';
$string['timeavailable_help'] = "Kreuzen Sie diese Option an um Studierenden den Zugang zu diesem Terminplaner
    erst ab einem bestimmten Zeitpunkt zu ermöglichen.";
$string['warning_groupingid'] = 'Gruppenmodus eingeschaltet. Sie müssen eine gültige Gruppierung auswählen.';
$string['addappointment'] = 'Termin hinzufügen';
$string['taballapp'] = 'Termine';
$string['tabstud'] = 'Studierendenansicht';
$string['maxparticipants'] = 'Höchstanzahl der Teilnehmer/innen';
$string['maxparticipants_help'] = 'Bestimmt die maximale Anzahl Student/innen die sich für die jeweiligen Slots registrieren können. Bei Gruppenterminplanern ist diese Anzahl immer auf eine Gruppe begrenzt.';
$string['emailteachers'] = 'E-Mail Benachrichtigung an Lehrende versenden';
$string['emailteachers_help'] = "Mitteilungen an Lehrende bezüglich der Erstanmeldung der Studierenden sind
    normalerweise unterdrückt. Kreuzen Sie diese Option an um diese zu Ermöglichen. Bitte beachten Sie, dass
    die Mitteilungen bezüglich der Um- und Abmeldungen der Studierenden immer gesendet werden.";
$string['absolutedeadline'] = 'Endgültige Deadline';
$string['locationlinkenable'] = 'Automatische Verlinkung zum Terminort';
$string['locationlink'] = 'Link URL des Ortes';
$string['locationlink_help'] = 'Geben Sie hier die volle Webadresse an, die beim Link zum Ort verwendet werden soll. Diese Seite sollte zumindest Informationen enthalten wie der Ort des Termins erreicht werden kann. Die volle Adresse (inklusive http://) wird benötigt.';
$string['absolutedeadline_help'] = "Ankreuzen um die Bestimmung einer absoluten Deadline zu ermöglichen.
    Es sind nach diesem Zeitpunkt keinerlei Aktionen seitens der Studierenden mehr möglich.";
$string['relativedeadline'] = 'Relative Deadline';
$string['relativedeadline_help'] = "Die Deadline wird relativ zum jeweiligen Slot gesetzt.
    Studierenden können sich nach Ablauf dieser Deadline nicht für diesen Slot anmelden oder abmelden.";
$string['grade'] = 'Höchste Bewertung';
$string['grade_help'] = 'Bestimmt die höchste erreichbare Bewertung für jeden Termin der beurteilt werden kann.';
$string['groupoptions'] = 'Gruppeneinstellungen';
$string['organizercommon'] = 'Gemeinsame Terminplaner Einstellungen';
$string['grouppicker'] = 'Gruppenauswahl';
$string['availablegrouplist'] = 'Verfügbare Gruppen';
$string['selectedgrouplist'] = 'Ausgewählte Gruppen';
$string['slotperiodstarttime'] = 'Startdatum';
$string['slotperiodendtime'] = 'Enddatum';
$string['slotperiodheader'] = 'Erzeuge Slots für Zeitraum';
$string['slotperiodheader_help'] = 'Geben Sie ein Start- und Enddatum an für welche die täglichen Zeitfenster (siehe darunter) verwendet werden.';
$string['slottimeframesheader'] = 'Zeitfenster angeben';
$string['slottimeframesheader_help'] = 'Hier können Sie Zeitfenster auf Wochentagsbasis definieren die mit Terminslots befüllt werden, wie oben spezifiziert. Mehr als ein Zeitfenster pro Tag ist erlaubt. Ist ein Zeitfenster an einem Tag ausgewählt (zB Montag), so werden für jeden Montag im Datumszeitraum Zeitfenster und Termine erstellt.';
$string['slotdetails'] = 'Slot Details';
$string['back'] = 'Zurück';
$string['teacherid'] = 'Lehrenden';
$string['teacherid_help'] = 'Bitte Lehrenden auswählen, der die Termine leitet';
$string['teacher'] = 'Lehrende/r';
$string['otherheader'] = 'Anderes';
$string['day_0'] = 'Montag';
$string['day_1'] = 'Dienstag';
$string['day_2'] = 'Mittwoch';
$string['day_3'] = 'Donnerstag';
$string['day_4'] = 'Freitag';
$string['day_5'] = 'Samstag';
$string['day_6'] = 'Sonntag';
$string['day'] = 'Tag';
$string['hour'] = 'h';
$string['min'] = 'min';
$string['sec'] = 'sek';
$string['day_pl'] = 'Tage';
$string['hour_pl'] = 'hh';
$string['min_pl'] = 'mins';
$string['sec_pl'] = 'seks';
$string['slotfrom'] = ' von';
$string['newslot'] = 'Weiteren Slot hinzufügen';
$string['slotto'] = 'bis';
$string['btn_comment'] = 'Kommentar bearbeiten';
$string['confirm_delete'] = 'Löschen';
$string['err_enddate'] = 'Enddatum kann nicht vor dem Startdatum gesetzt werden!';
$string['err_startdate'] = 'Startdatum muss in der Zukunft liegen!';
$string['err_fromto'] = 'Endzeit kann nicht vor Startzeit gesetzt werden!';
$string['err_collision'] = 'Dieses Zeitfenster fällt mit anderen Zeitfenstern zusammen:';
$string['err_location'] = 'Ein Ort muss angegeben werden!';
$string['err_posint'] = 'Nur positive Werte erlaubt!';
$string['create'] = 'Erstellen';
$string['availablefrom'] = 'Anfragen möglich ab';
$string['availablefrom_help'] = 'Definieren Sie das Zeitfenster, während welches Student/innen sich für diese Slots anmelden können. Ersatzweise checken Sie die "Ab jetzt" Checkbox, um die Anmeldungen sofort zu ermöglichen.';
$string['err_availablefromearly'] = 'Dieses Datum kann nicht vor dem Startdatum liegen!';
$string['err_availablefromlate'] = 'Dieses Datum kann nicht nach dem Enddatum liegen!';
$string['teachervisible'] = 'Lehrende sichtbar';
$string['teachervisible_help'] = 'Kreuzen Sie diese Option an um Student/innen zu erlauben den zugewiesenen Lehrenden dieses Zeitslots einzusehen.';
$string['notificationtime'] = 'Relative Terminerinnerung';
$string['notificationtime_help'] = 'Bestimmt wie weit im vorhinein der Student an den Termin erinnert wird.';
$string['title_add'] = 'Neue Terminslots hinzufügen';
$string['title_comment'] = 'Eigene Kommentare bearbeiten';
$string['title_delete'] = 'Ausgewählte Zeitslots löschen';
$string['createsubmit'] = 'Zeitslots erstellen';
$string['reviewsubmit'] = 'Zeitslots ansehen';
$string['edit_submit'] = 'Änderungen speichern';
$string['rewievslotsheader'] = 'Zeitslots ansehen';
$string['noslots'] = 'Keine Slots für ';
$string['err_noslots'] = 'Keine Slots ausgewählt!';
$string['err_comments'] = 'Beschreibung notwendig!';
$string['isanonymous'] = 'Anonym';
$string['isanonymous_help'] = 'Kreuzen Sie diese Option an, um zu verhindern, dass für den Zeitslot angemeldete Studierenden die Informationen anderer angemeldeter Studierenden einsehen können.';
$string['remindall_desc'] = 'Erinnerungen an alle Student/innen ohne Termin versenden';
$string['infobox_showmyslotsonly'] = 'Nur meine Slots anzeigen';
$string['th_datetime'] = 'Datum & Zeit';
$string['th_duration'] = 'Dauer';
$string['th_location'] = 'Ort';
$string['th_teacher'] = 'Lehrende/r';
$string['th_participants'] = 'Teilnehmer/innen';
$string['th_participant'] = 'Teilnehmer/innen';
$string['th_attended'] = 'Teilg.';
$string['th_grade'] = 'Bewertung';
$string['th_feedback'] = 'Feedback';
$string['th_details'] = 'Status';
$string['th_idnumber'] = 'Matrikelnummer';
$string['th_firstname'] = 'Vorname';
$string['th_lastname'] = 'Nachnahme';
$string['th_actions'] = 'Aktion';
$string['th_status'] = 'Status';
$string['th_comments'] = 'Kommentare';
$string['th_appdetails'] = 'Details';
$string['th_datetimedeadline'] = 'Datum & Uhrzeit';
$string['th_evaluated'] = 'bewertet';
$string['th_status'] = 'Status';
$string['th_groupname'] = 'Gruppe';
$string['teacher_unchanged'] = '-- unverändert --';
$string['warningtext1'] = 'Ausgewählte Slots enthalten andere Werte als dieses Feld!';
$string['warningtext2'] = 'WARNUNG! Die Inhalte dieses Feldes sind verändert worden!';
$string['teacher'] = 'Lehrende/r';
$string['tabstatus'] = 'Registrierungsstatus';
$string['title_edit'] = 'Ausgewählte Zeitslots bearbeiten';
$string['select_all_slots'] = 'Alle sichtbaren Slots auswählen';

$string['no_slots'] = 'Es wurden keine Zeitslots in diesem Terminplaner erstellt';
$string['no_due_slots'] = 'Alle in diesem Terminplaner erstellten Zeitslots sind abgelaufen';
$string['no_my_slots'] = 'Sie haben in diesem Terminplaner keine Slots erstellt';
$string['no_due_my_slots'] = 'All Ihre Zeitslots in diesem Terminplaner sind abgelaufen';

$string['title_eval'] = 'Ausgewählte Zeitslots bewerten';
$string['evaluate'] = 'Speichern';
$string['eval_header'] = 'Ausgewählte Zeitslots';
$string['collision'] = 'Warnung! Zeitkollision mit dem/n folgenden Termin/en entdeckt:';

$string['btn_add'] = 'Neue Slots hinzufügen';
$string['btn_edit'] = 'Ausgewählte Slots bearbeiten';
$string['btn_delete'] = 'Ausgewählte Slots entfernen';
$string['btn_eval'] = 'Ausgewählte Slots bewerten';
$string['btn_print'] = 'Ausgewählte Slots drucken';
$string['printsubmit'] = 'Tabellendruckansicht';
$string['title_print'] = 'Druckansicht';

$string['btn_register'] = 'Anmelden';
$string['btn_unregister'] = 'Abmelden';
$string['btn_reregister'] = 'Ummelden';
$string['btn_reeval'] = 'Neu bewerten';
$string['btn_eval_short'] = 'Bewerten';
$string['btn_remind'] = 'Erinnerung senden';
$string['btn_save'] = 'Kommentar speichern';
$string['btn_send'] = 'Senden';

$string['pdfsubmit'] = 'PDF-Datei generieren';
$string['teacherinvisible'] = 'Lehrende nicht sichtbar';

$string['img_title_evaluated'] = 'Der Slot ist bewertet';
$string['img_title_pending'] = 'Ausstehende Bewertung des Slots';
$string['img_title_no_participants'] = 'Der Slot hatte keine Teilnehmer/innen';
$string['img_title_past_deadline'] = 'Der Slot ist über der Deadline';
$string['img_title_due'] = 'Der Slot ist fällig';

$string['places_taken'] = '{$a->numtakenplaces} Plätze vergeben';
$string['places_taken_pl'] = '{$a->numtakenplaces}/{$a->totalplaces} Plätze vergeben';
$string['places_taken_sg'] = '{$a->numtakenplaces}/{$a->totalplaces} Platz vergeben';

$string['addslots_placesinfo'] = 'Diese Aktion erstellt {$a->numplaces} neue mögliche Plätze, was zu einer Gesamtanzahl von {$a->totalplaces} möglichen Plätzen für {$a->numstudents} Studierenden führt.';
$string['addslots_placesinfo_group'] = 'Diese Aktion erstellt {$a->numplaces} neue mögliche Plätze, was zu einer Gesamtanzahl von {$a->totalplaces} möglichen Plätzen für {$a->numgroups} Gruppen führt.';

$string['mymoodle_registered'] = '{$a->registered}/{$a->total} Student/innen haben sich für einen Termin angemeldet';
$string['mymoodle_attended'] = '{$a->attended}/{$a->total} Student/innen haben an einem Termin teilgenommen';
$string['mymoodle_registered_group'] = '{$a->registered}/{$a->total} Gruppen haben sich für einem Termin angemeldet';
$string['mymoodle_attended_group'] = '{$a->attended}/{$a->total} Gruppen haben an einem Termin teilgenommen';

$string['mymoodle_registered_short'] = '{$a->registered}/{$a->total} Student/innen angemeldet';
$string['mymoodle_attended_short'] = '{$a->attended}/{$a->total} Student/innen teilgenommen';
$string['mymoodle_registered_group_short'] = '{$a->registered}/{$a->total} Gruppen angemeldet';
$string['mymoodle_attended_group_short'] = '{$a->attended}/{$a->total} Gruppen teilgenommen';

$string['mymoodle_next_slot'] = 'Nächster Slot am {$a->date} um {$a->time}';
$string['mymoodle_no_slots'] = 'Keine bevorstehenden Slots';
$string['mymoodle_completed_app'] = 'Sie haben Ihren Termin am {$a->date} um {$a->time} abgeschlossen';
$string['mymoodle_completed_app_group'] = 'Ihre Gruppe {$a->groupname} hat am Termin am {$a->date} um {$a->time} teilgenommen';
$string['mymoodle_missed_app'] = 'Sie haben am Termin am {$a->date} um {$a->time} nicht teilgenommen';
$string['mymoodle_missed_app_group'] = 'Ihre Gruppe {$a->groupname} hat am Termin am {$a->date} um {$a->time} nicht teilgenommen';
$string['mymoodle_organizer_expires'] = 'Dieser Terminplaner läuft am {$a->date} um {$a->time} ab';
$string['mymoodle_pending_app'] = 'Ausstehende Bewertung Ihres Termins';
$string['mymoodle_pending_app_group'] = 'Ausstehende Bewertung des Termins Ihrer Gruppe {$a->groupname}';
$string['mymoodle_upcoming_app'] = 'Ihr Termin findet am {$a->date} um {$a->time} im {$a->location} statt';
$string['mymoodle_upcoming_app_group'] = 'Der Termin Ihrer Gruppe, {$a->groupname}, findet am {$a->date} um {$a->time} im {$a->location} statt';
$string['mymoodle_organizer_expired'] = 'Dieser Terminplaner lief am {$a->date} um {$a->time} ab. Sie können ihn nicht mehr benutzen';
$string['mymoodle_no_reg_slot'] = 'Sie haben sich noch nicht für einen Zeitslot angemeldet';
$string['mymoodle_no_reg_slot_group'] = 'Ihre Gruppe {$a->groupname} hat sich noch nicht für einen Zeitslot angemeldet';

$string['infobox_title'] = 'Infobox';
$string['infobox_myslot_title'] = 'Mein Slot';
$string['infobox_mycomments_title'] = 'Meine Kommentare';
$string['infobox_messaging_title'] = 'Benachrichtigungseinstellungen';
$string['infobox_deadlines_title'] = 'Deadlines';
$string['infobox_legend_title'] = 'Legende';
$string['infobox_organizer_expires'] = 'Dieser Terminplaner läuft am {$a->date} um {$a->time} ab.';
$string['infobox_organizer_never_expires'] = 'Dieser Terminplaner läuft nicht ab.';
$string['infobox_organizer_expired'] = 'Dieser Terminplaner lief am {$a->date} um {$a->time} ab';
$string['infobox_deadline_countdown'] = 'Zeit bis zur Deadline: {$a->days} Tage, {$a->hours} Stunden, {$a->minutes} Minuten, {$a->seconds} Sekunden';
$string['infobox_deadline_passed'] = 'Anmeldezeitraum abgelaufen. Sie können Anmeldungen nicht mehr ändern.';
$string['infobox_app_countdown'] = 'Zeit bis zum Termin: {$a->days} Tage, {$a->hours} Stunden, {$a->minutes} Minuten, {$a->seconds} Sekunden';
$string['infobox_app_occured'] = 'Der Termin hat schon stattgefunden.';
$string['infobox_feedback_title'] = 'Feedback';
$string['infobox_group'] = 'Meine Gruppe: {$a->groupname}';
$string['infobox_deadlines_title'] = 'Deadlines';

$string['fullname_template'] = '{$a->firstname} {$a->lastname}';

$string['infobox_showfreeslots'] = 'Nur freie Slots anzeigen';
$string['infobox_showslots'] = 'Vergangene Zeitslots anzeigen';
$string['infobox_showlegend'] = 'Legende einblenden';
$string['infobox_slotoverview_title'] = 'Slot Übersicht';
$string['infobox_description_title'] = 'Terminplanerbeschreibung';
$string['infobox_messages_title'] = 'Systemnachrichten';
$string['message_warning_no_slots_selected'] = 'Sie müssen zuerst mindestens einen Slot auswählen!';
$string['message_info_slots_added_sg'] = '{$a->count} neuer Slot hinzugefügt.';
$string['message_info_slots_added_pl'] = '{$a->count} neue Slots hinzugefügt.';
$string['message_warning_no_slots_added'] = 'Es wurden keine neuen Slots hinzugefügt!';

$string['grouporganizer_desc_nogroup'] = 'Dies ist ein Gruppenorganizer. Student/innen können hier Ihre Gruppen für Termine anmelden. Alle Gruppenmitglieder können die Anmeldung ändern und kommentieren.';
$string['grouporganizer_desc_hasgroup'] = 'Dies ist ein Gruppenorganizer. Das Betätigen des Anmeldebuttons meldet Sie und alle Mitglieder Ihrer Gruppe {$a->groupname} für diesen Slot an. Alle Gruppenmitglieder können die Anmeldung ändern und kommentieren.';
$string['status_no_entries'] = 'Für diesen Terminplaner sind keine Student/innen angemeldet.';
$string['infobox_link'] = 'Anzeigen/Verbergen';
$string['eval_not_occured'] = 'Dieser Slot hat noch nicht stattgefunden';
$string['eval_no_participants'] = 'Dieser Slot hatte keine Teilnehmer/innen';

$string['infobox_myslot_noslot'] = 'Sie sind derzeit für keinen Slot angemeldet.';
$string['resetorganizerall'] = 'Alle Daten des Terminplaners löschen (Slots & Termine)';
$string['deleteorganizergrades'] = 'Alle Bewertungen im Gradebook löschen';

$string['configintro'] = 'Die Werte die Sie hier einstellen, bestimmen die Standardwerte, die im Einstellungsformular aufscheinen, wenn Sie einen neuen Terminplaner erstellen.';
$string['configmaximumgrade'] = 'Voreinstellung für den Wert im Feld "Höchste Bewertung" beim Erstellen eines neuen Terminplaners. Diese Einstellung entspricht dem Beurteilungsmaximum, das ein Student erhalten kann.';
$string['configabsolutedeadline'] = 'Voreinstellung für den Offset der Datums- und Zeitauswahl, ausgehend vom jetzigen Zeitpunkt.';
$string['configrelativedeadline'] = 'Voreinstellung für den Zeitpunkt an dem Teilnehmer/innen vor einem Termin davon in Kenntnis gesetzt werden sollten.';
$string['configdigest'] = 'Zusammenfassung der Termine für den jeweils nächsten Tag an Lehrende versenden.';
$string['configemailteachers'] = 'E-Mail Benachrichtigungen an Lehrenden bezüglich Änderungen der Anmeldungsstatus';
$string['configlocationlink'] = 'Link zu Suchmaschine, die den Weg zum Zielort zeigt. Setzen Sie $searchstring in die URL ein, die die Anfrage bearbeitet.';

$string['configemailteachers_label'] = 'E-Mail Benachrichtigungen';
$string['configdigest_label'] = 'Zusammenfassungen';

$string['no_slots_defined'] = 'Derzeit sind keine Zeitslots verfügbar.';
$string['no_slots_defined_teacher'] = 'Derzeit sind keine Zeitslots verfügbar. Legen Sie <a href="{$a->link}">hier</a> neue an.';

$string['eventnoparticipants'] = 'Keine Teilnehmer/innen';
$string['eventteacheranonymous'] = '<em>anonymous</em>';

$string['organizer:addslots'] = 'Neue Zeitslots hinzufügen';
$string['organizer:editslots'] = 'Vorhandene Zeitslots bearbeiten';
$string['organizer:evalslots'] = 'Abgeschlossene Zeitslots bewerten';
$string['organizer:deleteslots'] = 'Vorhandene Zeitslots löschen';
$string['organizer:sendreminders'] = 'Anmeldungserinnerungen an Student/innen senden';
$string['organizer:printslots'] = 'Vorhandene Zeitslots drucken';
$string['organizer:receivemessagesstudent'] = 'Nachrichten wie als Student/in empfangen';
$string['organizer:receivemessagesteacher'] = 'Nachrichten wie als Lehrende/r empfangen';
$string['organizer:register'] = 'Für einen Zeitslot anmelden';
$string['organizer:unregister'] = 'Von Zeitslot abmelden';
$string['organizer:viewallslots'] = 'Alle Zeitslots als Lehrende/r ansehen';
$string['organizer:viewmyslots'] = 'Eigene Zeitslots als Lehrende/r ansehen';
$string['organizer:viewstudentview'] = 'Alle Zeitslots als Student/in ansehen';
$string['organizer:viewregistrations'] = 'Status der Anmeldung von Student/innen ansehen';

$string['messageprovider:test'] = 'Test string';
$string['messageprovider:appointment_reminder:student'] = 'Test string';
$string['messageprovider:manual_reminder:student'] = 'Test string';
$string['messageprovider:appointment_reminder:teacher'] = 'Test string';
$string['messageprovider:eval_notify:student'] = 'Test string';
$string['messageprovider:group_registration_notify:student'] = 'Test string';

/* Message templates following.
 * Please note that the following strings are available:
 * 	   sendername, receivername, courseshortname, courselongname, courseid, organizername,
 *     date, time, location, groupname
 * If more strings are required, add them to the $strings object in messaging.php
 */

$string['register_notify:teacher:register:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Student/in angemeldet';
$string['register_notify:teacher:register:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Student/in {$a->sendername} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.

TUWEL Messaging System';
$string['register_notify:teacher:register:smallmessage'] = 'Student/in {$a->sendername} hat sich für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.';

//--------------------------------------------------------------------------------------------------

$string['register_notify:teacher:reregister:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Student/in umgemeldet';
$string['register_notify:teacher:reregister:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Student/in {$a->sendername} für den neuen Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.

TUWEL Messaging System';
$string['register_notify:teacher:reregister:smallmessage'] = 'Student/in {$a->sendername} hat sich für den Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.';

//--------------------------------------------------------------------------------------------------

$string['register_notify:teacher:unregister:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Student/in abgemeldet';
$string['register_notify:teacher:unregister:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Student/in {$a->sendername} vom Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.

TUWEL Messaging System';
$string['register_notify:teacher:unregister:smallmessage'] = 'Student/in {$a->sendername} hat sich vom Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.';

//--------------------------------------------------------------------------------------------------

$string['register_notify:teacher:register:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Gruppe angemeldet';
$string['register_notify:teacher:register:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Student/in {$a->sendername} die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.

TUWEL Messaging System';
$string['register_notify:teacher:register:group:smallmessage'] = 'Student/in {$a->sendername} hat die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.';

//--------------------------------------------------------------------------------------------------

$string['register_notify:teacher:reregister:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Group umgemeldet';
$string['register_notify:teacher:reregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Student/in {$a->sendername} die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.

TUWEL Messaging System';
$string['register_notify:teacher:reregister:group:smallmessage'] = 'Student/in {$a->sendername} hat die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.';

//--------------------------------------------------------------------------------------------------

$string['register_notify:teacher:unregister:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Gruppe abgemeldet';
$string['register_notify:teacher:unregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Student/in {$a->sendername} die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.

TUWEL Messaging System';
$string['register_notify:teacher:unregister:group:smallmessage'] = 'Student/in {$a->sendername} hat die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.';

//--------------------------------------------------------------------------------------------------

$string['eval_notify:student:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Termin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location} bewertet worden.

TUWEL Messaging System';
$string['eval_notify:student:smallmessage'] = 'Ihr Termin am {$a->date} um {$a->time} im {$a->location} ist bewertet worden.';

//--------------------------------------------------------------------------------------------------

$string['eval_notify_newappointment:student:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify_newappointment:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Termin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location} bewertet worden.

Die LVA-Leitung des Kurses erm�glicht Ihnen sich nochmals im Terminpalner {$a->coursefullname} zu einem noch freien Termin anzumelden.
		
TUWEL Messaging System';
$string['eval_notify_newappointment:student:smallmessage'] = 'Ihr Termin am {$a->date} um {$a->time} im {$a->location} ist bewertet worden.';

//--------------------------------------------------------------------------------------------------

$string['eval_notify:student:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Gruppentermin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location} bewertet worden.

TUWEL Messaging System';
$string['eval_notify:student:group:smallmessage'] = 'Ihr Gruppentermin am {$a->date} um {$a->time} im {$a->location} ist bewertet worden.';

//--------------------------------------------------------------------------------------------------


$string['eval_notify_newappointment:student:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify_newappointment:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Gruppentermin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location} bewertet worden.

Die LVA-Leitung des Kurses erm�glicht Ihnen sich nochmals im Terminpalner {$a->coursefullname} zu einem noch freien Termin anzumelden.

TUWEL Messaging System';
$string['eval_notify_newappointment:student:group:smallmessage'] = 'Ihr Gruppentermin am {$a->date} um {$a->time} im {$a->location} ist bewertet worden.';

//--------------------------------------------------------------------------------------------------


$string['edit_notify:student:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Termindetails verändert';
$string['edit_notify:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Termins mit {$a->sendername} am {$a->date} um {$a->time} verändert worden.

TUWEL Messaging System';
$string['edit_notify:student:smallmessage'] = 'Die Details des Termins mit {$a->sendername} am {$a->date} um {$a->time} sind verändert worden.';

//--------------------------------------------------------------------------------------------------

$string['edit_notify:student:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Termindetails verändert';
$string['edit_notify:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Gruppentermins mit {$a->sendername} am {$a->date} um {$a->time} verändert worden.

TUWEL Messaging System';
$string['edit_notify:student:group:smallmessage'] = 'Die Details des Gruppentermins mit {$a->sendername} am {$a->date} um {$a->time} sind verändert worden.';

//--------------------------------------------------------------------------------------------------

$string['edit_notify:teacher:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Termindetails verändert';
$string['edit_notify:teacher:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Zeitslots am {$a->date} um {$a->time} von {$a->sendername} verändert worden.

TUWEL Messaging System';
$string['edit_notify:teacher:smallmessage'] = 'Die Details des Zeitslots am {$a->date} um {$a->time} sind von {$a->sendername} verändert worden.';

//--------------------------------------------------------------------------------------------------

$string['register_reminder:student:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Anmeldungserinnerung';
$string['register_reminder:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie sich entweder noch nicht für einen Zeitslot angemeldet, oder denjenigen verpasst für den Sie sich angemeldet haben.

TUWEL Messaging System';
$string['register_reminder:student:smallmessage'] = 'Bitte melden Sie sich für einen neuen Zeitslot an.';

//--------------------------------------------------------------------------------------------------

$string['register_reminder:student:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Anmeldungserinnerung';
$string['register_reminder:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Ihre Gruppe {$a->groupname} entweder noch nicht für einen Zeitslot angemeldet, oder denjenigen verpasst für den Sie sich angemeldet hat.

TUWEL Messaging System';
$string['register_reminder:student:group:smallmessage'] = 'Bitte melden Sie sich für einen neuen Zeitslot an.';

//--------------------------------------------------------------------------------------------------
$string['group_registration_notify:student:register:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Gruppe angemeldet';
$string['group_registration_notify:student:register:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.

TUWEL Messaging System';
$string['group_registration_notify:student:register:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} angemeldet.';

//--------------------------------------------------------------------------------------------------

$string['group_registration_notify:student:reregister:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Gruppe umgemeldet';
$string['group_registration_notify:student:reregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} für einen neuen Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.

TUWEL Messaging System';
$string['group_registration_notify:student:reregister:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} für einen neuen Zeitslot am {$a->date} um {$a->time} umgemeldet.';

//--------------------------------------------------------------------------------------------------

$string['group_registration_notify:student:unregister:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Gruppe abgemeldet';
$string['group_registration_notify:student:unregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} vom Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.

TUWEL Messaging System';
$string['group_registration_notify:student:unregister:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} vom Zeitslot am {$a->date} um {$a->time} abgemeldet.';

//--------------------------------------------------------------------------------------------------

$string['appointment_reminder:teacher:digest:subject'] = 'Terminerinnerung';
$string['appointment_reminder:teacher:digest:fullmessage'] = 'Hallo {$a->receivername}!

Sie haben heute folgende Termine:
{$a->digest}

TUWEL Messaging System';
$string['appointment_reminder:teacher:digest:smallmessage'] = 'Sie haben heute Termine!';

//--------------------------------------------------------------------------------------------------

$string['appointment_reminder:teacher:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Terminerinnerung';
$string['appointment_reminder:teacher:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie einen Termin mit Student/innen am {$a->date} um {$a->time} im {$a->location}.

TUWEL Messaging System';
$string['appointment_reminder:teacher:smallmessage'] = 'Sie haben einen Termin mit Student/innen am {$a->date} um {$a->time} im {$a->location}.';

//--------------------------------------------------------------------------------------------------

$string['appointment_reminder:teacher:digest:subject'] = 'Terminzusammenfassung';
$string['appointment_reminder:teacher:digest:fullmessage'] = 'Hallo {$a->receivername}!

Sie haben morgen folgende Termine:

{$a->digest}

TUWEL Messaging System';
$string['appointment_reminder:teacher:digest:smallmessage'] = 'Sie haben eine zusammenfassende Nachricht bezüglich Ihre morgigen Termine erhalten.';

//--------------------------------------------------------------------------------------------------

$string['appointment_reminder:teacher:group:digest:subject'] = 'Terminzusammenfassung';
$string['appointment_reminder:teacher:group:digest:fullmessage'] = 'Hallo {$a->receivername}!

Sie haben morgen folgende Termine:

{$a->digest}

TUWEL Messaging System';
$string['appointment_reminder:teacher:group:digest:smallmessage'] = 'Sie haben eine zusammenfassende Nachricht bezüglich Ihre morgigen Termine erhalten.';

//--------------------------------------------------------------------------------------------------

$string['appointment_reminder:student:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Terminerinnerung';
$string['appointment_reminder:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie einen Termin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location}.

TUWEL Messaging System';
$string['appointment_reminder:student:smallmessage'] = 'Sie haben einen Termin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location}.';

//--------------------------------------------------------------------------------------------------

$string['appointment_reminder:student:group:subject'] = '[{$a->courseid} {$a->courseshortname} / {$a->organizername}] - Gruppenterminerinnerung';
$string['appointment_reminder:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie einen Gruppentermin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location}.

TUWEL Messaging System';
$string['appointment_reminder:student:group:smallmessage'] = 'Sie haben einen Gruppentermin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location}.';

//TODO: proof read the translation! done
$string['reg_status_organizer_expired'] = 'Terminplaner abgelaufen';
$string['reg_status_slot_expired'] = 'Slot abgelaufen';
$string['reg_status_slot_pending'] = 'Slot hat eine ausstehende Bewertung';
$string['reg_status_slot_not_attended'] = 'Nicht anwesend';
$string['reg_status_slot_attended'] = 'Anwesend';
$string['reg_status_slot_not_attended_reapp'] = 'Nicht anwesend, erneute Anmeldung möglich';
$string['reg_status_slot_attended_reapp'] = 'Anwesend, erneute Anmeldung möglich';
$string['reg_status_slot_past_deadline'] = 'Slot über der Deadline';
$string['reg_status_slot_full'] = 'Slot ausgebucht';
$string['reg_status_slot_available'] = 'Slot verfügbar';
$string['reg_status_registered'] = 'Angemeldet';
$string['reg_status_not_registered'] = 'Nicht angemeldet';

$string['eval_attended'] = 'Anwesend';
$string['eval_grade'] = 'Bewertung';
$string['eval_feedback'] = 'Feedback';
$string['eval_allow_new_appointments'] = 'Erneute Anmeldung erlauben';

$string['legend_evaluated'] = 'Slot bewertet';
$string['legend_pending'] = 'Slot hat eine ausstehende Bewertung';
$string['legend_no_participants'] = 'Slot hatte keine Teilnehmer/innen';
$string['legend_past_deadline'] = 'Slot über der Deadline';
$string['legend_due'] = 'Slot fällig';
$string['legend_organizer_expired'] = 'Terminplaner abgelaufen';
$string['legend_anonymous'] = 'Anonymer Slot';

$string['legend_group_applicant'] = 'Anmelder/in der Gruppe';
$string['legend_comments'] = 'Kommentare der Studierenden/Lehrenden';
$string['legend_feedback'] = 'Feedback';
$string['legend_not_occured'] = 'Termin hat noch nicht stattgefunden';

$string['legend_section_status'] = 'Statussymbole';
$string['legend_section_details'] = 'Detailsymbole';

$string['th_group'] = 'Gruppe';

$string['reg_status'] = 'Status der Registrierung';

$string['modformwarningsingular'] = 'Dieses Feld kann nicht bearbeitet werden, da es in diesem Terminplaner schon angemeldete Student/innen gibt!';
$string['modformwarningplural'] = 'Diese Felder können nicht bearbeitet werden, da es in diesem Terminplaner schon angemeldete Student/innen gibt!';

// new strings
$string['teachercomment_title'] = 'Kommentare Lehrender';
$string['studentcomment_title'] = 'Kommentare Studierender';
$string['teacherfeedback_title'] = 'Rückmeldung Lehrender';
$string['relative_deadline_before'] = 'vor dem Termin';
$string['relative_deadline_now'] = 'Ab sofort';

// new strings done
$string['nogroup'] = 'Keine Gruppe';
$string['noparticipants'] = 'Keine Teilnehmer/innen';
$string['unavailableslot'] = 'Dieser Slot ist verfügbar ab';
$string['applicant'] = 'Person, die die Gruppe registriert hat';

$string['deleteheader'] = 'Löschen der folgenden Slots:';
$string['deletenoslots'] = 'Keine löschbaren Slots ausgewählt';
$string['deletekeep'] = 'Die folgenden Slots können nicht gelöscht werden, da sich bereits Student/innen für diesen Slot angemeldet haben:';

$string['atlocation'] = 'in';

$string['printoptions'] = 'Druckeinstellungen';
$string['numentries'] = 'Einträge pro Seite';
$string['textsize'] = 'Textgröße';
$string['pageorientation'] = 'Seitenausrichtung';
$string['orientationportrait'] = 'Hochformat';
$string['orientationlandscape'] = 'Querformat';

$string['headerfooter'] = 'Inkludiere Kopf-/Fußzeilen';
$string['printpreview'] = 'Druckvorschau (erste 10 Einträge)';

$string['unknown'] = 'Unbekannt';

// GANZ NEU! TODO
$string['totalslots'] = 'von {$a->starttime} bis {$a->endtime}, je {$a->duration} {$a->unit}, {$a->totalslots} Slot(s) insgesamt';

$string['warninggroupmode'] = 'Sie müssen den Gruppenmodus einschalten und eine Gruppierung auswählen, um einen Gruppenterminplaner zu erstellen!';

$string['multimember'] = 'Teilnehmer dürfen nicht binnen einer Gruppierung zu mehreren Gruppen gehören!';
$string['multimemberspecific'] = 'Teilnehmer {$a->username} {$a->idnumber} hat sich für mehr als eine Gruppe angemeldet! ({$a->groups})';
$string['groupwarning'] = 'Prüfen Sie die Gruppeneinstellungen unten!';

$string['enableuntilerror'] = 'Endgültige Deadline darf nicht vor dem Verfügbarkeitsdatum definiert werden!';
$string['invalidgrouping'] = 'Sie müssen eine gültige Gruppierung auswählen!';

$string['maillink'] = 'Der Terminplaner ist unter <a href="{$a}">diesem</a> Link verfügbar.';

//-----------------------------------

$string['eventappwith:single'] = 'Einzeltermin';
$string['eventappwith:group'] = 'Gruppentermin';

$string['eventwith'] = 'mit';
$string['eventwithout'] = '';

$string['eventnoparticipants'] = 'ohne Teilnehmer/innen';
$string['eventteacheranonymous'] = 'einem anonymen Lehrenden';

$string['eventtitle'] = '{$a->coursename} / {$a->organizername}: {$a->appwith}';

$string['eventtemplate'] = '{$a->courselink} / {$a->organizerlink}: {$a->appwith} {$a->with} {$a->participants}<br />Ort: {$a->location}<br />Beschreibung:<br />{$a->description}<br />';

$string['fulldatetimelongtemplate'] = '%A %d. %B %Y %H:%M';
$string['fulldatetimetemplate'] = '%a %d.%m.%Y %H:%M';
$string['fulldatelongtemplate'] = '%A %d. %B %Y';
$string['fulldatetemplate'] = '%a %d.%m.%Y';
$string['datetemplate'] = '%d.%m.%Y';
$string['timetemplate'] = '%H:%M';

$string['font_small'] = 'Klein (8pt)';
$string['font_medium'] = 'Mittel (10pt)';
$string['font_large'] = 'Groß (12pt)';

$string['printout'] = 'Ausdruck';
$string['confirm_organizer_remind_all'] = 'Senden';

$string['cannot_eval'] = 'Kann nicht bewertet werden. Diese(r) Teilnehmer/innen hat';
$string['eval_link'] = 'einen neuen Termin';

$string['group_slot_available'] = "Slot verfügbar";
$string['group_slot_full'] = "Slot vergeben";
$string['slot_anonymous'] = "Slot anonym";

$string['message_info_reminders_sent_sg'] = 'Es wurde {$a->count} Mitteilung versandt.';
$string['message_info_reminders_sent_pl'] = 'Es wurden {$a->count} Mitteilungen versandt.';

$string['organizer_remind_all_recepients_sg'] = 'Es wird insgesamt {$a->count} Mitteilung an nachfolgende Empfänger versandt:';
$string['organizer_remind_all_recepients_pl'] = 'Es werden insgesamt {$a->count} Mitteilungen an nachfolgende Empfänger versandt:';

$string['organizer_remind_all_no_recepients'] = 'Es gibt keine gültige Empfänger.';

$string['print_return'] = 'Zurück zur Terminansicht';

$string['message_error_slot_full_single'] = 'Dieser Slot hat keine freien Plätze mehr!';
$string['message_error_slot_full_group'] = 'Dieser Slot ist vergeben!';

$string['organizer_remind_all_title'] = 'Erinnerungen versenden';
$string['can_reregister'] = 'Sie können sich für einen anderen Termin neu anmelden.';

$string['messages_none'] = 'Keine Benachrichtigungen';
$string['messages_re_unreg'] = 'Nur Ab-/Ummeldungen';
$string['messages_all'] = 'Alle Anmeldungen und Ab-/Ummeldungen';

$string['reset_organizer_all'] = 'Löschen aller Slots, Anmeldungen und zugehörigen Kalendereinträge';
$string['delete_organizer_grades'] = 'Löschen aller Bewertungen';
$string['timeshift'] = 'Verschiebung endgültiger Deadline';