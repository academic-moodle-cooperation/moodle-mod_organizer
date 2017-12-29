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


define(
    ['jquery', 'core/log'], function($, log) {

        /**
         * @constructor
         * @alias module:mod_organizer/adddayslot
         */
        var Adddayslot = function () {

            this.totalslots = 0;
            this.displayallslots = 0;
            this.totalday = "";
            this.totaltotal = "";

        };

        var instance = new Adddayslot();

        instance.init = function (param) {

            instance.totalslots = param.totalslots;  // Initial maximum of days (despite only one is displayed).
            instance.displayallslots = param.displayallslots;  // Whether to display all days so far generated.
            instance.totalday = param.totalday;  // String for new slots amount status message for a day.
            instance.totaltotal = param.totaltotal;  //  String for new slots amount total status message.

            if (instance.displayallslots == 0) {  // So the form is loaded initially.

                // Hide all days except the first one.
                for (var i = instance.totalslots - 1; i > 0; i--) {
                    if ($('#id_newslots_' + String(i) + '_day').val() == -1) {
                        $("#id_newslots_" + String(i) + "_day").closest(".form-group.row.fitem").hide(); // Boost-theme.
                        $("#fgroup_id_slotgroup" + String(i)).hide(); // Clean-theme.
                    } else {
                        break;
                    }
                }
                if (i < instance.totalslots - 1) {
                    $('#id_addday').hide();
                }

                // As soon as one day is edited, display the next one.
                $('[id^=id_newslots_]').change(
                    function () {
                        var id = $(this).attr("id");
                        var nextindex = parseInt(id.split("_")[2]) + 1;
                        $("#id_newslots_" + String(nextindex) + "_day").closest(".form-group.row.fitem").show(); // Boost-theme.
                        $("#fgroup_id_slotgroup" + String(nextindex)).show(); // Clean-theme.
                        if (nextindex == instance.totalslots) {
                            $('#id_addday').show();
                        }
                    }
                );
            }

            $('[name^="newslots"]').not(':checkbox').on('change', evaluaterow);

            if ($("#id_now").prop("checked") == true) {
                $('[name^=availablefrom]').prop("disabled", true);
                $('#id_availablefrom_timeunit').prop("disabled", true);
            }

            function evaluaterow(e) {
                var target = $(e.target);
                var name = target.attr("name");
                var i = parseInt(name.replace('newslots[', ''));
                var days = get_days(i);
                var slots = get_slots_per_day(i);
                var pax = get_pax();
                log.info(i, "i");
                $("span[name='forecastday_"+i+"']").html(days*slots*pax);
            }

            function get_days(i) {
                var weekdays = 0;
                var val = parseInt($("select[name^='newslots\["+i+"\]\[day\]']").val());
                if (val >= 0 && val <= 6) {
                    var weekdayindex = val+1;
                    weekdays = get_weekdays(weekdayindex == 7 ? 0 : weekdayindex);
                }
                return weekdays;
            }

            function get_weekdays(weekdayindex) {
                var foundweekdays = 0;
                var startdateday = $("select[name='startdate\[day\]']").val();
                var startdatemonth = $("select[name='startdate\[month\]']").val() - 1;
                var startdateyear = $("select[name='startdate\[year\]']").val();
                var startdate = new Date(startdateyear, startdatemonth, startdateday);
                var enddateday = $("select[name='enddate\[day\]']").val();
                var enddatemonth = $("select[name='enddate\[month\]']").val() - 1;
                var enddateyear = $("select[name='enddate\[year\]']").val();
                var enddate = new Date(enddateyear, enddatemonth, enddateday);
                for (var idate = startdate;idate <= enddate;idate.addDays(1)) {
                    if (idate.getDay() == weekdayindex) {
                        foundweekdays++;
                    }
                }
                return foundweekdays;
            }

            function get_slots_per_day(i) {
                var slotsfound = 0;
                var timefrom = parseInt($("select[name^='newslots\["+i+"\]\[from\]']").val());
                var timeto = parseInt($("select[name^='newslots\["+i+"\]\[to\]']").val());
                if (timeto <= timefrom) {
                    return 0;
                }
                var durationnumber = parseInt($("input[name='duration\[number\]']").val());
                var durationtimeunit = parseInt($("input[name='duration\[timeunit\]']").val());
                var gapnumber = parseInt($("input[name='gap\[number\]']").val());
                var gaptimeunit = parseInt($("input[name='gap\[timeunit\]']").val());
                if ( durationnumber.isNaN() ) {
                    return 0;
                }
                var duration = durationnumber * durationtimeunit;
                if ( gapnumber.isNaN() ) {
                    gapnumber = 0;
                }
                var gap = gapnumber * gaptimeunit;
                for (var itime = timefrom;itime <= timeto;itime+duration+gap) {
                    slotsfound++;
                }
                return slotsfound;
            }

            function get_pax() {
                var pax = $('input[name=maxparticipants]').val();
                if ($.isNumeric(pax)) {
                    return pax;
                } else {
                    return 0;
                }
            }

            Date.prototype.addDays = function(days) {
                var dat = new Date(this.valueOf());
                dat.setDate(dat.getDate() + days);
                return dat;
            };
        };

        return instance;

    });
