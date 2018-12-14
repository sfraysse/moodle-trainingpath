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

class mform_schedule extends moodleform {
    
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$course_id = $this->_customdata['course_id'];
		$path_id = $this->_customdata['path_id'];
		$context_id = $this->_customdata['context_id'];
		$permission = $this->_customdata['permission'];

		
		//-------------------------------------------------------------------------------
		// General

		$mform->addElement('header', 'general', get_string('general', 'trainingpath'));

		// Groups
		$groupids = trainingpath_get_groups($course_id, $permission);
		$groups = $DB->get_records_list('groups', 'id', $groupids);		
		$groupsData = array();
		foreach($groups as $group) $groupsData[$group->id] = $group->name;
		$groupsSelect = $mform->addElement('select', 'group_id', get_string('group', 'trainingpath'), $groupsData);
		$mform->addRule('group_id', null, 'required', null, 'client');

		// Description
        $mform->addElement('editor', 'description', get_string('description', 'trainingpath'));
        $mform->setType('description', PARAM_RAW);

        
		//-------------------------------------------------------------------------------
		// Timing

		$mform->addElement('header', 'other_settings', get_string('other_settings', 'trainingpath'));

		// Access
		$schedule = trainingpath_get_default_schedule(EATPL_ITEM_TYPE_PATH);
		$mform->addElement('select', 'access', get_string('access', 'trainingpath'), trainingpath_access_select(array(EATPL_ACCESS_CLOSED, EATPL_ACCESS_OPEN, EATPL_ACCESS_ON_DATES)));
		$mform->setDefault('access', $schedule->access);

		// Opening date
		$mform->addElement('date_selector', 'time_open', get_string("access_from_date", "trainingpath"));
		$mform->disabledIf('time_open', 'access', 'neq', EATPL_ACCESS_ON_DATES);

		// Closing date
		$mform->addElement('date_selector', 'time_close', get_string("access_to_date", "trainingpath"));
		$mform->disabledIf('time_close', 'access', 'neq', EATPL_ACCESS_ON_DATES);

		// Calendar
		$mform->addElement('select', 'calendar_id', get_string('calendar', 'trainingpath'), trainingpath_calendar_select($path_id));
		//$mform->addRule('calendar_id', null, 'required', null, 'client');

		// Manage schedules button
		$calendars_url = (new moodle_url('/mod/trainingpath/calendars/manage.php', array('cmid'=>$cmid)))->out();
		$mform->addElement('html', '
			<div class="form-group row fitem">
				<div class="col-md-3"></div>
				<div class="col-md-9">
					<a href="'.$calendars_url.'" class="btn btn-secondary" role="button">'.get_string('manage_calendars', 'trainingpath').'</a>
				</div>
			</div>
		');

        
		//-------------------------------------------------------------------------------
		// Hidden

		// Schedule table
		
		$mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

		$mform->addElement('hidden', 'context_type', EATPL_ITEM_TYPE_PATH);
        $mform->setType('context_type', PARAM_INT);

		$mform->addElement('hidden', 'context_id', $context_id);
        $mform->setType('context_id', PARAM_INT);

		$mform->addElement('hidden', 'period_open', 0);
        $mform->setType('period_open', PARAM_INT);

		$mform->addElement('hidden', 'period_close', 0);
        $mform->setType('period_close', PARAM_INT);

		$mform->addElement('hidden', 'information', '');
        $mform->setType('information', PARAM_RAW);

		$mform->addElement('hidden', 'position', 1000);
        $mform->setType('position', PARAM_INT);

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
		
		// Group: Never add a schedule to a group that has already one
		$sameGroups = array_values($DB->get_records('trainingpath_schedule', array('context_id'=>$data['context_id'], 'context_type'=>EATPL_ITEM_TYPE_PATH, 'group_id'=>$data['group_id'])));
		if (is_array($sameGroups) && (count($sameGroups) > 1 || (count($sameGroups) == 1 && $sameGroups[0]->id != $data['id']))) {
			$errors['group_id'] = get_string('schedule_already_assigned', 'trainingpath');
		}
		
		// Return
        return $errors;
    }
	
	
	function data_preprocessing(&$data) {
		
		// Description
		$data->description = array('text'=>$data->description, 'format'=>1);
	}
	
	function data_postprocessing($data) {

		// Description
		$data->description = $data->description['text'];
	}
	
}

?>



