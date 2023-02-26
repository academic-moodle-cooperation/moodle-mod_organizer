<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Strings for component 'organizer', language 'de', version '3.11'.
 *
 * @package     mod_organizer
 * @category    string
 * @copyright   1999 Martin Dougiamas and contributors
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['absolutedeadline'] = 'Anmeldeende';
$string['absolutedeadline_help'] = 'Ankreuzen um die Bestimmung einer absoluten Deadline zu ermöglichen.
    Es sind nach diesem Zeitpunkt keinerlei Aktionen seitens der Teilnehmer/innen mehr möglich.';
$string['actionlink_delete'] = 'entfernen';
$string['actionlink_edit'] = 'bearbeiten';
$string['actionlink_eval'] = 'bewerten';
$string['actionlink_print'] = 'drucken';
$string['actions'] = 'Aktion';
$string['actions_help'] = 'Durchführbare Aktion(en).';
$string['addappointment'] = 'Termin hinzufügen';
$string['addslots_placesinfo'] = 'Diese Aktion erstellt {$a->numplaces} neue mögliche Plätze, was zu einer Gesamtanzahl von {$a->totalplaces} möglichen Plätzen für {$a->numstudents} Teilnehmer/innen führt.';
$string['addslots_placesinfo_group'] = 'Diese Aktion erstellt {$a->numplaces} neue mögliche Plätze, was zu einer Gesamtanzahl von {$a->totalplaces} möglichen Plätzen für {$a->numgroups} Gruppen führt.';
$string['allowcreationofpasttimeslots'] = 'Termine in der Vergangenheit anlegen';
$string['allowedprofilefieldsprint'] = 'Erlaubte Termin-Ausdruck Profilfelder';
$string['allowedprofilefieldsprint2'] = 'Erlaubte Termin-Ausdruck Profilfelder für den Druck von Slots.';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'Die Terminplanerdetails und die Registrierung stehen zur Verfügung ab <strong>{$a}</strong>';
$string['allowsubmissionsfromdate'] = 'Anmeldebeginn';
$string['allowsubmissionsfromdate_help'] = 'Kreuzen Sie diese Option an um den Teilnehmer/innen den Zugang zu diesem Terminplaner erst ab einem bestimmten Zeitpunkt zu ermöglichen.';
$string['allowsubmissionsfromdatesummary'] = 'Anmeldungen möglich ab <strong>{$a}</strong>';
$string['allowsubmissionstodate'] = 'Anmeldeende';
$string['alwaysshowdescription'] = 'Beschreibung immer anzeigen';
$string['alwaysshowdescription_help'] = 'Wenn diese Option deaktiviert ist, wird die Aufgabenbeschreibung für Teilnehmer/innen nur während des Anmeldezeitraums angezeigt.';
$string['applicant'] = 'Person, die die Gruppe registriert hat';
$string['appointment_reminder_student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie einen Termin {$a->sendername} am {$a->date} um {$a->time} im/in {$a->location}.

Moodle Messaging System';
$string['appointment_reminder_student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie einen Gruppentermin {$a->sendername} am {$a->date} um {$a->time} im/in {$a->location}.

Moodle Messaging System';
$string['appointment_reminder_student:group:smallmessage'] = 'Sie haben einen Gruppentermin {$a->sendername} am {$a->date} um {$a->time} im/in {$a->location}.';
$string['appointment_reminder_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppenterminerinnerung';
$string['appointment_reminder_student:smallmessage'] = 'Sie haben einen Termin {$a->sendername} am {$a->date} um {$a->time} im/in {$a->location}.';
$string['appointment_reminder_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Terminerinnerung';
$string['appointment_reminder_teacher:digest:fullmessage'] = 'Hallo {$a->receivername}!

Sie haben morgen folgende Termine:

{$a->digest}

Moodle Messaging System';
$string['appointment_reminder_teacher:digest:smallmessage'] = 'Sie haben eine zusammenfassende Nachricht bezüglich Ihrer morgigen Termine erhalten.';
$string['appointment_reminder_teacher:digest:subject'] = 'Terminzusammenfassung';
$string['appointment_reminder_teacher:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie einen Termin mit Teilnehmer/innen am {$a->date} um {$a->time} im/in {$a->location}.

Moodle Messaging System';
$string['appointment_reminder_teacher:group:digest:fullmessage'] = 'Hallo {$a->receivername}!

Sie haben morgen folgende Termine:

{$a->digest}

Moodle Messaging System';
$string['appointment_reminder_teacher:group:digest:smallmessage'] = 'Sie haben eine zusammenfassende Nachricht bezüglich Ihrer morgigen Termine erhalten.';
$string['appointment_reminder_teacher:group:digest:subject'] = 'Terminzusammenfassung';
$string['appointment_reminder_teacher:smallmessage'] = 'Sie haben einen Termin mit Teilnehmer/innen am {$a->date} um {$a->time} im/in {$a->location}.';
$string['appointment_reminder_teacher:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Terminerinnerung';
$string['appointmentcomments'] = 'Kommentare';
$string['appointmentcomments_help'] = 'Zusätzliche Informationen zum Termin können hier ergänzt werden.';
$string['appointmentdatetime'] = 'Datum & Zeit';
$string['assign'] = 'Zuweisen';
$string['assign_notify_student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, wurde Ihnen der Zeitslot mit {$a->slot_teacher} am {$a->date} um {$a->time} im/in {$a->location} durch {$a->sendername} zugewiesen.

Trainer/in: {$a->slot_teacher}
Ort: {$a->slot_location}
Datum: {$a->date} um {$a->time}

Moodle Messaging System';
$string['assign_notify_student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, wurde Ihrer Gruppe {$a->groupname} der Zeitslot mit {$a->slot_teacher} am {$a->date} um {$a->time} im/in {$a->location} durch {$a->sendername} zugewiesen.

Trainer/in: {$a->slot_teacher}
Ort: {$a->slot_location}
Datum: {$a->date} um {$a->time}

Moodle Messaging System';
$string['assign_notify_student:group:smallmessage'] = 'Gruppen-Termin am {$a->date} um {$a->time} durch {$a->sendername} zugewiesen.';
$string['assign_notify_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppen-Termin durch Trainer/in zugewiesen';
$string['assign_notify_student:smallmessage'] = 'Termin am {$a->date} um {$a->time} durch {$a->sendername} zugewiesen.';
$string['assign_notify_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin durch Trainer/in zugewiesen';
$string['assign_notify_teacher:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, wurde Ihnen {$a->participantname} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} von {$a->sendername} zugewiesen.

Teilnehmer/in: {$a->participantname}
Ort: {$a->slot_location}
Datum: {$a->date} um {$a->time}

Moodle Messaging System';
$string['assign_notify_teacher:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, wurde Ihnen die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} von {$a->sendername} zugewiesen.

Gruppe: {$a->groupname}
Ort: {$a->slot_location}
Datum: {$a->date} um {$a->time}

Moodle Messaging System';
$string['assign_notify_teacher:group:smallmessage'] = 'Termin am {$a->date} um {$a->time} für Gruppe {$a->groupname} von {$a->sendername} zugewiesen.';
$string['assign_notify_teacher:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppen-Termin zugewiesen';
$string['assign_notify_teacher:smallmessage'] = 'Termin am {$a->date} um {$a->time} von {$a->sendername} zugewiesen.';
$string['assign_notify_teacher:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin zugewiesen';
$string['assign_title'] = 'Termin zuweisen';
$string['assignsuccess'] = 'Der Termin wurde erfolgreich zugeteilt und der/die Teilnehmer/Innen verständigt.';
$string['assignsuccessnotsent'] = 'Der Slot wurde erfolgreich zugewiesen, aber die Teilnehmer/innen wurde nicht verständigt.';
$string['atlocation'] = 'in';
$string['attended'] = 'teilgenommen';
$string['auth'] = 'Authentifizierungsmethode';
$string['availability'] = 'Verfügbarkeit';
$string['availablefrom'] = 'Anfragen möglich ab';
$string['availablefrom_help'] = 'Definieren Sie das Zeitfenster, während welches Teilnehmer/innen sich für diese Slots anmelden können. Ersatzweise checken Sie die "Ab jetzt" Checkbox, um die Anmeldungen sofort zu ermöglichen.';
$string['availablegrouplist'] = 'Verfügbare Gruppen';
$string['availableslotsfor'] = 'Verfügbare Termine für';
$string['back'] = 'Zurück';
$string['btn_add'] = 'Neue Slots hinzufügen';
$string['btn_assign'] = 'Termin zuweisen';
$string['btn_comment'] = 'Kommentar bearbeiten';
$string['btn_delete'] = 'Ausgewählte Slots entfernen';
$string['btn_deleteappointment'] = 'Termin löschen';
$string['btn_deletesingle'] = 'Ausgewählten Slot entfernen';
$string['btn_edit'] = 'Ausgewählte Slots bearbeiten';
$string['btn_editsingle'] = 'Ausgewählten Slot bearbeiten';
$string['btn_eval'] = 'Ausgewählte Slots bewerten';
$string['btn_eval_short'] = 'Bewerten';
$string['btn_evalsingle'] = 'Ausgewählten Slot bewerten';
$string['btn_print'] = 'Ausgewählte Slots drucken';
$string['btn_printsingle'] = 'Ausgewählten Slot drucken';
$string['btn_queue'] = 'Warteliste';
$string['btn_reeval'] = 'Neu bewerten';
$string['btn_register'] = 'Anmelden';
$string['btn_remind'] = 'Erinnerung senden';
$string['btn_reregister'] = 'Ummelden';
$string['btn_save'] = 'Kommentar speichern';
$string['btn_send'] = 'Senden';
$string['btn_sendall'] = 'Erinnerungen an alle Teilnehmer:innen mit nicht genügend Buchungen versenden';
$string['btn_start'] = 'Start';
$string['btn_unqueue'] = 'Aus Warteliste entfernen';
$string['btn_unregister'] = 'Abmelden';
$string['calendarsettings'] = 'Kalender Einstellungen';
$string['cannot_eval'] = 'Kann nicht bewertet werden. Diese(r) Teilnehmer/innen hat';
$string['changegradewarning'] = 'In diesem Terminplaner sind bereits Termine bewertet worden. Bei einer Änderung der Bewertungseinstellungen sind Neuberechnungen der Bewertungen erforderlich. Sie müssen ggfs. die Neuberechnung gesondert starten.';
$string['collision'] = 'Warnung! Zeitkollision mit dem/n folgenden Termin/en entdeckt:';
$string['configabsolutedeadline'] = 'Voreinstellung für den Offset der Datums- und Zeitauswahl, ausgehend vom jetzigen Zeitpunkt.';
$string['configahead'] = 'vorher';
$string['configallowcreationofpasttimeslots'] = 'Das Anlegen von Terminen in der Vergangenheit zulassen?';
$string['configday'] = 'Tag';
$string['configdays'] = 'Tage';
$string['configdigest'] = 'Zusammenfassung der Termine für den jeweils nächsten Tag an Trainer/in versenden.';
$string['configdigest_label'] = 'Zusammenfassungen';
$string['configdontsend'] = 'Nicht senden';
$string['configemailteachers'] = 'E-Mail Benachrichtigungen an Trainer/in bezüglich Änderungen der Anmeldungsstatus';
$string['configemailteachers_label'] = 'E-Mail Benachrichtigungen';
$string['confighour'] = 'Stunde';
$string['confighours'] = 'Stunden';
$string['configintro'] = 'Die Werte die Sie hier einstellen, bestimmen die Standardwerte, die im Einstellungsformular aufscheinen, wenn Sie einen neuen Terminplaner erstellen.';
$string['configlocationlink'] = 'Link zu Suchmaschine, die den Weg zum Zielort zeigt. Setzen Sie $searchstring in die URL ein, die die Anfrage bearbeitet.';
$string['configlocationslist'] = 'Orte für die Autovervollständigung';
$string['configlocationslist_desc'] = 'Jeder Ort muss in einer neuen Spalte eingetragen werden!';
$string['configmaximumgrade'] = 'Voreinstellung für den Wert im Feld "Höchste Bewertung" beim Erstellen eines neuen Terminplaners. Diese Einstellung entspricht dem Beurteilungsmaximum, das ein/e Teilnehmer/in erhalten kann.';
$string['configminute'] = 'Minute';
$string['configminutes'] = 'Minuten';
$string['configmonth'] = 'Monat';
$string['configmonths'] = 'Monate';
$string['confignever'] = 'Nie';
$string['configrelativedeadline'] = 'Voreinstellung für den Zeitpunkt an dem Teilnehmer/innen vor einem Termin davon in Kenntnis gesetzt werden sollten.';
$string['configrequiremodintro'] = 'Deaktivieren Sie diese Option, wenn die Eingabe von Beschreibungen für jede Aktivität nicht verpflichtend sein soll.';
$string['configsingleslotprintfield'] = 'Profilfeld, das beim Ausdruck eines einzelnen Termins gedruckt wird.';
$string['configweek'] = 'Woche';
$string['configweeks'] = 'Wochen';
$string['configyear'] = 'Jahr';
$string['confirm_conflicts'] = 'Sind Sie sicher, dass Sie die Terminkollisionen übergehen möchten und die Zeitslots anlegen möchten?';
$string['confirm_delete'] = 'Löschen';
$string['confirm_organizer_remind_all'] = 'Senden';
$string['create'] = 'Erstellen';
$string['created'] = 'Erstellt';
$string['createsubmit'] = 'Zeitslots erstellen';
$string['crontaskname'] = 'Terminplaner cron Job';
$string['datapreviewtitle'] = 'Datenvorschau';
$string['datapreviewtitle_help'] = 'Klicken Sie in der Vorschau auf [+] bzw. [-], um Spalten ein- bzw. auszublenden.';
$string['datetemplate'] = '%d.%m.%Y';
$string['datetime'] = 'Datum und Zeit';
$string['datetime_help'] = 'Datum und Zeit des Termins.';
$string['day'] = 'Tag';
$string['day_0'] = 'Montag';
$string['day_1'] = 'Dienstag';
$string['day_2'] = 'Mittwoch';
$string['day_3'] = 'Donnerstag';
$string['day_4'] = 'Freitag';
$string['day_5'] = 'Samstag';
$string['day_6'] = 'Sonntag';
$string['day_pl'] = 'Tage';
$string['dbid'] = 'DB ID';
$string['defaultsingleslotprintfields'] = 'Standardmäßige Termin-Ausdruck Profilfelder';
$string['delete_organizer_grades'] = 'Löschen aller Bewertungen';
$string['deleteheader'] = 'Löschen der folgenden Slots:';
$string['deletekeep'] = 'Die folgenden Termine werden abgesagt, die angemeldeten Teilnehmer/innen werden benachrichtigt und die Slots gelöscht:';
$string['deletenoslots'] = 'Keine löschbaren Slots ausgewählt';
$string['deleteorganizergrades'] = 'Alle Bewertungen im Gradebook löschen';
$string['details'] = 'Statusanzeige';
$string['details_help'] = 'Derzeitiger Status des Termins.';
$string['downloadfile'] = 'Datei herunterladen';
$string['duedate'] = 'Abgabetermin';
$string['duedateerror'] = 'Endgültige Deadline darf nicht vor dem Verfügbarkeitsdatum definiert werden!';
$string['duration'] = 'Dauer';
$string['duration_help'] = 'Bestimmt die Dauer der Termine. Alle festgelegten Zeitfenster werden in Slots der hier definierten Dauer aufgeteilt. Überbleibende Zeit wird nicht verwendet (d.h ein 40 Minuten langes Zeitfenster und eine 15 minütige Dauer resultiert in 2 verfügbare Slots und 10 Minuten extra, die nicht verfügbar sind).';
$string['edit_notify_student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Termins mit {$a->sendername} am {$a->date} um {$a->time} verändert worden.

Lehrende/r: {$a->slot_teacher}
Ort: {$a->slot_location}
Höchstanzahl der Studierenden: {$a->slot_maxparticipants}
Kommentare:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify_student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Gruppentermins {$a->sendername} am {$a->date} um {$a->time} verändert worden.

Trainer/in: {$a->slot_teacher}
Ort: {$a->slot_location}
Höchstanzahl der Teilnehmer/innen: {$a->slot_maxparticipants}
Kommentar:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify_student:group:smallmessage'] = 'Die Details des Gruppentermins {$a->sendername} am {$a->date} um {$a->time} sind verändert worden.';
$string['edit_notify_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termindetails verändert';
$string['edit_notify_student:smallmessage'] = 'Die Details des Termins {$a->sendername} am {$a->date} um {$a->time} sind verändert worden.';
$string['edit_notify_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termindetails verändert';
$string['edit_notify_teacher:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Zeitslots am {$a->date} um {$a->time} von {$a->sendername} verändert worden.

Trainer/in: {$a->slot_teacher}
Ort: {$a->slot_location}
Höchstanzahl der Teilnehmer/innen: {$a->slot_maxparticipants}
Kommentar:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify_teacher:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, sind die Details des Zeitslots am {$a->date} um {$a->time} von {$a->sendername} verändert worden.

Trainer/in: {$a->slot_teacher}
Ort: {$a->slot_location}
Höchstanzahl der Teilnehmer/innen: {$a->slot_maxparticipants}
Kommentar:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify_teacher:group:smallmessage'] = 'Die Details des Zeitslots am {$a->date} um {$a->time} sind von {$a->sendername} verändert worden.';
$string['edit_notify_teacher:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] -  Termindetails verändert';
$string['edit_notify_teacher:smallmessage'] = 'Die Details des Zeitslots am {$a->date} um {$a->time} sind von {$a->sendername} verändert worden.';
$string['edit_notify_teacher:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termindetails verändert';
$string['edit_submit'] = 'Änderungen speichern';
$string['emailteachers'] = 'E-Mail Benachrichtigung an Trainer/in versenden';
$string['emailteachers_help'] = 'Mitteilungen an Trainer/in bezüglich der Erstanmeldung der Teilnehmer/innen sind
    normalerweise unterdrückt. Kreuzen Sie diese Option an um diese zu Ermöglichen. Bitte beachten Sie, dass
    die Mitteilungen bezüglich der Um- und Abmeldungen der Teilnehmer/innen immer gesendet werden.';
$string['enableprintslotuserfields'] = 'Änderung der Termin-Ausdruck Profilfelder zulassen';
$string['enableprintslotuserfieldsdesc'] = 'Ermöglicht es Lehrenden die unterhalb standardmäßig definierten Termin-Ausdruck Profilfelder individuell abzuändern.';
$string['err_availablefromearly'] = 'Dieses Datum kann nicht vor dem Startdatum liegen!';
$string['err_availablefromlate'] = 'Dieses Datum kann nicht nach dem Enddatum liegen!';
$string['err_availablepastdeadline'] = 'Dieser Slot kann nicht nach dem Ablauf des Terminplaners am {$a->deadline} verfügbar gemacht werden.';
$string['err_collision'] = 'Dieses Zeitfenster fällt mit anderen Zeitfenstern zusammen:';
$string['err_comments'] = 'Beschreibung notwendig!';
$string['err_enddate'] = 'Enddatum kann nicht vor dem Startdatum gesetzt werden!';
$string['err_fromto'] = 'Endzeit kann nicht vor Startzeit gesetzt werden!';
$string['err_fullminute'] = 'Die Dauer muss ganzen Minuten entsprechen.';
$string['err_fullminutegap'] = 'Die Lücke muss ganzen Minuten entsprechen.';
$string['err_isgrouporganizer_app'] = 'Der Gruppenmodus kann nicht verändert werden, da bereits gebuchte Termine in diesem Terminplaner existieren!';
$string['err_location'] = 'Ein Ort muss angegeben werden!';
$string['err_noslots'] = 'Keine Slots ausgewählt!';
$string['err_posint'] = 'Nur positive Werte erlaubt!';
$string['err_startdate'] = 'Startdatum muss in der Zukunft liegen!';
$string['eval_attended'] = 'Anwesend';
$string['eval_feedback'] = 'Feedback';
$string['eval_grade'] = 'Bewertung';
$string['eval_header'] = 'Ausgewählte Zeitslots';
$string['eval_link'] = 'einen neuen Termin';
$string['eval_no_participants'] = 'Dieser Slot hatte keine Teilnehmer/innen';
$string['eval_not_occured'] = 'Dieser Slot hat noch nicht stattgefunden';
$string['eval_notify_newappointment:student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Termin {$a->sendername} am {$a->date} um {$a->time} im/in {$a->location} bewertet worden.

Die Trainer/innen des Kurses ermöglichen Ihnen, sich nochmals im Terminplaner {$a->organizername} zu einem noch freien Termin anzumelden.

Moodle Messaging System';
$string['eval_notify_newappointment:student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Gruppentermin {$a->sendername} am {$a->date} um {$a->time} im/in {$a->location} bewertet worden.

Moodle Messaging System';
$string['eval_notify_newappointment:student:group:smallmessage'] = 'Ihr Gruppentermin am {$a->date} um {$a->time} im/in {$a->location} ist bewertet worden.';
$string['eval_notify_newappointment:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify_newappointment:student:smallmessage'] = 'Ihr Termin am {$a->date} um {$a->time} im/in {$a->location} ist bewertet worden.';
$string['eval_notify_newappointment:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify_student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Termin {$a->sendername} am {$a->date} um {$a->time} im/in {$a->location} bewertet worden.

Moodle Messaging System';
$string['eval_notify_student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, ist Ihr Gruppentermin {$a->sendername} am {$a->date} um {$a->time} im/in {$a->location} bewertet worden.

Moodle Messaging System';
$string['eval_notify_student:group:smallmessage'] = 'Ihr Gruppentermin am {$a->date} um {$a->time} im/in {$a->location} ist bewertet worden.';
$string['eval_notify_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['eval_notify_student:smallmessage'] = 'Ihr Termin am {$a->date} um {$a->time} im/in {$a->location} ist bewertet worden.';
$string['eval_notify_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin bewertet';
$string['evaluate'] = 'Speichern';
$string['event'] = 'Kalendereintrag';
$string['eventappointmentadded'] = 'Teilnehmer/in hat sich zu einem Termin angemeldet.';
$string['eventappointmentassigned'] = 'Termin wurde durch Trainer/in zugewiesen.';
$string['eventappointmentcommented'] = 'Termin wurde kommentiert.';
$string['eventappointmentevaluated'] = 'Termin wurde bewertet.';
$string['eventappointmentlistprinted'] = 'Terminliste wurde gedruckt.';
$string['eventappointmentremindersent'] = 'Terminerinnerung zu Anmeldung zu einem Termin gesendet.';
$string['eventappointmentremoved'] = 'Teilnehmer/in wurde von einem Termin abgemeldet.';
$string['eventappwith:group'] = 'Gruppentermin';
$string['eventappwith:single'] = 'Einzeltermin';
$string['eventnoparticipants'] = 'Ohne Teilnehmer/innen';
$string['eventqueueadded'] = 'Zur Warteliste hinzugefügt';
$string['eventqueueremoved'] = 'Aus Warteliste entfernt';
$string['eventregistrationsviewed'] = 'Anmeldungen angezeigt.';
$string['eventslotcreated'] = 'Neuer Termin angelegt.';
$string['eventslotdeleted'] = 'Termin abgesagt.';
$string['eventslotupdated'] = 'Termin bearbeitet.';
$string['eventslotviewed'] = 'Termin angezeigt.';
$string['eventteacheranonymous'] = 'einem/einer anonymen Trainer/in';
$string['eventtemplate'] = '{$a->courselink} / {$a->organizerlink}: {$a->appwith} {$a->with} {$a->participants}<br />Ort: {$a->location}<br />';
$string['eventtemplatecomment'] = 'Kommentar:<br />{$a}<br />';
$string['eventtemplatewithoutlinks'] = '{$a->coursename} / {$a->organizername}: {$a->appwith} {$a->with} {$a->participants}<br />Ort: {$a->location}<br />';
$string['eventtitle'] = '{$a->coursename} / {$a->organizername}: {$a->appwith}';
$string['eventwith'] = 'mit';
$string['eventwithout'] = '';
$string['exportsettings'] = 'Exporteinstellungen';
$string['filtertable'] = 'Diese Tabelle durchsuchen';
$string['filtertable_help'] = 'Alle Felder dieser Tabelle nach vorhandenen Begriffen durchsuchen';
$string['finalgrade'] = 'Dieser Wert wurde in der Kursbewertung eingetragen und kann im Terminplaner nicht überschrieben werden.';
$string['font_large'] = 'groß';
$string['font_medium'] = 'mittel';
$string['font_small'] = 'klein';
$string['format'] = 'Format';
$string['format_csv_comma'] = 'CSV (;)';
$string['format_csv_tab'] = 'CSV (tab)';
$string['format_ods'] = 'ODS';
$string['format_pdf'] = 'PDF';
$string['format_xls'] = 'XLS';
$string['format_xlsx'] = 'XLSX';
$string['fulldatelongtemplate'] = '%A %d. %B %Y';
$string['fulldatetemplate'] = '%a %d.%m.%Y';
$string['fulldatetimelongtemplate'] = '%A %d. %B %Y %H:%M';
$string['fulldatetimetemplate'] = '%a %d.%m.%Y %H:%M';
$string['fullname_template'] = '{$a->firstname} {$a->lastname}';
$string['gap'] = 'Lücke';
$string['gap_help'] = 'Bestimmt den Abstand zwischen den Terminen.';
$string['grade'] = 'Höchste Bewertung';
$string['grade_help'] = 'Bestimmt die höchste erreichbare Bewertung für jeden Termin der beurteilt werden kann.';
$string['gradeaggregationmethod'] = 'Bewertungsberechnungs-Methode';
$string['gradeaggregationmethod_help'] = 'Die Bewertungsberechnungs-Methode legt fest wie die Gesamtwertung eines/r Teilnehmer:in berechnet wird:

* Durchschnitt - Die Summe aller Terminbewertungen dividiert durch die Anzahl der Bewertungen
* Niedrigste Bewertung
* Höchste Bewertung
* Summe - Alle Terminbewertungen aufsummiert';
$string['grading_desc_grade'] = 'Bewertungen wurden aktiviert.';
$string['grading_desc_nograde'] = 'Bewertungen sind nicht aktiviert.';
$string['group_registration_notify:student:queue:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} in die Warteliste für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} eingetragen.

Moodle Messaging System';
$string['group_registration_notify:student:queue:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} in die Warteliste für den Zeitslot am {$a->date} um {$a->time} eingetragen.';
$string['group_registration_notify:student:queue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe in Warteliste eingetragen';
$string['group_registration_notify:student:register:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} angemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:register:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} angemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:register:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} angemeldet.';
$string['group_registration_notify:student:register:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe angemeldet';
$string['group_registration_notify:student:register:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} angemeldet.';
$string['group_registration_notify:student:register:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe angemeldet';
$string['group_registration_notify:student:reregister:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} für einen neuen Zeitslot am {$a->date} um {$a->time} im/in {$a->location} umgemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:reregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} für einen neuen Zeitslot am {$a->date} um {$a->time} im/in {$a->location} umgemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:reregister:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} für einen neuen Zeitslot am {$a->date} um {$a->time} umgemeldet.';
$string['group_registration_notify:student:reregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe umgemeldet';
$string['group_registration_notify:student:reregister:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} für einen neuen Zeitslot am {$a->date} um {$a->time} umgemeldet.';
$string['group_registration_notify:student:reregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe umgemeldet';
$string['group_registration_notify:student:unqueue:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} aus der Warteliste vom Zeitslot am {$a->date} um {$a->time} im/in {$a->location} ausgetragen.

Moodle Messaging System';
$string['group_registration_notify:student:unqueue:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} aus der Warteliste vom Zeitslot am {$a->date} um {$a->time} ausgetragen.';
$string['group_registration_notify:student:unqueue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe aus Warteliste ausgetragen';
$string['group_registration_notify:student:unregister:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} vom Zeitslot am {$a->date} um {$a->time} im/in {$a->location} abgemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:unregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat {$a->sendername} Ihre Gruppe {$a->groupname} vom Zeitslot am {$a->date} um {$a->time} im/in {$a->location} abgemeldet.

Moodle Messaging System';
$string['group_registration_notify:student:unregister:group:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} vom Zeitslot am {$a->date} um {$a->time} abgemeldet.';
$string['group_registration_notify:student:unregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe abgemeldet';
$string['group_registration_notify:student:unregister:smallmessage'] = '{$a->sendername} hat Ihre Gruppe {$a->groupname} vom Zeitslot am {$a->date} um {$a->time} abgemeldet.';
$string['group_registration_notify:student:unregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe abgemeldet';
$string['group_slot_available'] = 'Slot verfügbar';
$string['group_slot_full'] = 'Slot vergeben';
$string['groupmodeexistingcoursegroups'] = 'Vorhandene Gruppen';
$string['groupmodenogroups'] = 'Kein Gruppenmodus';
$string['groupmodeslotgroups'] = 'Gruppen für neue Termine anlegen';
$string['groupmodeslotgroupsappointment'] = 'Gruppen für gebuchte Termine anlegen';
$string['groupoptions'] = 'Gruppeneinstellungen';
$string['grouporganizer_desc_hasgroup'] = 'Dies ist ein Gruppenorganizer. Das Betätigen des Anmeldebuttons meldet Sie und alle Mitglieder Ihrer Gruppe {$a->groupname} für diesen Slot an. Alle Gruppenmitglieder können die Anmeldung ändern und kommentieren.';
$string['grouporganizer_desc_nogroup'] = 'Dies ist ein Gruppenorganizer. Teilnehmer/innen können hier Ihre Gruppen für Termine anmelden. Alle Gruppenmitglieder können die Anmeldung ändern und kommentieren.';
$string['grouppicker'] = 'Gruppenauswahl';
$string['groupwarning'] = 'Prüfen Sie die Gruppeneinstellungen unten!';
$string['headerfooter'] = 'Kopf-/Fußzeilen';
$string['headerfooter_help'] = 'Inkludiere Kopf-/Fußzeile';
$string['hidecalendar'] = 'Kalender verbergen';
$string['hidecalendar_help'] = 'Stellen Sie hier ein, ob der Kalender in diesem Terminplaner ausgeblendet werden soll.';
$string['hour'] = 'h';
$string['hour_pl'] = 'hrs';
$string['id'] = 'ID';
$string['img_title_due'] = 'Der Slot ist fällig';
$string['img_title_evaluated'] = 'Der Slot ist bewertet';
$string['img_title_no_participants'] = 'Der Slot hatte keine Teilnehmer/innen';
$string['img_title_past_deadline'] = 'Der Slot ist über der Deadline';
$string['img_title_pending'] = 'Ausstehende Bewertung des Slots';
$string['includetraineringroups'] = 'Trainer/in in die neu erstellten Gruppen aufnehmen';
$string['includetraineringroups_help'] = 'Wenn Sie diese Checkbox anklicken werden beim Anlegen neuer Gruppen bei der Terminerstellung auch die Trainer dieser Gruppe zugeordnet.';
$string['infobox_app_countdown'] = 'Zeit bis zum Termin: {$a->days} Tage, {$a->hours} Stunden, {$a->minutes} Minuten, {$a->seconds} Sekunden';
$string['infobox_app_occured'] = 'Der Termin hat schon stattgefunden.';
$string['infobox_appointmentsstatus_pl'] = '{$a->tooless} Buchung(en) sind noch fällig. Es gibt noch {$a->places} freie Plätze in {$a->slots} zukünftigen Slot(s).';
$string['infobox_appointmentsstatus_sg'] = '{$a->tooless} Buchung(en) sind noch fällig. Es gibt noch {$a->places} freien Platz in {$a->slots} zukünftigen Slot(s).';
$string['infobox_counter_slotrows'] = 'Slots sichtbar.';
$string['infobox_deadline_countdown'] = 'Zeit bis zur An-/Abmeldungsdeadline: {$a->days} Tage, {$a->hours} Stunden, {$a->minutes} Minuten, {$a->seconds} Sekunden';
$string['infobox_deadline_passed'] = 'Anmeldezeitraum abgelaufen. Sie können Anmeldungen nicht mehr ändern.';
$string['infobox_deadline_passed_slot'] = 'xxx Slots wurden nicht angelegt, da die Deadline zur Registrierung vorbei ist.';
$string['infobox_deadlines_title'] = 'Deadlines';
$string['infobox_description_title'] = 'Terminplanerbeschreibung';
$string['infobox_feedback_title'] = 'Feedback';
$string['infobox_group'] = 'Meine Gruppe: {$a->groupname}';
$string['infobox_link'] = 'Anzeigen/Verbergen';
$string['infobox_messages_title'] = 'Systemnachrichten';
$string['infobox_messaging_title'] = 'Benachrichtigungseinstellungen';
$string['infobox_minmax'] = 'Buchungen per Teilnehmer:in: Minimum {$a->min} - Maximum {$a->max}.';
$string['infobox_mycomments_title'] = 'Meine Kommentare';
$string['infobox_myslot_noslot'] = 'Sie sind derzeit für keinen Slot angemeldet.';
$string['infobox_myslot_title'] = 'Mein Slot';
$string['infobox_myslot_userslots_left'] = 'Sie haben noch {$a->left} Buchungen zur Verfügung.';
$string['infobox_myslot_userslots_left_group'] = 'Ihre Gruppe hat noch {$a->left} Buchungen zur Verfügung.';
$string['infobox_myslot_userslots_max_reached'] = 'Sie haben das Maximum von {$a->max} Slot(s) gebucht.';
$string['infobox_myslot_userslots_max_reached_group'] = 'Ihre Gruppe hat das Maximum von {$a->max} Slot(s) gebucht.';
$string['infobox_myslot_userslots_min_not_reached'] = 'Sie haben noch nicht die geforderte Anzahl von {$a->min} Slot(s) gebucht.';
$string['infobox_myslot_userslots_min_not_reached_group'] = 'Ihre Gruppe hat noch nicht die geforderte Anzahl von {$a->min} Slot(s) gebucht.';
$string['infobox_myslot_userslots_min_reached'] = 'Sie haben die geforderte Anzahl von {$a->min} Slot(s) gebucht.';
$string['infobox_myslot_userslots_min_reached_group'] = 'Ihre Gruppe hat die geforderte Anzahl von {$a->min} Slot(s) gebucht.';
$string['infobox_myslot_userslots_status'] = '{$a->booked} von {$a->max} Slots wurden gebucht.';
$string['infobox_organizer_expired'] = 'Dieser Terminplaner lief am {$a->date} um {$a->time} ab';
$string['infobox_organizer_expires'] = 'Dieser Terminplaner läuft am {$a->date} um {$a->time} ab.';
$string['infobox_organizer_never_expires'] = 'Dieser Terminplaner läuft nicht ab.';
$string['infobox_registrationstatistic_title'] = 'Status';
$string['infobox_showallparticipants'] = 'Alle Teilnehmer:innen anzeigen';
$string['infobox_showfreeslots'] = 'Freie Slots';
$string['infobox_showhiddenslots'] = 'Verborgene Slots';
$string['infobox_showmyslotsonly'] = 'Meine Slots';
$string['infobox_showregistrationsonly'] = 'Gebuchte Slots';
$string['infobox_showslots'] = 'Vergangene Slots';
$string['infobox_slotoverview_title'] = 'Slot Übersicht';
$string['infobox_slotsviewoptions'] = 'Optionen zum Filtern';
$string['infobox_slotsviewoptions_help'] = 'Diese Filteroptionen sind mit der Konjunktion UND verbunden!';
$string['infobox_statistic_maxreached'] = '{$a->maxreached} von {$a->entries} Teilnehmer:innen haben das Maximum von {$a->max} Slot(s) gebucht.';
$string['infobox_statistic_maxreached_group'] = '{$a->maxreached} von {$a->entries} Gruppen haben das Maximum von {$a->max} Slot(s) gebucht.';
$string['infobox_statistic_minreached'] = '{$a->minreached} von {$a->entries} Teilnehmer:innen haben das geforderte Minumum von {$a->min} Slot(s) gebucht.';
$string['infobox_statistic_minreached_group'] = '{$a->minreached} von {$a->entries} Gruppen haben das geforderte Minimum von {$a->min} Slot(s) gebucht.';
$string['infobox_title'] = 'Infobox';
$string['introeditor_error'] = 'Eine Beschreibung des Terminplaners muss vorhanden sein!';
$string['invalidgrouping'] = 'Sie müssen eine gültige Gruppierung auswählen!';
$string['inwaitingqueue'] = 'In Warteliste';
$string['isgrouporganizer'] = 'Gruppentermine';
$string['isgrouporganizer_help'] = 'Wählen Sie bei Bedarf einen Gruppenmodus für diesen Terminplaner aus. Dabei gibt es folgende Modi:
"Vorhanden Gruppen": Vorhandene Kurs-Gruppen für die Anmeldung verwenden.
"Gruppen für neue Termine anlegen": Eine neue Kurs-Gruppe wird für jeden neuen Termin erstellt.
"Gruppen für gebuchte Termine anlegen": Eine neue Kurs-Gruppe wird für jeden Termin erstellt, der das erste Mal gebucht wird.';
$string['location'] = 'Ort';
$string['location_help'] = 'Der Ort, wo der Termin stattfindet.';
$string['locationlink'] = 'Link URL des Ortes';
$string['locationlink_help'] = 'Geben Sie hier die volle Webadresse an, die beim Link zum Ort verwendet werden soll. Diese Seite sollte zumindest Informationen enthalten wie der Ort des Termins erreicht werden kann. Die volle Adresse (inklusive http://) wird benötigt.';
$string['locationlinkenable'] = 'Automatische Verlinkung zum Terminort';
$string['locationmandatory'] = 'Die Eingabe des Ortes ist ein Pflichtfeld';
$string['locationsettings'] = 'Globale Einstellungen zum Ort';
$string['maillink'] = 'Der Terminplaner ist unter <a href="{$a}">diesem</a> Link verfügbar.';
$string['maxparticipants'] = 'Höchstanzahl der Teilnehmer/innen';
$string['maxparticipants_help'] = 'Bestimmt die maximale Anzahl Teilnehmer/innen die sich für die jeweiligen Slots registrieren können. Bei Gruppenterminplanern ist diese Anzahl immer auf eine Gruppe begrenzt.';
$string['message_autogenerated2'] = 'Automatisch generierte Nachricht';
$string['message_custommessage'] = 'Benutzerdefinierte Nachricht';
$string['message_custommessage_help'] = 'Geben sie hier eine Nachricht ein die in die automatisch generierte Nachricht eingefügt wird.';
$string['message_error_action_notallowed'] = 'Diese Aktion kann nicht mehr ausgeführt werden!';
$string['message_error_groupsynchronization'] = 'Die Synchronisierung der Termin-Gruppen schlug fehl!';
$string['message_error_noactionchosen'] = 'Wählen Sie eine Aktion aus und drücken Sie dann auf den Start-Button.';
$string['message_error_slot_full_group'] = 'Dieser Slot ist vergeben!';
$string['message_error_slot_full_single'] = 'Dieser Slot hat keine freien Plätze mehr!';
$string['message_error_unknown_unqueue'] = 'Ihr Wartelisten-Eintrag konnte nicht entfernt werden! Unbekannter Fehler.';
$string['message_error_unknown_unregister'] = 'Ihre Registrierung konnte nicht entfernt werden! Unbekannter Fehler.';
$string['message_info_available'] = 'Es stehen noch {$a->freeslots} freie Slots für insgesamt {$a->notregistered} Teilnehmer/innen ohne Termin zur Verfügung.';
$string['message_info_available_group'] = 'Es stehen noch {$a->freeslots} freie Slots für insgesamt {$a->notregistered} Gruppen ohne Termin zur Verfügung.';
$string['message_info_reminders_sent_pl'] = 'Es wurden {$a->count} Mitteilungen versandt.';
$string['message_info_reminders_sent_sg'] = 'Es wurde {$a->count} Mitteilung versandt.';
$string['message_info_slots_added_pl'] = '{$a->count} neue Slots hinzugefügt.';
$string['message_info_slots_added_sg'] = '{$a->count} neuer Slot hinzugefügt.';
$string['message_info_slots_deleted_pl'] = '{$a->deleted} Slots wurden gelöscht. {$a->notified} Teilnehmer:innen wurden benachrichtigt.';
$string['message_info_slots_deleted_sg'] = 'Der Slot wurde gelöscht. {$a->notified} Teilnehmer:innen wurden benachrichtigt.';
$string['message_info_slots_edited_pl'] = '{$a->count} Slots wurden bearbeitet.';
$string['message_info_slots_edited_sg'] = 'Ein Slot wurde erfolgreich bearbeitet.';
$string['message_info_slots_evaluated_pl'] = '{$a->count} Slots wurden bewertet.';
$string['message_info_slots_evaluated_sg'] = 'Ein Slot wurde bewertet.';
$string['message_info_unqueued'] = 'Sie wurden aus der Warteliste entfernt.';
$string['message_info_unqueued_group'] = 'Ihre Gruppe wurde aus der Warteliste entfernt.';
$string['message_info_unregistered'] = 'Sie wurden von dem Slot ergfolgreich abgemeldet.';
$string['message_info_unregistered_group'] = 'Ihre Gruppe wurde von dem Slot erfolgreich abgemeldet.';
$string['message_warning_available'] = '<span style="color:red;">Warnung</span> Es stehen noch {$a->freeslots} freie Slots für insgesamt {$a->notregistered} Teilnehmer/innen ohne Termin zur Verfügung.';
$string['message_warning_available_group'] = '<span style="color:red;">Warnung</span> Es stehen noch {$a->freeslots} freie Slots für insgesamt {$a->notregistered} Gruppen ohne Termin zur Verfügung.';
$string['message_warning_no_slots_added'] = 'Es wurden keine neuen Slots hinzugefügt!';
$string['message_warning_no_slots_selected'] = 'Sie müssen zuerst mindestens einen Slot auswählen!';
$string['message_warning_no_visible_slots_selected'] = 'Sie müssen zuerst mindestens einen SICHTBAREN Slot auswählen!';
$string['messageprovider:appointment_reminder_student'] = 'Terminplaner Terminerinnerung';
$string['messageprovider:appointment_reminder_teacher'] = 'Terminplaner Terminerinnerung (Trainer/in)';
$string['messageprovider:assign_notify_student'] = 'Terminplaner Zuweisung durch Trainer/in';
$string['messageprovider:assign_notify_teacher'] = 'Terminplaner Zuweisung';
$string['messageprovider:edit_notify_student'] = 'Terminplaner Änderungen';
$string['messageprovider:edit_notify_teacher'] = 'Terminplaner Änderungen (Trainer/in)';
$string['messageprovider:eval_notify_student'] = 'Terminplaner Bewertungsbenachrichtigung';
$string['messageprovider:group_registration_notify_student'] = 'Terminplaner Gruppenregistrierung Benachrichtigung';
$string['messageprovider:manual_reminder_student'] = 'Terminplaner manuelle Terminerinnerung';
$string['messageprovider:register_notify_teacher'] = 'Terminplaner Registrierungsbenachrichtigung';
$string['messageprovider:register_notify_teacher_queue'] = 'Terminplaner Benachrichtigung über Wartelistenanmeldung';
$string['messageprovider:register_notify_teacher_register'] = 'Terminplaner Registrierungsbenachrichtigung';
$string['messageprovider:register_notify_teacher_reregister'] = 'Terminplaner Re-Registrierungsbenachrichtigung';
$string['messageprovider:register_notify_teacher_unqueue'] = 'Benachrichtigung des Organisators beim Austragen aus der Warteliste';
$string['messageprovider:register_notify_teacher_unregister'] = 'Terminplaner Abmeldungsbenachrichtigung';
$string['messageprovider:register_promotion_student'] = 'Terminplaner Systemnachricht zum Nachrücken aus Warteliste';
$string['messageprovider:register_reminder_student'] = 'Terminplaner Registrierungserinnerung';
$string['messageprovider:slotdeleted_notify_student'] = 'Terminplaner Slot absagen';
$string['messageprovider:test'] = 'Terminplaner Test Nachricht';
$string['messages_all'] = 'Alle Anmeldungen und Ab-/Ummeldungen';
$string['messages_none'] = 'Keine Benachrichtigungen';
$string['messages_re_unreg'] = 'Nur Ab-/Ummeldungen';
$string['min'] = 'min';
$string['min_pl'] = 'mins';
$string['modformwarningplural'] = 'Diese Felder können nicht bearbeitet werden, da es in diesem Terminplaner schon angemeldete Teilnehmer/innen gibt!';
$string['modformwarningsingular'] = 'Dieses Feld kann nicht bearbeitet werden, da es in diesem Terminplaner schon angemeldete Teilnehmer/innen gibt!';
$string['modulename'] = 'Terminplaner';
$string['modulename_help'] = 'Terminplaner ermöglichen es den Trainer/innen Termine bzw. Zeitfenster für die Teilnehmer/innen bereitzustellen.';
$string['modulenameplural'] = 'Terminplaner';
$string['multimember'] = 'Teilnehmer dürfen nicht binnen einer Gruppierung zu mehreren Gruppen gehören!';
$string['multimemberspecific'] = 'Teilnehmer {$a->username} {$a->idnumber} hat sich für mehr als eine Gruppe angemeldet! ({$a->groups})';
$string['multipleappointmentenddate'] = 'Enddatum';
$string['multipleappointmentstartdate'] = 'Startdatum';
$string['mymoodle_app_slot'] = 'Termin am {$a->date} um {$a->time}';
$string['mymoodle_attended'] = '{$a->attended}/{$a->total} Teilnehmer/innen haben an einem Termin teilgenommen';
$string['mymoodle_attended_group'] = '{$a->attended}/{$a->total} Gruppen haben zumindest an einem Termin teilgenommen';
$string['mymoodle_attended_group_short'] = '{$a->attended}/{$a->total} Gruppen zumindest an einem Termin teilgenommen';
$string['mymoodle_attended_short'] = '{$a->attended}/{$a->total} Teilnehmer/innen zumindest an einem Termin teilgenommen';
$string['mymoodle_completed_app'] = 'Sie haben Ihren Termin am {$a->date} um {$a->time} abgeschlossen';
$string['mymoodle_completed_app_group'] = 'Ihre Gruppe {$a->groupname} hat am Termin am {$a->date} um {$a->time} teilgenommen';
$string['mymoodle_missed_app'] = 'Sie haben am Termin am {$a->date} um {$a->time} nicht teilgenommen';
$string['mymoodle_missed_app_group'] = 'Ihre Gruppe {$a->groupname} hat am Termin am {$a->date} um {$a->time} nicht teilgenommen';
$string['mymoodle_next_slot'] = 'Nächster Slot am {$a->date} um {$a->time}';
$string['mymoodle_no_reg_slot'] = 'Sie haben sich noch nicht für einen Zeitslot angemeldet';
$string['mymoodle_no_reg_slot_group'] = 'Ihre Gruppe {$a->groupname} hat sich noch nicht für einen Zeitslot angemeldet';
$string['mymoodle_no_slots'] = 'Keine bevorstehenden Slots';
$string['mymoodle_organizer_expired'] = 'Dieser Terminplaner lief am {$a->date} um {$a->time} ab. Sie können ihn nicht mehr benutzen';
$string['mymoodle_organizer_expires'] = 'Dieser Terminplaner läuft am {$a->date} um {$a->time} ab';
$string['mymoodle_pending_app'] = 'Ausstehende Bewertung Ihres Termins';
$string['mymoodle_pending_app_group'] = 'Ausstehende Bewertung des Termins Ihrer Gruppe {$a->groupname}';
$string['mymoodle_reg_slot'] = 'Sie haben {a->booked} Slots gebucht und daher das Minimum von {a->slotsmin} Buchungen erreicht.';
$string['mymoodle_reg_slot_group'] = 'Ihre Gruppe {$a->groupname} hat {a->booked} Slots gebucht und daher das Minimum von {a->slotsmin} Buchungen erreicht.';
$string['mymoodle_registered'] = '{$a->registered}/{$a->total} Teilnehmer/innen haben sich für einen Termin angemeldet';
$string['mymoodle_registered_group'] = '{$a->registered}/{$a->total} Gruppen haben sich für einem Termin angemeldet';
$string['mymoodle_registered_group_short'] = '$a->registered} von {$a->total} Gruppen haben das Mimimum von {a->slotsmin} Slots gebucht';
$string['mymoodle_registered_short'] = '{$a->registered} von {$a->total} Teilnehmer:innen haben das Minimum von {a->slotsmin} Slots gebucht';
$string['mymoodle_upcoming_app'] = 'Ihr Termin findet am {$a->date} um {$a->time} im/in {$a->location} statt';
$string['mymoodle_upcoming_app_group'] = 'Der Termin Ihrer Gruppe, {$a->groupname}, findet am {$a->date} um {$a->time} im/in {$a->location} statt';
$string['newslot'] = 'Weitere Slots hinzufügen';
$string['no_due_my_slots'] = 'All Ihre Zeitslots in diesem Terminplaner sind abgelaufen oder verborgen';
$string['no_due_slots'] = 'Alle in diesem Terminplaner erstellten Zeitslots sind abgelaufen';
$string['no_my_slots'] = 'Sie haben in diesem Terminplaner keine Slots erstellt';
$string['no_slots'] = 'Es wurden keine Zeitslots in diesem Terminplaner erstellt';
$string['no_slots_defined'] = 'Derzeit sind keine Zeitslots verfügbar.';
$string['no_slots_defined_teacher'] = 'Derzeit sind keine Zeitslots verfügbar. Legen Sie <a href="{$a->link}">hier</a> neue an.';
$string['nocalendareventslotcreation'] = 'Keine Kalendereinträge für (noch) leere Slots';
$string['nocalendareventslotcreation_help'] = 'Wenn Sie diese Option anklicken werden beim Anlegen von Terminen noch keine Kalendereinträge erstellt. Erst Verabredungen führen zu Kalendereinträgen für Termine.';
$string['nofreeslots'] = 'Derzeit ist kein freier Termin verfügbar.';
$string['nogroup'] = 'Keine Gruppe';
$string['noparticipants'] = 'Keine Teilnehmer/innen';
$string['norightpage'] = 'Sie haben nicht das Recht, diese Seite aufzurufen.';
$string['nosingleslotprintfields'] = 'Es kann kein Ausdruck vorgenommen werden. Es wurden keine Profilfelder zum Ausdruck bestimmt. Siehe die Terminplaner-Einstellungen.';
$string['noslots'] = 'Keine Slots für';
$string['noslotsselected'] = 'Sie haben keine Termine ausgewählt!';
$string['notificationtime'] = 'Relative Terminerinnerung';
$string['notificationtime_help'] = 'Bestimmt wie weit im vorhinein der/die Teilnehmer/in an den Termin erinnert wird.';
$string['novalidparticipants'] = 'Keine gültige Teilnehmerin/ kein gültiger Teilnehmer';
$string['numentries'] = 'Einträge pro Seite';
$string['numentries_help'] = 'Wenn in Ihrem Kurs sehr viele Teilnehmer/innen eingeschrieben sind, können Sie mittels der Einstellung "Optimal" die Aufteilung der Listeneinträge pro Seite entsprechend der gewählten Schriftgröße und Seitenausrichtung optimieren.';
$string['organizer'] = 'Terminplaner';
$string['organizer:addinstance'] = 'Organizer hinzufügen';
$string['organizer:addslots'] = 'Neue Zeitslots hinzufügen';
$string['organizer:assignslots'] = 'Teilnehmer/innen Zeitslots zuweisen';
$string['organizer:comment'] = 'Kommentare hinzufügen';
$string['organizer:deleteslots'] = 'Vorhandene Zeitslots löschen';
$string['organizer:editslots'] = 'Vorhandene Zeitslots bearbeiten';
$string['organizer:evalslots'] = 'Abgeschlossene Zeitslots bewerten';
$string['organizer:leadslots'] = 'Zeitslot verwalten';
$string['organizer:printslots'] = 'Vorhandene Zeitslots drucken';
$string['organizer:receivemessagesstudent'] = 'Nachrichten wie als Teilnehmer/in empfangen';
$string['organizer:receivemessagesteacher'] = 'Nachrichten wie als Trainer/in empfangen';
$string['organizer:register'] = 'Für einen Zeitslot anmelden';
$string['organizer:sendreminders'] = 'Anmeldungserinnerungen an Teilnehmer/innen senden';
$string['organizer:unregister'] = 'Von Zeitslot abmelden';
$string['organizer:viewallslots'] = 'Alle Zeitslots als Trainer/in ansehen';
$string['organizer:viewmyslots'] = 'Eigene Zeitslots als Trainer/in ansehen';
$string['organizer:viewregistrations'] = 'Status der Anmeldung von Teilnehmer/innen ansehen';
$string['organizer:viewstudentview'] = 'Alle Zeitslots als Teilnehmer/in ansehen';
$string['organizer_remind_all_no_recepients'] = 'Es gibt keine gültige Empfänger.';
$string['organizer_remind_all_recepients_pl'] = 'Es werden insgesamt {$a->count} Mitteilungen an nachfolgende Empfänger versandt:';
$string['organizer_remind_all_recepients_sg'] = 'Es wird insgesamt {$a->count} Mitteilung an nachfolgende Empfänger versandt:';
$string['organizer_remind_all_title'] = 'Erinnerungen versenden';
$string['organizercommon'] = 'Terminplaner Einstellungen';
$string['organizername'] = 'Name des Terminplaners';
$string['orientationlandscape'] = 'Querformat';
$string['orientationportrait'] = 'Hochformat';
$string['otherheader'] = 'Anderes';
$string['pageorientation'] = 'Seitenausrichtung';
$string['participants'] = 'Teilnehmer/in';
$string['participants_help'] = 'Liste der Teilnehmer/innen oder Gruppen, die den Termin gebucht haben.';
$string['pasttimeslotstring'] = 'xxx Slots wurden nicht angelegt, da vergangene Slots nicht angelegt werden dürfen.';
$string['pdf_notactive'] = 'nicht aktiviert';
$string['pdfsettings'] = 'PDF Einstellungen';
$string['places_inqueue'] = '{$a->inqueue} in Warteliste';
$string['places_inqueue_withposition'] = 'Position {$a->queueposition} in Warteliste';
$string['places_taken_pl'] = '{$a->numtakenplaces}/{$a->totalplaces} Plätze vergeben';
$string['places_taken_sg'] = '{$a->numtakenplaces}/{$a->totalplaces} Platz vergeben';
$string['pluginadministration'] = 'Terminplaner Administration';
$string['pluginname'] = 'Terminplaner';
$string['position'] = 'Position in Warteliste';
$string['print_return'] = 'Zurück zur Terminansicht';
$string['printout'] = 'Ausdruck';
$string['printpreview'] = 'Druckvorschau (erste 10 Einträge)';
$string['printslotuserfieldsnotenabled'] = 'Die Druck-Funktion von Termin-Ausdruck Profilfeldern ist nicht durch Administrator/innen freigegeben.';
$string['printsubmit'] = 'Tabellendruckansicht';
$string['privacy:metadata:applicantidappointment'] = 'Nutzer-ID der Person, die den Slot für die Gruppe registriert hat.';
$string['privacy:metadata:applicantidqueue'] = 'Nutzer-ID der Person, die diesen Eintrag in die Warteliste der Gruppe eingetragen hat.';
$string['privacy:metadata:attended'] = 'Ob der/die Teilnehmer/in oder Gruppe beim Slot anwesend war oder nicht.';
$string['privacy:metadata:comments'] = 'Der Kommentar der/des Trainerin/Trainers für den Slot.';
$string['privacy:metadata:feedback'] = 'Das Feedback der/des Trainerin/Trainers bei Bewertung des Slots.';
$string['privacy:metadata:grade'] = 'Die Bewertung die ein/e Teilnehmer/in oder Gruppe für den Slot erhält.';
$string['privacy:metadata:groupidappointment'] = 'ID der Gruppe, die den Slot gebucht hat.';
$string['privacy:metadata:groupidqueue'] = 'ID der Gruppe, die sich in die Warteliste eines Slots eingetragen hat.';
$string['privacy:metadata:organizerslotappointments'] = 'In dieser Tabelle werden Anmeldungen zu Slots gespeichert.';
$string['privacy:metadata:organizerslotqueues'] = 'In dieser Tabelle werden Anmeldungen zur Warteliste gespeichert.';
$string['privacy:metadata:organizerslottrainer'] = 'In dieser Tabelle werden Trainer/innen eines Slots gespeichert.';
$string['privacy:metadata:showfreeslotsonly'] = 'Benutzereinstellung: Die Tabelle zeigt nur freie Slots an.';
$string['privacy:metadata:showhiddenslots'] = 'Benutzereinstellung: Die Tabelle zeigt nur verborgene Slots an.';
$string['privacy:metadata:showmyslotsonly'] = 'Benutzereinstellung: Die Tabelle zeigt nur meine Slots an.';
$string['privacy:metadata:showpasttimeslots'] = 'Benutzereinstellung: Die Tabelle zeigt auch vergangene Slots an.';
$string['privacy:metadata:showregistrationsonly'] = 'Benutzereinstellung: Soll Slot Tabelle nur Registrierungen anzeigen';
$string['privacy:metadata:teacherapplicantid'] = 'ID des Trainers/der Trainerin der/die einen Slot einem/r Teilnehmer/in oder einer Gruppe zuwies.';
$string['privacy:metadata:teacherapplicanttimemodified'] = 'Die Zeit der Zuweisung an eine/n Teilnehmer/in oder Gruppe durch den/die Trainer/in.';
$string['privacy:metadata:trainerid'] = 'ID des Trainers/der Trainerin eines Slots.';
$string['privacy:metadata:useridappointment'] = 'ID der Person, die einen Slot gebucht hat.';
$string['privacy:metadata:useridqueue'] = 'ID der Person, die den Eintrag zur Warteliste eines Slots getätigt hat.';
$string['queue'] = 'Wartelisten';
$string['queue_help'] = 'Wartelisten erlauben es, sich für einen Termin anzumelden auch wenn dieser schon ausgebucht ist.
		Sobald ein Termin dann doch noch frei wird, wird der/die erste Teilnehmer/in aus der Warteliste automatisch nachgerückt.';
$string['queuebody'] = 'Ihre Anmeldung zu einem Termin wurde vom Status "Warteliste" in den Status "Angemeldet" versetzt.';
$string['queuesubject'] = 'Moodle Organizer: Aus Warteliste nachgerückt';
$string['recipientname'] = '&lt;Empfängername&gt;';
$string['reg_not_occured'] = 'Dieser Termin hat noch nicht stattgefunden';
$string['reg_status'] = 'Status der Registrierung';
$string['reg_status_not_registered'] = 'Nicht angemeldet';
$string['reg_status_organizer_expired'] = 'Terminplaner abgelaufen';
$string['reg_status_registered'] = 'Angemeldet';
$string['reg_status_slot_attended'] = 'Anwesend';
$string['reg_status_slot_available'] = 'Slot verfügbar';
$string['reg_status_slot_expired'] = 'Slot abgelaufen';
$string['reg_status_slot_full'] = 'Slot ausgebucht';
$string['reg_status_slot_not_attended'] = 'Nicht anwesend';
$string['reg_status_slot_past_deadline'] = 'Slot über der Deadline';
$string['reg_status_slot_pending'] = 'Slot hat eine ausstehende Bewertung';
$string['register_notify_teacher:queue:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Teilnehmer/in {$a->sendername} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} in die Warteliste eingetragen.

Moodle Messaging System';
$string['register_notify_teacher:queue:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Teilnehmer/in {$a->sendername} die Gruppe {$a->groupname} in die Warteliste für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} eingetragen.

Moodle Messaging System';
$string['register_notify_teacher:queue:group:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat die Gruppe {$a->groupname} in die Warteliste für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} eingetragen.';
$string['register_notify_teacher:queue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe in Warteliste eingetragen';
$string['register_notify_teacher:queue:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat sich für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} in die Warteliste eingetragen.';
$string['register_notify_teacher:queue:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Teilnehmer/in in Warteliste eingetragen';
$string['register_notify_teacher:register:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Teilnehmer/in {$a->sendername} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} angemeldet.

Moodle Messaging System';
$string['register_notify_teacher:register:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Teilnehmer/in {$a->sendername} die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} angemeldet.

Moodle Messaging System';
$string['register_notify_teacher:register:group:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} angemeldet.';
$string['register_notify_teacher:register:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe angemeldet';
$string['register_notify_teacher:register:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat sich für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} angemeldet.';
$string['register_notify_teacher:register:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Teilnehmer/in angemeldet';
$string['register_notify_teacher:reregister:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Teilnehmer/in {$a->sendername} für den neuen Zeitslot am {$a->date} um {$a->time} im/in {$a->location} umgemeldet.

Moodle Messaging System';
$string['register_notify_teacher:reregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Teilnehmer/in {$a->sendername} die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} umgemeldet.

Moodle Messaging System';
$string['register_notify_teacher:reregister:group:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} umgemeldet.';
$string['register_notify_teacher:reregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe umgemeldet';
$string['register_notify_teacher:reregister:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat sich für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} umgemeldet.';
$string['register_notify_teacher:reregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Teilnehmer/in umgemeldet';
$string['register_notify_teacher:unqueue:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Teilnehmer/in {$a->sendername} im  Zeitslot am {$a->date} um {$a->time} im/in {$a->location} aus der Warteliste ausgetragen.

Moodle Messaging System';
$string['register_notify_teacher:unqueue:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Teilnehmer/in {$a->sendername} die Gruppe {$a->groupname} aus der Warteliste für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} ausgetragen.

Moodle Messaging System';
$string['register_notify_teacher:unqueue:group:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat die Gruppe {$a->groupname} aus der Warteliste für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} ausgetragen.';
$string['register_notify_teacher:unqueue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe aus Warteliste ausgetragen';
$string['register_notify_teacher:unqueue:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat sich im Zeitslot am {$a->date} um {$a->time} im/in {$a->location} aus der Warteliste ausgetragen.';
$string['register_notify_teacher:unqueue:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Teilnehmer/in aus Warteliste ausgetragen';
$string['register_notify_teacher:unregister:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Teilnehmer/in {$a->sendername} vom Zeitslot am {$a->date} um {$a->time} im/in {$a->location} abgemeldet.

Moodle Messaging System';
$string['register_notify_teacher:unregister:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat Teilnehmer/in {$a->sendername} die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} abgemeldet.

Moodle Messaging System';
$string['register_notify_teacher:unregister:group:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat die Gruppe {$a->groupname} für den Zeitslot am {$a->date} um {$a->time} im/in {$a->location} abgemeldet.';
$string['register_notify_teacher:unregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Gruppe abgemeldet';
$string['register_notify_teacher:unregister:smallmessage'] = 'Teilnehmer/in {$a->sendername} hat sich vom Zeitslot am {$a->date} um {$a->time} im/in {$a->location} abgemeldet.';
$string['register_notify_teacher:unregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Teilnehmer/in abgemeldet';
$string['register_promotion_student:fullmessage'] = 'Ihre Registrierung für einen Slot wurde vom Status "Warteliste" in "Gebucht" geändert.';
$string['register_promotion_student:smallmessage'] = 'Ihre Registrierung für einen Slot wurde vom Status "Warteliste" in "Gebucht" geändert.';
$string['register_promotion_student:subject'] = 'Moodle Terminplaner: Von Warteliste nachgerückt';
$string['register_reminder_student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, haben Sie sich entweder noch nicht für einen Zeitslot angemeldet, oder denjenigen verpasst für den Sie sich angemeldet haben.

{$a->custommessage}

Moodle Messaging System';
$string['register_reminder_student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseid} {$a->coursefullname}, hat sich Ihre Gruppe {$a->groupname} entweder noch nicht für einen Zeitslot angemeldet, oder denjenigen verpasst für den Sie sich angemeldet hat.

{$a->custommessage}

Moodle Messaging System';
$string['register_reminder_student:group:smallmessage'] = 'Bitte melden Sie sich für einen (neuen) Zeitslot an.';
$string['register_reminder_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Anmeldungserinnerung';
$string['register_reminder_student:smallmessage'] = 'Bitte melden Sie sich für einen (neuen) Zeitslot an.';
$string['register_reminder_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Anmeldungserinnerung';
$string['relative_deadline_before'] = 'vor dem Termin';
$string['relative_deadline_now'] = 'Ab sofort';
$string['relativedeadline'] = 'Relative Deadline';
$string['relativedeadline_help'] = 'Die Deadline wird relativ zum jeweiligen Slot gesetzt.
    Teilnehmer/innen können sich nach Ablauf dieser Deadline nicht für diesen Slot anmelden oder abmelden.';
$string['remindall_desc'] = 'Erinnerungen an alle Teilnehmer/innen ohne Termin versenden';
$string['remindallmultiple_desc'] = 'Erinnerungen an alle Teilnehmer/innen ohne genügend Buchungen versenden';
$string['requiremodintro'] = 'Beschreibung notwendig';
$string['reset_organizer_all'] = 'Löschen aller Slots, Anmeldungen und zugehörigen Kalendereinträge';
$string['resetorganizerall'] = 'Alle Daten des Terminplaners löschen (Slots & Termine)';
$string['reviewsubmit'] = 'Zeitslots ansehen';
$string['rewievslotsheader'] = 'Zeitslots ansehen';
$string['search:activity'] = 'Terminplaner - Aktivitätsinformation';
$string['sec'] = 'sek';
$string['sec_pl'] = 'seks';
$string['select'] = 'Termin(e) auswählen';
$string['select_all_slots'] = 'Alle sichtbaren Slots auswählen';
$string['select_help'] = 'Wählen Sie einen oder mehrere Termine zur Bearbeitung aus.';
$string['selectedgrouplist'] = 'Ausgewählte Gruppen';
$string['selectedslots'] = 'Ausgewählte Termine';
$string['showmore'] = 'Mehr anzeigen';
$string['signature'] = 'Unterschrift';
$string['singleslotcommands'] = 'Einzelnen Termin bearbeiten';
$string['singleslotcommands_help'] = 'Klicken Sie direkt auf einen Action-Link um einen einzelnen Termin zu bearbeiten.';
$string['singleslotprintfield'] = 'Termin-Ausdruck Profilfeld';
$string['singleslotprintfield0'] = 'Termin-Ausdruck Profilfeld';
$string['singleslotprintfield0_help'] = 'Diese Profilfelder werden für jede/n TeilnehmerIn beim Ausdruck eines einzelnen Termins ausgedruckt.';
$string['singleslotprintfields'] = 'Termin-Ausdruck Profilfelder';
$string['singleslotprintfields_help'] = 'Hier finden Sie die Termin-Ausdruck Profilfelder für den Export einzelner Slots über das Drucker-Symbol vor, welche Sie entsprechend der Instanz-Einstellungen von Moodle selbst auswählen können oder durch den Administrator/in vordefiniert wurden.';
$string['slot'] = 'Termin';
$string['slot_anonymous'] = 'Anonymer Slot';
$string['slot_slotvisible'] = 'Mitglieder nur sichtbar wenn eigener Slot';
$string['slot_visible'] = 'Mitglieder des Slots immer sichtbar';
$string['slotassignedby'] = 'Termin zugewiesen von';
$string['slotdeleted_notify_student:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseshortname} wurde ihr Termin am {$a->date} um {$a->time} im/in {$a->location} abgesagt.
Beachten Sie dabei, dass Sie keinen Termin mehr im Terminplaner {$a->organizername} haben!
Für einen Ersatztermin folgen Sie bitte dem Link: {$a->courselink}';
$string['slotdeleted_notify_student:group:fullmessage'] = 'Hallo {$a->receivername}!

Im Rahmen des Kurses {$a->courseshortname} wurde ihr Termin am {$a->date} um {$a->time} im/in {$a->location} abgesagt.
Beachten Sie dabei, dass Sie keinen Termin mehr im Terminplaner {$a->organizername} haben!
Für einen Ersatztermin folgen Sie bitte dem Link: {$a->courselink}';
$string['slotdeleted_notify_student:group:smallmessage'] = 'Ihr Termin am {$a->date} um {$a->time} im Terminplaner "{$a->organizername}" wurde abgesagt.';
$string['slotdeleted_notify_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin abgesagt';
$string['slotdeleted_notify_student:smallmessage'] = 'Ihr Termin am {$a->date} um {$a->time} im Terminplaner "{$a->organizername}" wurde abgesagt.';
$string['slotdeleted_notify_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Termin abgesagt';
$string['slotdetails'] = 'Slot Details';
$string['slotfrom'] = 'von';
$string['slotlistempty'] = 'Es konnten keine Termine gefunden werden.';
$string['slotperiodendtime'] = 'Enddatum';
$string['slotperiodheader'] = 'Erzeuge Slots für Zeitraum';
$string['slotperiodheader_help'] = 'Geben Sie ein Start- und Enddatum an für welche die täglichen Zeitfenster (siehe darunter) verwendet werden. Geben Sie hier auch bekannt, ob der Termin für Studierende sichtbar sein soll.';
$string['slotperiodstarttime'] = 'Startdatum';
$string['slottimeframesheader'] = 'Zeitfenster angeben';
$string['slottimeframesheader_help'] = 'Hier können Sie Zeitfenster auf Wochentagsbasis definieren die mit Terminslots befüllt werden, wie oben spezifiziert. Mehr als ein Zeitfenster pro Tag ist erlaubt. Ist ein Zeitfenster an einem Tag ausgewählt (zB Montag), so werden für jeden Montag im Datumszeitraum Zeitfenster und Termine erstellt.';
$string['slotto'] = 'bis';
$string['status'] = 'Statusanzeige';
$string['status_help'] = 'Derzeitiger Status des Termins.';
$string['status_no_entries'] = 'Für diesen Terminplaner sind keine Teilnehmer/innen angemeldet.';
$string['stroptimal'] = 'optimal';
$string['studentcomment_title'] = 'Kommentare Teilnehmer/innen';
$string['taballapp'] = 'Termine';
$string['tabstatus'] = 'Registrierungsstatus';
$string['tabstud'] = 'Teilnehmer/innen Ansicht';
$string['teacher'] = 'Trainer/in';
$string['teacher_help'] = 'Eine Liste der Trainer/innen des Termins.';
$string['teacher_unchanged'] = '-- unverändert --';
$string['teachercomment_title'] = 'Kommentare Trainer/innen';
$string['teacherfeedback_title'] = 'Rückmeldung Trainer/innen';
$string['teacherid'] = 'Trainer/in';
$string['teacherid_help'] = 'Bitte Trainer/in auswählen, der/die die Termine leitet';
$string['teacherinvisible'] = 'Trainer/in nicht sichtbar';
$string['teachervisible'] = 'Trainer/in sichtbar';
$string['teachervisible_help'] = 'Kreuzen Sie diese Option an um Teilnehmer/innen zu erlauben, den zugewiesenen Trainer oder die zugewiesene Trainerin dieses Zeitslots einzusehen.';
$string['textsize'] = 'Textgröße';
$string['th_actions'] = 'Aktion';
$string['th_appdetails'] = 'Details';
$string['th_attended'] = 'Teilg.';
$string['th_bookings'] = 'Buchungen gesamt';
$string['th_comments'] = 'Kommentar Teilnehmer/in';
$string['th_datetime'] = 'Datum & Zeit';
$string['th_datetimedeadline'] = 'Datum & Uhrzeit';
$string['th_details'] = 'Status';
$string['th_duration'] = 'Dauer';
$string['th_email'] = 'Email';
$string['th_evaluated'] = 'bewertet';
$string['th_feedback'] = 'Feedback';
$string['th_firstname'] = 'Vorname';
$string['th_grade'] = 'Bewertung';
$string['th_group'] = 'Gruppe';
$string['th_groupname'] = 'Gruppe';
$string['th_idnumber'] = 'Matrikelnummer';
$string['th_lastname'] = 'Nachname';
$string['th_location'] = 'Ort';
$string['th_participant'] = 'Teilnehmer/innen';
$string['th_participants'] = 'Teilnehmer/innen';
$string['th_status'] = 'Status';
$string['th_teacher'] = 'Trainer/in';
$string['th_teachercomments'] = 'Kommentar Trainer/in';
$string['timeshift'] = 'Verschiebung endgültiger Deadline';
$string['timeslot'] = 'Terminplaner-Termin';
$string['timetemplate'] = '%H:%M';
$string['title_add'] = 'Neue Terminslots hinzufügen';
$string['title_comment'] = 'Eigene Kommentare bearbeiten';
$string['title_delete'] = 'Ausgewählte Zeitslots löschen';
$string['title_delete_appointment'] = 'Einen zugewiesenen Termin löschen';
$string['title_edit'] = 'Ausgewählte Zeitslots bearbeiten';
$string['title_eval'] = 'Ausgewählte Zeitslots bewerten';
$string['title_print'] = 'Druckansicht';
$string['totalday'] = 'xxx Termine für yyy Personen';
$string['totalday_groups'] = 'xxx Termine für yyy Gruppen';
$string['totalslots'] = 'von {$a->starttime} bis {$a->endtime}, je {$a->duration} {$a->unit}, {$a->totalslots} Slot(s) insgesamt';
$string['totaltotal'] = 'Insgesamt xxx Termine für yyy Personen';
$string['totaltotal_groups'] = 'Insgesamt xxx Termine für yyy Gruppen';
$string['trainer'] = 'Trainer/in';
$string['trainerid'] = 'Trainer/in';
$string['trainerid_help'] = 'Markieren Sie den/die für den Termin zuständige/n Trainer/in.';
$string['unavailableslot'] = 'Dieser Slot ist verfügbar ab';
$string['unknown'] = 'Unbekannt';
$string['userslots_mingreatermax'] = 'Die Minimalanzahl von Buchungen ist höher als die Maximalanzahl.';
$string['userslotsmax'] = 'Maximum Buchungen';
$string['userslotsmax_help'] = 'Die Anzahl an möglichen Buchungen, die Teilnehmer:innen bzw. Gruppen buchen dürfen.';
$string['userslotsmin'] = 'Minimum Buchungen';
$string['userslotsmin_help'] = 'Die notwendige Anzahl an Buchungen, die Teilnehmer:innen bzw. Gruppen tätigen müssen.';
$string['visibility'] = 'Sichtbarkeit der Angemeldeten - Voreinstellung';
$string['visibility_all'] = 'Sichtbar';
$string['visibility_anonymous'] = 'Anonym';
$string['visibility_help'] = 'Geben Sie hier den Standard vor, wie neue Slots angelegt werden sollen:<br/><b>Anonym:</b> Die anderen Teilnehmer/innen eines Slots sind einem/r Teilnehmer/-in stets verborgen.<br/><b>Sichtbar nur, wenn eigener Slot:</b> Die anderen Teilnehmer/-innen eines Slots sind nur sichtbar, wenn man den Slot selber gebucht hat.<br/><b>Sichtbar:</b> Die Teilnehmer/innen eines Slots werden immer angezeigt.';
$string['visibility_slot'] = 'Sichtbar nur, wenn eigener Slot';
$string['visible'] = 'Termin sichtbar';
$string['waitinglists_desc_active'] = 'Wartelisten sind aktiviert.';
$string['waitinglists_desc_notactive'] = 'Wartelisten sind nicht aktiviert.';
$string['warning_groupingid'] = 'Gruppenmodus eingeschaltet. Sie müssen eine gültige Gruppierung auswählen.';
$string['warninggroupmode'] = 'Sie müssen den Gruppenmodus einschalten und eine Gruppierung auswählen, um einen Gruppenterminplaner zu erstellen!';
$string['warningtext1'] = 'Ausgewählte Slots enthalten andere Werte als dieses Feld!';
$string['warningtext2'] = 'WARNUNG! Die Inhalte dieses Feldes sind verändert worden!';
$string['weekdaylabel'] = 'Wochentermin';
$string['with'] = 'mit';
