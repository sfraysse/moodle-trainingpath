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
$batchId = optional_param('batch_id', 0, PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);

// Page URL
$params = array('cmid'=>$cmid, 'batch_id'=>$batchId);
$url = new moodle_url('/mod/trainingpath/edit/batch.php', $params);
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
if (trainingpath_check_edit_permission($course, $cm) == 'editschedule') redirect(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)));

// Locked edition
if ($learningpath->locked) redirect(new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cmid, 'via'=>'batches', 'batch_id'=>$batchId)));


//------------------------------------------- Form -------------------------------------------//

// Instantiate form 
require_once('batch_form.php');
$mform = new mform_batch(null, array('cmid'=>$cmid, 'path_id'=>$learningpath->id, 'parent_id'=>$topItem->id));
if ($mform->is_cancelled()) {
	
    // Form cancelled
	$redirect_url = (new moodle_url('/mod/trainingpath/edit/batches.php', array('cmid'=>$cmid)))->out();
	redirect($redirect_url);
	
} else if ($data = $mform->get_data()) {
	
	// Form saved
	$mform->data_postprocessing($data);
	
	if (!$data->id) {
		$DB->insert_record("trainingpath_item", $data);
	} else {
		$DB->update_record("trainingpath_item", $data);
	}
	
	// Redirect	
	$redirect_url = (new moodle_url('/mod/trainingpath/edit/batches.php', array('cmid'=>$cmid)))->out();
	redirect($redirect_url);

} else {
	
	// Page setup

	// Form display
	$breadcrumb = array();
	$breadcrumb[] = array('label'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/batches.php', array('cmid'=>$cmid)))->out());
	if ($batchId) {
		$record = $DB->get_record('trainingpath_item', array('id'=>$batchId), '*', MUST_EXIST);
		$breadcrumb[] = array('label'=>$record->code);
		$title = get_string('editing_batch', 'trainingpath');
		$title .= '<a href="'.(new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'batch_id'=>$batchId)))->out().'" style="margin-left:5px;">';
		
		// SF2017 - Icons
		// $title .= ' 	<img src="'.trainingpath_get_icon('children').'" title="'.get_string('edit_sequences', 'trainingpath').'" width="16" height="16">';
		$title .= trainingpath_get_icon('children', get_string('edit_sequences', 'trainingpath'));
		
		$title .= '</a>';
		trainingpath_edit_setup_page($course, 'batches', $breadcrumb, $title);
		$mform->data_preprocessing($record);
		$mform->set_data($record);
	} else {
		$breadcrumb[] = array('label'=>get_string('new_batch', 'trainingpath'));
		trainingpath_edit_setup_page($course, 'batches', $breadcrumb, get_string('new_batch', 'trainingpath'));
	}
	$mform->display();

	// End
	echo $OUTPUT->footer();
}

?>


