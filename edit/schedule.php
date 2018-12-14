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
$group_id = optional_param('group_id', 0, PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);

// Page URL
$params = array('cmid'=>$cmid, 'group_id'=>$group_id);
$url = new moodle_url('/mod/trainingpath/edit/schedule.php', $params);
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$permission = trainingpath_check_tutor_permission($course, $cm, $group_id);


//------------------------------------------- Form -------------------------------------------//

// Instantiate form 
require_once('schedule_form.php');
$mform = new mform_schedule(null, array('cmid'=>$cmid, 'path_id'=>$learningpath->id, 'context_id'=>$topItem->id, 'course_id'=>$course->id, 'permission'=>$permission));
if ($mform->is_cancelled()) {
	
    // Form cancelled
	$redirect_url = (new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)))->out();
	redirect($redirect_url);
	
} else if ($data = $mform->get_data()) {
	
	// Form saved
	$mform->data_postprocessing($data);
	
	if (!$data->id) {
		$DB->insert_record("trainingpath_schedule", $data);
	} else {
		$DB->update_record("trainingpath_schedule", $data);
	}
	$redirect_url = (new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)))->out();
	redirect($redirect_url);

} else {
	
	// Page setup

	// Form display
	$breadcrumb = array();
	$breadcrumb[] = array('label'=>get_string('schedules', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)))->out());
	if ($group_id) {
		$record = $DB->get_record('trainingpath_schedule', array('group_id'=>$group_id, 'context_id'=>$topItem->id, 'context_type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);
		$group = $DB->get_record('groups', array('id'=>$group_id));
		if ($group) {
			$title = $group->name;
			$titleWithIcons = $title;
			$titleWithIcons .= '<a href="'.(new moodle_url('/mod/trainingpath/edit/schedule_batches.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out().'" style="margin-left:10px;">';

			// SF2017 - Icons
			//$titleWithIcons .= ' 	<img src="'.trainingpath_get_icon('children').'" title="'.get_string('schedule_batches', 'trainingpath').'" width="16" height="16">';
			$title .= trainingpath_get_icon('children', get_string('schedule_batches', 'trainingpath'));

			$titleWithIcons .= '</a>';
		} else {
			$title = get_string('no_matching_group', 'trainingpath');
			$titleWithIcons = $title;
		}
		$breadcrumb[] = array('label'=>$title);
		trainingpath_schedule_setup_page($course, 'schedules', $breadcrumb, $titleWithIcons, $permission);
		$mform->data_preprocessing($record);
		$mform->set_data($record);
	} else {
		$breadcrumb[] = array('label'=>get_string('new_schedule', 'trainingpath'));
		trainingpath_schedule_setup_page($course, 'schedules', $breadcrumb, get_string('new_schedule', 'trainingpath'), $permission);
	}
	$mform->display();

	// End
	echo $OUTPUT->footer();
}

?>


