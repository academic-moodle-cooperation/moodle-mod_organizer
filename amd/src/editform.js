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
     * @alias module:mod_organizer/editform
     */
        var Editform = function() {
            this.imagepaths = false;
        };

        var instance = new Editform();

        instance.init = function (param) {

            if (param.imagepaths) {
                this.imagepaths = param.imagepaths;
            }

            function detect_change(e) {
                var element = $(e.target);
                var name = element.attr('name').split("[")[0];
                if ($("#mform1 input[name^=mod_" + name + "]").val() != "1") {
                    alert("hier" + name);
                    $("#mform1 input[name^=mod_" + name + "]").val("1");
                    set_icon_changed(name);
                }
            }

            function set_icon_changed(name) {
                var icon = $("#mform1 img[id$=" + name + "_warning]");
                if (icon.attr('src') != instance.imagepaths['changed']) {
                    icon.attr('src', instance.imagepaths['changed']);
                    icon.attr('title', $("#mform1 [name=warningtext2]").val());
                }
            }

            var initialstate;

            function toggle_hidden_field(e) {
                var target = $(e.target);
                if (typeof initialstate == 'undefined') {
                    initialstate = target.is(':checked');
                }
                $('#mform1 [name^=availablefrom]:not([name*=now])').attr('disabled', target.is(':checked'));
                if (target.is(':checked')) {
                    target.parent.append('<input type="hidden" name="availablefrom" value="0"></input>');
                } else {
                    var hidden = $('#mform1 input[name=availablefrom]');
                    if (hidden) {
                        hidden.remove();
                    }
                }
            }

            function reset_edit_form() {
                reset_modfields();
                reset_icons_warning();
                $('#mform1 [name^=availablefrom]:not([name*=now])').attr('disabled', initialstate);
            }

            function reset_modfields() {
                $("#mform1 input[name^=mod_]").val(0);
            }

            function reset_icons_warning() {
                var icons = $("#mform1 img[name$=_warning]");
                icons.attr('src', instance.imagepaths['warning']);
                icons.attr('title', $("#mform1 [name=warningtext1]").val());
            }

            $('#mform1').find('select, input[type=checkbox]').on('change', detect_change);
            $('#mform1').find('input[type=text], textarea').on('keydown', detect_change);
            $('#id_availablefrom_now').on('change', toggle_hidden_field);
            $('#id_editreset').on('click', reset_edit_form);

        };

        return instance;

    }
);
