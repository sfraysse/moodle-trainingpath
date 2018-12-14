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
require_once($CFG->dirroot.'/mod/trainingpath/calendars/lib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 
$group_id = required_param('group_id', PARAM_INT); 
$batch_id = required_param('batch_id', PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$group = $DB->get_record('groups', array('id'=>$group_id), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);
$topSchedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$topItem->id, 'context_type'=>EATPL_ITEM_TYPE_PATH, 'group_id'=>$group->id), '*', MUST_EXIST);
$batch = $DB->get_record('trainingpath_item', array('id'=>$batch_id), '*', MUST_EXIST);
$sequences = $DB->get_records('trainingpath_item', array('parent_id'=>$batch_id, 'type'=>EATPL_ITEM_TYPE_SEQUENCE), 'parent_position');

// Page URL
$url = new moodle_url('/mod/trainingpath/edit/schedule_auto.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'batch_id'=>$batch_id));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$permission = trainingpath_check_tutor_permission($course, $cm, $group_id);

// Additional checks
if (!$topSchedule->calendar_id) trainingpath_print_error('permission_denied_calendar_not_defined');


//------------------------------------------- Form -------------------------------------------//

// Instantiate form 
require_once('schedule_auto_form.php');
$mform = new mform_schedule_auto(null, array('cmid'=>$cmid, 'group_id'=>$group_id, 'batch_id'=>$batch_id));
if ($data = $mform->get_data()) {
	
	// Do it !
	$groupSchedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$topItem->id, 'context_type'=>EATPL_ITEM_TYPE_PATH, 'group_id'=>$group_id));
	$calendar = $DB->get_record('trainingpath_calendar', array('id'=>$groupSchedule->calendar_id));
	trainingpath_calendar_generate_schedules($cmid, $group_id, $sequences, $calendar, $data->generate_schedule_from);
}

//------------------------------------------- Page settup -------------------------------------------//

$breadcrumb = array();
$breadcrumb[] = array('label'=>get_string('schedules', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)))->out());
$breadcrumb[] = array('label'=>$group->name, 'url'=>(new moodle_url('/mod/trainingpath/edit/schedule.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out());
$breadcrumb[] = array('label'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/schedule_batches.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out());
$breadcrumb[] = array('label'=>$batch->code);
$breadcrumb[] = array('label'=>get_string('auto_scheduling', 'trainingpath'));
trainingpath_schedule_setup_page($course, 'schedules', $breadcrumb, get_string('auto_scheduling', 'trainingpath'), $permission);


//------------------------------------------- Sequence table -------------------------------------------//

$cells = array();
$cells[] = (object)array('content'=>get_string('sequences', 'trainingpath'));
$cells[] = (object)array('content'=>get_string('schedules', 'trainingpath'));
$header = (object)array('cells'=>$cells);
$rows = array();
foreach($sequences as $sequence) {
	$cells = array();

	// Sequence title
	$cells[] = (object)array('content'=>'['.$sequence->code.'] '.$sequence->title);
	
	// Sequence schedule
	$schedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$sequence->id, 'context_type'=>EATPL_ITEM_TYPE_SEQUENCE, 'group_id'=>$group_id));
	if ($schedule) {
		$scheduleInfo = trainingpath_get_schedule_access_info($sequence->type, $schedule, true, $sequence);
	} else {
		$scheduleInfo = trainingpath_get_default_schedule_access_info($sequence->type, true, $sequence);
	}
	$cells[] = (object)array('content'=>$scheduleInfo->display);
	$row = (object)array('cells'=>$cells);
	$rows[] = $row;
}
echo trainingpath_get_table($rows, $header);


//------------------------------------------- Form display -------------------------------------------//

$mform->display();

// End
echo $OUTPUT->footer();

?>


