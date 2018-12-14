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

require_once($CFG->dirroot.'/mod/trainingpath/calendars/lib.php');
require_once($CFG->libdir.'/formslib.php');

class mform_calendar extends moodleform {
    
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$pathId = $this->_customdata['path_id'];

		
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
		// Weekly closed days

		$mform->addElement('header', 'weekly_closed_section', get_string('weekly_closed', 'trainingpath'));

		// Days
        $mform->addElement('checkbox', 'weekly_closed_days[1]', get_string('monday', 'trainingpath'));
        $mform->addElement('checkbox', 'weekly_closed_days[2]', get_string('tuesday', 'trainingpath'));
        $mform->addElement('checkbox', 'weekly_closed_days[3]', get_string('wednesday', 'trainingpath'));
        $mform->addElement('checkbox', 'weekly_closed_days[4]', get_string('thursday', 'trainingpath'));
        $mform->addElement('checkbox', 'weekly_closed_days[5]', get_string('friday', 'trainingpath'));
        $mform->addElement('checkbox', 'weekly_closed_days[6]', get_string('saturday', 'trainingpath'));
        $mform->addElement('checkbox', 'weekly_closed_days[0]', get_string('sunday', 'trainingpath'));
        
		
		//-------------------------------------------------------------------------------
		// Weekly closed days

		$mform->addElement('header', 'yearly_closed_section', get_string('yearly_closed', 'trainingpath'));

		// Days
        $mform->addElement('textarea', 'yearly_closed', get_string('yearly_closed', 'trainingpath'), 'rows="10" cols="100%"');
        $mform->setType('yearly_closed', PARAM_RAW);
		$mform->addHelpButton('yearly_closed', 'yearly_closed', 'trainingpath');


		//-------------------------------------------------------------------------------
		// Hidden

		// Calendar table
		
		$mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

		$mform->addElement('hidden', 'weekly_closed', '');
        $mform->setType('weekly_closed', PARAM_RAW);

		$mform->addElement('hidden', 'position', 1000);
        $mform->setType('position', PARAM_INT);

		$mform->addElement('hidden', 'path_id', $pathId);
        $mform->setType('path_id', PARAM_INT);

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
		
		// Yearly closed dates
		if (!trainingpath_calendar_yearly_closed_valid($data['yearly_closed'])) {
			$errors['yearly_closed'] = get_string('yearly_closed_error', 'trainingpath');
		}
		
		// Return
        return $errors;
    }
	
	function data_preprocessing(&$data) {
		
		// Description
		$data->description = array('text'=>$data->description, 'format'=>1);

		// Weekly closed
		$closed = explode(',', $data->weekly_closed);
		foreach($closed as $ind) {
			$data->weekly_closed_days[$ind] = 1;
		}
	}
	
	function data_postprocessing($data) {

		// Description
		$data->description = $data->description['text'];

		// Weekly closed
		if (isset($data->weekly_closed_days)) {
			$checked = array();
			foreach($data->weekly_closed_days as $ind => $val) {
				if ($val) $checked[] = $ind;
			}
			$data->weekly_closed = implode(',', $checked);
		} else {
			$data->weekly_closed = '';
		}
	}
	
}

?>



