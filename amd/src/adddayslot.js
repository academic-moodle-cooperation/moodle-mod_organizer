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
 * Create new dayslots
 */


define(
    ['jquery'], function($) {

        /**
         * @constructor
         * @alias module:mod_organizer/adddayslot
         */
        var Adddayslot = function() {

            this.totalslots = 0;
            this.displayallslots = 0;
            this.totalday = "";
            this.totaltotal = "";
            this.current = "0";
        };

        var instance = new Adddayslot();

        instance.init = function(param) {

            instance.totalslots = param.totalslots; // Initial maximum of days (despite only one is displayed).
            instance.displayallslots = param.displayallslots; // Whether to display all days so far generated.
            instance.totalday = param.totalday; // String for new slots amount status message for a day.
            instance.totaltotal = param.totaltotal; // String for new slots amount total status message.
            instance.relativedeadline = param.relativedeadline; // Relative deadline for slot registrations in seconds.
            instance.relativedeadlinestring = param.relativedeadlinestring; /* String warning message if slots had
            not been created due to registration deadline. "xxx" is replaced by the number of not created slots. */
            instance.allowcreationofpasttimeslots = param.allowcreationofpasttimeslots;// Deadline for registrations(s).
            instance.pasttimeslotsstring = param.pasttimeslotsstring; // String warning message if slots had

            if (instance.displayallslots == 0) { // So the form is loaded initially.

                // Hide all days except the first one.
                for (var i = instance.totalslots - 1; i > 0; i--) {
                    if ($('#id_newslots_' + String(i) + '_day').val() == -1) {
                        $("#id_newslots_" + String(i) + "_day").closest(".form-group.row.fitem").hide(); // Boost-theme.
                        $("#fgroup_id_slotgroup" + String(i)).hide(); // Clean-theme.
                    } else {
                        break;
                    }
                }
                // As long as not all slots are visible hide the add day button.
                if (i < instance.totalslots - 1) {
                    $('#id_addday').hide();
                }

            } else {
                instance.current = instance.totalslots;
                evaluateallrows();
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

            /**
             * Get index of changed row and start evaluation.
             *
             * @param {object} e event of changed row element
             */
            function startevaluation(e) {
                var name = $(e.target).attr("name");
                var i = parseInt(name.replace('newslots[', ''));
                var valdayfrom = parseInt($("select[name='newslots\[" + i + "\]\[day\]']").val());
                var valdayto = parseInt($("select[name='newslots\[" + i + "\]\[dayto\]']").val());
                var valfrom = parseInt($("select[name='newslots\[" + i + "\]\[fromh\]']").val()) +
                    parseInt($("select[name='newslots\[" + i + "\]\[fromm\]']").val());
                // Proposal for to-date's time when from-date's time has been changed.
                if (valdayfrom != -1 && name.indexOf('dayto') == -1 && name.indexOf('toh') == -1 && name.indexOf('tom') == -1) {
                    var periodstartdate = getstartdate();
                    var periodenddate = getenddate();
                    for (var daydate = periodstartdate; daydate <= periodenddate; daydate = addDays(daydate * 1000, 1)) {
                        var jsdaydate = new Date(daydate * 1000);
                        var iday = jsdaydate.getDay();
                        var iweekday = iday ? (iday - 1) : 6;
                        if (valdayfrom == iweekday) {
                            break;
                        }
                    }
                    var pdate = (jsdaydate / 1000) + valfrom + getduration();
                    var jspdate = new Date(pdate * 1000);
                    var pday = jspdate.getDay();
                    var pweekday = pday ? (pday - 1) : 6;
                    $("select[name='newslots\[" + i + "\]\[dayto\]']").val(pweekday);
                    valdayto = pweekday;
                    var hours = jspdate.getHours();
                    var minutes = jspdate.getMinutes();
                    minutes = minutes % 5 ? minutes + (5 - (minutes % 5)) : minutes;
                    var pseconds = minutes * 60;
                    $("select[name='newslots\[" + i + "\]\[toh\]']").val(hours * 3600);
                    $("select[name='newslots\[" + i + "\]\[tom\]']").val(pseconds);
                }
                if (valdayfrom != -1 && valdayto != -1) {
                    evaluaterow(i);
                    shownextnewslot(i);
                } else {
                    resetrowevaluation(i);
                }
                writetotal();
            }

            /**
             * Evaluate a certain row to make forecast.
             *
             * @param {number} i Index of row
             */
            function evaluaterow(i) {
                var howmanyslots = getslots(i);
                var slots = howmanyslots[0];
                var slotsnotcreatedduetodeadline = howmanyslots[1];
                var slotsnotcreatedduetopasttime = howmanyslots[2];
                var pax = getpax();
                pax = pax * slots;
                var forecaststring = instance.totalday.replace("xxx", slots.toString()).replace("yyy", pax.toString());
                if (slotsnotcreatedduetodeadline > 0) {
                    forecaststring += " (" + instance.relativedeadlinestring.replace("xxx",
                        slotsnotcreatedduetodeadline.toString()) + ")";
                }
                if (slotsnotcreatedduetopasttime > 0) {
                    forecaststring += " (" + instance.pasttimeslotsstring.replace("xxx",
                        slotsnotcreatedduetopasttime.toString()) + ")";
                }
                $("span[name='forecastday_" + i + "']").html(forecaststring);
                $("span[name='newslots_" + i + "']").html(slots.toString());
                $("span[name='newpax_" + i + "']").html(pax.toString());
            }

            /**
             * Set row evaluation to zero.
             *
             * @param {number} i Index of row
             */
            function resetrowevaluation(i) {
                var slots = 0;
                var pax = 0;
                var forecaststring = instance.totalday.replace("xxx", slots.toString()).replace("yyy", pax.toString());
                $("span[name='forecastday_" + i + "']").html(forecaststring);
                $("span[name='newslots_" + i + "']").html(slots.toString());
                $("span[name='newpax_" + i + "']").html(pax.toString());
            }

            /**
             * Reevaluate all rows and write the totals.
             */
            function evaluateallrows() {
                for (var i = 0; i < instance.current; i++) {
                    evaluaterow(i);
                }
                writetotal();
            }

            /**
             * Get amount of slots of row i.
             *
             * @param {number} i Index of row
             * @returns {array} returnvalues slots, slotsnotcreated1, slotsnotcreated2
             */
            function getslots(i) {
                // No selected day-from: return 0.
                var dayfromvalue = parseInt($("select[name^='newslots\[" + i + "\]\[day\]']").val());
                if (dayfromvalue == -1) {
                    return [0, 0, 0];
                }
                // No selected day to: return 0.
                var daytovalue = parseInt($("select[name^='newslots\[" + i + "\]\[dayto\]']").val());
                if (daytovalue == -1) {
                    return [0, 0, 0];
                }
                // Get period, time from, slot duration and gap between slots.
                var periodstartdate = getstartdate();
                var periodenddate = getenddate();
                var valfrom = parseInt($("select[name='newslots\[" + i + "\]\[fromh\]']").val()) +
                    parseInt($("select[name='newslots\[" + i + "\]\[fromm\]']").val());
                var valto = parseInt($("select[name='newslots\[" + i + "\]\[toh\]']").val()) +
                    parseInt($("select[name='newslots\[" + i + "\]\[tom\]']").val());
                // Duration + gap in seconds.
                var duration = getduration();
                var iteration = duration + getgap();
                var iweekday, daydate, jsdaydate, datefrom, dateto, itime;
                var slots = 0;
                var slotsnotcreatedduetodeadline = 0;
                var slotsnotcreatedduetopasttime = 0;
                // Iterate through days of period.
                for (daydate = periodstartdate; daydate <= periodenddate; daydate = addDays(daydate * 1000, 1)) {
                    jsdaydate = new Date(daydate * 1000);
                    iweekday = jsdaydate.getDay() ? (jsdaydate.getDay() - 1) : 6;
                    if (dayfromvalue != iweekday) {
                        continue;
                    }
                    datefrom = daydate + valfrom;
                    dateto = daydate + ((daytovalue - dayfromvalue) * 86400) + valto;
                    while (dateto < datefrom) {
                        dateto += (7 * 86400);
                    }
                    // New: Slot overlapping period end date is allowed!
                    if (datefrom < periodstartdate || datefrom > periodenddate) {
                        continue;
                    }
                    var now = new Date();
                    var date = now.getTime() / 1000;
                    dateto = periodenddate < dateto ? periodenddate : dateto;
                    for (itime = datefrom; itime + duration <= dateto; itime += iteration) {
                        if (itime - date < instance.relativedeadline && itime - date > 0) {
                            slotsnotcreatedduetodeadline++;
                        } else if (itime - date < 0 && instance.allowcreationofpasttimeslots == 0) {
                            slotsnotcreatedduetopasttime++;
                        } else {
                            slots++;
                        }
                    }
                }
                var returnvalues = [slots, slotsnotcreatedduetodeadline, slotsnotcreatedduetopasttime];
                return returnvalues;
            }

            /**
             * Get max amount of participants of this slot.
             * @return {int} pax
             */
            function getpax() {
                var pax = $('input[name=maxparticipants]').val();
                if ($.isNumeric(pax)) {
                    return pax;
                } else {
                    return 0;
                }
            }

            /**
             * Write out overall sums of forecast bookings.
             */
            function writetotal() {
                var totalslots = 0;
                var totalpax = 0;
                $("span[name^='newslots_']").each(function() {
                    var slots = parseInt($(this).html());
                    if (isNaN(slots) == false) {
                        totalslots += slots;
                    }
                });
                $("span[name^='newpax_']").each(function() {
                    var pax = parseInt($(this).html());
                    if (isNaN(pax) == false) {
                        totalpax += pax;
                    }
                });
                var forecaststring = instance.totaltotal.replace("xxx", totalslots.toString()).replace("yyy", totalpax.toString());
                $("div[name='organizer_newslots_forecasttotal']").html(forecaststring);
            }

            /**
             * Get the startdate for the series of dates.
             * @return {number}
             */
            function getstartdate() {
                var startdateday = $("select[name='startdate\[day\]']").val();
                var startdatemonth = $("select[name='startdate\[month\]']").val() - 1;
                var startdateyear = $("select[name='startdate\[year\]']").val();
                var startdatedate = new Date(startdateyear, startdatemonth, startdateday);
                return startdatedate.getTime() / 1000;
            }

            /**
             * Get the enddate for the series of dates.
             * @return {number}
             */
            function getenddate() {
                var enddateday = $("select[name='enddate\[day\]']").val();
                var enddatemonth = $("select[name='enddate\[month\]']").val() - 1;
                var enddateyear = $("select[name='enddate\[year\]']").val();
                var enddatedate = new Date(enddateyear, enddatemonth, enddateday);
                return enddatedate.getTime() / 1000 + 86400; // Include last day of period.
            }

            /**
             * Get the duration of a slot.
             * @return {number} in seconds
             */
            function getduration() {
                var durationnumber = parseInt($("input[name='duration\[number\]']").val());
                var durationtimeunit = parseInt($("select[name='duration\[timeunit\]']").val());
                if (isNaN(durationnumber)) {
                    return 0;
                }
                // Duration in seconds.
                var duration = durationnumber * durationtimeunit;
                return duration;
            }

            /**
             * Get the gap between the dates, if any.
             * @return {number} in seconds
             */
            function getgap() {
                var gapnumber = parseInt($("input[name='gap\[number\]']").val());
                var gaptimeunit = parseInt($("select[name='gap\[timeunit\]']").val());
                if (isNaN(gapnumber)) {
                    gapnumber = 0;
                }
                // Gap in seconds.
                var gap = gapnumber * gaptimeunit;
                return gap;
            }

            /**
             * Function to add a day to a given date.
             * @param {string} date to add a day to
             * @param { number } days to add to the date
             * @return {number} the new date
             */
            function addDays(date, days) {
                var result = new Date(date);
                result.setDate(result.getDate() + days);
                return result.getTime() / 1000;
            }

            /**
             * Function to add a day to a given date.
             * @param {string} id contains the index of the current slot
             */
            function shownextnewslot(id) {
                // As soon as a to time is edited, display the next day.
                let nextindex = parseInt(id) + 1;
                if (nextindex > instance.current) {
                    $("#id_newslots_" + String(nextindex) + "_day").closest(".form-group.row.fitem").show(); // Boost-theme.
                    $("#fgroup_id_slotgroup" + String(nextindex)).show(); // Clean-theme.
                    if (nextindex == instance.totalslots) {
                        $('#id_addday').show();
                    }
                    instance.current++;
                }
            }

        };

        return instance;

    });