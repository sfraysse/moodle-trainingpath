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
$certificateId = optional_param('certificate_id', 0, PARAM_INT); 
$batchId = optional_param('batch_id', 0, PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$certificateId ? $via = 'certificates' : $via = 'batches';

// Page URL
$params = array('cmid'=>$cmid);
if ($certificateId) $params['certificate_id'] = $certificateId;
else if ($batchId) $params['batch_id'] = $batchId;
$url = new moodle_url('/mod/trainingpath/edit/sequences.php', $params);
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
if (trainingpath_check_edit_permission($course, $cm) == 'editschedule') redirect(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)));

// Locked edition
if ($learningpath->locked) {
    if ($certificateId) redirect(new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>$via, 'certificate_id'=>$certificateId)));
    else redirect(new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>$via, 'batch_id'=>$batchId)));
}

// Page setup
$breadcrumb = array();
if ($certificateId) {
    $certificate = $DB->get_record('trainingpath_item', array('id'=>$certificateId));
    $breadcrumb[] = array('label'=>get_string('certificates', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/certificates.php', array('cmid'=>$cmid)))->out());
    $breadcrumb[] = array('label'=>$certificate->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/certificate.php', array('cmid'=>$cmid, 'certificate_id'=>$certificateId)))->out());
    $breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'));
    trainingpath_edit_setup_page($course, 'certificates', $breadcrumb);
} else if ($batchId) {
    $batch = $DB->get_record('trainingpath_item', array('id'=>$batchId));
    $breadcrumb[] = array('label'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/batches.php', array('cmid'=>$cmid)))->out());
    $breadcrumb[] = array('label'=>$batch->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/batch.php', array('cmid'=>$cmid, 'batch_id'=>$batchId)))->out());
    $breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'));
    trainingpath_edit_setup_page($course, 'batches', $breadcrumb);
}


//------------------------------------------- Display certificates -------------------------------------------//

// Items
if ($certificateId) echo trainingpath_edit_get_items('sequence', $cmid, $certificateId, true);
else if ($batchId) echo trainingpath_edit_get_items('sequence', $cmid, $batchId);

// Buttons
if ($certificateId) {
    $sequence_url = (new moodle_url('/mod/trainingpath/edit/sequence.php', array('cmid'=>$cmid, 'certificate_id'=>$certificateId)))->out();
    $preview_url = (new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>'certificates', 'certificate_id'=>$certificateId)))->out();
} else {
    $sequence_url = (new moodle_url('/mod/trainingpath/edit/sequence.php', array('cmid'=>$cmid, 'batch_id'=>$batchId)))->out();
    $preview_url = (new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>'batches', 'batch_id'=>$batchId)))->out();
}
echo trainingpath_edit_get_commands(array(
    'add'=>array('class'=>'primary', 'label'=>get_string('add_sequence', 'trainingpath'), 'href'=>$sequence_url),
    'preview'=>array('class'=>'secondary', 'label'=>get_string('preview_sequences', 'trainingpath'), 'href'=>$preview_url)
));

// End
echo $OUTPUT->footer();


//------------------------------------------- Scripts -------------------------------------------//
?>
<script src="items.js"></script>
