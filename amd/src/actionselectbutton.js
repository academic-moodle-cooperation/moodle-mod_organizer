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
     * @alias module:mod_datalynx/actionselectbutton
     */
    var Actionselectbutton = function() {

        this.msgnocheckboxselected = 0;
        this.msgnoactionselected = 0;

    };

    var instance = new Actionselectbutton();

    instance.init = function(param) { // Parameter 'param' contains the parameter values!

        instance.msgnocheckboxselected = param.msgnocheckboxselected;
        instance.msgnoactionselected = param.msgnoactionselected;

        log.info(instance.msgnocheckboxselected, "msgnocheckboxselected");
        log.info(instance.msgnoactionselected, "msgnoactionselected");

        $( "form[name='viewform']" ).submit(function( event ) {
            // Check if any checkbox checked
            if ($("form[name='viewform'] input:checkbox:checked").length > 0)
            {
                // Check if any action is seleted
                if($("select[name='bulkaction']").val()){
                    // Everything fine
                    return true;
                } else {
                    // No action selected, abortion with warning
                    alert(instance.msgnoactionselected);
                    event.preventDefault();
                }
            }
            else
            {
                // no checkbox is checked, abortion with warning
                alert(instance.msgnocheckboxselected);
                event.preventDefault();
            }
        });

    };

    return instance;

});
