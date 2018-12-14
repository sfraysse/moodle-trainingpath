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
require_once($CFG->dirroot.'/mod/scormlite/sharedlib.php');
require_once($CFG->dirroot.'/mod/trainingpath/edit/uilib.php');
require_once($CFG->dirroot.'/mod/trainingpath/edit/ajaxlib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 
$activityId = optional_param('activity_id', 0, PARAM_INT); 
$sequenceId = required_param('sequence_id', PARAM_INT); 
$via = required_param('via', PARAM_RAW); 
$type = optional_param('activity_type', 0, PARAM_INT); 
$complementary = optional_param('complementary', 0, PARAM_INT);

// Recorded params
if ($activityId) {
	// eATPL Item
	$activity = $DB->get_record('trainingpath_item', array('id'=>$activityId), '*', MUST_EXIST);
	$type = $activity->activity_type;
	$complementary = $activity->complementary;
	// SCO
	if ($type == EATPL_ACTIVITY_TYPE_CONTENT || $type == EATPL_ACTIVITY_TYPE_EVAL) {
		$sco = $DB->get_record('scormlite_scoes', array('id'=>$activity->ref_id), '*', MUST_EXIST);
	} else if ($type == EATPL_ACTIVITY_TYPE_FILES) {
		$files = $DB->get_record('trainingpath_files', array('id'=>$activity->ref_id), '*', MUST_EXIST);
	}
}

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$sequence = $DB->get_record('trainingpath_item', array('id'=>$sequenceId));
if ($via == 'certificates') {
	$certificate = $DB->get_record('trainingpath_item', array('id'=>$sequence->grouping_id));
} else if ($via == 'batches') {
	$batch = $DB->get_record('trainingpath_item', array('id'=>$sequence->parent_id));
}
	
// Page URL
$url = new moodle_url('/mod/trainingpath/edit/activity.php', array('cmid'=>$cmid, 'sequence_id'=>$sequenceId, 'activity_id'=>$activityId, 'via'=>$via, 'activity_type'=>$type, 'complementary'=>$complementary));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
if (trainingpath_check_edit_permission($course, $cm) == 'editschedule') redirect(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)));

// Locked edition
if ($learningpath->locked) redirect(new moodle_url('/mod/trainingpath/view/activities.php', array('cmid'=>$cmid, 'via'=>$via, 'sequence_id'=>$sequence->id)));


//------------------------------------------- Form -------------------------------------------//

require_once('activity_'.trainingpath_activity_type_name($type).'_form.php');
$class = 'mform_activity_'.trainingpath_activity_type_name($type);
$mform = new $class(null, array('cmid'=>$cmid, 'learningpath_id'=>$learningpath->id, 'sequence_id'=>$sequenceId, 'via'=>$via, 'activity_type'=>$type, 'complementary'=>$complementary));
if ($mform->is_cancelled()) {
	
	// ----------- Form cancelled ---------
	
	$redirect_url = (new moodle_url('/mod/trainingpath/edit/activities.php', array('cmid'=>$cmid, 'sequence_id'=>$sequenceId, 'via'=>$via, 'complementary'=>$complementary)));
	redirect($redirect_url);
	
} else if ($data = $mform->get_data()) {
	
	// ----------- Form saved ---------

	// Prepare data 
	$mform->data_postprocessing($data, $context_module);
	
	// Save data - Should use transactions !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

	// e-ATPL common
	if ($data->id) {
		$DB->update_record("trainingpath_item", $data);
	} else {
		$data->id = $DB->insert_record("trainingpath_item", $data);
	}

	// SCO
	if ($type == EATPL_ACTIVITY_TYPE_CONTENT || $type == EATPL_ACTIVITY_TYPE_EVAL) {
		$sco_data = clone $data;
		$sco_data->id = $sco_data->scoid;
		$sco_data->colors = $learningpath->score_colors;
		$sco_data->maxtime = isset($sco_data->duration) ? ($sco_data->duration / 60) : 0;
		$data->ref_id = scormlite_save_sco($sco_data, $mform, $cmid, 'packagefile', true);
		$data->remedial = isset($data->remedial) ? $data->remedial : 0;
		if ($type == EATPL_ACTIVITY_TYPE_EVAL) $data->duration = 0;  // Don't record duration for evals
		$DB->update_record("trainingpath_item", $data);
	}
	
	// Files
	if ($type == EATPL_ACTIVITY_TYPE_FILES) {
		$files_data = clone $data;
		$files_data->id = $files_data->files_id;
		$data->ref_id = trainingpath_files_save($files_data, $mform, $cmid, 'packagefile');
		$DB->update_record("trainingpath_item", $data);
	}

	// Richtext
	if ($type == EATPL_ACTIVITY_TYPE_RICHTEXT) {
		$data->information = file_save_draft_area_files($data->information['itemid'], $context_module->id, 'mod_trainingpath', 'richtext', $data->id, array('subdirs'=>true), $data->information['text']);
		$DB->update_record("trainingpath_item", $data);
	}

	// Update rolldown information
	trainingpath_db_update_rolldown($sequenceId, EATPL_ITEM_TYPE_SEQUENCE);
	
	// Redirect
	ob_end_clean();   // REQUIRED! @PEAR called by get_draft_files generated an empty outpu string considered as an error by Moodle in debuging mode
	$redirect_url = (new moodle_url('/mod/trainingpath/edit/activities.php', array('cmid'=>$cmid, 'sequence_id'=>$sequenceId, 'via'=>$via, 'complementary'=>$complementary)));
	redirect($redirect_url);

} else {
	
	// ----------- Form display ---------
	
	// Breadcrumb & page setup
	$breadcrumb = array();
	if ($via == 'certificates') {
		if ($certificate)  {  // May not be the case after certificate deletion
			$breadcrumb[] = array('label'=>get_string('certificates', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/certificates.php', array('cmid'=>$cmid)))->out());
			$breadcrumb[] = array('label'=>$certificate->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/certificate.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate->id)))->out());
			$breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate->id)))->out());
			$breadcrumb[] = array('label'=>$sequence->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/sequence.php', array('cmid'=>$cmid, 'certificate_id'=>$certificate->id, 'sequence_id'=>$sequence->id)))->out());
			$breadcrumb[] = array('label'=>get_string('activities', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/activities.php', array('cmid'=>$cmid, 'sequence_id'=>$sequenceId, 'via'=>$via)))->out());
		}
	} else if ($via == 'batches') {
		$breadcrumb[] = array('label'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/batches.php', array('cmid'=>$cmid)))->out());
		$breadcrumb[] = array('label'=>$batch->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/batch.php', array('cmid'=>$cmid, 'batch_id'=>$batch->id)))->out());
		$breadcrumb[] = array('label'=>get_string('sequences', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/sequences.php', array('cmid'=>$cmid, 'batch_id'=>$batch->id)))->out());
		$breadcrumb[] = array('label'=>$sequence->code, 'url'=>(new moodle_url('/mod/trainingpath/edit/sequence.php', array('cmid'=>$cmid, 'batch_id'=>$batch->id, 'sequence_id'=>$sequence->id)))->out());
		$breadcrumb[] = array('label'=>get_string('activities', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/activities.php', array('cmid'=>$cmid, 'sequence_id'=>$sequenceId, 'via'=>$via)))->out());
	}
	if ($activityId) {
		if ($complementary) {
			$breadcrumb[] = array('label'=>get_string(trainingpath_activity_type_name($type), 'trainingpath'));
			$title = get_string('editing_'.trainingpath_activity_type_name($type), 'trainingpath').' ('.get_string('complementary_l', 'trainingpath').')';
		} else {
			$breadcrumb[] = array('label'=>$activity->code);
			$title = get_string('editing_'.trainingpath_activity_type_name($type), 'trainingpath').' ('.get_string('formal_l', 'trainingpath').')';
		}
		trainingpath_edit_setup_page($course, $via, $breadcrumb, $title);
		
		// Prepare data

		// SCO
		if ($type == EATPL_ACTIVITY_TYPE_CONTENT || $type == EATPL_ACTIVITY_TYPE_EVAL) {
			$activity->scoid = $sco->id;
			$activity->reference = $sco->reference;
			$activity->sha1hash = $sco->sha1hash;
			$activity->revision = $sco->revision;
			$activity->timeopen = $sco->timeopen;
			$activity->timeclose = $sco->timeclose;
			$activity->manualopen = $sco->manualopen;
			$activity->duration = $sco->maxtime * 60;
			$activity->passingscore = $sco->passingscore;
			$activity->displaychrono = $sco->displaychrono;
			$activity->popup = $sco->popup;
			$activity->maxattempt = $sco->maxattempt;
			$activity->whatgrade = $sco->whatgrade;
		}
		
		// Files
		if ($type == EATPL_ACTIVITY_TYPE_FILES) {
			$activity->files_id = $files->id;
			$activity->popup = $files->popup;
			$activity->reference = $files->reference;
			$activity->launch_file = $files->launch_file;
			$activity->sha1hash = $files->sha1hash;
			$activity->revision = $files->revision;
		}
		
		// Form
		$mform->data_preprocessing($activity, $context_module);
		
		// Pass data
		$mform->set_data($activity);
	} else {

		// Breadcrumb & page setup
		if (!$complementary) {
			$breadcrumb[] = array('label'=>get_string('new_'.trainingpath_activity_type_name($type), 'trainingpath'));
			trainingpath_edit_setup_page($course, $via, $breadcrumb, get_string('new_'.trainingpath_activity_type_name($type), 'trainingpath').' ('.get_string('formal_l', 'trainingpath').')');
		} else {
			$breadcrumb[] = array('label'=>get_string('new_'.trainingpath_activity_type_name($type), 'trainingpath'));
			trainingpath_edit_setup_page($course, $via, $breadcrumb, get_string('new_'.trainingpath_activity_type_name($type), 'trainingpath').' ('.get_string('complementary_l', 'trainingpath').')');
		}
	}
	$mform->display();

	// End
	echo $OUTPUT->footer();
}

?>


