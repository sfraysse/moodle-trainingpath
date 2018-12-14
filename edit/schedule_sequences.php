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
$batch_id = required_param('batch_id', PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$group = $DB->get_record('groups', array('id'=>$group_id), '*', MUST_EXIST);
$batch = $DB->get_record('trainingpath_item', array('id'=>$batch_id), '*', MUST_EXIST);
$sequences = $DB->get_records('trainingpath_item', array('parent_id'=>$batch_id, 'type'=>EATPL_ITEM_TYPE_SEQUENCE), 'parent_position');

// Page URL
$url = new moodle_url('/mod/trainingpath/edit/schedule_sequences.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'batch_id'=>$batch_id));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$permission = trainingpath_check_tutor_permission($course, $cm, $group_id);


//------------------------------------------- Form -------------------------------------------//

// Instantiate form 
require_once('schedule_sequences_form.php');
$activities_url = (new moodle_url('/mod/trainingpath/edit/schedule_activities.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out();
$mform = new mform_schedule_sequences(null, array('cmid'=>$cmid, 'group_id'=>$group_id, 'batch_id'=>$batch_id, 'sequences'=>$sequences, 'activities_url'=>$activities_url));
if ($mform->is_cancelled()) {
	
    // No cancel on this form
	
} else if ($data = $mform->get_data()) {
	
	// Form saved
	$mform->data_postprocessing($data);

	// Parse data
	foreach($data->schedule_id as $sequence_id => $schedule_id) {
		$schedule = new stdClass();
		$schedule->cmid = $cmid;
		$schedule->context_id = $sequence_id;
		$schedule->context_type = EATPL_ITEM_TYPE_SEQUENCE;
		$schedule->group_id = $group_id;
		$schedule->access = $data->access[$sequence_id];
		$schedule->time_open = $data->time_open[$sequence_id];
		$schedule->time_close = $data->time_close[$sequence_id];
		$schedule->period_open = !isset($data->period_open[$sequence_id]) ? 0 : $data->period_open[$sequence_id];
		$schedule->period_close = !isset($data->period_close[$sequence_id]) ? 0 : $data->period_close[$sequence_id];
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
	$redirect_url = (new moodle_url('/mod/trainingpath/edit/schedule_sequences.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'batch_id'=>$batch_id)))->out();
	redirect($redirect_url);
	
} else {
	
	// Page setup
	$breadcrumb = array();
	$breadcrumb[] = array('label'=>get_string('schedules', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)))->out());
	$breadcrumb[] = array('label'=>$group->name, 'url'=>(new moodle_url('/mod/trainingpath/edit/schedule.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out());
	$breadcrumb[] = array('label'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/schedule_batches.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out());
	$breadcrumb[] = array('label'=>$batch->code);
	$breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'));
	trainingpath_schedule_setup_page($course, 'schedules', $breadcrumb, get_string('scheduling_sequences', 'trainingpath'), $permission);

	// Data load
	foreach($sequences as $sequence) {
		$sequence->schedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$sequence->id, 'context_type'=>EATPL_ITEM_TYPE_SEQUENCE, 'group_id'=>$group_id));
	}

	// Form display
	$data = $mform->data_preprocessing($sequences);
	$mform->set_data($data);
	$mform->display();
	
	// End
	echo $OUTPUT->footer();
}

?>


