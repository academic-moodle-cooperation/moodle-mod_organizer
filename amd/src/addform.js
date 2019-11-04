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
 * In add form: Deactivate available from-date when checkbox "now" is clicked and vice versa.
 */


define(
    ['jquery'], function($) {

        /**
     * @constructor
     * @alias module:mod_organizer/addform
     */
        var Addform = function() {};

        var instance = new Addform();

        instance.init = function () {

            var togglebox = $('#id_now');

            if (togglebox) {
                togglebox.on('change', function(){
                    $("select[name^=availablefrom]").attr('disabled', togglebox.is(':checked'));
                    $("input[type=text][name^=availablefrom]").attr('disabled', togglebox.is(':checked'));
                });
            }

        };

        return instance;

    }
);
