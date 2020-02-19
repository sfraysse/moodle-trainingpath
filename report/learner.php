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
$userId = optional_param('user_id', 0, PARAM_RAW); 
$evalOnly = optional_param('eval_only', 1, PARAM_INT);
$format = optional_param('format', 'lms', PARAM_ALPHA);

// Multiple ids
$userIds = explode(',', $userId);
$userId = $userIds[0];

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);

// Page URL
$urlParams = array('cmid'=>$cmid, 'user_id'=>$userId, 'eval_only'=>$evalOnly);
$url = new moodle_url('/mod/trainingpath/report/learner.php', $urlParams);
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$groupId = trainingpath_get_scheduled_group_id($course, $cm, $userId);
$permission = trainingpath_check_tutor_permission($course, $cm, $groupId);


//------------------------------------------- Page setup -------------------------------------------//

$group = $DB->get_record('groups', array('id'=>$groupId));
$user = $DB->get_record('user', array('id'=>$userId));
$username = $user->firstname.' '.$user->lastname;

if ($format == 'lms') {
    
    // LMS
    $breadcrumb = array();
    $breadcrumb[] = array('label'=>get_string('reporting', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/report/groups.php', array('cmid'=>$cmid)))->out());
    $breadcrumb[] = array('label'=>$group->name);
    $breadcrumb[] = array('label'=>get_string('learners_progress', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/report/learners.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'eval_only'=>$evalOnly)))->out());
    $breadcrumb[] = array('label'=>$username);
    trainingpath_tutor_setup_page($course, 'reporting', $breadcrumb, null, $permission);

    // Title
    $statusData = trainingpath_report_get_user_indicator_data($userId, $topItem->id, EATPL_ITEM_TYPE_PATH, $learningpath);
    $status = trainingpath_report_get_indicator_html($statusData, 'right-align');
    echo trainingpath_get_title_with_status($username, $status);
    
} else if ($format == 'xls') {
    
    // Excel
    $workbook = trainingpath_report_excel_get_workbook();
}


//------------------------------------------- Display certificates -------------------------------------------//


$backurl = (object)array('base'=>'/mod/trainingpath/report/learner.php', 'params'=>$urlParams, 'full'=>$url->out());

if ($format == 'lms') {
    
    // Comments
    echo trainingpath_report_comments_get_form('/mod/trainingpath/report/learner.php', $urlParams, $topItem->id, EATPL_ITEM_TYPE_PATH, $userId);
    
    // Switch
    $switchUrl = (new moodle_url('/mod/trainingpath/report/learner.php', array('cmid'=>$cmid, 'user_id'=>$userId, 'eval_only'=>!$evalOnly)))->out();
    echo '<p><a href="'.$switchUrl.'">'.get_string('eval_only_certificate_switch_'.$evalOnly, 'trainingpath').'</a></p>';
    
    // Get data
    $tables = trainingpath_report_get_items_data($userId, EATPL_ITEM_TYPE_CERTIFICATE, $course, $cm, $learningpath, $topItem->id, $context_module, $evalOnly, true, true, $backurl);
    foreach($tables as $table) {
        
        // Print table
        echo trainingpath_get_table($table->rows, $table->header, true, $table->item->id);
        
        // Print comments
        echo trainingpath_report_comments_get_form($backurl->base, $backurl->params, $table->item->id, $table->item->type, $userId);
    }
    
    // Exports
    $exports = array();
    $exports[] = (object)array('title'=>get_string('xls_export', 'trainingpath'), 'format'=>'xls', 'url'=>'/mod/trainingpath/report/learner.php', 'params'=>$urlParams);
    echo trainingpath_get_export_div($exports);

    // End
    echo $OUTPUT->footer();

} else if ($format == 'xls') {
    
    foreach ($userIds as $userId) {

        // Get user
        $user = $DB->get_record('user', array('id'=>$userId));
        $username = $user->firstname.' '.$user->lastname;

        // Add worksheet
        $sheet = trainingpath_report_excel_add_worksheet($workbook,
            array(
                (object)array('content'=>get_string('group_results_', 'trainingpath', $group->name), 'size'=>11, 'italic'=>1),
                (object)array('content'=>$learningpath->name, 'size'=>16, 'bold'=>1),
                (object)array('content'=>$username, 'size'=>13, 'bold'=>1)
            ),
            array('progress', 'time', 'success'),
            array(30),
            5,
            $username
        );
    
        // Comments
        $commentRecord = trainingpath_report_comments_get_record($topItem->id, EATPL_ITEM_TYPE_PATH, $userId);
        if ($commentRecord) trainingpath_report_excel_add_comment($workbook, $sheet, get_string('comments', 'trainingpath'), $commentRecord->comment);
    
        // Get data
        $tables = trainingpath_report_get_items_data_xls($userId, EATPL_ITEM_TYPE_CERTIFICATE, $course, $cm, $learningpath, $topItem->id, $context_module, $evalOnly, true, true, $backurl);
        foreach($tables as $table) {
            
            // Add table
            trainingpath_report_excel_add_table($workbook, $sheet, $table->rows, $table->header, true, true);
            
            // Add comments
            $commentRecord = trainingpath_report_comments_get_record($table->item->id, $table->item->type, $userId);
            if ($commentRecord) trainingpath_report_excel_add_comment($workbook, $sheet, get_string('comments', 'trainingpath'), $commentRecord->comment);
        }
    }

    // End
    $workbook->close();
}

