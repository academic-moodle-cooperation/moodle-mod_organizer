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
        this.displayallslots = 0;

    };


    var instance = new Adddayslot();

    instance.init = function(param) { // Parameter 'param' contains the parameter values!

        instance.totalslots = param.totalslots;
        instance.displayallslots = param.displayallslots;

        log.info(instance.totalslots, "totalslots");
        log.info(instance.displayallslots, "displayallslots");

        if(instance.displayallslots==0) {

            for(var i=instance.totalslots-1; i>0; i--) {
                if($('#id_newslots_' + String(i) + '_day').val()==-1) {
                    $( "#id_newslots_" + String(i) + "_day" ).closest( ".form-group.row.fitem" ).hide(); //boost
                    $( "#fgroup_id_slotgroup" + String(i)).hide(); //clean
                } else {
                    break;
                }
            }
            if(i<instance.totalslots-1) {
                $('#id_addday').hide();
            }

            $('[id^=id_newslots_]').change(function () {
                var id =$(this).attr("id");
                var nextindex = parseInt(id.split("_")[2]) + 1;
                $( "#id_newslots_" + String(nextindex) + "_day" ).closest( ".form-group.row.fitem" ).show(); // boost
                $( "#fgroup_id_slotgroup" + String(nextindex)).show(); // clean
                if(nextindex==instance.totalslots) {
                    $('#id_addday').show();
                }
            });

        }


        if( $( "#id_now" ).prop( "checked") == true) {
            $('[name^=availablefrom]').prop("disabled", true);
            $('#id_availablefrom_timeunit').prop("disabled", true);
        }

    };

    return instance;

});

