<?php

/* * *************************************************************
 *  This script has been developed for Moodle - http://moodle.org/
 *
 *  You can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
  *
 * ************************************************************* */

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/trainingpath/edit/uilib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);

// Page URL
$url = new moodle_url('/mod/trainingpath/edit/certificates.php', array('cmid'=>$cmid));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
if (trainingpath_check_edit_permission($course, $cm) == 'editschedule') redirect(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)));

// Locked edition
if ($learningpath->locked) redirect(new moodle_url('/mod/trainingpath/view/certificates.php', array('cmid'=>$cmid)));

// Page setup
trainingpath_edit_setup_page($course, 'certificates');


//------------------------------------------- Display certificates -------------------------------------------//

// Items
echo trainingpath_edit_get_items('certificate', $cmid);

// Buttons
$certificate_url = (new moodle_url('/mod/trainingpath/edit/certificate.php', array('cmid'=>$cmid)))->out();
$preview_url = (new moodle_url('/mod/trainingpath/view/certificates.php', array('cmid'=>$cmid)))->out();
echo trainingpath_edit_get_commands(array(
    'add'=>array('class'=>'primary', 'label'=>get_string('add_certificate', 'trainingpath'), 'href'=>$certificate_url),
    'preview'=>array('class'=>'secondary', 'label'=>get_string('preview_certificates', 'trainingpath'), 'href'=>$preview_url)
));


// End
echo $OUTPUT->footer();


//------------------------------------------- Scripts -------------------------------------------//
?>
<script src="items.js"></script>
