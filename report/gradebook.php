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
require_once($CFG->dirroot.'/mod/trainingpath/report/uilib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);
$evalOnly = optional_param('eval_only', 1, PARAM_INT);

// Page URL
$urlParams = array('cmid'=>$cmid, 'eval_only'=>$evalOnly);
$url = new moodle_url('/mod/trainingpath/report/gradebook.php', $urlParams);
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
trainingpath_check_view_permission_or_redirect($course, $cm);



//------------------------------------------- Logs -------------------------------------------//

trainingpath_trigger_path_event('page_viewed', $course, $cm, $learningpath, ['page' => 'gradebook']);


//------------------------------------------- Page setup -------------------------------------------//

trainingpath_view_setup_page($course, 'gradebook');

// Title
$status = trainingpath_report_get_my_indicator_html($topItem->id, EATPL_ITEM_TYPE_PATH, 'right-align', $learningpath);
echo trainingpath_get_title_with_status($learningpath->name, $status);


//------------------------------------------- Display certificates -------------------------------------------//


// Comments
echo trainingpath_report_comments_get_div($topItem->id, EATPL_ITEM_TYPE_PATH, $USER->id);

// Switch
$switchUrl = (new moodle_url('/mod/trainingpath/report/gradebook.php', array('cmid'=>$cmid, 'eval_only'=>!$evalOnly)))->out();
echo '<p><a href="'.$switchUrl.'">'.get_string('eval_only_certificate_switch_'.$evalOnly, 'trainingpath').'</a></p>';

// Items
$backurl = (object)array('base'=>'/mod/trainingpath/report/gradebook.php', 'params'=>$urlParams, 'full'=>$url->out());
$tables = trainingpath_report_get_my_items_data(EATPL_ITEM_TYPE_CERTIFICATE, $course, $cm, $learningpath, $topItem->id, $context_module, $evalOnly, true, true, $backurl);
foreach($tables as $table) {
    
    // Table
    echo trainingpath_get_table($table->rows, $table->header, true, $table->item->id);
    
    // Comments
    echo trainingpath_report_comments_get_div($table->item->id, $table->item->type, $USER->id);
}

// End
echo $OUTPUT->footer();


