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
require_once($CFG->dirroot.'/mod/scormlite/report/reportlib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 
$via = required_param('via', PARAM_ALPHA); 
$activityId = required_param('activity_id', PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$activity = $DB->get_record('trainingpath_item', array('id'=>$activityId), '*', MUST_EXIST);
$sequence = $DB->get_record('trainingpath_item', array('id'=>$activity->parent_id), '*', MUST_EXIST);
$certificate = $DB->get_record('trainingpath_item', array('id'=>$sequence->grouping_id), '*', MUST_EXIST);
$batch = $DB->get_record('trainingpath_item', array('id'=>$sequence->parent_id), '*', MUST_EXIST);

// Page URL
$url = new moodle_url('/mod/trainingpath/report/eval_statistics.php', array('cmid'=>$cmid, 'activity_id'=>$activityId, 'via'=>$via));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
if (trainingpath_check_edit_permission($course, $cm) == 'editschedule') redirect(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)));


// ----------- Page setup ---------

$breadcrumb = array();
if ($via == 'certificates') {
	$breadcrumb[] = array('label'=>get_string('certificates', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/certificates.php', array('cmid'=>$cmid)))->out());
	$breadcrumb[] = array('label'=>$certificate->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/certificate.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate->id)))->out());
	$breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate->id)))->out());
	$breadcrumb[] = array('label'=>$sequence->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/sequence.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate->id, 'sequence_id'=>$sequence->id)))->out());
} else if ($via == 'batches') {
	$breadcrumb[] = array('label'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/batches.php', array('cmid'=>$cmid)))->out());
	$breadcrumb[] = array('label'=>$batch->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/batch.php', array('cmid'=>$cmid, 'batch_id'=>$batch->id)))->out());
	$breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'batch_id'=>$batch->id)))->out());
	$breadcrumb[] = array('label'=>$sequence->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/sequence.php', array('cmid'=>$cmid, 'batch_id'=>$batch->id, 'sequence_id'=>$sequence->id)))->out());
}
$breadcrumb[] = array('label'=>get_string('activities', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/activities.php', array('cmid'=>$cmid, 'sequence_id'=>$sequence->id, 'via'=>$via)))->out());
$breadcrumb[] = array('label'=>$activity->code);
$title = get_string('statistics', 'trainingpath');
trainingpath_edit_setup_page($course, $via, $breadcrumb, $title);


// ----------- Relevant data ---------

// Print the stats
scormlite_report_print_quetzal_statistics($activity->ref_id);


// ----------- Commands ---------

$previewUrl = new moodle_url('/mod/trainingpath/view/activity_eval.php', array('cmid'=>$cmid, 'activity_id'=>$activityId, 'via'=>$via));
//$editUrl = new moodle_url('/mod/trainingpath/edit/activity.php', array('cmid'=>$cmid, 'via'=>$via, 'sequence_id'=>$sequence->id, 'activity_id'=>$activityId));
$commands = array();
$commands[] = (object)array('href'=>$previewUrl, 'class'=>'secondary', 'title'=>get_string('back_to_activity', 'trainingpath'));
//$commands[] = (object)array('href'=>$editUrl, 'class'=>'secondary', 'title'=>get_string('edit', 'trainingpath'));
echo trainingpath_get_commands_div($commands);

// End
echo $OUTPUT->footer();

?>


