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

class mform_schedule_auto extends moodleform {
    
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$group_id = $this->_customdata['group_id'];
		$batch_id = $this->_customdata['batch_id'];

		
		//-------------------------------------------------------------------------------
		// Settings

		// Opening date
		$mform->addElement('date_selector', 'generate_schedule_from', get_string("generate_schedule_from", "trainingpath"));

		
		//-------------------------------------------------------------------------------
		// Hidden

		// Context
		
		$mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);

		$mform->addElement('hidden', 'group_id', $group_id);
        $mform->setType('group_id', PARAM_INT);

		$mform->addElement('hidden', 'batch_id', $batch_id);
        $mform->setType('batch_id', PARAM_INT);


		//-------------------------------------------------------------------------------
		// Buttons

		$auto_url = (new moodle_url('/mod/trainingpath/edit/schedule_auto.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'batch_id'=>$batch_id)))->out();
		$manual_url = (new moodle_url('/mod/trainingpath/edit/schedule_sequences.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'batch_id'=>$batch_id)))->out();
		$cancel_url = (new moodle_url('/mod/trainingpath/edit/schedule_batches.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out();
		$nav = trainingpath_edit_get_commands(array(
			'submit'=>array('class'=>'primary', 'label'=>get_string('generate_schedules', 'trainingpath'), 'href'=>$auto_url),
			'manual'=>array('class'=>'secondary', 'label'=>get_string('manual_scheduling', 'trainingpath'), 'href'=>$manual_url),
			'cancel'=>array('class'=>'secondary', 'label'=>get_string('cancel', 'trainingpath'), 'href'=>$cancel_url)
		));
		$mform->addElement('html', $nav);

		//$this->add_action_buttons(true, get_string('generate_schedules', 'trainingpath'));
    }
    
	
    function validation($data, $files) {
		global $DB;
		$errors = array();
		
		// Return
        return $errors;
    }
		
}

?>



