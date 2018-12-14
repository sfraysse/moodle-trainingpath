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

/**
 * Get icon mapping for font-awesome.
 * SF2017 - Added for 3.3 compatibility
 */
function mod_trainingpath_get_fontawesome_icon_map() {
    return [
        'mod_trainingpath:schedule' => 'fa-calendar',
        'mod_trainingpath:dragdrop' => 'fa-arrows',
        'mod_trainingpath:edit' => 'fa-pencil',
        'mod_trainingpath:delete' => 'fa-close',
        'mod_trainingpath:children' => 'fa-navicon',
        'mod_trainingpath:content' => 'fa-play-circle-o',
        'mod_trainingpath:eval' => 'fa-check-square-o',
        'mod_trainingpath:classroom' => 'fa-group',
        'mod_trainingpath:virtual' => 'fa-user-circle-o',
        'mod_trainingpath:files' => 'fa-file-text-o',
        'mod_trainingpath:richtext' => 'fa-newspaper-o',
        'mod_trainingpath:alert' => 'fa-warning',
        'mod_trainingpath:review' => 'fa-search',
        'mod_trainingpath:duration' => 'fa-clock-o',
        'mod_trainingpath:comments' => 'fa-comment',
    ];
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @global object
 * @param object $data
 * @return bool|int
 */
function trainingpath_add_instance($data) {
    global $DB, $CFG;

	// Colors
	if (is_array($data->score_colors)) $data->score_colors = implode(',', $data->score_colors);

	// Timestamps
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    $data->locked = isset($data->locked);
	
	// DB update
    $pathId = $DB->insert_record("trainingpath", $data);
	
	// Create a top level item (path level)
	require_once($CFG->dirroot.'/mod/trainingpath/locallib.php');
	$item = (object)array('path_id'=>$pathId, 'type'=>EATPL_ITEM_TYPE_PATH, 'code'=>'', 'title'=>'', 'description'=>'', 'information'=>'');
	$DB->insert_record("trainingpath_item", $item);

	return $pathId;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @global object
 * @param object $data
 * @return bool
 */
function trainingpath_update_instance($data) {
    global $DB;

	// Colors
	if (is_array($data->score_colors)) $data->score_colors = implode(',', $data->score_colors);

	// Timestamps
    $data->timemodified = time();
    $data->locked = isset($data->locked);

	// DB update
    $data->id = $data->instance;
    return $DB->update_record("trainingpath", $data);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @global object
 * @param int $id
 * @return bool
 */
function trainingpath_delete_instance($id) {
    global $DB, $CFG;
    if (!$trainingpath = $DB->get_record('trainingpath', array('id'=>$id))) return false;
	
	// Check permissions
	$module = $DB->get_record('modules', array('name'=>'trainingpath'));
	$cm = $DB->get_record("course_modules", array('module'=>$module->id, 'instance'=>$id));
	$context = context_module::instance($cm->id);
	if (!has_capability('mod/trainingpath:addinstance', $context)) {
		echo 'Not allowed!';
		die;
	}
	
	// Delete top item
	if (!$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$id, 'type'=>EATPL_ITEM_TYPE_PATH))) return false;
	require_once($CFG->dirroot.'/mod/trainingpath/edit/ajaxlib.php');
	trainingpath_db_delete_item($topItem->id);
		
   // Delete calendars
   $res = $DB->delete_records('trainingpath_calendar', array('path_id'=>$id));
   if (!$res) return false;

   // Delete the activity
    if (! $DB->delete_records("trainingpath", array("id"=>$id))) return false;
    return true;
}


////////////////////////////////////////////////////////////////////////////////
// Reset data                                                                 //
////////////////////////////////////////////////////////////////////////////////

/**
 * Implementation of the function for printing the form elements that control
 * whether the course reset functionality affects the scorm.
 *
 * @param object $mform form passed by reference
 */
function trainingpath_reset_course_form_definition(&$mform) {
	$mform->addElement('header', 'trainingpathheader', get_string('modulenameplural', 'trainingpath'));
	$mform->addElement('advcheckbox', 'trainingpath_reset_tracks', get_string('reset_tracks','trainingpath'));
	$mform->addElement('advcheckbox', 'trainingpath_reset_comments', get_string('reset_comments','trainingpath'));
	$mform->addElement('advcheckbox', 'trainingpath_reset_schedules', get_string('reset_schedules','trainingpath'));
}

/**
 * Course reset form defaults.
 *
 * @return array
 */
function trainingpath_reset_course_form_defaults($course) {
	return array('trainingpath_reset_tracks'=>0, 'trainingpath_reset_comments'=>0, 'trainingpath_reset_schedules'=>0);
}

/**
 * This function is used by the reset_course_userdata function in moodlelib.
 *
 * @param object $data the data submitted from the reset course.
 * @return array status array
 */
function trainingpath_reset_userdata($data) {
	global $CFG;
	require_once($CFG->dirroot.'/mod/trainingpath/locallib.php');
	$status = array();
	
	// Reset tracks
	if (!empty($data->trainingpath_reset_tracks)) {
		
		// Delete ScormLite tracks
		$sql = '
			DELETE SST
			FROM {scormlite_scoes_track} SST
			INNER JOIN {trainingpath_item} I ON I.ref_id=SST.scoid
			INNER JOIN {trainingpath} A ON A.id=I.path_id
			INNER JOIN {course_modules} CM ON CM.instance=A.id
			WHERE CM.course=? AND (I.activity_type=? OR I.activity_type=?)';
		global $DB;
		$DB->execute($sql, array($data->courseid, EATPL_ACTIVITY_TYPE_CONTENT, EATPL_ACTIVITY_TYPE_EVAL));

		// Delete e-ATPL tracks
		$sql = '
			DELETE T
			FROM {trainingpath_tracks} T
			INNER JOIN {trainingpath_item} I ON I.id=T.context_id
			INNER JOIN {trainingpath} A ON A.id=I.path_id
			INNER JOIN {course_modules} CM ON CM.instance=A.id
			WHERE CM.course=?';
		global $DB;
		$DB->execute($sql, array($data->courseid));

		// Status
		$status[] = array(
			'component' => get_string('modulenameplural', 'trainingpath'),
			'item' => get_string('reset_tracks', 'trainingpath'),
			'error' => false);
	}
	
	// Reset comments
	if (!empty($data->trainingpath_reset_comments)) {
		
		// Delete e-ATPL comments
		$sql = '
			DELETE C
			FROM {trainingpath_comments} C
			INNER JOIN {trainingpath_item} I ON I.id=C.context_id
			INNER JOIN {trainingpath} A ON A.id=I.path_id
			INNER JOIN {course_modules} CM ON CM.instance=A.id
			WHERE CM.course=?';
		global $DB;
		$DB->execute($sql, array($data->courseid));

		// Status
		$status[] = array(
			'component' => get_string('modulenameplural', 'trainingpath'),
			'item' => get_string('reset_comments', 'trainingpath'),
			'error' => false);
	}
	
	// Reset schedules
	if (!empty($data->trainingpath_reset_schedules)) {
		
		// Delete e-ATPL schedules
		$sql = '
			DELETE S
			FROM {trainingpath_schedule} S
			INNER JOIN {trainingpath_item} I ON I.id=S.context_id
			INNER JOIN {trainingpath} A ON A.id=I.path_id
			INNER JOIN {course_modules} CM ON CM.instance=A.id
			WHERE CM.course=?';
		global $DB;
		$DB->execute($sql, array($data->courseid));

		// Status
		$status[] = array(
			'component' => get_string('modulenameplural', 'trainingpath'),
			'item' => get_string('reset_schedules', 'trainingpath'),
			'error' => false);
	}
	
	return $status;
}


////////////////////////////////////////////////////////////////////////////////
// Plugin capabilities                                                        //
////////////////////////////////////////////////////////////////////////////////

/**
 * Returns all other caps used in module
 *
 * @return array
 */
function trainingpath_get_extra_capabilities() {
    return array('moodle/site:accessallgroups');
}

/**
 * @uses FEATURE_IDNUMBER
 * @uses FEATURE_GROUPS
 * @uses FEATURE_GROUPINGS
 * @uses FEATURE_MOD_INTRO
 * @uses FEATURE_COMPLETION_TRACKS_VIEWS
 * @uses FEATURE_GRADE_HAS_GRADE
 * @uses FEATURE_GRADE_OUTCOMES
 * @param string $feature FEATURE_xx constant for requested feature
 * @return bool|null True if module supports feature, false if not, null if doesn't know
 */
function trainingpath_supports($feature) {
    switch($feature) {
        case FEATURE_IDNUMBER:                return false;
        case FEATURE_GROUPS:                  return true;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}

////////////////////////////////////////////////////////////////////////////////
// File API                                                                   //
////////////////////////////////////////////////////////////////////////////////


/**
 * Returns the lists of all browsable file areas within the given module context
 *
 * The file area 'intro' for the activity introduction field is added automatically
 * by {@link file_browser::get_file_info_context_module()}
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @return array of [(string)filearea] => (string)description
 */
function trainingpath_get_file_areas($course, $cm, $context) {
	$areas = array();
	$areas['content'] = get_string('areacontent', 'scormlite');
	$areas['package'] = get_string('areapackage', 'scormlite');
	return $areas;
}

/**
 * File browsing support for SCORM file areas
 *
 * @param stdclass $browser
 * @param stdclass $areas
 * @param stdclass $course
 * @param stdclass $cm
 * @param stdclass $context
 * @param string $filearea
 * @param int $itemid
 * @param string $filepath
 * @param string $filename
 * @return stdclass file_info instance or null if not found
 */
function trainingpath_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
	global $CFG;
	$file_info = null;

	if (has_capability('moodle/course:managefiles', $context) && ($filearea === 'content' || $filearea === 'package')) {
		$fs = get_file_storage();
		$filepath = is_null($filepath) ? '/' : $filepath;
		$filename = is_null($filename) ? '.' : $filename;
		$urlbase = $CFG->wwwroot.'/pluginfile.php';

		if ($itemid === null) {
			// itemid is the scoid
			global $DB;
			$scormlite = $DB->get_record('scormlite', array('id' => $cm->instance), 'id,scoid');
			if ($scormlite) {
				$itemid = $scormlite->scoid;
			}
		}

		$storedfile = $fs->get_file($context->id, 'mod_trainingpath', $filearea, $itemid, $filepath, $filename);
		if ($storedfile === false && $filepath === '/' && $filename === '.') {
			$storedfile = new virtual_root_file($context->id, 'mod_trainingpath', $filearea, null);
		}

		if ($storedfile !== false) {
			if ($filearea === 'content') {
				require_once($CFG->dirroot.'/mod/trainingpath/locallib.php');
				$file_info = new trainingpath_content_file_info($browser, $context, $storedfile, $urlbase, $areas[$filearea], true, true, false, false);
			} else if ($filearea === 'package') {
				$file_info = new file_info_stored($browser, $context, $storedfile, $urlbase, $areas[$filearea], false, true, false, false);
			}
		}
	}
	return $file_info;
}

/**
 * Serves the files from the trainingpath file areas
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param stdClass $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return void this should never return to the caller
 */
function trainingpath_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
	global $CFG;	

	// ScormLite
	if ($filearea == 'content' || $filearea == 'package') {
		require_once($CFG->dirroot.'/mod/scormlite/sharedlib.php');
		return scormlite_shared_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, $options, 'trainingpath');
	}
	
	// All begin
	if ($context->contextlevel != CONTEXT_MODULE) return false;
	require_login($course, true, $cm);
	$lifetime = isset($CFG->filelifetime) ? $CFG->filelifetime : 86400;
	$itemid = (int)array_shift($args);

	// Files
	if ($filearea === 'files_content') {
		$revision = (int)array_shift($args); // prevents caching problems - ignored here
	} else if ($filearea === 'files_package') {
		if (!has_capability('moodle/course:manageactivities', $context)) {
			return false;
		}
		$lifetime = 0; // no caching here
		
	// Schedule
	} else if ($filearea === 'schedule_package') {
		if (!has_capability('mod/trainingpath:editschedule', $context)) {
			return false;
		}
		$lifetime = 0; // no caching here
	}
	
	// All end
	$filename = array_pop($args);
	$filepath = '/' . implode('/', $args);
	if (count($args) > 0) $filepath .= '/';
	$fs = get_file_storage();
	$file = $fs->get_file($context->id, 'mod_trainingpath', $filearea, $itemid, $filepath, $filename);
	if (! $file || $file->is_directory()) return false;
    send_stored_file($file, $lifetime, 0, false, $options);
}


////////////////////////////////////////////////////////////////////////////////
// Gradebook API                                                              //
////////////////////////////////////////////////////////////////////////////////

/**
 * Return grade for given user or all users.
 *
 * @global stdClass
 * @global object
 * @param int $scormid id of scorm
 * @param int $userid optional user id, 0 means all users
 * @return array array of grades, false if none
 */
function trainingpath_get_user_grades($activity, $userid=0) {
	return false;
}

/**
 * Creates or updates grade item for the give trainingpath instance
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $trainingpath instance object with extra cmidnumber and modname property
 * @return void
 */
function trainingpath_grade_item_update($activity, $grades=null) {
}

/**
 * Update trainingpath grades in the gradebook
 *
 * Needed by grade_update_mod_grades() in lib/gradelib.php
 *
 * @param stdClass $trainingpath instance object with extra cmidnumber and modname property
 * @param int $userid update grade of specific user only, 0 means all participants
 * @return void
 */
function trainingpath_update_grades($activity, $userid=0, $nullifnone=true) {
}

/**
 * Delete grade item for given scorm
 *
 * @global stdClass
 * @param object $scorm object
 * @return object grade_item
 */
function trainingpath_grade_item_delete($activity) {
	return false;
}

