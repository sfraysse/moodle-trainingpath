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

require_once($CFG->libdir.'/formslib.php');

class mform_sequence extends moodleform {
    
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$certificateId = $this->_customdata['certificate_id'];
		$batchId = $this->_customdata['batch_id'];
		$learningpathId = $this->_customdata['learningpath_id'];
		$topItemId = $this->_customdata['topitem_id'];

		
		//-------------------------------------------------------------------------------
		// Context

		$mform->addElement('header', 'context', get_string('context', 'trainingpath'));

		// Certificate
		$certificates = $DB->get_records('trainingpath_item', array('type'=>EATPL_ITEM_TYPE_CERTIFICATE, 'parent_id'=>$topItemId));
		$certificatesData = array();
		foreach($certificates as $certificate) $certificatesData[$certificate->id] = '['.$certificate->code.'] '.$certificate->title;
		$certificatesSelect = $mform->addElement('select', 'grouping_id', get_string('certificate', 'trainingpath'), $certificatesData);
		if ($certificateId) $certificatesSelect->setSelected($certificateId);
		$mform->addRule('grouping_id', null, 'required', null, 'client');
		
		// Batch
		$batches = $DB->get_records('trainingpath_item', array('type'=>EATPL_ITEM_TYPE_BATCH, 'parent_id'=>$topItemId));
		$batchesData = array();
		foreach($batches as $batch) $batchesData[$batch->id] = '['.$batch->code.'] '.$batch->title;
		$batchesSelect = $mform->addElement('select', 'parent_id', get_string('batch', 'trainingpath'), $batchesData);
		if ($batchId) $batchesSelect->setSelected($batchId);
		$mform->addRule('parent_id', null, 'required', null, 'client');

		
		//-------------------------------------------------------------------------------
		// General

		$mform->addElement('header', 'general', get_string('general', 'trainingpath'));

		// Title
		$mform->addElement('text', 'title', get_string('title', 'trainingpath'), 'maxlength="255" size="100%"');
		$mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', null, 'required', null, 'client');

		// Code (kind of short name for the reports)
		$mform->addElement('text', 'code', get_string('code', 'trainingpath'), 'maxlength="255" size="16"');
		$mform->setType('code', PARAM_NOTAGS);
		$mform->addRule('code', null, 'required', null, 'client');

		// Description
        $mform->addElement('editor', 'description', get_string('description', 'trainingpath'));
        $mform->setType('description', PARAM_RAW);

        
		//-------------------------------------------------------------------------------
		// Timing

		$mform->addElement('header', 'other_settings', get_string('other_settings', 'trainingpath'));

		// Duration
		$error = get_string('sequence_duration_error', 'trainingpath');
		$mform->addElement('text', 'duration', get_string('sequence_duration', 'trainingpath'), 'maxlength="3" size="3"');
        $mform->setType('duration', PARAM_RAW);
		$mform->setDefault('duration', 0);
		$mform->addRule('duration', $error, 'required', null, 'client');
		//$mform->addRule('duration', $error, 'nonzero', null, 'client');
		$mform->addRule('duration', $error, 'regex', '/^[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$/', 'client');
        $mform->addHelpButton('duration', 'sequence_duration', 'trainingpath');
        
		
		//-------------------------------------------------------------------------------
		// Hidden

		// Item table
		
		$mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

		$mform->addElement('hidden', 'type', EATPL_ITEM_TYPE_SEQUENCE);
        $mform->setType('type', PARAM_INT);

		$mform->addElement('hidden', 'grouping_position', 1000);
		$mform->setType('grouping_position', PARAM_INT);

		$mform->addElement('hidden', 'parent_position', 1000);
		$mform->setType('parent_position', PARAM_INT);

		$mform->addElement('hidden', 'path_id', $learningpathId);
        $mform->setType('path_id', PARAM_INT);

		$mform->addElement('hidden', 'information', '');
        $mform->setType('information', PARAM_RAW);

		// Context
		
		if ($certificateId) {
			$mform->addElement('hidden', 'certificate_id', $certificateId);
			$mform->setType('certificate_id', PARAM_INT);
		}
		
		if ($batchId) {
			$mform->addElement('hidden', 'batch_id', $batchId);
			$mform->setType('batch_id', PARAM_INT);
		}
		
		$mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);
		

		
		//-------------------------------------------------------------------------------
		// Buttons

		$this->add_action_buttons(true, null);
    }
    
	
    function validation($data, $files) {
		$errors = array();
		
		// Duration
		$chunks = explode('.', $data['duration']);
		if (count($chunks) > 1 && $chunks[1] != '5') {
			$errors['duration'] = get_string('sequence_duration_error', 'trainingpath');
		}

        return $errors;
        
    }

	function data_preprocessing(&$data) {
		$data->description = array('text'=>$data->description, 'format'=>1);
		$data->duration = $data->duration/86400.0;
	}
	
	function data_postprocessing($data) {
		$data->description = $data->description['text'];
		$data->duration = $data->duration*86400;
	}

}

?>



