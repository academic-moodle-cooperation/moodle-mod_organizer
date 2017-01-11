// This file is part of Moodle - http://moodle.org/.
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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package mod
 * @subpackage datalynx
 * @copyright 2017 Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Create new dayslots as duplicates of the seven original day slots
 */


define(['jquery', 'core/log'], function($, log) {

    /**
     * @constructor
     * @alias module:mod_datalynx/adddayslot
     */
    var Adddayslot = function() {

        this.totalslots = 0;

    };

    var instance = new Adddayslot();

    instance.init = function(param) { // Parameter 'param' contains the parameter values!

        instance.totalslots = param.totalslots;
        log.info("totalslots:" + instance.totalslots, "organizer");

        $('.adddayslot').css("display", "inline");

        $('[id^=id_addday_]').on('click', function () {

            var button = $(this);
            var dayslot = button.attr("slot");
            log.info("oldslot: " + dayslot, "organizer");
            var newslot = instance.totalslots;
            log.info("newslot: " + newslot, "organizer");
            instance.totalslots++;
            log.info("totalslots: " + instance.totalslots, "organizer");
            var oldfieldset = $('<fieldset>').append(button.closest("fieldset").clone());
            var newfieldset = oldfieldset.clone();
            newfieldset.find(':button').first().remove();
            newfieldset.append('<input name="newslots[' + dayslot + '][day]" value="' + dayslot + '" type="hidden">');
            var oldfieldsetfieldset = oldfieldset.find('fieldset').first();
            var newfieldsetfieldset = newfieldset.find('fieldset').first();
            newfieldsetfieldset.attr("id", oldfieldsetfieldset.attr("id") + "_1");
            log.info("newfieldset ID: " + newfieldsetfieldset.attr("id"), "organizer");
            var fieldsetelements = newfieldset.find('input, select');
            log.info("elements 1: " + fieldsetelements.length, "organizer");
            var ix = 0;
            $.each(fieldsetelements, function() {
                log.info("ix: " + ix++, "organizer");
                var oldid = $(this).attr("id");
                if(oldid) {
                    log.info("oldid: " + oldid, "organizer");
                    var newid = oldid.replace("newslots_" + dayslot + "_", "newslots_" + newslot + "_");
                    $(this).attr("id", newid);
                    log.info("newid: " +  $(this).attr("id"), "organizer");
                }
                var oldname = $(this).attr("name");
                if(oldname) {
                    log.info("oldname: " + oldname, "organizer");
                    var newname = oldname.replace("newslots[" + dayslot + "]", "newslots[" + newslot + "]");
                    $(this).attr("name", newname);
                    log.info("newname: " +  $(this).attr("name"), "organizer");
                }
            });
            fieldsetelements = newfieldset.find('label');
            log.info("elements 2: " + fieldsetelements.length, "organizer");
            ix = 0;
            $.each(fieldsetelements, function() {
                log.info("ix: " + ix++, "organizer");
                var oldfor = $(this).attr("for");
                if(oldfor) {
                    log.info("oldfor: " + oldfor, "organizer");
                    var newfor = oldfor.replace("newslots_" + dayslot + "_", "newslots_" + newslot + "_");
                    $(this).attr("for", newfor);
                    log.info("newfor: " +  $(this).attr("for"), "organizer");
                }
             });
            var oldfieldsetparent = oldfieldsetfieldset.parent();
            log.info("LASTfieldset: " +  oldfieldsetparent.html(), "organizer");
            var lastfieldsetfieldset = oldfieldsetparent.find('fieldset').last();
//            newfieldset.before( nextfieldset );
            log.info("LASTfieldsetfieldset ID: " +  lastfieldsetfieldset.attr("id"), "organizer");
            $("#" + lastfieldsetfieldset.attr("id")).after(newfieldset);

        });
    };

    return instance;

});

