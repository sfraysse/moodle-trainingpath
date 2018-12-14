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

class mform_schedule_sequences extends moodleform {
    
	public $sequences;
	
	
    public function definition() {
        global $CFG, $DB;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$group_id = $this->_customdata['group_id'];
		$batch_id = $this->_customdata['batch_id'];
		$activities_url = $this->_customdata['activities_url'];
		$this->sequences = $this->_customdata['sequences'];

		
		//-------------------------------------------------------------------------------
		// Sequences

		if (empty($this->sequences)) {
			$mform->addElement('html', '<p>'.get_string('no_sequence', 'trainingpath').'</p>');
			return;
		}
		$schedule = trainingpath_get_default_schedule(EATPL_ITEM_TYPE_SEQUENCE);
		foreach($this->sequences as $sequence) {

			// Section title			
			$mform->addElement('header', 'title['.$sequence->id.']', '['.$sequence->code.'] '.$sequence->title);

			// Schedule id
			$mform->addElement('hidden', 'schedule_id['.$sequence->id.']', 0);
			$mform->setType('schedule_id['.$sequence->id.']', PARAM_INT);

			// Access
			$mform->addElement('select', 'access['.$sequence->id.']', get_string('access', 'trainingpath'), trainingpath_access_select(array(EATPL_ACCESS_CLOSED, EATPL_ACCESS_OPEN, EATPL_ACCESS_ON_DATES, EATPL_ACCESS_FROM_DATE, EATPL_ACCESS_TO_DATE)));
			$mform->setDefault('access['.$sequence->id.']', $schedule->access);
	
			// Opening date
			$mform->addElement('date_selector', 'time_open['.$sequence->id.']', get_string("access_from_date", "trainingpath"));
			$mform->disabledIf('time_open['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_CLOSED);
			$mform->disabledIf('time_open['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_OPEN);
			$mform->disabledIf('time_open['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_TO_DATE);
	
			// Opening period
			$mform->addElement('select', 'period_open['.$sequence->id.']', '', trainingpath_access_periods_select());
			$mform->disabledIf('period_open['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_CLOSED);
			$mform->disabledIf('period_open['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_OPEN);
			$mform->disabledIf('period_open['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_TO_DATE);

			// Closing date
			$mform->addElement('date_selector', 'time_close['.$sequence->id.']', get_string("access_to_date", "trainingpath"));
			$mform->disabledIf('time_close['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_CLOSED);
			$mform->disabledIf('time_close['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_OPEN);
			$mform->disabledIf('time_close['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_FROM_DATE);
		
			// Closing period
			$mform->addElement('select', 'period_close['.$sequence->id.']', '', trainingpath_access_periods_select());
			$mform->disabledIf('period_close['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_CLOSED);
			$mform->disabledIf('period_close['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_OPEN);
			$mform->disabledIf('period_close['.$sequence->id.']', 'access['.$sequence->id.']', 'eq', EATPL_ACCESS_FROM_DATE);
		
			// Edit button
			$reporting_url = (new moodle_url('/mod/trainingpath/report/sequence.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'sequence_id'=>$sequence->id)))->out();
			$mform->addElement('html', '
				<div class="form-group row fitem">
					<div class="col-md-3"></div>
					<div class="col-md-9">
						<a href="'.$activities_url.'&sequence_id='.$sequence->id.'" class="btn btn-sm btn-primary" role="button">'.get_string('schedule_activities', 'trainingpath').'</a>
						<a href="'.$reporting_url.'" class="btn btn-sm btn-secondary" role="button">'.get_string('learners_progress', 'trainingpath').'</a>
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

		$mform->addElement('hidden', 'batch_id', $batch_id);
        $mform->setType('batch_id', PARAM_INT);


		
		//-------------------------------------------------------------------------------
		// Buttons

		// Problem: can't close the section because need a visible form element for that !
		/*
		$mform->closeHeaderBefore('batch_id');
		$auto_url = (new moodle_url('/mod/trainingpath/edit/schedule_auto.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'batch_id'=>$batch_id)))->out();
		$manual_url = (new moodle_url('/mod/trainingpath/edit/schedule_sequences.php', array('cmid'=>$cmid, 'group_id'=>$group_id, 'batch_id'=>$batch_id)))->out();
		$nav = trainingpath_edit_get_commands(array(
			'submit'=>array('class'=>'primary', 'label'=>get_string('save', 'trainingpath'), 'href'=>$manual_url),
			'auto'=>array('class'=>'secondary', 'label'=>get_string('auto_scheduling', 'trainingpath'), 'href'=>$auto_url)
		));
		$mform->addElement('html', $nav);
		*/
		
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
				$res['period_open['.$index.']'] = $record->schedule->period_open;
				$res['period_close['.$index.']'] = $record->schedule->period_close;
				
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
				$data->period_close[$index] = 0;
				
			} else if ($val == EATPL_ACCESS_TO_DATE) {
				
				$data->access[$index] = EATPL_ACCESS_ON_DATES;
				$data->time_open[$index] = 0;
				$data->period_open[$index] = 0;
				
			}
		}
	}
	
}

?>



