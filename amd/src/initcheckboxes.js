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
 * Tab appointments view: Check or uncheck all checkboxes.
 */


define(
    ['jquery'], function($) {

        /**
     * @constructor
     * @alias module:mod_organizer/initcheckboxes
     */
        var Initcheckboxes = function() {};

        var instance = new Initcheckboxes();

        instance.init = function() {

            /**
             * Check or uncheck checkboxes of slot overview.
             * @param {object} e element which has been clicked
             */
            function organizer_check_all(e) {
                var checked = $(e.target).is(':checked');
                var table = $('#slot_overview');

                table.find('tbody').find('tr').each(
                    function() {
                        if (($(this).css('offsetWidth') === 0 &&
                            $(this).css('offsetHeight') === 0) || $(this).css('display') === 'none') {
                            $(this).find('input[type=checkbox]').prop('checked', false);
                        } else {
                            $(this).find('input:not([disabled])[type=checkbox]').prop('checked', checked);
                        }
                    }
                );

                table.find('thead').find('input[type=checkbox]').prop('checked', checked);
                table.find('tfoot').find('input[type=checkbox]').prop('checked', checked);
            }

            $('#slot_overview thead').find('input[type=checkbox]').on('click', organizer_check_all);
            $('#slot_overview tfoot').find('input[type=checkbox]').on('click', organizer_check_all);
            $("input:not([disabled])[type=checkbox]").click(function() {
                $("#bulkactionbutton").attr("disabled", true);
                $('#slot_overview').find('input:not([disabled]):not([name="select"])[type=checkbox]').each(
                    function() {
                        if ($(this).prop('checked') == true) {
                            $("#bulkactionbutton").attr("disabled", false);
                            return false;
                        }
                    }
                );
            });
        };

        return instance;

    }
);
