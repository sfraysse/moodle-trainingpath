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
$group_id = required_param('group_id', PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$group = $DB->get_record('groups', array('id'=>$group_id), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);
$topSchedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$topItem->id, 'context_type'=>EATPL_ITEM_TYPE_PATH, 'group_id'=>$group->id), '*', MUST_EXIST);
$batches = $DB->get_records('trainingpath_item', array('parent_id'=>$topItem->id, 'type'=>EATPL_ITEM_TYPE_BATCH), 'parent_position');

// Page URL
$url = new moodle_url('/mod/trainingpath/edit/schedule_batches.php', array('cmid'=>$cmid, 'group_id'=>$group_id));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$permission = trainingpath_check_tutor_permission($course, $cm, $group_id);


//------------------------------------------- Form -------------------------------------------//

// Instantiate form 
require_once('schedule_batches_form.php');
$sequences_url = (new moodle_url('/mod/trainingpath/edit/schedule_sequences.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out();
$mform = new mform_schedule_batches(null, array('cmid'=>$cmid, 'group_id'=>$group_id, 'batches'=>$batches, 'sequences_url'=>$sequences_url, 'top_schedule'=>$topSchedule));
if ($mform->is_cancelled()) {
	
    // No cancel on this form
	
} else if ($data = $mform->get_data()) {
	
	// Form saved
	$mform->data_postprocessing($data);
	
	// Parse data
	foreach($data->schedule_id as $batch_id => $schedule_id) {
		$schedule = new stdClass();
		$schedule->cmid = $cmid;
		$schedule->context_id = $batch_id;
		$schedule->context_type = EATPL_ITEM_TYPE_BATCH;
		$schedule->group_id = $group_id;
		$schedule->access = $data->access[$batch_id];
		$schedule->time_open = $data->time_open[$batch_id];
		$schedule->time_close = $data->time_close[$batch_id];
		$schedule->information = '';
		$schedule->description = '';
		if (!$schedule_id) {
			$DB->insert_record("trainingpath_schedule", $schedule);
		} else {
			$schedule->id = $schedule_id;
			$DB->update_record("trainingpath_schedule", $schedule);
		}
	}
	
	// Redirect
	$redirect_url = (new moodle_url('/mod/trainingpath/edit/schedule_batches.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out();
	redirect($redirect_url);

} else {
	
	// Page setup
	$breadcrumb = array();
	$breadcrumb[] = array('label'=>get_string('schedules', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)))->out());
	$breadcrumb[] = array('label'=>$group->name, 'url'=>(new moodle_url('/mod/trainingpath/edit/schedule.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out());
	$breadcrumb[] = array('label'=>get_string('batches', 'trainingpath'));
	trainingpath_schedule_setup_page($course, 'schedules', $breadcrumb, get_string('scheduling_batches', 'trainingpath'), $permission);

	// Data load
	foreach($batches as $batch) {
		$batch->schedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$batch->id, 'context_type'=>EATPL_ITEM_TYPE_BATCH, 'group_id'=>$group_id));
	}

	// Form display
	$data = $mform->data_preprocessing($batches);
	$mform->set_data($data);
	$mform->display();
	
	// End
	echo $OUTPUT->footer();
}

?>


