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

class mform_schedule_batches extends moodleform {
    
	public $batches;
	
	
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$group_id = $this->_customdata['group_id'];
		$sequences_url = $this->_customdata['sequences_url'];
		$top_schedule = $this->_customdata['top_schedule'];
		$this->batches = $this->_customdata['batches'];

		
		//-------------------------------------------------------------------------------
		// Batches

		if (empty($this->batches)) {
			$mform->addElement('html', '<p>'.get_string('no_batch', 'trainingpath').'</p>');
			return;
		}
		$schedule = trainingpath_get_default_schedule(EATPL_ITEM_TYPE_BATCH);
		foreach($this->batches as $batch) {

			// Section title			
			$mform->addElement('header', 'title['.$batch->id.']', '['.$batch->code.'] '.$batch->title);

			// Schedule id
			$mform->addElement('hidden', 'schedule_id['.$batch->id.']', 0);
			$mform->setType('schedule_id['.$batch->id.']', PARAM_INT);

			// Access
			$mform->addElement('select', 'access['.$batch->id.']', get_string('access', 'trainingpath'), trainingpath_access_select(array(EATPL_ACCESS_CLOSED, EATPL_ACCESS_OPEN, EATPL_ACCESS_ON_DATES, EATPL_ACCESS_FROM_DATE, EATPL_ACCESS_TO_DATE)));
			$mform->setDefault('access['.$batch->id.']', $schedule->access);
	
			// Opening date
			$mform->addElement('date_selector', 'time_open['.$batch->id.']', get_string("access_from_date", "trainingpath"));
			$mform->disabledIf('time_open['.$batch->id.']', 'access['.$batch->id.']', 'eq', EATPL_ACCESS_CLOSED);
			$mform->disabledIf('time_open['.$batch->id.']', 'access['.$batch->id.']', 'eq', EATPL_ACCESS_OPEN);
			$mform->disabledIf('time_open['.$batch->id.']', 'access['.$batch->id.']', 'eq', EATPL_ACCESS_TO_DATE);
	
			// Closing date
			$mform->addElement('date_selector', 'time_close['.$batch->id.']', get_string("access_to_date", "trainingpath"));
			$mform->disabledIf('time_close['.$batch->id.']', 'access['.$batch->id.']', 'eq', EATPL_ACCESS_CLOSED);
			$mform->disabledIf('time_close['.$batch->id.']', 'access['.$batch->id.']', 'eq', EATPL_ACCESS_OPEN);
			$mform->disabledIf('time_close['.$batch->id.']', 'access['.$batch->id.']', 'eq', EATPL_ACCESS_FROM_DATE);
		
			// Edit button
			$sequences_auto_url = (new moodle_url('/mod/trainingpath/edit/schedule_auto.php', array('cmid'=>$cmid, 'group_id'=>$group_id)))->out();
			$mform->addElement('html', '
				<div class="form-group row fitem">
					<div class="col-md-3"></div>
					<div class="col-md-9">
						<a href="'.$sequences_url.'&batch_id='.$batch->id.'" class="btn btn-sm btn-primary" role="button">'.get_string('schedule_sequences', 'trainingpath').'</a>');
			if ($top_schedule->calendar_id) {
				$mform->addElement('html', '
						<a href="'.$sequences_auto_url.'&batch_id='.$batch->id.'" class="btn btn-sm btn-secondary" role="button">'.get_string('generate_schedule', 'trainingpath').'</a>');
			}
			$mform->addElement('html', '
					</div>
				</div>
			');
		}
		

		//-------------------------------------------------------------------------------
		// Hidden

		// Context
		
		$mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);

		$mform->addElement('hidden', 'group_id', $group_id);
        $mform->setType('group_id', PARAM_INT);


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

				// From & To date
				if ($record->schedule->access ==  EATPL_ACCESS_ON_DATES) {
					if ($record->schedule->time_close ==  0) {
						$res['access['.$index.']'] = EATPL_ACCESS_FROM_DATE;
					} else if ($record->schedule->time_open ==  0) {
						$res['access['.$index.']'] = EATPL_ACCESS_TO_DATE;
					}
				}
			}
		}
		return $res;
	}
	
	
	function data_postprocessing($data) {
		foreach($data->access as $index => $val) {
			
			if ($val == EATPL_ACCESS_FROM_DATE) {
				
				$data->access[$index] = EATPL_ACCESS_ON_DATES;
				$data->time_close[$index] = 0;
				
			} else if ($val == EATPL_ACCESS_TO_DATE) {
				
				$data->access[$index] = EATPL_ACCESS_ON_DATES;
				$data->time_open[$index] = 0;
				
			}
		}
	}
	
}

?>



