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
require_once($CFG->dirroot.'/mod/trainingpath/view/uilib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 
$via = required_param('via', PARAM_ALPHA); 
$certificate_id = optional_param('certificate_id', 0, PARAM_INT);
if (!$certificate_id) $batch_id = required_param('batch_id', PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
if ($certificate_id) $certificate = $DB->get_record('trainingpath_item', array('id'=>$certificate_id));
else $batch = $DB->get_record('trainingpath_item', array('id'=>$batch_id));

// Page URL
if ($certificate_id) $url = new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate_id, 'via'=>$via));
else $url = new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'batch_id'=>$batch_id, 'via'=>$via));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
if ($certificate_id) trainingpath_check_view_permission_or_redirect($course, $cm);
else trainingpath_check_view_permission_or_redirect($course, $cm, $batch);


//------------------------------------------- Logs -------------------------------------------//

if ($certificate_id) {
	trainingpath_trigger_item_event('item_viewed', $course, $cm, $learningpath, $certificate);
} else {
	trainingpath_trigger_item_event('item_viewed', $course, $cm, $learningpath, $batch);
}


//------------------------------------------- Page setup -------------------------------------------//

$breadcrumb = array();
if ($certificate_id) {
	$breadcrumb[] = array('label'=>get_string('certificates', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view/certificates.php', array('cmid'=>$cmid)))->out());
	$breadcrumb[] = array('label'=>$certificate->code);
} else {
	$breadcrumb[] = array('label'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view/batches.php', array('cmid'=>$cmid)))->out());
	$breadcrumb[] = array('label'=>$batch->code);
}
$breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'));
trainingpath_view_setup_page($course, $via, $breadcrumb);


//------------------------------------------- Display sequences -------------------------------------------//

// Title & My Status
if ($certificate_id) {
	$status = trainingpath_report_get_my_indicator_html($certificate->id, $certificate->type, 'right-align', $learningpath);
	echo trainingpath_get_title_with_status($certificate->title, $status);
} else {
	$status = trainingpath_report_get_my_indicator_html($batch->id, $batch->type, 'right-align', $learningpath);
	echo trainingpath_get_title_with_status($batch->title, $status);
}

// Items
$openUrl = (new moodle_url('/mod/trainingpath/view/activities.php', array('cmid'=>$cmid, 'via'=>$via)))->out().'&sequence_id=';
if ($certificate_id) {
	$editUrl = (new moodle_url('/mod/trainingpath/edit/sequence.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate_id)))->out().'&sequence_id=';
	echo trainingpath_view_get_items(EATPL_ITEM_TYPE_SEQUENCE, $course, $cm, $learningpath, $certificate_id, $openUrl, $editUrl, true);
} else {
	$editUrl = (new moodle_url('/mod/trainingpath/edit/sequence.php', array('cmid'=>$cmid, 'batch_id'=>$batch_id)))->out().'&sequence_id=';
	echo trainingpath_view_get_items(EATPL_ITEM_TYPE_SEQUENCE, $course, $cm, $learningpath, $batch_id, $openUrl, $editUrl);
}

// Buttons
$commands = array();
if ($certificate_id) {
	$editUrl = new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cm->id, 'certificate_id'=>$certificate_id));
	$backUrl = (new moodle_url('/mod/trainingpath/view/certificates.php', array('cmid'=>$cmid)))->out();
	$commands[] = (object)array('href'=>$backUrl, 'class'=>'secondary', 'title'=>get_string('back_to_certificates', 'trainingpath'));
} else {
	$editUrl = new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cm->id, 'batch_id'=>$batch_id));
	$backUrl = (new moodle_url('/mod/trainingpath/view/batches.php', array('cmid'=>$cmid)))->out();
	$commands[] = (object)array('href'=>$backUrl, 'class'=>'secondary', 'title'=>get_string('back_to_batches', 'trainingpath'));
}
if (!$learningpath->locked) {
	$commands = trainingpath_view_get_edit_commands($cmid, get_string('edit_sequences', 'trainingpath'), $editUrl, $commands);
}
echo trainingpath_get_commands_div($commands);

// End
echo $OUTPUT->footer();


//------------------------------------------- Scripts -------------------------------------------//
?>
<script src="items.js"></script>
