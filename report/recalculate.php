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
$confirm = optional_param('confirm', 1, PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);

// Page URL
$url = new moodle_url('/mod/trainingpath/report/recalculate.php', array('cmid'=>$cmid, 'confirm'=>$confirm));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$permission = trainingpath_check_edit_permission($course, $cm);

// Page setup
trainingpath_edit_setup_page($course);


//------------------------------------------- Display groups -------------------------------------------//

if ($confirm) {
    $target = new moodle_url('/mod/trainingpath/report/recalculate.php', array('confirm'=>0, 'cmid'=>$cmid));
    echo '
        <br>
        <p>'.get_string('tracks_recalculate_desc', 'trainingpath').'</p>
        <a class="btn btn-primary" href="'.$target.'" role="button">'
            .get_string('tracks_recalculate', 'trainingpath').
        '</a>';
} else {
    
    // Get batches
    $batches = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$topItem->id, 'type'=>EATPL_ITEM_TYPE_BATCH)));
    $batches = array_map(function($batch) { return $batch->id; }, $batches);
    
    // Get certificates
    $certificates = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$topItem->id, 'type'=>EATPL_ITEM_TYPE_CERTIFICATE)));
    $certificates = array_map(function($certificate) { return $certificate->id; }, $certificates);
    
    // Get sequences
    $sequences = [];
    foreach($batches as $batch) {
        $batchSequences = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$batch, 'type'=>EATPL_ITEM_TYPE_SEQUENCE)));
        $batchSequences = array_map(function($sequence) { return $sequence->id; }, $batchSequences);
        $sequences = array_merge($sequences, $batchSequences);
    }
    
    // Get users
    $users = [];
    $roles = ['manager', 'coursecreator', 'editingteacher', 'teacher', 'student'];
    foreach($roles as $role) {
        $role = $DB->get_record('role', array('shortname' => $role));
        $members = get_role_users($role->id, $context_course);
        $memberIds = array_map(function($member) { return $member->id; }, $members);
        $users = array_merge($users, $memberIds);
    }
    $users = array_unique($users);

    // Update tracks
    foreach($users as $user) {

        // Update sequences
        foreach($sequences as $sequence) {
            trainingpath_report_rollup_track($learningpath, $sequence, EATPL_ITEM_TYPE_SEQUENCE, $user, false);
        }
        
        // Update batches
        foreach($batches as $batch) {
            trainingpath_report_rollup_track($learningpath, $batch, EATPL_ITEM_TYPE_BATCH, $user, false);
        }
        
        // Update certificates
        foreach($certificates as $certificate) {
            trainingpath_report_rollup_track($learningpath, $certificate, EATPL_ITEM_TYPE_CERTIFICATE, $user, false);
        }
        
        // Update path
		trainingpath_report_rollup_track($learningpath, $topItem->id, EATPL_ITEM_TYPE_PATH, $user);		
    }

    echo '<br>'.get_string('tracks_recalculate_confirm', 'trainingpath');
	echo '
        <p><a href="'.new moodle_url('/mod/trainingpath/view.php', array('id'=>$cmid)).'">'
            .get_string('tracks_recalculate_back', 'trainingpath').
        '...</a></p>';
}


// End
echo $OUTPUT->footer();


//------------------------------------------- Scripts -------------------------------------------//
?>
