// This file is part of mod_organizer for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * module.js
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

M.mod_organizer = {};

M.mod_organizer.init_mod_form = function (Y, activatecheckbox) {
    var warningdiv = Y.one("#groupingid_warning").ancestor().ancestor();
    warningdiv.addClass('advanced');
    warningdiv.hide();

    function check_group_members_only(e) {
        var groupcheckbox = e.target;
        var groupmembersonlycheckbox = Y.one('#id_groupmembersonly');

        if (groupcheckbox.get('checked') == true) {
            if (groupmembersonlycheckbox) {
                groupmembersonlycheckbox.set('checked', true);
            }
            if (Y.one('.hide.advanced') != null) {
                Y.one('input[name="mform_showadvanced"]').simulate('click');
            }
            Y.one('#id_groupmode option[value="2"]').set('selected', 'selected');
            warningdiv.show();
        } else {
            if (groupmembersonlycheckbox) {
                groupmembersonlycheckbox.set('checked', false);
            }
            if (Y.one('.hide.advanced') != null) {
                Y.one('input[name="mform_showadvanced"]').simulate('click');
            }
            Y.one('#id_groupmode option[value="0"]').set('selected', 'selected');
            warningdiv.hide();
        }
    }

    Y.one('#id_isgrouporganizer').on('change', check_group_members_only);

    if (activatecheckbox && !Y.one('.error')) {
        Y.one('#id_duedate_enabled').simulate('click');
    }
}

M.mod_organizer.init_add_form = function (Y) {
    var togglebox = Y.one('#id_now');

    if (togglebox) {
        togglebox.on('change', toggle_available_from);
    }

    var form1 = Y.one('#mform1');

    // For safari workaround.
    form1.all("input[type=submit]").on(
        ['click','keypress'], function(e){
            var sender = Y.one(e.target);
            var that = form1 = Y.one('#mform1');
            that.setData("callerid",sender.get('name'));
        }
    );

    if (form1) {
        form1.on(
            'submit', function (e) {
                var sender = Y.one(e.target);

                sendername = sender.get('name');

                if (!sendername) {
                    // Safari workaround.
                    sendername = sender.getData('callerid');
                }

                if (sendername == "createslots") {

                    conflicts = Y.one("input[type=hidden][name=conflicts]").get('value');

                    if (conflicts > 0) {
                        val = confirm(M.util.get_string('confirm_conflicts', 'organizer'));

                        if (!val) {
                            e.preventDefault();
                        }
                    }
                }
            }
        );
    }

    function toggle_available_from() {
        Y.one("select[name^=availablefrom]").set('disabled', togglebox.get('checked'));
        Y.one("input[type=text][name^=availablefrom]").set('disabled', togglebox.get('checked'));
    }
}

M.mod_organizer.init_edit_form = function (Y, imagepaths) {
    function detect_change() {
        var name = this.get('name').split("[")[0];
        set_modfield(name, 1);
        set_icon(name, 'changed');
    }

    function set_modfield(name, value) {
        Y.all("#mform1 input[name^=mod_" + name + "]").set('value', value);
    }

    function set_icon(name, type) {
        var icons = Y.all("#mform1 img[name$=" + name + "_warning]");

        if (type == 'warning') {
            icons.set('src', imagepaths['warning']);
            icons.set('title', M.util.get_string('warningtext1', 'organizer'));
        } else if (type == 'changed') {
            icons.set('src', imagepaths['changed']);
            icons.set('title', M.util.get_string('warningtext2', 'organizer'));
        } else {
            // Do nothing.
        }
    }

    var initialstate;

    function toggle_hidden_field(e) {
        var parent = e.target.ancestor();
        if (typeof initialstate == 'undefined') {
            initialstate = e.target.get('checked');
        }
        Y.all('#mform1 [name^=availablefrom]:not([name*=now])').set('disabled', e.target.get('checked'));
        if (e.target.get('checked')) {
            Y.Node.create('<input />')
                .set('type', 'hidden')
                .set('name', 'availablefrom')
                .set('value', '0')
                .appendTo(parent);
        } else {
            var hidden = Y.one('#mform1 input[name=availablefrom]');
            if (hidden) {
                hidden.remove();
            }
        }
    }

    function reset_edit_form(e) {
        set_modfield('', 0);
        set_icon('', 'warning');
        Y.all('#mform1 [name^=availablefrom]:not([name*=now])').set('disabled', initialstate);
    }

    Y.one('#mform1').delegate('change', detect_change, 'select, input[type=checkbox]');
    Y.one('#mform1').delegate('keydown', detect_change, 'input[type=text], textarea');
    Y.one('#id_availablefrom_now').on('change', toggle_hidden_field);
    Y.one('#id_editreset').on('click', reset_edit_form);

    toggle_hidden_field({'target' : Y.one('#id_availablefrom_now')});
}

M.mod_organizer.init_eval_form = function (Y) {
    function toggle_all(e) {
        var checked = e.target.get('checked');
        var sender_class = e.target.getAttribute('class').match(/allow\d+/g)[0];

        Y.all('input.' + sender_class).each(
            function (node) {
                node.set('value', checked ? 1 : 0);
            }
        );
    }

    Y.all('[name*=allownewappointments]').each(
        function (node) {
            node.on('click', toggle_all);
        }
    );
}

M.mod_organizer.init_checkboxes = function (Y) {
    function organizer_check_all(e) {
        var checked = e.target.get('checked');
        var table = Y.one('#slot_overview');

        var hidden = [];
        var visible = [];

        table.one('tbody').all('tr').each(
            function(node) {
                if ((node.get('offsetWidth') === 0 && node.get('offsetHeight') === 0) || node.get('display') === 'none') {
                    node.all('input[type=checkbox]').set('checked', false);
                } else {
                    node.all('input[type=checkbox]').set('checked', checked);
                }
            }
        );

        table.one('thead').all('input[type=checkbox]').set('checked', checked);
        table.one('tfoot').all('input[type=checkbox]').set('checked', checked);
    }

    Y.one('#slot_overview thead').all('input[type=checkbox]').on('click', organizer_check_all);
    Y.one('#slot_overview tfoot').all('input[type=checkbox]').on('click', organizer_check_all);
}

M.mod_organizer.init_infobox = function (Y) {
    function toggle_past_slots(e) {
        var tablebody = Y.one('#slot_overview tbody');
        var showpastslots = Y.one('#show_past_slots').get('checked');
        var showmyslotsonly = Y.one('#show_my_slots_only').get('checked');
        var showfreeslotsonly = Y.one('#show_free_slots_only').get('checked');

        if (showpastslots) {
            if (showmyslotsonly) {
                if (showfreeslotsonly) {
                    tablebody.all('tr.past_due.my_slot.free_slot').show();
                } else {
                    tablebody.all('tr.past_due.my_slot').show();
                }
            } else {
                if (showfreeslotsonly) {
                    tablebody.all('tr.past_due.free_slot').show();
                } else {
                    tablebody.all('tr.past_due').show();
                }
            }
        } else {
            tablebody.all('tr.past_due').hide();
        }

        toggle_info();

        M.util.set_user_preference('mod_organizer_showpasttimeslots', (showpastslots));

    }

    function toggle_free_slots(e) {
        var tablebody = Y.one('#slot_overview tbody');
        var showpastslots = Y.one('#show_past_slots').get('checked');
        var showmyslotsonly = Y.one('#show_my_slots_only').get('checked');
        var showfreeslotsonly = Y.one('#show_free_slots_only').get('checked');

        if (!showfreeslotsonly) {
            if (!showmyslotsonly) {
                if (showpastslots) {
                    tablebody.all('tr:not(.info)').show();
                } else {
                    tablebody.all('tr:not(.info):not(.past_due)').show();
                }
            } else {
                if (showpastslots) {
                    tablebody.all('tr.past_due.my_slot').show();
                } else {
                    tablebody.all('tr:not(.past_due).my_slot').show();
                }
            }
        } else {
            tablebody.all('tr:not(.info):not(.free_slot)').hide();
        }

        toggle_info();

        M.util.set_user_preference('mod_organizer_showfreeslotsonly', (showfreeslotsonly));

    }

    function toggle_other_slots(e) {
        var tablebody = Y.one('#slot_overview tbody');
        var showpastslots = Y.one('#show_past_slots').get('checked');
        var showmyslotsonly = Y.one('#show_my_slots_only').get('checked');
        var showfreeslotsonly = Y.one('#show_free_slots_only').get('checked');

        if (!showmyslotsonly) {
            if (showpastslots) {
                if (showfreeslotsonly) {
                    tablebody.all('tr:not(.info).free_slot').show();
                } else {
                    tablebody.all('tr:not(.info)').show();
                }
            } else {
                if (showfreeslotsonly) {
                    tablebody.all('tr:not(.info):not(.past_due).free_slot').show();
                } else {
                    tablebody.all('tr:not(.info):not(.past_due)').show();
                }
            }
        } else {
            tablebody.all('tr:not(.info):not(.my_slot)').hide();
        }

        toggle_info();

        M.util.set_user_preference('mod_organizer_showmyslotsonly', (showmyslotsonly));
    }

    function toggle_info() {
        var tablebody = Y.one('#slot_overview tbody');
        var noninforows = tablebody.all('tr:not(.info)');
        var noneexist = noninforows.size() == 0;
        var anyvisible = false;

        noninforows.each(
            function (node) {
                if (!((node.get('offsetWidth') === 0
                    && node.get('offsetHeight') === 0)
                    || node.get('display') === 'none')
                ) {
                    anyvisible = true;
                }
            }
        );

        var showpastslots = Y.one('#show_past_slots').get('checked');
        var showmyslotsonly = Y.one('#show_my_slots_only').get('checked');

        tablebody.all('tr.info').hide();
        if (!anyvisible) {
            if (noneexist) {
                tablebody.one('tr.no_slots_defined').show();
            } else if (showpastslots && !showmyslotsonly) {
                tablebody.one('tr.no_slots').show();
            } else if (showpastslots && showmyslotsonly) {
                tablebody.one('tr.no_my_slots').show();
            } else if (!showpastslots && showmyslotsonly) {
                tablebody.one('tr.no_due_my_slots').show();
            } else {
                tablebody.one('tr.no_due_slots').show();
            }
        }
    }

    function toggle_legend() {
        var legend = Y.one('#infobox_legend_box');

        if (!((legend.get('offsetWidth') === 0 && legend.get('offsetHeight') === 0) || legend.get('display') === 'none')) {
            legend.hide();
            Y.one('#toggle_legend').set('value',M.util.get_string('infobox_showlegend', 'organizer'));
        } else {
            legend.show();
            Y.one('#toggle_legend').set('value',M.util.get_string('infobox_hidelegend', 'organizer'));
        }
    }

    Y.one('#show_past_slots').on('click', toggle_past_slots);
    Y.one('#show_my_slots_only').on('click', toggle_other_slots); // Toggle_free_slots.
    Y.one('#show_free_slots_only').on('click', toggle_free_slots);
    Y.one('#toggle_legend').on('click', toggle_legend);
}

M.mod_organizer.init_popups = function (Y, popups) {
    var title, id, element;

    var titles = [M.util.get_string('studentcomment_title', 'organizer'),
                  M.util.get_string('teachercomment_title', 'organizer'),
                  M.util.get_string('teacherfeedback_title', 'organizer')];

    var myslotoverview = Y.one('#my_slot_overview');
    if (myslotoverview) {
        Y.one('#my_slot_overview').delegate('mouseover', show_popup, '*[id*=organizer_popup_icon]');
        Y.one('#my_slot_overview').delegate('mouseout', hide_popup, '*[id*=organizer_popup_icon]');
    }
    Y.one('#slot_overview').delegate('mouseover', show_popup, '*[id*=organizer_popup_icon]');
    Y.one('#slot_overview').delegate('mouseout', hide_popup, '*[id*=organizer_popup_icon]');

    var popuppanel = new Y.Panel(
        {
            contentBox         : Y.Node.create('<div id="organizer_popup_panel" class="block_course_overview block"/>'),
            headerContent    : '<div id="organizer_popup_header" class="header" />',
            bodyContent        : '<div id="organizer_popup_body" class="content" />',
            width              : 300,
            zIndex             : 100,
            centered           : false,
            modal              : false,
            visible            : false
        }
    );

    popuppanel.render();
    Y.one('#organizer_popup_panel .yui3-widget-buttons').remove();
    Y.one('#organizer_popup_panel .yui3-widget-hd').removeClass('yui3-widget-hd');
    Y.one('#organizer_popup_panel .yui3-widget-bd').removeClass('yui3-widget-bd');

    function show_popup(e) {
        var posx = 0;
        var posy = 0;

        e = e || window.event;

        if (e.pageX || e.pageY) {
            posx = e.pageX;
            posy = e.pageY;
        } else if (e.clientX || e.clientY) {
            posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
            posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
        }

        var data = this.get('id').split('_');

        var title = titles[data[3]];
        var content = popups[data[3]][data[4]];

        Y.one('#organizer_popup_header').set('innerHTML', '<div class="title"><h2>' + title + '</h2></div>');
        Y.one('#organizer_popup_body').set('innerHTML', '<p>' + content + '</p>');

        popuppanel.move([posx + 16, posy + 16]);
        popuppanel.show();

    }

    function hide_popup() {
        popuppanel.hide();
    }
}

M.mod_organizer.init_organizer_print_slots_form = function (Y) {
    function toggle_column() {
        var column = this.get('id').split('_')[1];
        var imgurl = this.get('src');
        var unchecked = imgurl.search('switch_minus') !== -1;

        if (unchecked) {
            imgurl = imgurl.replace('switch_minus', 'switch_plus');
            Y.one('#col_' + column).set('value', '');
        } else {
            imgurl = imgurl.replace('switch_plus', 'switch_minus');
            Y.one('#col_' + column).set('value', column);
        }
        this.set('src', imgurl);

        Y.all("[name=" + column + "_cell]").each(
            function(node) {
                if (!unchecked) {
                    node.show();
                } else {
                    node.hide();
                }
            }
        );
    }

    Y.one('.forced_scroll').delegate('click', toggle_column, 'table th img');
}

M.mod_organizer.init_organizer_print_slots_form_old = function (Y) {
    function toggle_column() {
        var column = this.get('id').split('_')[1];
        var checked = this.get('checked');

        Y.one("#col_" + column).set('checked', checked);

        Y.all("[name=" + column + "_cell]").each(
            function(node) {
                if (checked) {
                    node.show();
                } else {
                    node.hide();
                }
            }
        );
    }

    Y.one('#mform1').delegate('change', toggle_column, 'table th input[type=checkbox]');
}

M.mod_organizer.fix_calendar_styles = function (Y) {
    var t = Y.one('#region-post');
    if (t != null) {
        t.addClass('path-calendar');
    }
}

M.mod_organizer.init_grade_change = function(Y) {
    var gradenode = Y.one('#id_grade');
    if (gradenode) {
        var originalvalue = gradenode.get('value');
        gradenode.on(
            'change', function() {
                if (gradenode.get('value') != originalvalue) {
                    alert(M.str.mod_organizer.changegradewarning);
                }
            }
        );
    }
};