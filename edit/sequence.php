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
require_once($CFG->dirroot.'/mod/trainingpath/edit/ajaxlib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 
$sequenceId = optional_param('sequence_id', 0, PARAM_INT); 
$certificateId = optional_param('certificate_id', 0, PARAM_INT); 
$batchId = optional_param('batch_id', 0, PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);
$certificateId ? $via = 'certificates' : $via = 'batches';

// Page URL
$params = array('cmid'=>$cmid);
if ($sequenceId) $params['sequence_id'] = $sequenceId;
if ($certificateId) $params['certificate_id'] = $certificateId;
if ($batchId) $params['batch_id'] = $batchId;
$url = new moodle_url('/mod/trainingpath/edit/sequence.php', $params);
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
if (trainingpath_check_edit_permission($course, $cm) == 'editschedule') redirect(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)));

// Locked edition
if ($learningpath->locked) redirect(new moodle_url('/mod/trainingpath/view/activities.php', array('cmid'=>$cmid, 'via'=>$via, 'sequence_id'=>$sequenceId)));


//------------------------------------------- Form -------------------------------------------//

// Instantiate form 
require_once('sequence_form.php');
$mform = new mform_sequence(null, array('cmid'=>$cmid, 'certificate_id'=>$certificateId, 'batch_id'=>$batchId, 'learningpath_id'=>$learningpath->id, 'topitem_id'=>$topItem->id));
if ($mform->is_cancelled()) {
	
    // Form cancelled
	if ($certificateId) $redirect_url = (new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'certificate_id'=>$certificateId)))->out();
	else if ($batchId) $redirect_url = (new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'batch_id'=>$batchId)))->out();
	redirect($redirect_url);
	
} else if ($data = $mform->get_data()) {
	
	// Form saved
	$mform->data_postprocessing($data);
	
	if (!$data->id) {
		$pastSequence = false;
		$data->id = $DB->insert_record("trainingpath_item", $data);
	} else {
		$pastSequence = $DB->get_record('trainingpath_item', array('id'=>$data->id, 'type'=>EATPL_ITEM_TYPE_SEQUENCE), '*', MUST_EXIST);
		$DB->update_record("trainingpath_item", $data);
	}
	
	// Update rolldown information
	trainingpath_db_update_rolldown($data->grouping_id, EATPL_ITEM_TYPE_CERTIFICATE);
	if ($pastSequence && $pastSequence->grouping_id != $data->grouping_id) {
		// Sequence may have been moved from a certificate to another. So update both.
		trainingpath_db_update_rolldown($pastSequence->grouping_id, EATPL_ITEM_TYPE_CERTIFICATE);
	}
	
	// Redirect
	if ($certificateId) $redirect_url = (new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'certificate_id'=>$certificateId)))->out();
	else if ($batchId) $redirect_url = (new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'batch_id'=>$batchId)))->out();
	redirect($redirect_url);

} else {
	
	// Form display
	$breadcrumb = array();
	if ($certificateId) {
		$certificate = $DB->get_record('trainingpath_item', array('id'=>$certificateId));
		$breadcrumb[] = array('label'=>get_string('certificates', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/certificates.php', array('cmid'=>$cmid)))->out());
		$breadcrumb[] = array('label'=>$certificate->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/certificate.php', array('cmid'=>$cmid, 'certificate_id'=>$certificateId)))->out());
		$breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'certificate_id'=>$certificateId)))->out());
	} else if ($batchId) {
		$batch = $DB->get_record('trainingpath_item', array('id'=>$batchId));
		$breadcrumb[] = array('label'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/batches.php', array('cmid'=>$cmid)))->out());
		$breadcrumb[] = array('label'=>$batch->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/batch.php', array('cmid'=>$cmid, 'batch_id'=>$batchId)))->out());
		$breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'batch_id'=>$batchId)))->out());
	}
	if ($sequenceId) {
		$record = $DB->get_record('trainingpath_item', array('id'=>$sequenceId), '*', MUST_EXIST);
		$breadcrumb[] = array('label'=>$record->code);
		$title = get_string('editing_sequence', 'trainingpath');
		if ($certificateId) $via = 'certificates';
		else if ($batchId) $via = 'batches';
		$title .= '<a href="'.(new moodle_url('/mod/trainingpath/edit/activities.php', array('cmid'=>$cmid, 'sequence_id'=>$sequenceId, 'via'=>$via)))->out().'" style="margin-left:5px;">';
		
		// SF2017 - Icons
		//$title .= ' 	<img src="'.trainingpath_get_icon('children').'" title="'.get_string('edit_activities', 'trainingpath').'" width="16" height="16">';
		$title .= trainingpath_get_icon('children', get_string('edit_activities', 'trainingpath'));
		
		$title .= '</a>';
		if ($certificateId) {
			trainingpath_edit_setup_page($course, 'certificates', $breadcrumb, $title);
		} else if ($batchId) {
			trainingpath_edit_setup_page($course, 'batches', $breadcrumb, $title);
		}
		$mform->data_preprocessing($record);
		$mform->set_data($record);
	} else {
		$breadcrumb[] = array('label'=>get_string('new_sequence', 'trainingpath'));
		trainingpath_edit_setup_page($course, 'certificates', $breadcrumb, get_string('new_sequence', 'trainingpath'));
	}
	$mform->display();

	// End
	echo $OUTPUT->footer();
}

?>




