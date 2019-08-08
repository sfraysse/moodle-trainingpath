<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Assessment Path for Moodle.
 *
 * @package    mod_trainingpath
 * @copyright  2019 Sébastien Fraysse {@link http://fraysse.eu}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

// Params.
$courseid = required_param('id', PARAM_INT);

// Objects.
$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

// Permissions.
require_course_login($course, true);

// Trigger instances list viewed event.
$context = context_course::instance($course->id);
$event = \mod_trainingpath\event\course_module_instance_list_viewed::create(['context' => $context]);
$event->add_record_snapshot('course', $course);
$event->trigger();

// Some strings.
$strtrainingpath = get_string('modulename', 'trainingpath');
$strtrainingpaths = get_string('modulenameplural', 'trainingpath');
$strname = get_string('name');
$strintro = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

// Page setup.
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/trainingpath/index.php', array('id' => $course->id));
$PAGE->set_title($course->shortname . ': ' . $strtrainingpaths);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strtrainingpaths);

// Content.

echo $OUTPUT->header();
echo $OUTPUT->heading($strtrainingpaths);
if (!$trainingpaths = get_all_instances_in_course('trainingpath', $course)) {
    notice(get_string('thereareno', 'moodle', $strtrainingpaths), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

$usesections = course_format_uses_sections($course->format);

$table = new html_table();
$table->attributes['class'] = 'generaltable mod_index';

if ($usesections) {
    $strsectionname = get_string('sectionname', 'format_'.$course->format);
    $table->head  = array ($strsectionname, $strname, $strintro);
    $table->align = array ('center', 'left', 'left');
} else {
    $table->head  = array ($strlastmodified, $strname, $strintro);
    $table->align = array ('left', 'left', 'left');
}

$modinfo = get_fast_modinfo($course);
$currentsection = '';
foreach ($trainingpaths as $trainingpath) {
    $cm = $modinfo->cms[$trainingpath->coursemodule];
    if ($usesections) {
        $printsection = '';
        if ($trainingpath->section !== $currentsection) {
            if ($trainingpath->section) {
                $printsection = get_section_name($course, $trainingpath->section);
            }
            if ($currentsection !== '') {
                $table->data[] = 'hr';
            }
            $currentsection = $trainingpath->section;
        }
    } else {
        $printsection = '<span class="smallinfo">'.userdate($trainingpath->timemodified)."</span>";
    }

    $class = $trainingpath->visible ? '' : 'class="dimmed"'; // hidden modules are dimmed

    $table->data[] = array (
        $printsection,
        "<a $class href=\"view.php?id=$cm->id\">".format_string($trainingpath->name)."</a>",
        format_module_intro('trainingpath', $trainingpath, $cm->id));
}

echo html_writer::table($table);

echo $OUTPUT->footer();
