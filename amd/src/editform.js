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
 * @subpackage organizer
 * @copyright 2017 Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * In edit form: Force selection of grouping if group mode is choosen.
 */


define(
    ['jquery'], function($) {

        /**
     * @constructor
     * @alias module:mod_organizer/modform
     */
        var Editform = function() {
            this.imagepaths = false;
        };

        var instance = new Editform();

        instance.init = function (param) {

            if (param.imagepaths) {
                this.imagepaths = param.imagepaths;
            }

            function detect_change() {
                var name = this.attr('name').split("[")[0];
                set_modfield(name, 1);
                set_icon_changed(name);
            }

            function set_modfield(name, value) {
                $("#mform1 input[name^=mod_" + name + "]").each(function() {
                    $( this ).val(value);
                });
            }

            function set_icon_changed(name) {
                var icons = $("#mform1 img[name$=" + name + "_warning]");

                icons.attr('src', imagepaths['changed']);
                icons.attr('title', $("#warningtext2").val());
            }

            function set_icon_warning() {
                var icons = $("#mform1 img[name$=_warning]");

                icons.attr('src', imagepaths['warning']);
                icons.attr('title', $("#warningtext1").val());
            }

            var initialstate;

            function toggle_hidden_field(e) {
                var parent = e.target.parent();
                if (typeof initialstate == 'undefined') {
                    initialstate = e.target.is(':checked');
                }
                $('#mform1 [name^=availablefrom]:not([name*=now])').attr('disabled', e.target.is(':checked'));
                if (e.target.is(':checked')) {
                    $(parent).append('<input type="hidden" name="availablefrom" value="0"></input>');
                } else {
                    var hidden = $('#mform1 input[name=availablefrom]');
                    if (hidden) {
                        hidden.remove();
                    }
                }
            }

            function reset_edit_form(e) {
                set_modfield('', 0);
                set_icon_warning();
                $('#mform1 [name^=availablefrom]:not([name*=now])').attr('disabled', initialstate);
            }

            $('#mform1').delegate('select, input[type=checkbox]', 'change', detect_change);
            $('#mform1').delegate('input[type=text], textarea', 'keydown', detect_change);
            $('#id_availablefrom_now').on('change', toggle_hidden_field);
            $('#id_editreset').on('click', reset_edit_form);

        };

        return instance;

    }
);
