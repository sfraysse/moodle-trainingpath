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
$sequence_id = required_param('sequence_id', PARAM_INT);

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$sequence = $DB->get_record('trainingpath_item', array('id'=>$sequence_id), '*', MUST_EXIST);
$certificate = $DB->get_record('trainingpath_item', array('id'=>$sequence->grouping_id));
$batch = $DB->get_record('trainingpath_item', array('id'=>$sequence->parent_id));

// Page URL
$url = new moodle_url('/mod/trainingpath/view/activities.php', array('cmid'=>$cmid, 'via'=>$via, 'sequence_id'=>$sequence_id));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
trainingpath_check_view_permission_or_redirect($course, $cm, $batch, $sequence, $via);


//------------------------------------------- Page setup -------------------------------------------//

$breadcrumb = array();
$breadcrumb[] = array('label'=>get_string($via, 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view/'.$via.'.php', array('cmid'=>$cmid)))->out());
if ($via == 'certificates') {
	$breadcrumb[] = array('label'=>$certificate->code);
	$breadcrumb[] = array('label'=>'sequences', 'url'=>(new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>$via, 'certificate_id'=>$certificate->id)))->out());
} else {
	$breadcrumb[] = array('label'=>$batch->code);
	$breadcrumb[] = array('label'=>'sequences', 'url'=>(new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>$via, 'batch_id'=>$batch->id)))->out());
}
$breadcrumb[] = array('label'=>$sequence->code);
$breadcrumb[] = array('label'=>get_string('activities', 'trainingpath'));
trainingpath_view_setup_page($course, $via, $breadcrumb);


//------------------------------------------- Display activities -------------------------------------------//

// Title
$status = trainingpath_report_get_my_indicator_html($sequence->id, $sequence->type, 'right-align', $learningpath);
echo trainingpath_get_title_with_status($sequence->title, $status);

// Items
echo trainingpath_view_get_activities($course, $cm, $learningpath, $sequence_id, $via);

// Buttons
$commands = array();
$editUrl = new moodle_url('/mod/trainingpath/edit/activities.php', array('cmid'=>$cm->id, 'via'=>$via, 'sequence_id'=>$sequence_id));
if ($via == 'certificates') {
	$backUrl = (new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>$via, 'certificate_id'=>$certificate->id)))->out();
	$commands[] = (object)array('href'=>$backUrl, 'class'=>'secondary', 'title'=>get_string('back_to_certificate', 'trainingpath'));
} else {
	$backUrl = (new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>$via, 'batch_id'=>$batch->id)))->out();
	$commands[] = (object)array('href'=>$backUrl, 'class'=>'secondary', 'title'=>get_string('back_to_batch', 'trainingpath'));
}
if (!$learningpath->locked) {
	$commands = trainingpath_view_get_edit_commands($cmid, get_string('edit_activities', 'trainingpath'), $editUrl, $commands);
}
echo trainingpath_get_commands_div($commands);

// End
echo $OUTPUT->footer();


//------------------------------------------- Scripts -------------------------------------------//
?>
<script src="items.js"></script>
