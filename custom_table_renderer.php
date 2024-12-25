<?php
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
 * custom_table_renderer.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Render table for organizer
 * @param html_table $table
 * @param $printfooter
 * @param $overrideevenodd
 * @return string
 */
function organizer_render_table_with_footer(html_table $table, $printfooter = true, $overrideevenodd = false) {
    // Prepare table data and populate missing properties with reasonable defaults.
    if (!empty($table->align)) {
        foreach ($table->align as $key => $aa) {
            if ($aa) {
                $table->align[$key] = 'text-align:'. fix_align_rtl($aa) .';';  // Fix for RTL languages.
            } else {
                $table->align[$key] = null;
            }
        }
    }
    if (!empty($table->size)) {
        foreach ($table->size as $key => $ss) {
            if ($ss) {
                $table->size[$key] = 'width:'. $ss .';';
            } else {
                $table->size[$key] = null;
            }
        }
    }
    if (!empty($table->wrap)) {
        foreach ($table->wrap as $key => $ww) {
            if ($ww) {
                $table->wrap[$key] = 'white-space:nowrap;';
            } else {
                $table->wrap[$key] = '';
            }
        }
    }
    if (!empty($table->head)) {
        foreach ($table->head as $key => $val) {
            if (!isset($table->align[$key])) {
                $table->align[$key] = null;
            }
            if (!isset($table->size[$key])) {
                $table->size[$key] = null;
            }
            if (!isset($table->wrap[$key])) {
                $table->wrap[$key] = null;
            }

        }
    }
    if (empty($table->attributes['class'])) {
        $table->attributes['class'] = 'generaltable';
    }
    if (!empty($table->tablealign)) {
        $table->attributes['class'] .= ' boxalign' . $table->tablealign;
    }

    // Explicitly assigned properties override those defined via $table->attributes.
    $table->attributes['class'] = trim($table->attributes['class']);
    $attributes = array_merge(
        $table->attributes, [
            'id'            => $table->id,
            'width'         => $table->width,
            'summary'       => $table->summary,
            'cellpadding'   => $table->cellpadding,
            'cellspacing'   => $table->cellspacing,
        ]
    );
    $output = html_writer::start_tag('table', $attributes) . "\n";

    $countcols = 0;

    $headfoot = $printfooter ? ['thead', 'tfoot'] : ['thead'];

    if (!empty($table->head)) {
        foreach ($headfoot as $tag) {
            $countcols = count($table->head);

            $output .= html_writer::start_tag($tag, []) . "\n";
            $output .= html_writer::start_tag('tr', []) . "\n";
            $keys = array_keys($table->head);
            $lastkey = end($keys);

            foreach ($table->head as $key => $heading) {
                // Convert plain string headings into html_table_cell objects.
                if (!($heading instanceof html_table_cell)) {
                    $headingtext = $heading;
                    $heading = new html_table_cell();
                    $heading->text = $headingtext;
                    $heading->header = true;
                }

                if ($heading->header !== false) {
                    $heading->header = true;
                }

                if ($heading->header && empty($heading->scope)) {
                    $heading->scope = 'col';
                }

                $heading->attributes['class'] .= ' header c' . $key;
                if (isset($table->headspan[$key]) && $table->headspan[$key] > 1) {
                    $heading->colspan = $table->headspan[$key];
                    $countcols += $table->headspan[$key] - 1;
                }

                if ($key == $lastkey) {
                    $heading->attributes['class'] .= ' lastcol';
                }
                if (isset($table->colclasses[$key])) {
                    $heading->attributes['class'] .= ' ' . $table->colclasses[$key];
                }
                $heading->attributes['class'] = trim($heading->attributes['class']);
                $attributes = array_merge(
                    $heading->attributes, [
                        'style'     => $table->align[$key] . $table->size[$key] . $heading->style,
                        'scope'     => $heading->scope,
                        'colspan'   => $heading->colspan,
                    ]
                );

                $tagtype = 'td';
                if ($heading->header === true) {
                    $tagtype = 'th';
                }
                $output .= html_writer::tag($tagtype, $heading->text, $attributes) . "\n";
            }
            $output .= html_writer::end_tag('tr') . "\n";
            $output .= html_writer::end_tag($tag) . "\n";
        }

        if (empty($table->data)) {
            // For valid XHTML strict every table must contain either a valid tr
            // or a valid tbody... both of which must contain a valid td.
            $output .= html_writer::start_tag('tbody', ['class' => 'empty']);
            $output .= html_writer::tag('tr', html_writer::tag('td', '', ['colspan' => count($table->head)]));
            $output .= html_writer::end_tag('tbody');
        }
    }

    if (!empty($table->data)) {
        $oddeven    = 1;
        $keys       = array_keys($table->data);
        $lastrowkey = end($keys);
        $output .= html_writer::start_tag('tbody', []);

        foreach ($table->data as $key => $row) {
            if (($row === 'hr') && ($countcols)) {
                $output .= html_writer::tag(
                    'td', html_writer::tag('div', '', ['class' => 'tabledivider']),
                    ['colspan' => $countcols]
                );
            } else {
                // Convert array rows to html_table_rows and cell strings to html_table_cell objects.
                if (!($row instanceof html_table_row)) {
                    $newrow = new html_table_row();

                    foreach ($row as $item) {
                        $cell = new html_table_cell();
                        $cell->text = $item;
                        $newrow->cells[] = $cell;
                    }
                    $row = $newrow;
                }

                $oddeven = $oddeven ? 0 : 1;
                if (isset($table->rowclasses[$key])) {
                    $row->attributes['class'] .= ' ' . $table->rowclasses[$key];
                }

                if (!$overrideevenodd) {
                    $row->attributes['class'] .= ' r' . $oddeven;
                }

                if ($key == $lastrowkey) {
                    $row->attributes['class'] .= ' lastrow';
                }

                if (!isset($row->attributes['name'])) {
                    $row->attributes['name'] = '';
                }

                $output .= html_writer::start_tag(
                    'tr',
                    ['class' => trim($row->attributes['class']),
                    'style' => $row->style, 'id' => $row->id, 'name' => trim($row->attributes['name'])]
                )
                        . "\n";
                $keys2 = array_keys($row->cells);
                $lastkey = end($keys2);

                $gotlastkey = false; // Flag for sanity checking.
                foreach ($row->cells as $key => $cell) {
                    if ($gotlastkey) {
                        // This should never happen. Why do we have a cell after the last cell?
                        mtrace("A cell with key ($key) was found after the last key ($lastkey)");
                    }

                    if (!($cell instanceof html_table_cell)) {
                        $mycell = new html_table_cell();
                        $mycell->text = $cell;
                        $cell = $mycell;
                    }

                    if (($cell->header === true) && empty($cell->scope)) {
                        $cell->scope = 'row';
                    }

                    if (isset($table->colclasses[$key])) {
                        $cell->attributes['class'] .= ' ' . $table->colclasses[$key];
                    }

                    $cell->attributes['class'] .= ' cell c' . $key;
                    if ($key == $lastkey) {
                        $cell->attributes['class'] .= ' lastcol';
                        $gotlastkey = true;
                    }
                    $tdstyle = '';
                    $tdstyle .= isset($table->align[$key]) ? $table->align[$key] : '';
                    $tdstyle .= isset($table->size[$key]) ? $table->size[$key] : '';
                    $tdstyle .= isset($table->wrap[$key]) ? $table->wrap[$key] : '';
                    $cell->attributes['class'] = trim($cell->attributes['class']);
                    $tdattributes = array_merge(
                        $cell->attributes, [
                            'style' => $tdstyle . $cell->style,
                            'colspan' => $cell->colspan,
                            'rowspan' => $cell->rowspan,
                            'id' => $cell->id,
                            'abbr' => $cell->abbr,
                            'scope' => $cell->scope,
                        ]
                    );
                    $tagtype = 'td';
                    if ($cell->header === true) {
                        $tagtype = 'th';
                    }
                    $output .= html_writer::tag($tagtype, organizer_filter_text($cell->text), $tdattributes) . "\n";
                }
            }
            $output .= html_writer::end_tag('tr') . "\n";
        }
        $output .= html_writer::end_tag('tbody') . "\n";
    }
    $output .= html_writer::end_tag('table') . "\n";
    $output = html_writer::tag('div', $output);
    return $output;
}


/**
 * Builds part of the print settings form
 *
 * @param Moodle_form $mform
 * @param array $exportformats
 * @return Moodle_form
 * @throws coding_exception
 */
function organizer_build_printsettingsform($mform, $exportformats) {

    $mform->addElement('select', 'format', get_string('format', 'organizer'), $exportformats);

    $mform->addElement('static', 'pdf_settings', get_string('pdfsettings', 'organizer'));

    $entriesperpage = get_user_preferences('organizer_printperpage', 20);
    $printperpageoptimal = get_user_preferences('organizer_printperpage_optimal', 0);
    $textsize = get_user_preferences('organizer_textsize', 10);
    $pageorientation = get_user_preferences('organizer_pageorientation', 'P');
    $headerfooter = get_user_preferences('organizer_headerfooter', 1);

    // Submissions per page.
    $pppgroup = [];
    $pppgroup[] = &$mform->createElement('text', 'entriesperpage', get_string('numentries', 'organizer'), ['size' => '2']);
    $pppgroup[] = &$mform->createElement(
        'advcheckbox', 'printperpage_optimal',
        '', get_string('stroptimal', 'organizer'), ["group" => 1]
    );

    $mform->addGroup($pppgroup, 'printperpagegrp', get_string('numentries', 'organizer'), [' '], false);
    $mform->setType('entriesperpage', PARAM_INT);

    $mform->setDefault('entriesperpage', $entriesperpage);
    $mform->setDefault('printperpage_optimal', $printperpageoptimal);

    $mform->addHelpButton('printperpagegrp', 'numentries', 'organizer');

    $mform->disabledif ('entriesperpage', 'printperpage_optimal', 'checked');
    $mform->disabledif ('printperpagegrp', 'format', 'neq', 'pdf');

    $mform->addElement(
        'select', 'textsize', get_string('textsize', 'organizer'),
        ['8' => get_string('font_small', 'organizer'), '10' => get_string('font_medium', 'organizer'),
            '12' => get_string('font_large', 'organizer')]
    );

    $mform->setDefault('textsize', $textsize);
    $mform->disabledif ('textsize', 'format', 'neq', 'pdf');

    $mform->addElement(
        'select', 'pageorientation', get_string('pageorientation', 'organizer'),
        ['P' => get_string('orientationportrait', 'organizer'),
            'L' => get_string('orientationlandscape', 'organizer')]
    );

    $mform->setDefault('pageorientation', $pageorientation);
    $mform->disabledif ('pageorientation', 'format', 'neq', 'pdf');

    $mform->addElement(
        'advcheckbox', 'headerfooter', get_string('headerfooter', 'organizer'), null, null,
        [0, 1]
    );
    $mform->setType('headerfooter', PARAM_BOOL);
    $mform->setDefault('headerfooter', $headerfooter);
    $mform->addHelpButton('headerfooter', 'headerfooter', 'organizer');

    return $mform;
}

/**
 * Finalize the printform and give back its output
 *
 * @param moodle_form $mform the printform
 * @return string output of form
 * @throws coding_exception
 */
function organizer_printtablepreview_icons($output) {
    global $OUTPUT;

    $helpicon = new help_icon('datapreviewtitle', 'organizer');
    $output .= html_writer::tag(
        'div',
        get_string('datapreviewtitle', 'organizer') . $OUTPUT->render($helpicon),
        ['class' => 'datapreviewtitle']
    );

    return $output;
}
