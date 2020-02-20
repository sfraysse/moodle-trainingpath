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
$url = new moodle_url('/mod/trainingpath/report/content.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'activity_id'=>$activityId, 'eval_only'=>$evalOnly));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$permission = trainingpath_check_tutor_permission($course, $cm, $groupId);


//------------------------------------------- Prepare data -------------------------------------------//

$session = new stdClass();
$session->users = trainingpath_report_get_users_and_tracks($group->id, $context_module, $activity->id);
foreach ($session->users as $user) {
    $track = isset($user->track) ? $user->track : null;
    $user->forcableTime = trainingpath_report_get_forcable_time($activity, $track);
}
$session->learningpath = $learningpath;


//------------------------------------------- Form -------------------------------------------//

require_once('content_form.php');
$mform = new mform_content(null, array('cmid'=>$cmid, 'group_id'=>$groupId, 'activity_id'=>$activityId, 'sequence_id'=>$sequence->id, 'session'=>$session, 'eval_only'=>$evalOnly));
if ($mform->is_cancelled()) {
	
	// ----------- Form cancelled ---------
	
	$redirect_url = new moodle_url('/mod/trainingpath/report/sequence.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'sequence_id'=>$sequence->id, 'eval_only'=>$evalOnly));
	redirect($redirect_url);
	
} else if ($data = $mform->get_data()) {
	
	// ----------- Form saved ---------

	// Prepare data 
	$mform->data_postprocessing($data, $context_module);
	
    // Record data
	foreach($data->force as $userId => $force) {
        $time = intval($data->time[$userId] * 60);
		trainingpath_report_force_content_completion($course, $cm, $learningpath, $userId, $force, $activity, $time);
	}
	
	// Redirect
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


