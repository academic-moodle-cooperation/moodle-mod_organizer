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
 * In mod_form: Force selection of grouping if group mode is choosen.
 */


define(
    ['jquery'], function($) {

        /**
     * @constructor
     * @alias module:mod_organizer/modform
     */
        var Modform = function() {
            this.activateduedatecheckbox = false;
        };

        var instance = new Modform();

        instance.init = function (param) {

            if (param.activateduedatecheckbox) {
                this.activateduedatecheckbox = param.activateduedatecheckbox;
            }
            var warningdiv = $("#groupingid_warning").parent().parent();
            warningdiv.addClass('advanced');
            warningdiv.hide();

            function check_group_members_only(e) {
                var groupmodeselect = $(e.target);

                $('.collapseexpand').click();
                if (groupmodeselect.val() == 1) {
                    $('#id_groupmode').val('2');
                    warningdiv.show();
                } else {
                    $('#id_groupmode').val('0');
                    warningdiv.hide();
                }
            }

            $('#id_isgrouporganizer').on('change', check_group_members_only);

            if (this.activateduedatecheckbox && !$('.error')) {
                $('#id_duedate_enabled').click();
            }
        };

        instance.init_gradechange = function (param) {
            $('input[id^=id_grade]').on("change", function() {
                alert(param.changegradewarning);
            });
        };

            return instance;

    }
);
