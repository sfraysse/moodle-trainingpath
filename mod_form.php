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

defined('MOODLE_INTERNAL') || die;

require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/trainingpath/edit/uilib.php');


class mod_trainingpath_mod_form extends moodleform_mod {

    function definition() {

		//-------------------------------------------------------------------------------
		// Check permissions
		
		if (!is_null($this->_cm)) {
			$check = trainingpath_check_edit_permission($this->_course, $this->_cm);
			if ($check == 'editschedule') redirect(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$this->_cm->id)));
		}


		//-------------------------------------------------------------------------------
		// Form with tabs

        $mform = $this->_form;
		$mform->addElement('html', trainingpath_edit_get_tabs());


		//-------------------------------------------------------------------------------
		// General

		$mform->addElement('header', 'general', get_string('general', 'trainingpath'));

		// Name
		$mform->addElement('text', 'name', get_string('title', 'trainingpath'));
		$mform->setType('name', PARAM_TEXT);
		$mform->addRule('name', null, 'required', null, 'client');

		// Intro element
        $this->standard_intro_elements();
		
		// Locked
		$mform->addElement('checkbox', 'locked', get_string('locked', 'trainingpath'));
		
		// Tracking data
		if (!is_null($this->_cm)) {
			$mform->addElement('html', '
				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-9">
						<p><a href="'.new moodle_url('/mod/trainingpath/report/recalculate.php', array('confirm'=>1, 'cmid'=>$this->_cm->id)).'">'
							.get_string('tracks_recalculate', 'trainingpath').
						'</a></p>
					</div>
				</div>
			');
		}
		
		
		//-------------------------------------------------------------------------------
		// Colors
		
		$mform->addElement('header', 'colors', get_string('colors', 'trainingpath'));
		trainingpath_form_add_score_colors($mform);
		
		
		//-------------------------------------------------------------------------------
		// Other settings
		
		$mform->addElement('header', 'time', get_string('time_settings', 'trainingpath'));
		trainingpath_form_add_time_settings($mform);


		//-------------------------------------------------------------------------------
		// Common settings

        $this->standard_coursemodule_elements();

		
		//-------------------------------------------------------------------------------
		// Buttons

		$this->add_action_buttons(true);

    }

	function data_preprocessing(&$default_values) {
	
		// Colors
		trainingpath_form_preprocess_score_colors($default_values);
		
		// Time settings
		trainingpath_form_preprocess_time_settings($default_values);

		// Parent processing
		parent::data_preprocessing($default_values);
	}
	
    function validation($data, $files) {
		global $DB;
		$errors = array();

		// Colors
		trainingpath_form_check_score_colors($data, $errors);

		// Time settings
		trainingpath_form_check_time_settings($data, $errors);
		
		// Return
		return array_merge($errors, parent::validation($data, $files));
    }

}
