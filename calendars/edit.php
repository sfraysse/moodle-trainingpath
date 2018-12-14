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
$id = optional_param('id', 0, PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);

// Page URL
$params = array('cmid'=>$cmid, 'id'=>$id);
$url = new moodle_url('/mod/trainingpath/calendars/edit.php', $params);
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$permission = trainingpath_check_edit_permission($course, $cm);


//------------------------------------------- Form -------------------------------------------//

// Instantiate form 
require_once('edit_form.php');
$mform = new mform_calendar(null, array('cmid'=>$cmid, 'path_id'=>$learningpath->id));
if ($mform->is_cancelled()) {
	
    // Form cancelled
	$redirect_url = (new moodle_url('/mod/trainingpath/calendars/manage.php', array('cmid'=>$cmid)))->out();
	redirect($redirect_url);
	
} else if ($data = $mform->get_data()) {
	
	// Form saved
	$mform->data_postprocessing($data);
	
	if (!$data->id) {
		$DB->insert_record("trainingpath_calendar", $data);
	} else {
		$DB->update_record("trainingpath_calendar", $data);
	}
	$redirect_url = (new moodle_url('/mod/trainingpath/calendars/manage.php', array('cmid'=>$cmid)))->out();
	redirect($redirect_url);

} else {
	
	// Page setup

	// Form display
	$breadcrumb = array();
	$breadcrumb[] = array('label'=>get_string('manage_calendars', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/calendars/manage.php', array('cmid'=>$cmid)))->out());
	if ($id) {
		$record = $DB->get_record('trainingpath_calendar', array('id'=>$id), '*', MUST_EXIST);
		$breadcrumb[] = array('label'=>$record->title);
		trainingpath_schedule_setup_page($course, 'schedules', $breadcrumb, $record->title, $permission);
		$mform->data_preprocessing($record);
		$mform->set_data($record);
	} else {
		$breadcrumb[] = array('label'=>get_string('new_calendar', 'trainingpath'));
		trainingpath_schedule_setup_page($course, 'schedules', $breadcrumb, get_string('new_calendar', 'trainingpath'), $permission);
	}
	$mform->display();

	// End
	echo $OUTPUT->footer();
}

?>


