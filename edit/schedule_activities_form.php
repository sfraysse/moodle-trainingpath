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

class mform_schedule_activities extends moodleform {
    
	public $activities;
	
	
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$group_id = $this->_customdata['group_id'];
		$sequence_id = $this->_customdata['sequence_id'];
		$this->activities = $this->_customdata['activities'];

		
		//-------------------------------------------------------------------------------
		// Activities

		if (empty($this->activities)) {
			$mform->addElement('html', '<p>'.get_string('no_activity', 'trainingpath').'</p>');
			return;
		}
		foreach($this->activities as $activity) {

			// Section title
			if ($activity->complementary) {
				$mform->addElement('header', 'title['.$activity->id.']', $activity->title);
			} else {
				$mform->addElement('header', 'title['.$activity->id.']', '['.$activity->code.'] '.$activity->title);
			}

			// Schedule id
			$mform->addElement('hidden', 'schedule_id['.$activity->id.']', 0);
			$mform->setType('schedule_id['.$activity->id.']', PARAM_INT);

			// Access
			$schedule = trainingpath_get_default_activity_schedule($activity->activity_type, $activity->complementary, $activity->remedial);
			if ($activity->activity_type == EATPL_ACTIVITY_TYPE_CONTENT) {

				$options = array(EATPL_ACCESS_CLOSED, EATPL_ACCESS_OPEN);
				if ($activity->complementary) $options[] = EATPL_ACCESS_HIDDEN;
				else $options[] = EATPL_ACCESS_ON_COMPLETION;
				$mform->addElement('select', 'access['.$activity->id.']', get_string('access', 'trainingpath'), trainingpath_access_select($options));
				$mform->setDefault('access['.$activity->id.']', $schedule->access);
				
			} else if ($activity->activity_type == EATPL_ACTIVITY_TYPE_EVAL) {

				$options = array(EATPL_ACCESS_CLOSED, EATPL_ACCESS_OPEN, EATPL_ACCESS_ON_DATES);
				if ($activity->complementary) $options[] = EATPL_ACCESS_HIDDEN;
				else if ($activity->remedial) $options[] = EATPL_ACCESS_AS_REMEDIAL;
				else $options[] = EATPL_ACCESS_ON_COMPLETION;
				$mform->addElement('select', 'access['.$activity->id.']', get_string('access', 'trainingpath'), trainingpath_access_select($options));
				$mform->setDefault('access['.$activity->id.']', $schedule->access);
				
			} else if ($activity->activity_type == EATPL_ACTIVITY_TYPE_VIRTUAL || $activity->activity_type == EATPL_ACTIVITY_TYPE_CLASSROOM) {

				$options = array(EATPL_ACCESS_CLOSED, EATPL_ACCESS_ON_DATES);
				if ($activity->complementary) $options[] = EATPL_ACCESS_HIDDEN;
				$mform->addElement('select', 'access['.$activity->id.']', get_string('access', 'trainingpath'), trainingpath_access_select($options));
				$mform->setDefault('access['.$activity->id.']', $schedule->access);
				
			} else if ($activity->activity_type == EATPL_ACTIVITY_TYPE_FILES || $activity->activity_type == EATPL_ACTIVITY_TYPE_RICHTEXT) {

				$options = array(EATPL_ACCESS_OPEN, EATPL_ACCESS_HIDDEN);
				$mform->addElement('select', 'access['.$activity->id.']', get_string('access', 'trainingpath'), trainingpath_access_select($options));
				$mform->setDefault('access['.$activity->id.']', $schedule->access);
				
			}
			
			if ($activity->activity_type == EATPL_ACTIVITY_TYPE_EVAL || $activity->activity_type == EATPL_ACTIVITY_TYPE_VIRTUAL || $activity->activity_type == EATPL_ACTIVITY_TYPE_CLASSROOM) {
				
				// Opening date
				$mform->addElement('date_time_selector', 'time_open['.$activity->id.']', get_string("access_from_date", "trainingpath"));
				$mform->disabledIf('time_open['.$activity->id.']', 'access['.$activity->id.']', 'neq', EATPL_ACCESS_ON_DATES);
		
				// Closing date
				$mform->addElement('date_time_selector', 'time_close['.$activity->id.']', get_string("access_to_date", "trainingpath"));
				$mform->disabledIf('time_close['.$activity->id.']', 'access['.$activity->id.']', 'neq', EATPL_ACCESS_ON_DATES);
			}
			
			if ($activity->activity_type == EATPL_ACTIVITY_TYPE_VIRTUAL || $activity->activity_type == EATPL_ACTIVITY_TYPE_CLASSROOM) {
				
				// Information
				$mform->addElement('editor', 'information['.$activity->id.']', get_string('practical_information', 'trainingpath'));
				$mform->setType('information['.$activity->id.']', PARAM_RAW);

			} else {
				
				// Information
				$mform->addElement('hidden', 'information['.$activity->id.']', '');
				$mform->setType('information['.$activity->id.']', PARAM_RAW);
			}
			
			if (!$activity->complementary) {
				
				// Tutoring link
				if ($activity->activity_type == EATPL_ACTIVITY_TYPE_VIRTUAL) $page = 'virtual';
				else if ($activity->activity_type == EATPL_ACTIVITY_TYPE_CLASSROOM) $page = 'classroom';
				else if ($activity->activity_type == EATPL_ACTIVITY_TYPE_CONTENT) $page = 'content';
				else if ($activity->activity_type == EATPL_ACTIVITY_TYPE_EVAL) $page = 'eval';
				$reporting_url = (new moodle_url('/mod/trainingpath/report/'.$page.'.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'activity_id'=>$activity->id)))->out();
				$mform->addElement('html', '
					<div class="form-group row fitem">
						<div class="col-md-3"></div>
						<div class="col-md-9">
							<a href="'.$reporting_url.'" class="btn btn-sm btn-secondary" role="button">'.get_string('learners_progress', 'trainingpath').'</a>
						</div>
					</div>
				');
			}

		}
		

		//-------------------------------------------------------------------------------
		// Hidden

		// Context
		
		$mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);

		$mform->addElement('hidden', 'group_id', $group_id);
        $mform->setType('group_id', PARAM_INT);

		$mform->addElement('hidden', 'sequence_id', $sequence_id);
        $mform->setType('sequence_id', PARAM_INT);


		//-------------------------------------------------------------------------------
		// Buttons

		$this->add_action_buttons(false, null);
    }
    
	
    function validation($data, $files) {
		global $DB;
		$errors = array();
		
		// Return
        return $errors;
    }
	
	
	function data_preprocessing(&$data) {
		$res = array();
		foreach($data as $index => $record) {
			if ($record->schedule) {
				$res['schedule_id['.$index.']'] = $record->schedule->id;
				$res['access['.$index.']'] = $record->schedule->access;
				$res['time_open['.$index.']'] = $record->schedule->time_open;
				$res['time_close['.$index.']'] = $record->schedule->time_close;
				if ($record->schedule->information)
					$res['information['.$index.']'] = array('text'=>$record->schedule->information, 'format'=>1);
			}
		}
		return $res;
	}
	
	
	function data_postprocessing($data) {
		
		// Information
		foreach($data->information as $index => $information) {
			if ($information) $data->information[$index] = $information['text'];
		}
	}
	
}

?>



