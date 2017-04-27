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
        instance.lastslotid = new Array;

        $('.adddayslot').css("display", "inline");

        $('[id^=id_addday_]').on('click', function () {

            var button = $(this);
            var day = button.attr("slot");
            var newslot_index = instance.totalslots++;

            // Clone original fieldset
            var originalfieldset = $('<fieldset>').append(button.closest("fieldset").clone());
            var newfieldset = originalfieldset.clone();

            // newfieldset: remove addday-button
            newfieldset.find(':button').first().remove();
            // newfieldset: insert hidden field "day"
            newfieldset.append('<input name="newslots[' + day + '][day]" value="' + day + '" type="hidden">');

            var originalfieldsetfieldset = originalfieldset.find('fieldset').first();
            var newfieldsetfieldset = newfieldset.find('fieldset').first();
            newfieldsetfieldset.attr("id", originalfieldsetfieldset.attr("id") + "_" + newslot_index);
            var fieldsetelements = newfieldset.find('input, select');
            $.each(fieldsetelements, function() {
                var originalid = $(this).attr("id");
                if(originalid) {
                    var newid = originalid.replace("newslots_" + day + "_", "newslots_" + newslot_index + "_");
                    $(this).attr("id", newid);
                }
                var originalname = $(this).attr("name");
                if(originalname) {
                    var newname = originalname.replace("newslots[" + day + "]", "newslots[" + newslot_index + "]");
                    $(this).attr("name", newname);
                }
            });
            fieldsetelements = newfieldset.find('label');
            $.each(fieldsetelements, function() {
                var originalfor = $(this).attr("for");
                if(originalfor) {
                    var newfor = originalfor.replace("newslots_" + day + "_", "newslots_" + newslot_index + "_");
                    $(this).attr("for", newfor);
                }
             });

            var originalfieldsetparent = originalfieldsetfieldset.parent();
            var lastfieldsetfieldset = originalfieldsetparent.find('fieldset').last();

            if(instance.lastslotid[day]!=undefined) {
                $(instance.lastslotid[day]).after(newfieldset);
            } else {
                $("#" + lastfieldsetfieldset.attr("id")).after(newfieldset);
            }
            instance.lastslotid[day] = "#" + newfieldsetfieldset.attr("id");
        });
    };

    return instance;

});

