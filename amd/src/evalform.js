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
 * In eval form: If group organizer and "allownewappointments" is clicked, the hidden field of every group user
 * is synchronized.
 */


define(
    ['jquery'], function($) {

        /**
     * @constructor
     * @alias module:mod_organizer/evalform
     */
        var Evalform = function() {};

        var instance = new Evalform();

        instance.init = function () {
            function toggle_all(e) {
                var target = $(e.target);
                var checked = target.is(':checked');
                var sender_class = target.attr('class').match(/allow\d+/g)[0];

                $('input.' + sender_class).val(checked ? "1" : "0");
            }

            $('[name*=allownewappointments]').on('click', toggle_all);
        };

        return instance;

    }
);
