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
            this.current = "0";
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
                        if (nextindex > instance.current) {
                            $("#id_newslots_" + String(nextindex) + "_day").closest(".form-group.row.fitem").show(); // Boost-theme.
                            $("#fgroup_id_slotgroup" + String(nextindex)).show(); // Clean-theme.
                            if (nextindex == instance.totalslots) {
                                $('#id_addday').show();
                            }
                            instance.current++;
                        }
                    }
                );
            }

            // If a new slot field is changed evaluate the row to provide the forecast.
            $('[name^="newslots"]').not(':checkbox').on('change', startevaluation);

            // After relevant input is changed recalculate the forecasts.
            $('input[name^="duration"]').on('change', evaluateallrows);
            $('select[name^="duration"]').on('change', evaluateallrows);
            $('input[name^="gap"]').on('change', evaluateallrows);
            $('select[name^="gap"]').on('change', evaluateallrows);
            $('input[name="maxparticipants"]').on('change', evaluateallrows);
            $('select[name^="startdate"]').on('change', evaluateallrows);
            $('select[name^="enddate"]').on('change', evaluateallrows);

            // If apply period starts now deactivate apply date in form.
            if ($("#id_now").prop("checked") == true) {
                $('[name^=availablefrom]').prop("disabled", true);
                $('#id_availablefrom_timeunit').prop("disabled", true);
            }

            // Get index of changed row and start evaluation.
            function startevaluation(e) {
                var target = $(e.target);
                var name = target.attr("name");
                var i = parseInt(name.replace('newslots[', ''));
                var valdayfrom = parseInt($("select[name='newslots\["+i+"\]\[day\]']").val());
                var valdayto = parseInt($("select[name='newslots\["+i+"\]\[dayto\]']").val());
                var valfrom = parseInt($("select[name='newslots\["+i+"\]\[from\]']").val());
                if (valdayfrom != -1 && valdayto == -1) {
                    var adaptvalue;
                    if (valfrom < 29700) {
                        adaptvalue = valfrom + 300;
                        $("select[name='newslots\["+i+"\]\[dayto\]']").val(valdayfrom);
                        $("select[name='newslots\["+i+"\]\[to\]']").val(adaptvalue);
                    } else {
                        adaptvalue = 0;
                        var adaptday = (valdayto + 1) % 6;
                        $("select[name='newslots\["+i+"\]\[dayto\]']").val(adaptday);
                        $("select[name='newslots\["+i+"\]\[to\]']").val(adaptvalue);
                    }
                }
                if (valdayfrom != -1 && valdayto != -1) {
                    evaluaterow(i);
                    writetotal();
                }
            }

            // Evaluate a certain row to make forecast.
            function evaluaterow(i) {
                var slots = getslots(i);
                var pax = getpax();
                pax = pax * slots;
                var forecaststring = instance.totalday.replace("xxx", slots.toString()).replace("yyy", pax.toString());
                $("span[name='forecastday_"+i+"']").html(forecaststring);
                $("span[name='newslots_"+i+"']").html(slots.toString());
                $("span[name='newpax_"+i+"']").html(pax.toString());
            }

            // Reevaluate all rows and write the totals.
            function evaluateallrows() {
                for (var i = 0;i < instance.current;i++) {
                    evaluaterow(i);
                }
                writetotal();
            }

            function getslots(i) {

                var startdateday = $("select[name='startdate\[day\]']").val();
                var startdatemonth = $("select[name='startdate\[month\]']").val() - 1;
                var startdateyear = $("select[name='startdate\[year\]']").val();
                var startdatedate = new Date(startdateyear, startdatemonth, startdateday);
                var startdate = startdatedate.getTime()/1000;
                var enddateday = $("select[name='enddate\[day\]']").val();
                var enddatemonth = $("select[name='enddate\[month\]']").val() - 1;
                var enddateyear = $("select[name='enddate\[year\]']").val();
                var enddatedate = new Date(enddateyear, enddatemonth, enddateday);
                var enddate = enddatedate.getTime()/1000;
                var valday = parseInt($("select[name^='newslots\["+i+"\]\[day\]']").val());
                if (valday == -1) {
                    return 0;
                }
                var valdayto = parseInt($("select[name^='newslots\["+i+"\]\[dayto\]']").val());
                if (valdayto == -1) {
                    return 0;
                }
                var valfrom = parseInt($("select[name='newslots\["+i+"\]\[from\]']").val());
                var valto = parseInt($("select[name='newslots\["+i+"\]\[to\]']").val());
                var durationnumber = parseInt($("input[name='duration\[number\]']").val());
                var durationtimeunit = parseInt($("select[name='duration\[timeunit\]']").val());
                var gapnumber = parseInt($("input[name='gap\[number\]']").val());
                var gaptimeunit = parseInt($("select[name='gap\[timeunit\]']").val());
                if ( isNaN(durationnumber) ) {
                    return 0;
                }
                var duration = durationnumber * durationtimeunit;
                if ( isNaN(gapnumber) ) {
                    gapnumber = 0;
                }
                var gap = gapnumber * gaptimeunit;
                var iteration = duration + gap;
                var iweekday, daydate, daydatedate, datefrom, dateto, itime;
                var slots = 0;
                for (daydate = startdate; daydate <= enddate; daydate += 86400) {
                    daydatedate = new Date(daydate*1000);
                    iweekday = daydatedate.getDay() - 1;
                    if (iweekday == -1) iweekday = 6;
                    if (valday != iweekday) {
                        continue;
                    }
                    datefrom = daydate + valfrom;
                    dateto = daydate + ((valdayto-valday) * 86400) + valto;
                    if (dateto < datefrom) {
                        dateto += (7 * 86400);
                    }
                    if (datefrom < startdate || datefrom > enddate || dateto > enddate) {
                        continue;
                    }
                    for (itime = datefrom; itime + duration <= dateto; itime += iteration) {
                        slots++;
                    }
                }
                return slots;
            }

            function getpax() {
                var pax = $('input[name=maxparticipants]').val();
                if ($.isNumeric(pax)) {
                    return pax;
                } else {
                    return 0;
                }
            }

            function writetotal() {
                var totalslots = 0;
                var totalpax = 0;
                $("span[name^='newslots_']").each (function() {
                    var slots = parseInt($( this ).html());
                    if (isNaN(slots) == false) {
                        totalslots += slots;
                    }
                });
                $("span[name^='newpax_']").each (function() {
                    var pax = parseInt($( this ).html());
                    if (isNaN(pax) == false) {
                        totalpax += pax;
                    }
                });
                var forecaststring = instance.totaltotal.replace("xxx", totalslots.toString()).replace("yyy", totalpax.toString());
                $("div[name='organizer_newslots_forecasttotal']").html(forecaststring);
            }

            function adddays(dat, days) {
                dat.setDate(dat.getDate() + days);
                return dat;
            }
        };

        return instance;

    });
