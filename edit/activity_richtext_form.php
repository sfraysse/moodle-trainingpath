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

class mform_activity_richtext extends moodleform {
    
    public function definition() {
        global $CFG;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$learningpathId = $this->_customdata['learningpath_id'];
		$sequenceId = $this->_customdata['sequence_id'];
		$via = $this->_customdata['via'];
		$type = $this->_customdata['activity_type'];
		$complementary = $this->_customdata['complementary'];

		
		//-------------------------------------------------------------------------------
		// General

		$mform->addElement('header', 'general', get_string('general', 'trainingpath'));

		// Title
		$mform->addElement('text', 'title', get_string('title', 'trainingpath'), 'maxlength="255" size="100%"');
		$mform->setType('title', PARAM_TEXT);
		$mform->addRule('title', null, 'required', null, 'client');

		// Description
        $mform->addElement('editor', 'description', get_string('description', 'trainingpath'));
        $mform->setType('description', PARAM_RAW);

        
		//-------------------------------------------------------------------------------
		// Other settings

		$mform->addElement('header', 'other_settings', get_string('other_settings', 'trainingpath'));

		// Information
        $mform->addElement('editor', 'information', get_string('content', 'trainingpath'), null, array('maxfiles' => EDITOR_UNLIMITED_FILES));
        $mform->setType('information', PARAM_RAW);

		
		//-------------------------------------------------------------------------------
		// Hidden

		// trainingpath_item table elements
		
		$mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

		$mform->addElement('hidden', 'type', EATPL_ITEM_TYPE_ACTIVITY);
        $mform->setType('type', PARAM_INT);

		$mform->addElement('hidden', 'parent_id', $sequenceId);
        $mform->setType('parent_id', PARAM_INT);

		$mform->addElement('hidden', 'parent_position', 1000);
        $mform->setType('parent_position', PARAM_INT);

		$mform->addElement('hidden', 'ref_id', 0);
        $mform->setType('ref_id', PARAM_INT);

		$mform->addElement('hidden', 'path_id', $learningpathId);
        $mform->setType('path_id', PARAM_INT);

		$mform->addElement('hidden', 'code', '');
		$mform->setType('code', PARAM_RAW);

		$mform->addElement('hidden', 'duration', 0);
		$mform->setType('duration', PARAM_INT);

		$mform->addElement('hidden', 'activity_type', $type);
        $mform->setType('activity_type', PARAM_INT);

		$mform->addElement('hidden', 'complementary', $complementary);
        $mform->setType('complementary', PARAM_INT);

		$mform->addElement('hidden', 'remedial', 0);
        $mform->setType('remedial', PARAM_INT);

		$mform->addElement('hidden', 'evaluation', 0);
        $mform->setType('evaluation', PARAM_INT);

		
		// Context
		
		$mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);
		
		$mform->addElement('hidden', 'sequence_id', $sequenceId);
        $mform->setType('sequence_id', PARAM_INT);

		$mform->addElement('hidden', 'via', $via);
        $mform->setType('via', PARAM_RAW);


		//-------------------------------------------------------------------------------
		// Buttons

		$this->add_action_buttons(true, null);
    }
    
	
    function validation($data, $files) {
		global $DB;
		$errors = array();

		// Return
        return $errors;
    }
	
	function data_preprocessing(&$data, $context_module) {
		
		// Description
		$data->description = array('text'=>$data->description, 'format'=>1);
		
		// Information
		$draftitemid = file_get_submitted_draft_itemid('information');
		$currenttext = file_prepare_draft_area($draftitemid, $context_module->id, 'mod_trainingpath', 'richtext', $data->id, array('subdirs'=>true), $data->information);
		$data->information = array('text'=>$currenttext, 'format'=>1, 'itemid'=>$draftitemid);
	}
	
	function data_postprocessing($data) {

		// Description
		$data->description = $data->description['text'];
	}
		
}

?>



