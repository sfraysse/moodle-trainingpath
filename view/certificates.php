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

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);

// Page URL
$url = new moodle_url('/mod/trainingpath/view/certificates.php', array('cmid'=>$cmid));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
trainingpath_check_view_permission_or_redirect($course, $cm);


//------------------------------------------- Logs -------------------------------------------//

trainingpath_trigger_path_event('themes_viewed', $course, $cm, $learningpath);


//------------------------------------------- Page setup -------------------------------------------//

trainingpath_view_setup_page($course, 'certificates');


//------------------------------------------- Display certificates -------------------------------------------//

// Title
$status = trainingpath_report_get_my_indicator_html($topItem->id, EATPL_ITEM_TYPE_PATH, 'right-align', $learningpath);
echo trainingpath_get_title_with_status($learningpath->name, $status);

// Items
$openUrl = (new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>'certificates')))->out().'&certificate_id=';
$editUrl = (new moodle_url('/mod/trainingpath/edit/certificate.php', array('cmid'=>$cmid)))->out().'&certificate_id=';
echo trainingpath_view_get_items(EATPL_ITEM_TYPE_CERTIFICATE, $course, $cm, $learningpath, $topItem->id, $openUrl, $editUrl);

// Edit button
if (!$learningpath->locked) {
    $url = new moodle_url('/mod/trainingpath/edit/certificates.php', array('cmid'=>$cm->id));
    $commands = trainingpath_view_get_edit_commands($cmid, get_string('edit_certificates', 'trainingpath'), $url);
    echo trainingpath_get_commands_div($commands);
}

// End
echo $OUTPUT->footer();


//------------------------------------------- Scripts -------------------------------------------//
?>
<script src="items.js"></script>
