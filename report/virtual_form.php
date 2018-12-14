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

class mform_virtual extends moodleform {
    
    public function definition() {
        global $CFG, $OUTPUT;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$activityId = $this->_customdata['activity_id'];
		$groupId = $this->_customdata['group_id'];
		$evalOnly = $this->_customdata['eval_only'];
		$session = $this->_customdata['session'];
		$duration = $this->_customdata['duration'] / 60.0;

		//-------------------------------------------------------------------------------
		// General

		$mform->addElement('header', 'global', get_string('global_followup', 'trainingpath'));

		// Duration
		$error = get_string('session_duration_error', 'trainingpath');
		$mform->addElement('text', 'duration', get_string('session_duration_', 'trainingpath', $duration), 'maxlength="3" size="3"');
		$mform->setType('duration', PARAM_INT);
		$mform->setDefault('duration', $duration);
		$mform->addRule('duration', $error, 'required', null, 'client');
		$mform->addRule('duration', $error, 'nonzero', null, 'client');
		$mform->addRule('duration', $error, 'regex', '/^[0-9]\d*$/', 'client');

		// Package
		$mform->addElement('filepicker', 'packagefile', get_string('files','trainingpath'));

        
		//-------------------------------------------------------------------------------
		// Users table

		$mform->addElement('header', 'individual', get_string('participation', 'trainingpath'));
		
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
			$mform->addElement('advcheckbox', 'participation['.$user->id.']', '', '', null, array(0, 1));
			$mform->addElement('html', '
					</td>
				</tr>');
		}
		$mform->addElement('html', '</tbody></table>');


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

		// File
		
		$mform->addElement('hidden', 'file_reference', '');
        $mform->setType('file_reference', PARAM_ALPHA);


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
		$res = array();
		
		// Duration
		if (isset($data->schedule->duration)) $res['duration'] = $data->schedule->duration/60.0;
		if (isset($data->schedule->file_reference)) $res['file_reference'] = $data->schedule->file_reference;

		// Packaging
		$draftitemid = file_get_submitted_draft_itemid('packagefile');
		file_prepare_draft_area($draftitemid, $context_module->id, 'mod_trainingpath', 'schedule_package', $data->schedule->id);
		$res['packagefile'] = $draftitemid;

		// Participation
		foreach($data->users as $user) {
			if (isset($user->track)) {
				$res['participation['.$user->id.']'] = ($user->track->completion == EATPL_COMPLETION_COMPLETED);
			}
		}
		return $res;
	}
	
	function data_postprocessing($data) {

		// Duration
		if (isset($data->duration)) $data->duration = $data->duration*60;
	}
		
}

?>



