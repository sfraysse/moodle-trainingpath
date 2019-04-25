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
require_once($CFG->dirroot.'/lib/tablelib.php');

class mform_content extends moodleform {
    
    public function definition() {
        global $CFG, $OUTPUT;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$activityId = $this->_customdata['activity_id'];
		$sequenceId = $this->_customdata['sequence_id'];
		$groupId = $this->_customdata['group_id'];
		$evalOnly = $this->_customdata['eval_only'];
		$session = $this->_customdata['session'];

		
		//-------------------------------------------------------------------------------
		// Users table

		$mform->addElement('html', '<table class="table table-striped trainingpath-table"><tbody>');
		foreach($session->users as $user) {
			$userUrl = (new moodle_url('/mod/trainingpath/report/learner.php', array('cmid'=>$cmid, 'user_id'=>$user->id, 'eval_only'=>$evalOnly)))->out();
			$mform->addElement('html', '
				<tr>
					<td>
						'.$OUTPUT->user_picture($user).' '.'<a href="'.$userUrl.'">'.$user->firstname.' '.$user->lastname.'</a>
					</td>
					<td class="trainingpath-cell-status">');
			if (isset($user->track)) {
				$mform->addElement('html', trainingpath_report_get_user_combined_indicator_html($user->id, $activityId, EATPL_ITEM_TYPE_ACTIVITY, $session->learningpath));
			}
			$mform->addElement('html', '
					</td>
					<td class="trainingpath-text-right trainingpath-text-middle">');
			$mform->addElement('advcheckbox', 'force['.$user->id.']', '', '', array('group' => 1), array(0, 1));
			$mform->addElement('html', '
					</td>
				</tr>');
		}
		$mform->addElement('html', '</tbody></table>');
		$this->add_checkbox_controller(1);

		
		//-------------------------------------------------------------------------------
		// Hidden

		// Context
		
		$mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);

		$mform->addElement('hidden', 'activity_id', $activityId);
        $mform->setType('activity_id', PARAM_INT);

		$mform->addElement('hidden', 'group_id', $groupId);
        $mform->setType('group_id', PARAM_INT);

		$mform->addElement('hidden', 'eval_only', $evalOnly);
        $mform->setType('eval_only', PARAM_INT);


		//-------------------------------------------------------------------------------
		// Buttons

		$buttons = array();
		$buttons['submit'] = (object)array('label'=>get_string('force_completion', 'trainingpath'));
		$buttons['cancel'] = (object)array('label'=>get_string('cancel', 'trainingpath'), 'url'=>new moodle_url('/mod/trainingpath/edit/schedule_activities.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'sequence_id'=>$sequenceId)));
		$mform->addElement('html', trainingpath_form_get_buttons($buttons));
    }
    
	
    function validation($data, $files) {
		global $DB;
		$errors = array();

		// Return
        return $errors;
    }
	
	function data_preprocessing(&$data, $context_module) {
		return array();
	}
	
	function data_postprocessing($data) {

	}
		
}

?>



