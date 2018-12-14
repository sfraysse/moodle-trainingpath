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

class mform_activity_files extends moodleform {
    
    public function definition() {
        global $CFG;
        $mform = $this->_form; 
 
		// Custom params
		$cmid = $this->_customdata['cmid'];
		$learningpathId = $this->_customdata['learningpath_id'];
		$sequenceId = $this->_customdata['sequence_id'];
		$via = $this->_customdata['via'];
		$type = $this->_customdata['activity_type'];
		$complementary = $this->_customdata['complementary'];

		
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
		// Other settings

		$mform->addElement('header', 'other_settings', get_string('other_settings', 'trainingpath'));

		// Popup
		$mform->addElement('select', 'popup', get_string('display', 'scormlite'), scormlite_get_popup_display_array());
		$mform->setDefault('popup', 1);

		// Package
		$mform->addElement('filepicker', 'packagefile', get_string('package','scormlite'));
		$mform->addHelpButton('packagefile', 'package', 'scormlite');
		$mform->addRule('packagefile', null, 'required', null, 'client');
		
		// Launch file
		$mform->addElement('text', 'launch_file', get_string('launch_file', 'trainingpath'), 'maxlength="255" size="32"');
		$mform->setType('launch_file', PARAM_RAW);
		//$mform->addRule('launch_file', null, 'required', null, 'client');
		
		
		//-------------------------------------------------------------------------------
		// Hidden

		// trainingpath_item table elements
		
		$mform->addElement('hidden', 'id', 0);
        $mform->setType('id', PARAM_INT);

		$mform->addElement('hidden', 'type', EATPL_ITEM_TYPE_ACTIVITY);
        $mform->setType('type', PARAM_INT);

		$mform->addElement('hidden', 'parent_id', $sequenceId);
        $mform->setType('parent_id', PARAM_INT);

		$mform->addElement('hidden', 'parent_position', 1000);
        $mform->setType('parent_position', PARAM_INT);

		$mform->addElement('hidden', 'ref_id', 0);
        $mform->setType('ref_id', PARAM_INT);

		$mform->addElement('hidden', 'path_id', $learningpathId);
        $mform->setType('path_id', PARAM_INT);

		$mform->addElement('hidden', 'duration', 0);
		$mform->setType('duration', PARAM_INT);

		$mform->addElement('hidden', 'code', '');
		$mform->setType('code', PARAM_RAW);

		$mform->addElement('hidden', 'activity_type', $type);
        $mform->setType('activity_type', PARAM_INT);

		$mform->addElement('hidden', 'complementary', $complementary);
        $mform->setType('complementary', PARAM_INT);

		$mform->addElement('hidden', 'remedial', 0);
        $mform->setType('remedial', PARAM_INT);

		$mform->addElement('hidden', 'information', '');
        $mform->setType('information', PARAM_RAW);

		$mform->addElement('hidden', 'evaluation', 0);
        $mform->setType('evaluation', PARAM_INT);

		
		// trainingpath_files table elements
		
		$mform->addElement('hidden', 'files_id', 0);
        $mform->setType('files_id', PARAM_INT);

		$mform->addElement('hidden', 'reference', '');
        $mform->setType('reference', PARAM_ALPHA);

		$mform->addElement('hidden', 'sha1hash', '');
        $mform->setType('sha1hash', PARAM_RAW);

		$mform->addElement('hidden', 'revision', 0);
        $mform->setType('revision', PARAM_INT);

		
		// Context
		
		$mform->addElement('hidden', 'cmid', $cmid);
        $mform->setType('cmid', PARAM_INT);
		
		$mform->addElement('hidden', 'sequence_id', $sequenceId);
        $mform->setType('sequence_id', PARAM_INT);

		$mform->addElement('hidden', 'via', $via);
        $mform->setType('via', PARAM_RAW);


		//-------------------------------------------------------------------------------
		// Buttons

		$this->add_action_buttons(true, null);
    }
    
	
    function validation($data, $files) {
		global $DB;
		$errors = array();

		// Packaging
		$this->check_package($data, $files, $errors);

		// Return
        return $errors;
    }
	
	function data_preprocessing(&$data, $context_module) {
		
		// Description
		$data->description = array('text'=>$data->description, 'format'=>1);
		
		// Packaging
		$draftitemid = file_get_submitted_draft_itemid('packagefile');
		file_prepare_draft_area($draftitemid, $context_module->id, 'mod_trainingpath', 'files_package', $data->files_id);
		$data->packagefile = $draftitemid;
	}
	
	function data_postprocessing($data) {

		// Description
		$data->description = $data->description['text'];
	}
	
	// Check ScormLite package: same function in ScormLite. Duplicate because can not be inheritated.
	
	function check_package($data, $files, &$errors, $pluginname = 'trainingpath') {
		global $CFG;
		
		// If no file
		if (empty($data['packagefile'])) {
			// If no file
			$errors['packagefile'] = get_string('required');
		} else {
			$files = $this->get_draft_files('packagefile');
			if (!$files || count($files)<1) {
				// If no file
				$errors['packagefile'] = get_string('required');
				return;
			}
			
			// Check file extension
			$file = reset($files);
            $ext = pathinfo($file->get_filename(), PATHINFO_EXTENSION);
			$allowed = explode(',', get_config('trainingpath')->file_extensions);
			if (!in_array($ext, $allowed)) {
				$errors['packagefile'] = get_string('error_files_invalid_extension', 'trainingpath');
				return;
			}

			// Upload file
			$filename = "{$CFG->tempdir}/".$pluginname."import/".$pluginname."_".time();
			make_temp_directory($pluginname.'import');
			$file->copy_content_to($filename);

			// Zip file
            if ($ext == 'zip') {
				
				// Check launch file
				if (empty($data['launch_file'])) {
					$errors['launch_file'] = get_string('error_files_missing_launch', 'trainingpath');
					unlink($filename);
					return;
				}
		
				// Unzip
				$packer = get_file_packer('application/zip');
				$filelist = $packer->list_files($filename);			
				if (!is_array($filelist)) {
					
					// If not a package
					$errors['packagefile'] = get_string('error_files_invalid_package', 'trainingpath');
				} else {
					
					// Check if the launch file exists
					$indexfound = false;
					foreach ($filelist as $info) {
						if ($info->pathname == $data['launch_file']) {
							$indexfound = true;
							break;
						}
					}
					if (!$indexfound) {
						$errors['launch_file'] = get_string('error_files_invalid_launch', 'trainingpath');
					}
				}
			}
			unlink($filename);
		}
	}
	
}

?>



