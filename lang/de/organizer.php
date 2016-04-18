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
 * lang/de/organizer.php
 *
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Terminplaner';
$string['modulenameplural'] = 'Terminplaner';
$string['modulename_help'] = 'Terminplaner ermöglichen es den Trainer/innen Termine bzw. Zeitfenster für die Teilnehmer/innen bereitzustellen.';
$string['organizername'] = 'Name des Terminplaners';
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
$string['gap'] = 'Lücke';
$string['gap_help'] = 'Bestimmt den Abstand zwischen den Terminen.';
$string['location'] = 'Ort';
$string['location_help'] = 'Name des Ortes wo die Termine stattfinden';
$string['introeditor_error'] = 'Eine Beschreibung des Terminplaners muss vorhanden sein!';
$string['allowsubmissionsfromdate'] = 'Anmeldebeginn';
$string['allowsubmissionsfromdate_help'] = "Kreuzen Sie diese Option an um den Teilnehmer/innen den Zugang zu diesem Terminplaner erst ab einem bestimmten Zeitpunkt zu ermöglichen.";
$string['allowsubmissionsfromdatesummary'] = 'Anmeldungen möglich ab <strong>{$a}</strong>';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'Die Terminplanerdetails und die Registrierung stehen zur Verfügung ab <strong>{$a}</strong>';
$string['duedate'] = 'Abgabetermin';
$string['availability'] = 'Verfügbarkeit';

$string['eventslotviewed'] = 'Termine angezeigt.';
$string['eventregistrationsviewed'] = 'Anmeldungen angezeigt.';
$string['eventslotcreated'] = 'Neuer Termin angelegt.';
$string['eventslotupdated'] = 'Termin bearbeitet.';
$string['eventslotdeleted'] = 'Termin abgesagt.';
$string['eventslotviewed'] = 'Termin angezeigt.';
$string['eventappointmentadded'] = 'Teilnehmer/in hat sich zu einem Termin angemeldet.';
$string['eventappointmentcommented'] = 'Termin wurde kommentiert.';
$string['eventappointmentevaluated'] = 'Termin wurde bewertet.';
$string['eventappointmentremoved'] = 'Teilnehmer/in wurde von einem Termin abgemeldet.';
$string['eventappointmentremindersent'] = 'Terminerinnerung zu Anmeldung zu einem Termin gesendet.';
$string['eventappointmentlistprinted'] = 'Terminliste wurde gedruckt.';

$string['alwaysshowdescription'] = 'Beschreibung immer anzeigen';
$string['alwaysshowdescription_help'] = 'Wenn diese Option deaktiviert ist, wird die Aufgabenbeschreibung für Teilnehmer/innen nur während des Anmeldezeitraums angezeigt.';
$string['warning_groupingid'] = 'Gruppenmodus eingeschaltet. Sie müssen eine gültige Gruppierung auswählen.';
$string['addappointment'] = 'Termin hinzufügen';
$string['taballapp'] = 'Termine';
$string['tabstud'] = 'Teilnehmer/innen Ansicht';
$string['maxparticipants'] = 'Höchstanzahl der Teilnehmer/innen';
$string['maxparticipants_help'] = 'Bestimmt die maximale Anzahl Teilnehmer/innen die sich für die jeweiligen Slots registrieren können. Bei Gruppenterminplanern ist diese Anzahl immer auf eine Gruppe begrenzt.';
$string['emailteachers'] = 'E-Mail Benachrichtigung an Trainer/in versenden';
$string['emailteachers_help'] = "Mitteilungen an Trainer/in bezüglich der Erstanmeldung der Teilnehmer/innen sind
    normalerweise unterdrückt. Kreuzen Sie diese Option an um diese zu Ermöglichen. Bitte beachten Sie, dass
    die Mitteilungen bezüglich der Um- und Abmeldungen der Teilnehmer/innen immer gesendet werden.";
$string['absolutedeadline'] = 'Anmeldeende';
$string['locationlinkenable'] = 'Automatische Verlinkung zum Terminort';
$string['locationlink'] = 'Link URL des Ortes';
$string['locationlink_help'] = 'Geben Sie hier die volle Webadresse an, die beim Link zum Ort verwendet werden soll. Diese Seite sollte zumindest Informationen enthalten wie der Ort des Termins erreicht werden kann. Die volle Adresse (inklusive http://) wird benötigt.';
$string['absolutedeadline_help'] = "Ankreuzen um die Bestimmung einer absoluten Deadline zu ermöglichen.
    Es sind nach diesem Zeitpunkt keinerlei Aktionen seitens der Teilnehmer/innen mehr möglich.";
$string['relativedeadline'] = 'Relative Deadline';
$string['relativedeadline_help'] = "Die Deadline wird relativ zum jeweiligen Slot gesetzt.
    Teilnehmer/innen können sich nach Ablauf dieser Deadline nicht für diesen Slot anmelden oder abmelden.";
$string['grade'] = 'Höchste Bewertung';
$string['grade_help'] = 'Bestimmt die höchste erreichbare Bewertung für jeden Termin der beurteilt werden kann.';
$string['changegradewarning'] = 'In diesem Terminplaner sind bereits Termine bewertet worden. Bei einer Änderung der Bewertungsskala sind Neuberechnungen der Bewertungen erforderlich. Sie müssen ggfs. die Neuberechnung gesondert starten.';
$string['groupoptions'] = 'Gruppeneinstellungen';
$string['organizercommon'] = 'Terminplaner Einstellungen';
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
$string['teacherid'] = 'Trainer/in';
$string['teacherid_help'] = 'Bitte Trainer/in auswählen, der/die die Termine leitet';
$string['teacher'] = 'Trainer/in';
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
$string['err_fullminute'] = 'Die Dauer muss ganzen Minuten entsprechen.';
$string['err_fullminutegap'] = 'Die Lücke muss ganzen Minuten entsprechen.';
$string['create'] = 'Erstellen';
$string['availablefrom'] = 'Anfragen möglich ab';
$string['availablefrom_help'] = 'Definieren Sie das Zeitfenster, während welches Teilnehmer/innen sich für diese Slots anmelden können. Ersatzweise checken Sie die "Ab jetzt" Checkbox, um die Anmeldungen sofort zu ermöglichen.';
$string['err_availablefromearly'] = 'Dieses Datum kann nicht vor dem Startdatum liegen!';
$string['err_availablefromlate'] = 'Dieses Datum kann nicht nach dem Enddatum liegen!';
$string['teachervisible'] = 'Trainer/in sichtbar';
$string['teachervisible_help'] = 'Kreuzen Sie diese Option an um Teilnehmer/innen zu erlauben, den zugewiesenen Trainer oder die zugewiesene Trainerin dieses Zeitslots einzusehen.';
$string['notificationtime'] = 'Relative Terminerinnerung';
$string['notificationtime_help'] = 'Bestimmt wie weit im vorhinein der/die Teilnehmer/in an den Termin erinnert wird.';
$string['title_add'] = 'Neue Terminslots hinzufügen';
$string['title_comment'] = 'Eigene Kommentare bearbeiten';
$string['title_delete'] = 'Ausgewählte Zeitslots löschen';
$string['createsubmit'] = 'Zeitslots erstellen';
$string['confirm_conflicts'] = 'Sind Sie sicher, dass Sie die Terminkollisionen übergehen möchten und die Zeitslots anlegen möchten?';
$string['reviewsubmit'] = 'Zeitslots ansehen';
$string['edit_submit'] = 'Änderungen speichern';
$string['rewievslotsheader'] = 'Zeitslots ansehen';
$string['noslots'] = 'Keine Slots für ';
$string['err_noslots'] = 'Keine Slots ausgewählt!';
$string['err_comments'] = 'Beschreibung notwendig!';
$string['visibility'] = 'Sichtbarkeit der Angemeldeten - Voreinstellung';
$string['visibility_help'] = 'Geben Sie hier den Standard vor, wie neue Slots angelegt werden sollen:<br/><b>Anonym:</b> Die anderen Teilnehmer/innen eines Slots sind einem/r Teilnehmer/-in stets verborgen.<br/><b>Sichtbar nur, wenn eigener Slot:</b> Die anderen Teilnehmer/-innen eines Slots sind nur sichtbar, wenn man den Slot selber gebucht hat.<br/><b>Sichtbar:</b> Die Teilnehmer/innen eines Slots werden immer angezeigt.';
$string['remindall_desc'] = 'Erinnerungen an alle Teilnehmer/innen ohne Termin versenden';
$string['infobox_showmyslotsonly'] = 'Nur meine Slots anzeigen';
$string['th_datetime'] = 'Datum & Zeit';
$string['th_duration'] = 'Dauer';
$string['th_location'] = 'Ort';
$string['th_teacher'] = 'Trainer/in';
$string['th_participants'] = 'Teilnehmer/innen';
$string['th_participant'] = 'Teilnehmer/innen';
$string['th_email'] = 'Email';
$string['th_attended'] = 'Teilg.';
$string['th_grade'] = 'Bewertung';
$string['th_feedback'] = 'Feedback';
$string['th_details'] = 'Status';
$string['th_idnumber'] = 'Matrikelnummer';
$string['th_firstname'] = 'Vorname';
$string['th_lastname'] = 'Nachname';
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
$string['teacher'] = 'Trainer/in';
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
$string['btn_queue'] = 'Warteliste';
$string['btn_unqueue'] = 'Aus Warteliste entfernen';
$string['btn_reeval'] = 'Neu bewerten';
$string['btn_eval_short'] = 'Bewerten';
$string['btn_remind'] = 'Erinnerung senden';
$string['btn_save'] = 'Kommentar speichern';
$string['btn_send'] = 'Senden';

$string['downloadfile'] = 'Datei herunterladen';
$string['teacherinvisible'] = 'Trainer/in nicht sichtbar';

$string['img_title_evaluated'] = 'Der Slot ist bewertet';
$string['img_title_pending'] = 'Ausstehende Bewertung des Slots';
$string['img_title_no_participants'] = 'Der Slot hatte keine Teilnehmer/innen';
$string['img_title_past_deadline'] = 'Der Slot ist über der Deadline';
$string['img_title_due'] = 'Der Slot ist fällig';

$string['places_taken'] = '{$a->numtakenplaces} Plätze vergeben';
$string['places_taken_pl'] = '{$a->numtakenplaces}/{$a->totalplaces} Plätze vergeben';
$string['places_taken_sg'] = '{$a->numtakenplaces}/{$a->totalplaces} Platz vergeben';
$string['places_inqueue'] = '{$a->inqueue} in Warteliste';
$string['places_inqueue_withposition'] = 'Position {$a->queueposition} in Warteliste';

$string['addslots_placesinfo'] = 'Diese Aktion erstellt {$a->numplaces} neue mögliche Plätze, was zu einer Gesamtanzahl von {$a->totalplaces} möglichen Plätzen für {$a->numstudents} Teilnehmer/innen führt.';
$string['addslots_placesinfo_group'] = 'Diese Aktion erstellt {$a->numplaces} neue mögliche Plätze, was zu einer Gesamtanzahl von {$a->totalplaces} möglichen Plätzen für {$a->numgroups} Gruppen führt.';

$string['mymoodle_registered'] = '{$a->registered}/{$a->total} Teilnehmer/innen haben sich für einen Termin angemeldet';
$string['mymoodle_attended'] = '{$a->attended}/{$a->total} Teilnehmer/innen haben an einem Termin teilgenommen';
$string['mymoodle_registered_group'] = '{$a->registered}/{$a->total} Gruppen haben sich für einem Termin angemeldet';
$string['mymoodle_attended_group'] = '{$a->attended}/{$a->total} Gruppen haben an einem Termin teilgenommen';

$string['mymoodle_registered_short'] = '{$a->registered}/{$a->total} Teilnehmer/innen angemeldet';
$string['mymoodle_attended_short'] = '{$a->attended}/{$a->total} Teilnehmer/innen teilgenommen';
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
$string['infobox_hidelegend'] = 'Legende ausblenden';
$string['infobox_slotoverview_title'] = 'Slot Übersicht';
$string['infobox_description_title'] = 'Terminplanerbeschreibung';
$string['infobox_messages_title'] = 'Systemnachrichten';
$string['message_warning_no_slots_selected'] = 'Sie müssen zuerst mindestens einen Slot auswählen!';
$string['message_info_slots_added_sg'] = '{$a->count} neuer Slot hinzugefügt.';
$string['message_info_slots_added_pl'] = '{$a->count} neue Slots hinzugefügt.';
$string['message_warning_no_slots_added'] = 'Es wurden keine neuen Slots hinzugefügt!';
$string['message_info_slots_deleted'] = 'Folgende Slots wurden gelöscht:<br/>
{$a->deleted} Slots gelöscht.<br/>
{$a->notified} Teilnehmer/innen wurden benachrichtigt.';
$string['message_info_slots_deleted_group'] = 'Folgende Slots wurden gelöscht:<br/>
{$a->deleted} Slots gelöscht.<br/>
{$a->notified} Teilnehmer/innen wurden benachrichtigt.';

$string['message_autogenerated2'] = 'Automatisch generierte Nachricht';
$string['message_custommessage'] = 'Benutzerdefinierte Nachricht';
$string['message_custommessage_help'] = 'Geben sie hier eine Nachricht ein die in die automatisch generierte Nachricht eingefügt wird.';

$string['message_info_available'] = 'Es stehen noch {$a->freeslots} freie Slots für insgesamt {$a->notregistered} Teilnehmer/innen ohne Termin zur Verfügung.';
$string['message_info_available_group'] = 'Es stehen noch {$a->freeslots} freie Slots für insgesamt {$a->notregistered} Gruppen ohne Termin zur Verfügung.';

$string['message_warning_available'] = '<span style="color:red;">Warnung</span> Es stehen noch {$a->freeslots} freie Slots für insgesamt {$a->notregistered} Teilnehmer/innen ohne Termin zur Verfügung.';
$string['message_warning_available_group'] = '<span style="color:red;">Warnung</span> Es stehen noch {$a->freeslots} freie Slots für insgesamt {$a->notregistered} Gruppen ohne Termin zur Verfügung.';


$string['grouporganizer_desc_nogroup'] = 'Dies ist ein Gruppenorganizer. Teilnehmer/innen können hier Ihre Gruppen für Termine anmelden. Alle Gruppenmitglieder können die Anmeldung ändern und kommentieren.';
$string['grouporganizer_desc_hasgroup'] = 'Dies ist ein Gruppenorganizer. Das Betätigen des Anmeldebuttons meldet Sie und alle Mitglieder Ihrer Gruppe {$a->groupname} für diesen Slot an. Alle Gruppenmitglieder können die Anmeldung ändern und kommentieren.';
$string['status_no_entries'] = 'Für diesen Terminplaner sind keine Teilnehmer/innen angemeldet.';
$string['infobox_link'] = 'Anzeigen/Verbergen';
$string['eval_not_occured'] = 'Dieser Slot hat noch nicht stattgefunden';
$string['eval_no_participants'] = 'Dieser Slot hatte keine Teilnehmer/innen';

$string['infobox_myslot_noslot'] = 'Sie sind derzeit für keinen Slot angemeldet.';
$string['resetorganizerall'] = 'Alle Daten des Terminplaners löschen (Slots & Termine)';
$string['deleteorganizergrades'] = 'Alle Bewertungen im Gradebook löschen';

$string['configintro'] = 'Die Werte die Sie hier einstellen, bestimmen die Standardwerte, die im Einstellungsformular aufscheinen, wenn Sie einen neuen Terminplaner erstellen.';
$string['requiremodintro'] = 'Beschreibung notwendig';
$string['configrequiremodintro'] = 'Deaktivieren Sie diese Option, wenn die Eingabe von Beschreibungen für jede Aktivität nicht verpflichtend sein soll.';
$string['configmaximumgrade'] = 'Voreinstellung für den Wert im Feld "Höchste Bewertung" beim Erstellen eines neuen Terminplaners. Diese Einstellung entspricht dem Beurteilungsmaximum, das ein/e Teilnehmer/in erhalten kann.';
$string['configabsolutedeadline'] = 'Voreinstellung für den Offset der Datums- und Zeitauswahl, ausgehend vom jetzigen Zeitpunkt.';
$string['configrelativedeadline'] = 'Voreinstellung für den Zeitpunkt an dem Teilnehmer/innen vor einem Termin davon in Kenntnis gesetzt werden sollten.';
$string['configdigest'] = 'Zusammenfassung der Termine für den jeweils nächsten Tag an Trainer/in versenden.';
$string['configemailteachers'] = 'E-Mail Benachrichtigungen an Trainer/in bezüglich Änderungen der Anmeldungsstatus';
$string['configlocationlink'] = 'Link zu Suchmaschine, die den Weg zum Zielort zeigt. Setzen Sie $searchstring in die URL ein, die die Anfrage bearbeitet.';
$string['confignever'] = 'Nie';
$string['configdontsend'] = 'Nicht senden';
$string['configminute'] = 'Minute';
$string['configminutes'] = 'Minuten';
$string['confighour'] = 'Stunde';
$string['confighours'] = 'Stunden';
$string['configday'] = 'Tag';
$string['configdays'] = 'Tage';
$string['configweek'] = 'Woche';
$string['configweeks'] = 'Wochen';
$string['configmonth'] = 'Monat';
$string['configmonths'] = 'Monate';
$string['configyear'] = 'Jahr';
$string['configahead'] = 'vorher';

$string['configemailteachers_label'] = 'E-Mail Benachrichtigungen';
$string['configdigest_label'] = 'Zusammenfassungen';

$string['no_slots_defined'] = 'Derzeit sind keine Zeitslots verfügbar.';
$string['no_slots_defined_teacher'] = 'Derzeit sind keine Zeitslots verfügbar. Legen Sie <a href="{$a->link}">hier</a> neue an.';

$string['eventnoparticipants'] = 'Keine Teilnehmer/innen';
$string['eventteacheranonymous'] = '<em>anonymous</em>';

$string['organizer:addinstance'] = 'Organizer hinzufügen';
$string['organizer:comment'] = 'Kommentare hinzufügen';
$string['organizer:addslots'] = 'Neue Zeitslots hinzufügen';
$string['organizer:editslots'] = 'Vorhandene Zeitslots bearbeiten';
$string['organizer:evalslots'] = 'Abgeschlossene Zeitslots bewerten';
$string['organizer:deleteslots'] = 'Vorhandene Zeitslots löschen';
$string['organizer:sendreminders'] = 'Anmeldungserinnerungen an Teilnehmer/innen senden';
$string['organizer:printslots'] = 'Vorhandene Zeitslots drucken';
$string['organizer:receivemessagesstudent'] = 'Nachrichten wie als Teilnehmer/in empfangen';
$string['organizer:receivemessagesteacher'] = 'Nachrichten wie als Trainer/in empfangen';
$string['organizer:register'] = 'Für einen Zeitslot anmelden';
$string['organizer:unregister'] = 'Von Zeitslot abmelden';
$string['organizer:viewallslots'] = 'Alle Zeitslots als Trainer/in ansehen';
$string['organizer:viewmyslots'] = 'Eigene Zeitslots als Trainer/in ansehen';
$string['organizer:viewstudentview'] = 'Alle Zeitslots als Teilnehmer/in ansehen';
$string['organizer:viewregistrations'] = 'Status der Anmeldung von Teilnehmer/innen ansehen';

$string['messageprovider:test'] = 'Terminplaner Test Nachricht';
$string['messageprovider:appointment_reminder:student'] = 'Terminplaner Terminerinnerung';
$string['messageprovider:appointment_reminder:teacher'] = 'Terminplaner Terminerinnerung (Trainer/in)';
$string['messageprovider:manual_reminder:student'] = 'Terminplaner manuele Terminerinnerung';
$string['messageprovider:eval_notify:student'] = 'Terminplaner Bewertungsbenachrichtigung';
$string['messageprovider:register_notify:teacher'] = 'Terminplaner Registrierungsbenachrichtigung';
$string['messageprovider:group_registration_notify:student'] = 'Terminplaner Gruppenregistrierung Benachrichtigung';
$string['messageprovider:register_reminder:student'] = 'Terminplaner Registrierungserinnerung';
$string['messageprovider:edit_notify:student'] = 'Terminplaner Änderungen';
$string['messageprovider:edit_notify:teacher'] = 'Terminplaner Änderungen (Trainer/in)';
$string['messageprovider:slotdeleted_notify:student'] = 'Terminplaner Slot absagen';

/* Message templates following.
 * Please note that the following strings are available:
 * 	   sendername, receivername, courseshortname, courselongname, courseid, organizername,
 *     date, time, location, groupname, courselink
 * If more strings are required, add them to the $strings object in messaging.php
 */

// slotdeleted student

$string['slotdeleted_notify:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin abgesagt';
$string['slotdeleted_notify:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseshortname} wurde ihr Termin am {$a->date} um {$a->time} im {$a->location} abgesagt.
Beachten Sie dabei, dass Sie keinen Termin mehr im Terminplaner {$a->organizername} haben!
Für einen Ersatztermin folgen Sie bitte dem Link: {$a->courselink}';
$string['slotdeleted_notify:student:smallmessage'] = 'Ihr Termin am {$a->date} um {$a->time} im {$a->organizername} wurde abgesagt.';

// slotdeleted student group

$string['slotdeleted_notify:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin abgesagt';
$string['slotdeleted_notify:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseshortname} wurde ihr Termin am {$a->date} um {$a->time} im {$a->location} abgesagt.
Beachten Sie dabei, dass Sie keinen Termin mehr im Terminplaner {$a->organizername} haben!
Für einen Ersatztermin folgen Sie bitte dem Link: {$a->courselink}';
$string['slotdeleted_notify:student:group:smallmessage'] = 'Ihr Termin am {$a->date} um {$a->time} im {$a->organizername} wurde abgesagt.';

// register teacher register

$string['register_notify:teacher:register:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Teilnehmer/in angemeldet';
$string['register_notify:teacher:register:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Teilnehmer/in {$a->sendername} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.

Moodle Messaging System';
$string['register_notify:teacher:register:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat sich für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.';

// register teacher queue

$string['register_notify:teacher:queue:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Teilnehmer/in in Warteliste eingetragen';
$string['register_notify:teacher:queue:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Teilnehmer/in {$a->sendername} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} in die Warteliste eingetragen.

Moodle Messaging System';
$string['register_notify:teacher:queue:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat sich für den Zeitslot am {$a->date} um {$a->time} im {$a->location} in die Warteliste eingetragen.';

// register teacher reregister

$string['register_notify:teacher:reregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Teilnehmer/in umgemeldet';
$string['register_notify:teacher:reregister:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Teilnehmer/in {$a->sendername} für den neuen Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.

Moodle Messaging System';
$string['register_notify:teacher:reregister:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat sich für den Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.';

// register teacher unregister

$string['register_notify:teacher:unregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Teilnehmer/in abgemeldet';
$string['register_notify:teacher:unregister:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Teilnehmer/in {$a->sendername} vom Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.

Moodle Messaging System';
$string['register_notify:teacher:unregister:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat sich vom Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.';

// register teacher unqueue

$string['register_notify:teacher:unqueue:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Teilnehmer/in aus Warteliste ausgetragen';
$string['register_notify:teacher:unqueue:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Teilnehmer/in {$a->sendername} im  Zeitslot am {$a->date} um {$a->time} im {$a->location} aus der Warteliste ausgetragen.

Moodle Messaging System';
$string['register_notify:teacher:unqueue:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat sich im Zeitslot am {$a->date} um {$a->time} im {$a->location} aus der Warteliste ausgetragen.';

// register teacher register group

$string['register_notify:teacher:register:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe angemeldet';
$string['register_notify:teacher:register:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Teilnehmer/in {$a->sendername} die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.

Moodle Messaging System';
$string['register_notify:teacher:register:group:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.';

// register teacher queue group

$string['register_notify:teacher:queue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe in Warteliste eingetragen';
$string['register_notify:teacher:queue:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Teilnehmer/in {$a->sendername} die Gruppe {$a->groupname} in die Warteliste für den Zeitslot am {$a->date} um {$a->time} im {$a->location} eingetragen.

Moodle Messaging System';
$string['register_notify:teacher:queue:group:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat die Gruppe {$a->groupname} in die Warteliste für den Zeitslot am {$a->date} um {$a->time} im {$a->location} eingetragen.';

// register teacher reregister group

$string['register_notify:teacher:reregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group umgemeldet';
$string['register_notify:teacher:reregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Teilnehmer/in {$a->sendername} die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.

Moodle Messaging System';
$string['register_notify:teacher:reregister:group:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.';

// register teacher unregister group

$string['register_notify:teacher:unregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe abgemeldet';
$string['register_notify:teacher:unregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Teilnehmer/in {$a->sendername} die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.

Moodle Messaging System';
$string['register_notify:teacher:unregister:group:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.';

// register teacher unqueue group

$string['register_notify:teacher:unqueue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe aus Warteliste ausgetragen';
$string['register_notify:teacher:unqueue:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Teilnehmer/in {$a->sendername} die Gruppe {$a->groupname} aus der Warteliste für den Zeitslot am {$a->date} um {$a->time} im {$a->location} ausgetragen.

Moodle Messaging System';
$string['register_notify:teacher:unqueue:group:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat die Gruppe {$a->groupname} aus der Warteliste für den Zeitslot am {$a->date} um {$a->time} im {$a->location} ausgetragen.';

// eval student

$string['eval_notify:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Termin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location} bewertet worden.

Moodle Messaging System';
$string['eval_notify:student:smallmessage'] = 'Ihr Termin am {$a->date} um {$a->time} im {$a->location} ist bewertet worden.';

// eval newappointment student

$string['eval_notify_newappointment:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify_newappointment:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Termin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location} bewertet worden.

Die Trainer/innen des Kurses ermölichen Ihnen, sich nochmals im Terminplaner {$a->organizername} zu einem noch freien Termin anzumelden.

Moodle Messaging System';
$string['eval_notify_newappointment:student:smallmessage'] = 'Ihr Termin am {$a->date} um {$a->time} im {$a->location} ist bewertet worden.';

// eval student group.

$string['eval_notify:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Gruppentermin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location} bewertet worden.

Moodle Messaging System';
$string['eval_notify:student:group:smallmessage'] = 'Ihr Gruppentermin am {$a->date} um {$a->time} im {$a->location} ist bewertet worden.';

// eval newappointment student group

$string['eval_notify_newappointment:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify_newappointment:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Gruppentermin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location} bewertet worden.

Die Trainer/innen des Kurses ermöglichen Ihnen, sich nochmals im Terminplaner {$a->coursefullname} zu einem noch freien Termin anzumelden.

Moodle Messaging System';
$string['eval_notify_newappointment:student:group:smallmessage'] = 'Ihr Gruppentermin am {$a->date} um {$a->time} im {$a->location} ist bewertet worden.';

// edit student

$string['edit_notify:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termindetails verändert';
$string['edit_notify:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Termins mit {$a->sendername} am {$a->date} um {$a->time} verändert worden.

Trainer/in: {$a->slot_teacher}
Ort: {$a->slot_location}
Höchstanzahl der Teilnehmer/innen: {$a->slot_maxparticipants}
Kommentar:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify:student:smallmessage'] = 'Die Details des Termins mit {$a->sendername} am {$a->date} um {$a->time} sind verändert worden.';

// edit student group

$string['edit_notify:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termindetails verändert';
$string['edit_notify:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Gruppentermins mit {$a->sendername} am {$a->date} um {$a->time} verändert worden.

Trainer/in: {$a->slot_teacher}
Ort: {$a->slot_location}
Höchstanzahl der Teilnehmer/innen: {$a->slot_maxparticipants}
Kommentar:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify:student:group:smallmessage'] = 'Die Details des Gruppentermins mit {$a->sendername} am {$a->date} um {$a->time} sind verändert worden.';

// edit teacher

$string['edit_notify:teacher:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termindetails verändert';
$string['edit_notify:teacher:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Zeitslots am {$a->date} um {$a->time} von {$a->sendername} verändert worden.

Trainer/in: {$a->slot_teacher}
Ort: {$a->slot_location}
Höchstanzahl der Teilnehmer/innen: {$a->slot_maxparticipants}
Kommentar:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify:teacher:smallmessage'] = 'Die Details des Zeitslots am {$a->date} um {$a->time} sind von {$a->sendername} verändert worden.';

// edit teacher group

$string['edit_notify:teacher:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] -  Termindetails verändert';
$string['edit_notify:teacher:group:fullmessage'] =
'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Zeitslots am {$a->date} um {$a->time} von {$a->sendername} verändert worden.

Trainer/in: {$a->slot_teacher}
Ort: {$a->slot_location}
Höchstanzahl der Teilnehmer/innen: {$a->slot_maxparticipants}
Kommentar:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify:teacher:group:smallmessage'] = 'Die Details des Zeitslots am {$a->date} um {$a->time} sind von {$a->sendername} verändert worden.';

// register reminder student

$string['register_reminder:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Anmeldungserinnerung';
$string['register_reminder:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie sich entweder noch nicht für einen Zeitslot angemeldet, oder denjenigen verpasst für den Sie sich angemeldet haben.

{$a->custommessage}

Moodle Messaging System';
$string['register_reminder:student:smallmessage'] = 'Bitte melden Sie sich für einen neuen Zeitslot an.';

// register reminder ??? group

$string['register_reminder:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Anmeldungserinnerung';
$string['register_reminder:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Ihre Gruppe {$a->groupname} entweder noch nicht für einen Zeitslot angemeldet, oder denjenigen verpasst für den Sie sich angemeldet hat.

{$a->custommessage}

Moodle Messaging System';
$string['register_reminder:group:smallmessage'] = 'Bitte melden Sie sich für einen neuen Zeitslot an.';

// register reminder student group

$string['register_reminder:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Anmeldungserinnerung';
$string['register_reminder:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Ihre Gruppe {$a->groupname} entweder noch nicht für einen Zeitslot angemeldet, oder denjenigen verpasst für den Sie sich angemeldet hat.

{$a->custommessage}

Moodle Messaging System';
$string['register_reminder:student:group:smallmessage'] = 'Bitte melden Sie sich für einen neuen Zeitslot an.';

// group registration student register group

$string['group_registration_notify:student:register:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe angemeldet';
$string['group_registration_notify:student:register:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:register:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} angemeldet.';

// Section Warteliste.

$string['group_registration_notify:student:queue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe in Warteliste eingetragen';
$string['group_registration_notify:student:queue:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} in die Warteliste für den Zeitslot am {$a->date} um {$a->time} im {$a->location} eingetragen.

Moodle Messaging System';
$string['group_registration_notify:student:queue:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} in die Warteliste für den Zeitslot am {$a->date} um {$a->time} eingetragen.';

// group registration student reregister group

$string['group_registration_notify:student:reregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe umgemeldet';
$string['group_registration_notify:student:reregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} für einen neuen Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:reregister:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} für einen neuen Zeitslot am {$a->date} um {$a->time} umgemeldet.';

// group registration student unregister group

$string['group_registration_notify:student:unregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe abgemeldet';
$string['group_registration_notify:student:unregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} vom Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:unregister:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} vom Zeitslot am {$a->date} um {$a->time} abgemeldet.';

// group registration student unqueue group

$string['group_registration_notify:student:unqueue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe aus Warteliste ausgetragen';
$string['group_registration_notify:student:unqueue:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} aus der Warteliste vom Zeitslot am {$a->date} um {$a->time} im {$a->location} ausgetragen.

Moodle Messaging System';
$string['group_registration_notify:student:unqueue:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} aus der Warteliste vom Zeitslot am {$a->date} um {$a->time} ausgetragen.';

// group registration student register

$string['group_registration_notify:student:register:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe angemeldet';
$string['group_registration_notify:student:register:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im {$a->location} angemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:register:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} angemeldet.';

// group registration student reregister

$string['group_registration_notify:student:reregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe umgemeldet';
$string['group_registration_notify:student:reregister:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} für einen neuen Zeitslot am {$a->date} um {$a->time} im {$a->location} umgemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:reregister:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} für einen neuen Zeitslot am {$a->date} um {$a->time} umgemeldet.';

// group registration student unregister

$string['group_registration_notify:student:unregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe abgemeldet';
$string['group_registration_notify:student:unregister:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} vom Zeitslot am {$a->date} um {$a->time} im {$a->location} abgemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:unregister:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} vom Zeitslot am {$a->date} um {$a->time} abgemeldet.';

// appointment reminder teacher

$string['appointment_reminder:teacher:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Terminerinnerung';
$string['appointment_reminder:teacher:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie einen Termin mit Teilnehmer/innen am {$a->date} um {$a->time} im {$a->location}.

Moodle Messaging System';
$string['appointment_reminder:teacher:smallmessage'] = 'Sie haben einen Termin mit Teilnehmer/innen am {$a->date} um {$a->time} im {$a->location}.';

// appointment reminder teacher digest

$string['appointment_reminder:teacher:digest:subject'] = 'Terminzusammenfassung';
$string['appointment_reminder:teacher:digest:fullmessage'] = 'Hallo {$a->receivername}!

Sie haben morgen folgende Termine:

{$a->digest}

Moodle Messaging System';
$string['appointment_reminder:teacher:digest:smallmessage'] = 'Sie haben eine zusammenfassende Nachricht bezüglich Ihre morgigen Termine erhalten.';

// appointment reminder teacher group digest

$string['appointment_reminder:teacher:group:digest:subject'] = 'Terminzusammenfassung';
$string['appointment_reminder:teacher:group:digest:fullmessage'] = 'Hallo {$a->receivername}!

Sie haben morgen folgende Termine:

{$a->digest}

Moodle Messaging System';
$string['appointment_reminder:teacher:group:digest:smallmessage'] = 'Sie haben eine zusammenfassende Nachricht bezüglich Ihre morgigen Termine erhalten.';

// appointment reminder student

$string['appointment_reminder:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Terminerinnerung';
$string['appointment_reminder:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie einen Termin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location}.

Moodle Messaging System';
$string['appointment_reminder:student:smallmessage'] = 'Sie haben einen Termin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location}.';

// appointment reminder student group

$string['appointment_reminder:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppenterminerinnerung';
$string['appointment_reminder:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie einen Gruppentermin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location}.

Moodle Messaging System';
$string['appointment_reminder:student:group:smallmessage'] = 'Sie haben einen Gruppentermin mit {$a->sendername} am {$a->date} um {$a->time} im {$a->location}.';

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
$string['legend_comments'] = 'Kommentare Teilnehmer/innen oder Trainer/innen';
$string['legend_feedback'] = 'Feedback';
$string['legend_not_occured'] = 'Termin hat noch nicht stattgefunden';

$string['legend_section_status'] = 'Statussymbole';
$string['legend_section_details'] = 'Detailsymbole';

$string['th_group'] = 'Gruppe';

$string['reg_status'] = 'Status der Registrierung';

$string['recipientname'] = '&lt;Empfängername&gt;';

$string['modformwarningsingular'] = 'Dieses Feld kann nicht bearbeitet werden, da es in diesem Terminplaner schon angemeldete Teilnehmer/innen gibt!';
$string['modformwarningplural'] = 'Diese Felder können nicht bearbeitet werden, da es in diesem Terminplaner schon angemeldete Teilnehmer/innen gibt!';

$string['teachercomment_title'] = 'Kommentare Trainer/innen';
$string['studentcomment_title'] = 'Kommentare Teilnehmer/innen';
$string['teacherfeedback_title'] = 'Rückmeldung Trainer/innen';
$string['relative_deadline_before'] = 'vor dem Termin';
$string['relative_deadline_now'] = 'Ab sofort';

$string['nogroup'] = 'Keine Gruppe';
$string['noparticipants'] = 'Keine Teilnehmer/innen';
$string['unavailableslot'] = 'Dieser Slot ist verfügbar ab';
$string['applicant'] = 'Person, die die Gruppe registriert hat';

$string['deleteheader'] = 'Löschen der folgenden Slots:';
$string['deletenoslots'] = 'Keine löschbaren Slots ausgewählt';
$string['deletekeep'] = 'Die folgenden Termine werden abgesagt, die angemeldeten Teilnehmer/innen werden benachrichtigt und die Slots gelöscht:';

$string['atlocation'] = 'in';

$string['exportsettings'] = 'Exporteinstellungen';
$string['format'] = 'Format';
$string['format_pdf'] = 'PDF';
$string['format_xls'] = 'XLS';
$string['format_xlsx'] = 'XLSX';
$string['format_ods'] = 'ODS';
$string['format_csv_tab'] = 'CSV (tab)';
$string['format_csv_comma'] = 'CSV (;)';
$string['pdfsettings'] = 'PDF Einstellungen';
$string['numentries'] = 'Einträge pro Seite';
$string['numentries_help'] = 'Wenn in Ihrem Kurs sehr viele Teilnehmer/innen eingeschrieben sind, können Sie mittels der Einstellung "Optimal" die Aufteilung der Listeneinträge pro Seite entsprechend der gewählten Schriftgröße und Seitenausrichtung optimieren.';
$string['stroptimal'] = 'optimal';
$string['textsize'] = 'Textgröße';
$string['pageorientation'] = 'Seitenausrichtung';
$string['orientationportrait'] = 'Hochformat';
$string['orientationlandscape'] = 'Querformat';

$string['headerfooter'] = 'Kopf-/Fußzeilen';
$string['headerfooter_help'] = 'Inkludiere Kopf-/Fußzeile';
$string['printpreview'] = 'Druckvorschau (erste 10 Einträge)';
$string['pdf_notactive'] = 'nicht aktiviert';

$string['datapreviewtitle'] = 'Datenvorschau';
$string['datapreviewtitle_help'] = "Klicken Sie in der Vorschau auf [+] bzw. [-], um die zu druckenden Spalten ein- bzw. auszublenden.";

$string['unknown'] = 'Unbekannt';

$string['totalslots'] = 'von {$a->starttime} bis {$a->endtime}, je {$a->duration} {$a->unit}, {$a->totalslots} Slot(s) insgesamt';

$string['warninggroupmode'] = 'Sie müssen den Gruppenmodus einschalten und eine Gruppierung auswählen, um einen Gruppenterminplaner zu erstellen!';

$string['multimember'] = 'Teilnehmer dürfen nicht binnen einer Gruppierung zu mehreren Gruppen gehören!';
$string['multimemberspecific'] = 'Teilnehmer {$a->username} {$a->idnumber} hat sich für mehr als eine Gruppe angemeldet! ({$a->groups})';
$string['groupwarning'] = 'Prüfen Sie die Gruppeneinstellungen unten!';

$string['duedateerror'] = 'Endgültige Deadline darf nicht vor dem Verfügbarkeitsdatum definiert werden!';
$string['invalidgrouping'] = 'Sie müssen eine gültige Gruppierung auswählen!';

$string['maillink'] = 'Der Terminplaner ist unter <a href="{$a}">diesem</a> Link verfügbar.';

// Section.

$string['eventappwith:single'] = 'Einzeltermin';
$string['eventappwith:group'] = 'Gruppentermin';

$string['eventwith'] = 'mit';
$string['eventwithout'] = '';

$string['eventnoparticipants'] = 'ohne Teilnehmer/innen';
$string['eventteacheranonymous'] = 'einem/einer anonymen Trainer/in';

$string['eventtitle'] = '{$a->coursename} / {$a->organizername}: {$a->appwith}';

$string['eventtemplate'] = '{$a->courselink} / {$a->organizerlink}: {$a->appwith} {$a->with} {$a->participants}<br />Ort: {$a->location}<br />';
$string['eventtemplatecomment'] = 'Kommentar:<br />{$a}<br />';

$string['fulldatetimelongtemplate'] = '%A %d. %B %Y %H:%M';
$string['fulldatetimetemplate'] = '%a %d.%m.%Y %H:%M';
$string['fulldatelongtemplate'] = '%A %d. %B %Y';
$string['fulldatetemplate'] = '%a %d.%m.%Y';
$string['datetemplate'] = '%d.%m.%Y';
$string['timetemplate'] = '%H:%M';

$string['font_small'] = 'klein';
$string['font_medium'] = 'mittel';
$string['font_large'] = 'groß';

$string['printout'] = 'Ausdruck';
$string['confirm_organizer_remind_all'] = 'Senden';

$string['cannot_eval'] = 'Kann nicht bewertet werden. Diese(r) Teilnehmer/innen hat';
$string['eval_link'] = 'einen neuen Termin';

$string['group_slot_available'] = "Slot verfügbar";
$string['group_slot_full'] = "Slot vergeben";
$string['slot_anonymous'] = "Anonymer Slot";
$string['slot_slotvisible'] = "Mitglieder nur sichtbar wenn eigener Slot";
$string['slot_visible'] = "Mitglieder des Slots immer sichtbar";

$string['message_info_reminders_sent_sg'] = 'Es wurde {$a->count} Mitteilung versandt.';
$string['message_info_reminders_sent_pl'] = 'Es wurden {$a->count} Mitteilungen versandt.';

$string['organizer_remind_all_recepients_sg'] = 'Es wird insgesamt {$a->count} Mitteilung an nachfolgende Empfänger versandt:';
$string['organizer_remind_all_recepients_pl'] = 'Es werden insgesamt {$a->count} Mitteilungen an nachfolgende Empfänger versandt:';

$string['organizer_remind_all_no_recepients'] = 'Es gibt keine gültige Empfänger.';

$string['print_return'] = 'Zurück zur Terminansicht';

$string['message_error_slot_full_single'] = 'Dieser Slot hat keine freien Plätze mehr!';
$string['message_error_slot_full_group'] = 'Dieser Slot ist vergeben!';

$string['message_error_unknown_unqueue'] = 'Ihr Wartelisten-Eintrag konnte nicht entfernt werden! Unbekannter Fehler.';
$string['message_error_unknown_unregister'] = 'Ihre Registrierung konnte nicht entfernt werden! Unbekannter Fehler.';

$string['organizer_remind_all_title'] = 'Erinnerungen versenden';
$string['can_reregister'] = 'Sie können sich für einen anderen Termin neu anmelden.';

$string['messages_none'] = 'Keine Benachrichtigungen';
$string['messages_re_unreg'] = 'Nur Ab-/Ummeldungen';
$string['messages_all'] = 'Alle Anmeldungen und Ab-/Ummeldungen';

$string['reset_organizer_all'] = 'Löschen aller Slots, Anmeldungen und zugehörigen Kalendereinträge';
$string['delete_organizer_grades'] = 'Löschen aller Bewertungen';
$string['timeshift'] = 'Verschiebung endgültiger Deadline';

$string['queue'] = 'Wartelisten';
$string['queue_help'] = 'Wartelisten erlauben es, sich für einen Termin anzumelden auch wenn dieser schon ausgebucht ist.
		Sobald ein Termin dann doch noch frei wird, wird der/die erste Teilnehmer/in aus der Warteliste automatisch nachgerückt.';
$string['queuesubject'] = 'Moodle Organizer: Aus Warteliste nachgerückt';
$string['queuebody'] = 'Ihre Anmeldung zu einem Termin wurde vom Status "Warteliste" in den Status "Angemeldet" versetzt.';
$string['eventqueueadded'] = 'Zur Warteliste hinzugefügt';
$string['eventqueueremoved'] = 'Aus Warteliste entfernt';

$string['visibility_anonymous'] = 'Anonym';
$string['visibility_slot'] = 'Sichtbar nur, wenn eigener Slot';
$string['visibility_all'] = 'Sichtbar';

$string['hidecalendar'] = 'Kalender verbergen';
$string['hidecalendar_help'] = 'Stellen Sie hier ein, ob der Kalender in diesem Terminplaner ausgeblendet werden soll.';
