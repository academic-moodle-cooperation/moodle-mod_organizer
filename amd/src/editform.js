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
 * @package
 * @subpackage organizer
 * @copyright 2017 Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * In edit form: Signalling fields with different values when multi-editing.
 */


define(
    ['jquery'], function($) {

        /**
         * @constructor
         * @alias module:mod_organizer/editform
         */
        var Editform = function() {
            this.imagepaths = false;
        };

        var instance = new Editform();

        instance.init = function(param) {

            if (param.imagepaths) {
                this.imagepaths = param.imagepaths;
            }

            /**
             * Check if input field has been changed.
             * @param {object} e input element
             */
            function detect_change(e) {
                var element = $(e.target);
                var name = element.attr('name').split("[")[0];
                if ($("[id^=mform1] input[name^=mod_" + name + "]").val() != "1") {
                    $("[id^=mform1] input[name^=mod_" + name + "]").val("1");
                    set_icon_changed(name);
                }
            }

            /**
             * Display the icon for changed content.
             * @param {string} name of the icon
             */
            function set_icon_changed(name) {
                var icon = $("[id^=mform1] img[id$=" + name + "_warning]");
                if (icon.attr('src') != instance.imagepaths.changed) {
                    icon.attr('src', instance.imagepaths.changed);
                    icon.attr('title', $("[id^=mform1] [name=warningtext2]").val());
                }
            }

            var initialstate;

            /**
             * Reset the edit form.
             */
            function reset_edit_form() {
                $("[id^=mform1] input[name^=mod_]").val(0);
                var icons = $("[id^=mform1] img[name$=_warning]");
                icons.attr('src', instance.imagepaths.warning);
                icons.attr('title', $("[id^=mform1] [name=warningtext1]").val());
                $('[id^=mform1] [name^=availablefrom]:not([name*=now])').attr('disabled', initialstate);
            }

            $('[id^=mform1]').find('select, input[type=checkbox]').on('change', detect_change);
            $('[id^=mform1]').find('input[type=text], textarea').on('keydown', detect_change);
            $('#id_editreset').on('click', reset_edit_form);

        };

        return instance;

    }
);
