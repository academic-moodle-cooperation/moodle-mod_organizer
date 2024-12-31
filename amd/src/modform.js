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
 * @copyright 2020 Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * In mod_form: Show/Hide select field grouping if select field group mode is chosen/not chosen.
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

        instance.init = function(param) {

            if (param.activateduedatecheckbox) {
                this.activateduedatecheckbox = param.activateduedatecheckbox;
            }

            /**
             * Display/Hide groupings if groupmode is selected.
             * @param {object} e event of groupmode field
             */
            function check_group_members_only(e) {
                var isgrouporganizer = $(e.target);

                if (isgrouporganizer.val() == 1) {
                    $('#id_groupmode').val('1').click();
                    $('#fitem_id_groupingid').removeAttr('hidden');
                    $('#fitem_id_groupingid').show();
                    $('#fitem_id_groupingid').find().each(function() {
                        $(this).css('display', 'block');
                        $(this).removeAttr('hidden');
                    });
                    $('#id_groupingid').removeAttr('disabled');
                } else if (isgrouporganizer.val() == 0) {
                    $('#id_groupmode').val('0').click();
                    $('#fitem_id_groupingid').prop('hidden', true);
                    $('#fitem_id_groupingid').find().each(function() {
                        $(this).css('display', 'none');
                        $(this).prop('hidden', true);
                    });
                    $('#id_groupingid').prop("disabled", true);
                }
            }

            /**
             * Disable groupings if groupmode is deselected.
             * @param {object} e event of groupmode field
             */
            function check_group_mode(e) {
                var groupmodeselect = $(e.target);

                if (groupmodeselect.val() == 0) {
                    $('#id_isgrouporganizer').val('0');
                    $('#fitem_id_groupingid').hide();
                    $('#id_groupingid').prop('disabled', 'disabled');
                }
            }

            $('#id_isgrouporganizer').on('change', check_group_members_only);
            $('#id_groupmode').on('change', check_group_mode);

            if (this.activateduedatecheckbox && !$('.error')) {
                $('#id_duedate_enabled').click();
            }
        };

        instance.init_gradechange = function(param) {
            $('input[id^=id_grade]').on("change", function() {
                alert(param.changegradewarning);
            });
        };

            return instance;

    }
);
