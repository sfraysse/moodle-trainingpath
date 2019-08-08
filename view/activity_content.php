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
require_once($CFG->dirroot.'/mod/trainingpath/report/lib.php');
require_once($CFG->dirroot.'/mod/scormlite/report/reportlib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 
$via = required_param('via', PARAM_ALPHA); 
$activity_id = required_param('activity_id', PARAM_INT);

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$item = $DB->get_record('trainingpath_item', array('id'=>$activity_id), '*', MUST_EXIST);
$sco = $DB->get_record('scormlite_scoes', array('id'=>$item->ref_id), '*', MUST_EXIST);
$sequence = $DB->get_record('trainingpath_item', array('id'=>$item->parent_id), '*', MUST_EXIST);
$certificate = $DB->get_record('trainingpath_item', array('id'=>$sequence->grouping_id), '*', MUST_EXIST);
$batch = $DB->get_record('trainingpath_item', array('id'=>$sequence->parent_id), '*', MUST_EXIST);

// Page URL
$url = new moodle_url('/mod/trainingpath/view/activity_content.php', array('cmid'=>$cmid, 'via'=>$via, 'activity_id'=>$activity_id));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$scheduleInfo = trainingpath_check_view_permission_or_redirect($course, $cm, $batch, $sequence, $via, $item);


//------------------------------------------- Logs -------------------------------------------//

trainingpath_trigger_item_event('activity_viewed', $course, $cm, $learningpath, $item);


//------------------------------------------- Page setup -------------------------------------------//

$breadcrumb = array();
$breadcrumb[] = array('label'=>get_string($via, 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view/'.$via.'.php', array('cmid'=>$cmid)))->out());
if ($via == 'certificates') {
	$breadcrumb[] = array('label'=>$certificate->code);
	$breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>$via, 'certificate_id'=>$certificate->id)))->out());
} else {
	$breadcrumb[] = array('label'=>$batch->code);
	$breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>$via, 'batch_id'=>$batch->id)))->out());
}
$breadcrumb[] = array('label'=>$sequence->code);
$breadcrumb[] = array('label'=>get_string('activities', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view/activities.php', array('cmid'=>$cmid, 'via'=>$via, 'sequence_id'=>$sequence->id)))->out());
if ($item->complementary) {
	$breadcrumb[] = array('label'=>get_string('content', 'trainingpath'));
	$title = $item->title;
} else {
	$breadcrumb[] = array('label'=>$item->code);
	$title = '['.$item->code.'] '.$item->title;
}

trainingpath_view_setup_page($course, $via, $breadcrumb);


//------------------------------------------- Get & record tracks -------------------------------------------//

// Get Status
$html = '';
$res = scormlite_get_mystatus($cm, $sco, false, false);
$html .= $res[0];
$trackdata = $res[1];
if (!$item->complementary) trainingpath_report_record_scormlite_track($trackdata, $item, $learningpath, false);


//------------------------------------------- Display activity -------------------------------------------//

// Title
$status = trainingpath_report_get_my_indicator_html($item->id, $item->type, 'right-align', $learningpath);
echo trainingpath_get_title_with_status($title, $status);

// Scheduling
if (isset($scheduleInfo->display)) echo trainingpath_get_div(trainingpath_text_icon($scheduleInfo->display, 'schedule'), 'schedule');

// Description
if (!empty($item->description)) echo trainingpath_get_div($item->description, 'activity-description');

// Get Status
$res = scormlite_get_availability($cm, $sco, $trackdata);
$html .= $res[0];
$scormopen = $res[1];
if (!empty($html)) echo trainingpath_get_div($html, 'activity-status');

// Commands
$res = scormlite_get_myactions($cm, $sco, $trackdata, $scormopen, $url, false);
$backUrl = (new moodle_url('/mod/trainingpath/view/activities.php', array('cmid'=>$cmid, 'via'=>$via, 'sequence_id'=>$sequence->id)))->out();
$editUrl = new moodle_url('/mod/trainingpath/edit/activity.php', array('cmid'=>$cmid, 'via'=>$via, 'sequence_id'=>$sequence->id, 'activity_id'=>$activity_id));
$commands = array();
$commands[] = (object)array('html'=>$res[0]);
$commands[] = (object)array('href'=>$backUrl, 'class'=>'secondary', 'title'=>get_string('back_to_sequence', 'trainingpath'));
if (has_capability('mod/trainingpath:addinstance', context_module::instance($cmid)) && !$learningpath->locked) 
	$commands[] = (object)array('href'=>$editUrl, 'class'=>'secondary', 'title'=>get_string('edit', 'trainingpath'));
echo trainingpath_get_commands_div($commands);

// End
echo $OUTPUT->footer();


//------------------------------------------- Scripts -------------------------------------------//
?>
<script src="items.js"></script>
