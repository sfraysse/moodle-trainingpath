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
require_once($CFG->dirroot.'/mod/trainingpath/report/lib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 
$groupId = required_param('group_id', PARAM_INT); 
$activityId = required_param('activity_id', PARAM_INT); 
$evalOnly = optional_param('eval_only', 0, PARAM_INT);
	
// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$activity = $DB->get_record('trainingpath_item', array('id'=>$activityId), '*', MUST_EXIST);
$sequence = $DB->get_record('trainingpath_item', array('id'=>$activity->parent_id), '*', MUST_EXIST);
$certificate = $DB->get_record('trainingpath_item', array('id'=>$sequence->grouping_id), '*', MUST_EXIST);
$group = $DB->get_record('groups', array('id'=>$groupId), '*', MUST_EXIST);

// Page URL
$url = new moodle_url('/mod/trainingpath/report/virtual.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'activity_id'=>$activityId, 'eval_only'=>$evalOnly));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$permission = trainingpath_check_tutor_permission($course, $cm, $groupId);


//------------------------------------------- Prepare data -------------------------------------------//

$session = new stdClass();
$session->users = trainingpath_report_get_users_and_tracks($group->id, $context_module, $activity->id);
$session->learningpath = $learningpath;
$session->schedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$activity->id, 'context_type'=>$activity->type, 'group_id'=>$group->id));


//------------------------------------------- Form -------------------------------------------//

if (!$session->schedule) {
	$schedule = trainingpath_get_default_schedule($activity->type, $activity->activity_type, $activity->complementary, $activity->remedial);
	$schedule->cmid = $cmid;
	$schedule->context_id = $activity->id;
	$schedule->context_type = $activity->type;
	$schedule->group_id = $groupId;
	$schedule->information = '';
	$schedule->description = '';
	$schedule->id = $DB->insert_record("trainingpath_schedule", $schedule);
	$session->schedule = $schedule;
}


//------------------------------------------- Form -------------------------------------------//

require_once('virtual_form.php');
$mform = new mform_virtual(null, array('cmid'=>$cmid, 'group_id'=>$groupId, 'activity_id'=>$activityId, 'session'=>$session, 'eval_only'=>$evalOnly, 'duration'=>$activity->duration));
if ($mform->is_cancelled()) {
	
	// ----------- Form cancelled ---------
	
	$redirect_url = new moodle_url('/mod/trainingpath/report/sequence.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'sequence_id'=>$sequence->id, 'eval_only'=>$evalOnly));
	redirect($redirect_url);
	
} else if ($data = $mform->get_data()) {
	
	// ----------- Form saved ---------

	// Prepare data 
	$mform->data_postprocessing($data, $context_module);
	
	// Schedule
	$session->schedule->duration = $data->duration;
	trainingpath_schedule_files_save($session->schedule, $mform, $cmid, 'packagefile');
	$DB->update_record("trainingpath_schedule", $session->schedule);
	
	// Participation
	foreach($data->participation as $userId => $participation) {
		trainingpath_report_record_session_track($course, $cm, $learningpath, $userId, $participation, $activity, $session->schedule);
	}
	
	// Redirect
	ob_end_clean();   // REQUIRED! @PEAR called by get_draft_files generated an empty outpu string considered as an error by Moodle in debuging mode
	$redirect_url = new moodle_url('/mod/trainingpath/report/sequence.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'sequence_id'=>$sequence->id, 'eval_only'=>$evalOnly));
	redirect($redirect_url);

} else {
	
	// ----------- Form display ---------
	
	// Page setup
	$breadcrumb = array();
	$breadcrumb[] = array('label'=>get_string('reporting', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/report/groups.php', array('cmid'=>$cmid)))->out());
	$breadcrumb[] = array('label'=>$group->name);
	$breadcrumb[] = array('label'=>get_string('learners_progress', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/report/learners.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'eval_only'=>$evalOnly)))->out());
	$breadcrumb[] = array('label'=>$certificate->code, 'url'=>(new moodle_url('/mod/trainingpath/report/certificate.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'certificate_id'=>$certificate->id, 'eval_only'=>$evalOnly)))->out());
	$breadcrumb[] = array('label'=>$sequence->code, 'url'=>(new moodle_url('/mod/trainingpath/report/sequence.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'sequence_id'=>$sequence->id, 'eval_only'=>$evalOnly)))->out());
	$breadcrumb[] = array('label'=>$activity->code);
	trainingpath_tutor_setup_page($course, 'reporting', $breadcrumb, $group->name, $permission);

	// Form display
	$data = $mform->data_preprocessing($session, $context_module);
	$mform->set_data($data);
	$mform->display();
	
	// End
	echo $OUTPUT->footer();
}

?>


