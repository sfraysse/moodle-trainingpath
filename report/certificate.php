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
$certificate_id = required_param('certificate_id', PARAM_RAW);
$evalOnly = optional_param('eval_only', 1, PARAM_INT);
$format = optional_param('format', 'lms', PARAM_ALPHA);

// Multiple ids
$certificate_ids = explode(',', $certificate_id);
$certificate_id = $certificate_ids[0];

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$group = $DB->get_record('groups', array('id'=>$group_id), '*', MUST_EXIST);
$certificate = $DB->get_record('trainingpath_item', array('id'=>$certificate_id), '*', MUST_EXIST);

// Page URL
$urlParams = array('cmid'=>$cmid, 'group_id'=>$group_id, 'certificate_id'=>$certificate_id, 'eval_only'=>$evalOnly);
$url = new moodle_url('/mod/trainingpath/report/certificate.php', $urlParams);
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
    $breadcrumb[] = array('label'=>get_string('learners_progress', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/report/learners.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'eval_only'=>$evalOnly)))->out());
    $breadcrumb[] = array('label'=>$certificate->code);
    trainingpath_tutor_setup_page($course, 'reporting', $breadcrumb, $group->name, $permission);
    
} else if ($format == 'xls') {
    
    // Excel
    $workbook = trainingpath_report_excel_get_workbook();
}


//------------------------------------------- Display learners -------------------------------------------//


if ($format == 'lms') {
    
    // Switch
    $switchUrl = (new moodle_url('/mod/trainingpath/report/certificate.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'certificate_id'=>$certificate_id, 'eval_only'=>!$evalOnly)))->out();
    echo '<p><a href="'.$switchUrl.'">'.get_string('eval_only_certificate_switch_'.$evalOnly, 'trainingpath').'</a></p>';

    // Get data
    $data = trainingpath_report_get_learners_progress_data($cmid, $learningpath, $group_id, $certificate_id, EATPL_ITEM_TYPE_CERTIFICATE, $context_module, $evalOnly, $url);
    if (!$data) {
        echo '<p>'.get_string('no_data', 'trainingpath').'</p>';
        echo $OUTPUT->footer();
        die;
    }

    // Print table
    echo trainingpath_get_table($data->rows, $data->header);

    // Print comment
    echo trainingpath_report_comments_get_form('/mod/trainingpath/report/certificate.php', $urlParams, $certificate_id, EATPL_ITEM_TYPE_CERTIFICATE, null, $group_id);

    // Exports
    $exports = array();
    
    //      Simple exports
    $exports[] = (object)array('title'=>get_string('xls_export', 'trainingpath'), 'format'=>'xls', 'url'=>'/mod/trainingpath/report/certificate.php', 'params'=>$urlParams);
    
    //      Export sequences
    if ($evalOnly) {
		$sequences = $DB->get_records('trainingpath_item', array('grouping_id'=>$certificate_id, 'type'=>EATPL_ITEM_TYPE_SEQUENCE, 'evaluation'=>1, 'complementary'=>0), 'parent_position');
	} else {
		$sequences = $DB->get_records('trainingpath_item', array('grouping_id'=>$certificate_id, 'type'=>EATPL_ITEM_TYPE_SEQUENCE, 'complementary'=>0), 'parent_position');
	}

    $sequenceIds = array_keys($sequences);
    $sequenceIds = implode(',', $sequenceIds);
    $sequenceParams = array('cmid'=>$cmid, 'group_id'=>$group_id, 'eval_only'=>$evalOnly, 'sequence_id'=>$sequenceIds);
    $exports[] = (object)array('title'=>get_string('xls_export_sequences', 'trainingpath'), 'format'=>'xls', 'url'=>'/mod/trainingpath/report/sequence.php', 'params'=>$sequenceParams);
    
    echo trainingpath_get_export_div($exports);

    // End
    echo $OUTPUT->footer();

} else if ($format == 'xls') {
    
    foreach ($certificate_ids as $certificate_id) {
        
        // Get certificate
        $certificate = $DB->get_record('trainingpath_item', array('id'=>$certificate_id), '*', MUST_EXIST);

        // Get data
        $data = trainingpath_report_get_learners_progress_data($cmid, $learningpath, $group_id, $certificate_id, EATPL_ITEM_TYPE_CERTIFICATE, $context_module, $evalOnly, $url);
    
        // Determine the number of columns.
        $columnsNumber = ((count($data->header->cells) - 1) * 3) + 1;

        // Add worksheet
        $sheet = trainingpath_report_excel_add_worksheet($workbook,
            array(
                (object)array('content'=>get_string('group_results_', 'trainingpath', $group->name), 'size'=>11, 'italic'=>1),
                (object)array('content'=>$learningpath->name, 'size'=>16, 'bold'=>1),
                (object)array('content'=>'['.$certificate->code.'] '.$certificate->title, 'size'=>13, 'bold'=>1)
            ),
            array('progress', 'time', 'success'),
            array(30),
            $columnsNumber,
            $certificate->code
        );
    
        // Add table
        trainingpath_report_excel_add_table($workbook, $sheet, $data->rows, $data->header);
        
        // Add comment
        $commentRecord = trainingpath_report_comments_get_record($certificate_id, EATPL_ITEM_TYPE_CERTIFICATE, null, $group_id);
        if ($commentRecord) trainingpath_report_excel_add_comment($workbook, $sheet, get_string('comments', 'trainingpath'), $commentRecord->comment);
    }
    
    // End
    $workbook->close();
}

