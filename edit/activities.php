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
require_once($CFG->dirroot.'/mod/trainingpath/edit/uilib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 
$sequenceId = required_param('sequence_id', PARAM_INT); 
$via = required_param('via', PARAM_RAW); // batches or certificates

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);

// Page URL
$url = new moodle_url('/mod/trainingpath/edit/activities.php', array('cmid'=>$cmid, 'sequence_id'=>$sequenceId, 'via'=>$via));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
if (trainingpath_check_edit_permission($course, $cm) == 'editschedule') redirect(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)));

// Locked edition
if ($learningpath->locked) redirect(new moodle_url('/mod/trainingpath/view/activities.php', array('cmid'=>$cmid, 'via'=>$via, 'sequence_id'=>$sequenceId)));

// Page setup
$breadcrumb = array();
$sequence = $DB->get_record('trainingpath_item', array('id'=>$sequenceId));
if ($via == 'certificates') {
    $certificate = $DB->get_record('trainingpath_item', array('id'=>$sequence->grouping_id));
    if ($certificate)  {  // May not be the case after certificate deletion
        $breadcrumb[] = array('label'=>get_string('certificates', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/certificates.php', array('cmid'=>$cmid)))->out());
        $breadcrumb[] = array('label'=>$certificate->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/certificate.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate->id)))->out());
        $breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate->id)))->out());
        $breadcrumb[] = array('label'=>$sequence->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/sequence.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate->id, 'sequence_id'=>$sequence->id)))->out());
        $breadcrumb[] = array('label'=>get_string('activities', 'trainingpath'));
    }
} else if ($via == 'batches') {
    $batch = $DB->get_record('trainingpath_item', array('id'=>$sequence->parent_id));
    $breadcrumb[] = array('label'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/batches.php', array('cmid'=>$cmid)))->out());
    $breadcrumb[] = array('label'=>$batch->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/batch.php', array('cmid'=>$cmid, 'batch_id'=>$batch->id)))->out());
    $breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'batch_id'=>$batch->id)))->out());
    $breadcrumb[] = array('label'=>$sequence->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/sequence.php', array('cmid'=>$cmid, 'batch_id'=>$batch->id, 'sequence_id'=>$sequence->id)))->out());
    $breadcrumb[] = array('label'=>get_string('activities', 'trainingpath'));
}
trainingpath_edit_setup_page($course, $via, $breadcrumb);


//------------------------------------------- Display activities -------------------------------------------//


// Items
echo trainingpath_edit_get_items('activity', $cmid, $sequence->id, null, $via);

// Buttons
$activity_url = (new moodle_url('/mod/trainingpath/edit/activity.php', array('cmid'=>$cmid, 'sequence_id'=>$sequence->id, 'via'=>$via)))->out();
$preview_url = (new moodle_url('/mod/trainingpath/view/activities.php', array('cmid'=>$cmid, 'sequence_id'=>$sequence->id, 'via'=>$via)))->out();
echo '
<div id="trainingpath-commands">
    <div class="dropup" style="float:left">
        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            '.get_string('add_formal_activity', 'trainingpath').'
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="'.$activity_url.'&activity_type='.EATPL_ACTIVITY_TYPE_CONTENT.'&complementary=0">'.get_string('content', 'trainingpath').'</a>
            <a class="dropdown-item" href="'.$activity_url.'&activity_type='.EATPL_ACTIVITY_TYPE_EVAL.'&complementary=0">'.get_string('eval', 'trainingpath').'</a>
            <a class="dropdown-item" href="'.$activity_url.'&activity_type='.EATPL_ACTIVITY_TYPE_VIRTUAL.'&complementary=0">'.get_string('virtual', 'trainingpath').'</a>
            <a class="dropdown-item" href="'.$activity_url.'&activity_type='.EATPL_ACTIVITY_TYPE_CLASSROOM.'&complementary=0">'.get_string('classroom', 'trainingpath').'</a>
        </div>
    </div>
    <div class="dropup" style="float:left;margin-left:7px;">
        <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            '.get_string('add_complementary_activity', 'trainingpath').'
        </button>
        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="'.$activity_url.'&activity_type='.EATPL_ACTIVITY_TYPE_CONTENT.'&complementary=1">'.get_string('content', 'trainingpath').'</a>
            <a class="dropdown-item" href="'.$activity_url.'&activity_type='.EATPL_ACTIVITY_TYPE_EVAL.'&complementary=1">'.get_string('eval', 'trainingpath').'</a>
            <a class="dropdown-item" href="'.$activity_url.'&activity_type='.EATPL_ACTIVITY_TYPE_VIRTUAL.'&complementary=1">'.get_string('virtual', 'trainingpath').'</a>
            <a class="dropdown-item" href="'.$activity_url.'&activity_type='.EATPL_ACTIVITY_TYPE_CLASSROOM.'&complementary=1">'.get_string('classroom', 'trainingpath').'</a>
            <a class="dropdown-item" href="'.$activity_url.'&activity_type='.EATPL_ACTIVITY_TYPE_FILES.'&complementary=1">'.get_string('files', 'trainingpath').'</a>
            <a class="dropdown-item" href="'.$activity_url.'&activity_type='.EATPL_ACTIVITY_TYPE_RICHTEXT.'&complementary=1">'.get_string('richtext', 'trainingpath').'</a>
        </div>
    </div>
    <div style="float:left;margin-left:7px;">
        <a class="btn btn-secondary" href="'.$preview_url.'" role="button">'.get_string('preview_activities', 'trainingpath').'</a>		
    </div>
</div>
';

// End
echo $OUTPUT->footer();


//------------------------------------------- Scripts -------------------------------------------//
?>
<script src="items.js"></script>
