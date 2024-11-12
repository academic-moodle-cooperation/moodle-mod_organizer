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
 * In print form: Toggle columns (show/hide).
 */


define(
    ['jquery', 'core/config', 'core/log'], function($, config, log) {

        /**
     * @constructor
     * @alias module:mod_organizer/printform
     */
        var Printform = function() {
            this.iconminus = "";
            this.iconplus = "";
        };

        var instance = new Printform();

        instance.init = function(param) {

            /**
             * Writes expand icon (+) and hide icon (-) in every header cell. Every expand icon is set to hidden.
             */
            function init_header() {
                var printpreview = $('#print_preview');
                printpreview.find('th').find('a[name$=_cell]').each(function() {
                    var name = $(this).attr("name");
                    var col = name.split("_")[0];
                    var iconminus = instance.iconminus.replace('xxx', col + '_thiconminus').replace('yyy', col);
                    var iconplus = instance.iconplus.replace('xxx', col + '_thiconplus').replace('yyy', col);
                    $(this).parent().append(iconminus).append(iconplus);
                    $('#' + col + '_thiconplus').hide();
                });
                var $form = $('#organizer_main_cointainer form');
                $('input[name="downloadfile"]').click(function() { $form.attr('target', '_blank'); });
                $('input[name="cancel"]').click(function() { $form.attr('target', '_self'); });
            }

            /**
             * During initialization of the table every column which is set to hidden in the user preferences
             * will be hidden here.
             */
            function init_noprints() {
                var printpreview = $('#print_preview');
                printpreview.find('th').find('a[noprint*=1]').each(function() {
                    var name = $(this).attr("name");
                    var col = name.split("_")[0];
                    $('#' + col + '_thiconminus').trigger("click");
                });
            }

            /**
             * Stores the invisible columms of the print preview table in the moodle user preferences table.
             */
            function set_user_preference() {
                var name = "mod_organizer_noprintfields";
                var values = "";
                $("img[id$='_thiconplus']").not(':hidden').each(function() {
                    values += $(this).attr("col") + ',';
                });
                var cfg = {
                    method: 'get',
                    url: config.wwwroot + '/lib/ajax/setuserpref.php',
                    data: {
                        'sesskey': config.sesskey,
                        'pref': encodeURI(name),
                        'value': encodeURI(values)
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        log.info("set user preference " + name + ": " + values, "organizer");
                    },
                    success: function() {
                        log.info("set user preference OK", "organizer");
                    },
                    error: function() {
                        log.error("set user preference FAILED", "organizer");
                    }
                };
                $.ajax(cfg);
            }

            /**
             * Hide a former visible column or show a former hidden column here.
             * @param {object} e the click event of the + or - icon
             */
            function toggle_column(e) {
                var target = $(e.target);
                var col = target.attr('col');
                var src = target.attr('src');
                var hide;
                if (src.indexOf("minus") != -1) {
                    hide = true;
                    $('#' + col + '_thiconminus').hide();
                    $('#' + col + '_thiconplus').show();
                    $('a[name=' + col + '_cell]').hide();
                } else {
                    hide = false;
                    $('#' + col + '_thiconplus').hide();
                    $('#' + col + '_thiconminus').show();
                    $('a[name=' + col + '_cell]').show();
                }
                var printpreview = $('#print_preview');
                printpreview.find('span[name="' + col + '_cell"]').each(
                    function() {
                        if (hide) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    }
                );
                set_user_preference();
            }

            instance.iconminus = param.iconminus;
            instance.iconplus = param.iconplus;

            init_header();

            var printpreview = $('#print_preview');
            printpreview.find('th').find('img[id*=_thicon]').on('click', toggle_column);

            init_noprints();
        };
        return instance;

    }
);
