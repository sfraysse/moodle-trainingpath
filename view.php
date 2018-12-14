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

require_once('../../config.php');
require_once($CFG->dirroot.'/mod/trainingpath/view/uilib.php');
require_once($CFG->dirroot.'/mod/trainingpath/report/lib.php');

// Params
$cmid = required_param('id', PARAM_INT); 

// Objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record("course", array("id"=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record("trainingpath", array("id"=>$cm->instance), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);

// Page URL
$url = new moodle_url('/mod/trainingpath/view.php', array('id'=>$cmid));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$access = trainingpath_check_view_permission($course, $cm);
if (isset($access->schedule)) $scheduleInfo = trainingpath_get_schedule_access_info(EATPL_ITEM_TYPE_PATH, $access->schedule, true);

// Page setup
($access->permission != 'view' || $scheduleInfo->status == 'open') ? $tab = 'home' : $tab = null;
trainingpath_view_setup_page($course, $tab);


//------------------------------------------- Home content -------------------------------------------//

// Title
$status = trainingpath_report_get_my_indicator_html($topItem->id, EATPL_ITEM_TYPE_PATH, 'right-align', $learningpath);
echo trainingpath_get_title_with_status($learningpath->name, $status);

// Scheduling
if (isset($access->schedule)) echo trainingpath_get_div($scheduleInfo->display, 'schedule');

// Home content
$learningpath->intro = file_rewrite_pluginfile_urls($learningpath->intro, 'pluginfile.php', $context_module->id, 'mod_trainingpath', 'intro', null);
echo format_text($learningpath->intro, $learningpath->introformat, array('noclean'=>true, 'context'=>$context_module));

// Items
$openBatchUrl = (new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>'batches')))->out().'&batch_id=';
$openSequenceUrl = (new moodle_url('/mod/trainingpath/view/activities.php', array('cmid'=>$cmid, 'via'=>'batches')))->out().'&sequence_id=';
echo trainingpath_view_get_current_item($course, $cm, $learningpath, $topItem->id, $openBatchUrl, $openSequenceUrl);

// Edit button
$url = new moodle_url('/course/modedit.php', array('update'=>$cm->id));
$commands = trainingpath_view_get_edit_commands($cmid, get_string('edit', 'trainingpath'), $url);
echo trainingpath_get_commands_div($commands);

// End
echo $OUTPUT->footer();

?>
