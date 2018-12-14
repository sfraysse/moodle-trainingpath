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

class mform_batch extends moodleform {
    
    public function definition() {
        global $CFG;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$path_id = $this->_customdata['path_id'];
		$parent_id = $this->_customdata['parent_id'];

		
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
		// Hidden

		// Item table
		
		$mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

		$mform->addElement('hidden', 'type', EATPL_ITEM_TYPE_BATCH);
        $mform->setType('type', PARAM_INT);

		$mform->addElement('hidden', 'parent_id', $parent_id);
        $mform->setType('parent_id', PARAM_INT);

		$mform->addElement('hidden', 'parent_position', 1000);
        $mform->setType('parent_position', PARAM_INT);

		$mform->addElement('hidden', 'path_id', $path_id);
        $mform->setType('path_id', PARAM_INT);

		$mform->addElement('hidden', 'information', '');
        $mform->setType('information', PARAM_RAW);

		// Context
		
		$mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);
		

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
	
	
	function data_preprocessing(&$data) {
		$data->description = array('text'=>$data->description, 'format'=>1);
	}
	
	function data_postprocessing($data) {
		$data->description = $data->description['text'];
	}
	
}

?>



