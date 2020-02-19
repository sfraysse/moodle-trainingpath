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
$group_id = required_param('group_id', PARAM_INT); 
$evalOnly = optional_param('eval_only', 1, PARAM_INT);
$format = optional_param('format', 'lms', PARAM_ALPHA);

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);
$group = $DB->get_record('groups', array('id'=>$group_id), '*', MUST_EXIST);

// Page URL
$urlParams = array('cmid'=>$cmid, 'group_id'=>$group_id, 'eval_only'=>$evalOnly);
$url = new moodle_url('/mod/trainingpath/report/learners.php', $urlParams);
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$permission = trainingpath_check_tutor_permission($course, $cm, $group_id);


//------------------------------------------- Page setup -------------------------------------------//

if ($format == 'lms') {
    
    // LMS
    $breadcrumb = array();
    $breadcrumb[] = array('label'=>get_string('reporting', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/report/groups.php', array('cmid'=>$cmid)))->out());
    $breadcrumb[] = array('label'=>$group->name);
    $breadcrumb[] = array('label'=>get_string('learners_progress', 'trainingpath'));
    trainingpath_tutor_setup_page($course, 'reporting', $breadcrumb, $group->name, $permission);
    
} else if ($format == 'xls') {
    
    // Excel
    $workbook = trainingpath_report_excel_get_workbook();
}


//------------------------------------------- Display learners -------------------------------------------//


if ($format == 'lms') {
    
    // Get data
    $data = trainingpath_report_get_learners_progress_data($cmid, $learningpath, $group_id, $topItem->id, EATPL_ITEM_TYPE_PATH, $context_module, false, $url, true);
    if (!$data) {
        echo '<p>'.get_string('no_data', 'trainingpath').'</p>';
        echo $OUTPUT->footer();
        die;
    }
    
    // Print table
    echo trainingpath_get_table($data->rows, $data->header);
    
    // Print comment
    echo trainingpath_report_comments_get_form('/mod/trainingpath/report/learners.php', $urlParams, $topItem->id, EATPL_ITEM_TYPE_PATH, null, $group_id);
    
    // Export buttons: simple reports.
    echo trainingpath_get_export_div(
        trainingpath_report_learners_exports($cmid, $group_id, $topItem->id, $context_module, $urlParams, true),
        get_string('xls_export_simple', 'trainingpath')
    );

    // Export buttons: full reports.
    echo trainingpath_get_export_div(
        trainingpath_report_learners_exports($cmid, $group_id, $topItem->id, $context_module, $urlParams, false),
        get_string('xls_export_full', 'trainingpath')
    );

    // End
    echo $OUTPUT->footer();
   
} else if ($format == 'xls') {
    
    // Get data
    $data = trainingpath_report_get_learners_progress_data($cmid, $learningpath, $group_id, $topItem->id, EATPL_ITEM_TYPE_PATH, $context_module, false, $url, true);
    
    // Determine columns.
    $subColumnsNumber = $evalOnly ? 2 : 4;
    $columnsNumber = ((count($data->header->cells) - 1) * $subColumnsNumber) + 1;
    $indicators = $evalOnly ? ['success'] : ['progress', 'time', 'success'];

    // Add worksheet
    $sheet = trainingpath_report_excel_add_worksheet($workbook,
        array(
            (object)array('content'=>get_string('group_results_', 'trainingpath', $group->name), 'size'=>11, 'italic'=>1),
            (object)array('content'=>$learningpath->name, 'size'=>16, 'bold'=>1)
        ),
        $indicators,
        [30],
        $columnsNumber
    );

    // Add table
    trainingpath_report_excel_add_table($workbook, $sheet, $data->rows, $data->header, true);
    
    // Add comment
    $commentRecord = trainingpath_report_comments_get_record($topItem->id, EATPL_ITEM_TYPE_PATH, null, $group_id);
    if ($commentRecord) trainingpath_report_excel_add_comment($workbook, $sheet, get_string('comments', 'trainingpath'), $commentRecord->comment);
    
    // End
    $workbook->close();
}

function trainingpath_report_learners_exports($cmid, $group_id, $topItemId, $context_module, $urlParams, $evalOnly = true) {
    global $DB;

    // Exports
    $exports = array();
    
    //      Simple export
    $urlParams['eval_only'] = $evalOnly;
    $exports[] = (object)array('title'=>get_string('xls_export_global', 'trainingpath'), 'format'=>'xls', 'url'=>'/mod/trainingpath/report/learners.php', 'params' => $urlParams);
    
    //      Export certificates
    $certificates = $DB->get_records('trainingpath_item', array('parent_id'=>$topItemId, 'type'=>EATPL_ITEM_TYPE_CERTIFICATE), 'parent_position');
    $certificateIds = array_keys($certificates);
    $certificateIds = implode(',', $certificateIds);
    $certificateParams = array('cmid' => $cmid, 'group_id' => $group_id, 'eval_only' => $evalOnly, 'certificate_id' => $certificateIds);
    $exports[] = (object)array('title'=>get_string('xls_export_certificates', 'trainingpath'), 'format'=>'xls', 'url'=>'/mod/trainingpath/report/certificate.php', 'params' => $certificateParams);
    
    //      Export leaners
    $userIds = array();
    $users = groups_get_members($group_id, 'u.*', 'lastname ASC, firstname ASC');
    foreach($users as $user) {
        if (has_capability('mod/trainingpath:addinstance', $context_module, $user)) continue;
        if (has_capability('mod/trainingpath:editschedule', $context_module, $user)) continue;
        $userIds[] = $user->id;
    }
    $userIds = implode(',', $userIds);
    $learnerParams = array('cmid'=>$cmid, 'eval_only'=>$evalOnly, 'user_id'=>$userIds);
    $exports[] = (object)array('title'=>get_string('xls_export_users', 'trainingpath'), 'format'=>'xls', 'url'=>'/mod/trainingpath/report/learner.php', 'params' => $learnerParams);

    return $exports;
}
    





