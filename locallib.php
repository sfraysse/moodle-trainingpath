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

// Item types

define('EATPL_ITEM_TYPE_PATH', 0);
define('EATPL_ITEM_TYPE_BATCH', 1);
define('EATPL_ITEM_TYPE_SEQUENCE', 2);
define('EATPL_ITEM_TYPE_ACTIVITY', 3);
define('EATPL_ITEM_TYPE_CERTIFICATE', 4);

// Item type names

function trainingpath_item_type_name($type, $plurial = false) {
	if ($plurial) {
		switch($type) {
			case EATPL_ITEM_TYPE_PATH : return 'paths';
			case EATPL_ITEM_TYPE_BATCH : return 'batches';
			case EATPL_ITEM_TYPE_SEQUENCE : return 'sequences';
			case EATPL_ITEM_TYPE_ACTIVITY : return 'activities';
			case EATPL_ITEM_TYPE_CERTIFICATE : return 'certificates';
		}
	} else {
		switch($type) {
			case EATPL_ITEM_TYPE_PATH : return 'path';
			case EATPL_ITEM_TYPE_BATCH : return 'batch';
			case EATPL_ITEM_TYPE_SEQUENCE : return 'sequence';
			case EATPL_ITEM_TYPE_ACTIVITY : return 'activity';
			case EATPL_ITEM_TYPE_CERTIFICATE : return 'certificate';
		}
	}
}

// Get child type

function trainingpath_item_child_type($type) {
	switch($type) {
		case EATPL_ITEM_TYPE_PATH : return EATPL_ITEM_TYPE_CERTIFICATE;
		case EATPL_ITEM_TYPE_CERTIFICATE : return EATPL_ITEM_TYPE_SEQUENCE;
		case EATPL_ITEM_TYPE_BATCH : return EATPL_ITEM_TYPE_SEQUENCE;
		case EATPL_ITEM_TYPE_SEQUENCE : return EATPL_ITEM_TYPE_ACTIVITY;
		case EATPL_ITEM_TYPE_ACTIVITY : return null;
	}
}

// Activity types

define('EATPL_ACTIVITY_TYPE_CONTENT', 1);
define('EATPL_ACTIVITY_TYPE_EVAL', 2);
define('EATPL_ACTIVITY_TYPE_CLASSROOM', 3);
define('EATPL_ACTIVITY_TYPE_VIRTUAL', 4);
define('EATPL_ACTIVITY_TYPE_FILES', 5);
define('EATPL_ACTIVITY_TYPE_RICHTEXT', 6);

// Activity type names

function trainingpath_activity_type_name($type) {
	switch($type) {
		case EATPL_ACTIVITY_TYPE_CONTENT : return 'content';
		case EATPL_ACTIVITY_TYPE_EVAL : return 'eval';
		case EATPL_ACTIVITY_TYPE_CLASSROOM : return 'classroom';
		case EATPL_ACTIVITY_TYPE_VIRTUAL : return 'virtual';
		case EATPL_ACTIVITY_TYPE_FILES : return 'files';
		case EATPL_ACTIVITY_TYPE_RICHTEXT : return 'richtext';
	}
}

// Access modes (schedules)

define('EATPL_ACCESS_CLOSED', 0);
define('EATPL_ACCESS_OPEN', 1);
define('EATPL_ACCESS_ON_DATES', 2);
define('EATPL_ACCESS_ON_COMPLETION', 3);
define('EATPL_ACCESS_AS_REMEDIAL', 4);
define('EATPL_ACCESS_HIDDEN', 5);
define('EATPL_ACCESS_FROM_DATE', 6);
define('EATPL_ACCESS_TO_DATE', 7);

// Access select

function trainingpath_access_select($options) {
	$res = array();
	if (in_array(EATPL_ACCESS_CLOSED, $options)) $res[EATPL_ACCESS_CLOSED] = get_string('access_closed', 'trainingpath');
	if (in_array(EATPL_ACCESS_OPEN, $options)) $res[EATPL_ACCESS_OPEN] = get_string('access_open', 'trainingpath');
	if (in_array(EATPL_ACCESS_ON_DATES, $options)) $res[EATPL_ACCESS_ON_DATES] = get_string('access_between_dates', 'trainingpath');
	if (in_array(EATPL_ACCESS_FROM_DATE, $options)) $res[EATPL_ACCESS_FROM_DATE] = get_string('access_from_date', 'trainingpath');
	if (in_array(EATPL_ACCESS_TO_DATE, $options)) $res[EATPL_ACCESS_TO_DATE] = get_string('access_to_date', 'trainingpath');
	if (in_array(EATPL_ACCESS_ON_COMPLETION, $options)) $res[EATPL_ACCESS_ON_COMPLETION] = get_string('access_on_completion', 'trainingpath');
	if (in_array(EATPL_ACCESS_AS_REMEDIAL, $options)) $res[EATPL_ACCESS_AS_REMEDIAL] = get_string('access_as_remedial', 'trainingpath');
	if (in_array(EATPL_ACCESS_HIDDEN, $options)) $res[EATPL_ACCESS_HIDDEN] = get_string('access_hidden', 'trainingpath');
	return $res;
}

// Access periods (schedules)

define('EATPL_ACCESS_DAY', 0);
define('EATPL_ACCESS_MORNING', 1);  // Dont use 0 which is reserved for "no period" meaning
define('EATPL_ACCESS_AFTERNOON', 2);

// Access select

function trainingpath_access_periods_select() {
	$res = array();
	$res[EATPL_ACCESS_MORNING] = get_string('morning', 'trainingpath');
	$res[EATPL_ACCESS_AFTERNOON] = get_string('afternoon', 'trainingpath');
	return $res;
}


/*************************************************************************************************
 *                                             Permissions check                                         
 *************************************************************************************************/


function trainingpath_check_view_permission_or_redirect($course, $cm, $batch = null, $sequence = null, $via = 'batches', $item = null) {
	global $DB;
	
	// Learning path level
    $access = trainingpath_check_view_permission($course, $cm);
	if ($access->permission != 'view') return $access;
    if (isset($access->schedule)) {
        $scheduleInfo = trainingpath_get_schedule_access_info(EATPL_ITEM_TYPE_PATH, $access->schedule, false, $item);
        if ($scheduleInfo->status != 'open') redirect(new moodle_url('/mod/trainingpath/view.php', array('id'=>$cm->id)));
    }
	// Batch level
	if (isset($batch)) {
		$schedule = $DB->get_record('trainingpath_schedule', array('cmid'=>$cm->id, 'context_id'=>$batch->id, 'context_type'=>EATPL_ITEM_TYPE_BATCH, 'group_id'=>$access->group_id));
		if ($schedule) {
			$scheduleInfo = trainingpath_get_schedule_access_info(EATPL_ITEM_TYPE_BATCH, $schedule, false, $item);
			if ($scheduleInfo->status != 'open') redirect(new moodle_url('/mod/trainingpath/view/batches.php', array('cmid'=>$cm->id)));
		}
	}
	// Sequence level
	if (isset($sequence)) {
		$schedule = $DB->get_record('trainingpath_schedule', array('cmid'=>$cm->id, 'context_id'=>$sequence->id, 'context_type'=>EATPL_ITEM_TYPE_SEQUENCE, 'group_id'=>$access->group_id));
		if ($schedule) {
			$scheduleInfo = trainingpath_get_schedule_access_info(EATPL_ITEM_TYPE_SEQUENCE, $schedule, false, $item);
			if ($scheduleInfo->status != 'open') redirect(new moodle_url('/mod/trainingpath/view/sequences.php', array('cmid'=>$cm->id, 'batch_id'=>$batch->id, 'via'=>$via)));
		}
	}
	// Activity level
	if (isset($item)) {
		$schedule = $DB->get_record('trainingpath_schedule', array('cmid'=>$cm->id, 'context_id'=>$item->id, 'context_type'=>EATPL_ITEM_TYPE_ACTIVITY, 'group_id'=>$access->group_id));
		if ($schedule) {
			$scheduleInfo = trainingpath_get_schedule_access_info(EATPL_ITEM_TYPE_ACTIVITY, $schedule, false, $item);
			if ($scheduleInfo->status != 'open') redirect(new moodle_url('/mod/trainingpath/view/activities.php', array('cmid'=>$cm->id, 'sequence_id'=>$sequence->id, 'via'=>$via)));
		}
	}
	return $scheduleInfo; 
}

function trainingpath_check_view_permission($course, $cm) {
	global $DB;
	
	// e-ATPL edit permission
	if (has_capability('mod/trainingpath:addinstance', context_module::instance($cm->id)))
		return (object)array('permission'=>'addinstance', 'status'=>'open');

	// e-ATPL schedule permission
	if (has_capability('mod/trainingpath:editschedule', context_module::instance($cm->id)))
		return (object)array('permission'=>'editschedule', 'status'=>'open');

	// User must belong to a group
	$groups = trainingpath_get_groups($course->id);
	if (count($groups) == 0) trainingpath_print_error('permission_denied_view_no_group', $course);
	
	// The group must have an associated eATPL schedule (get the first if many)
	foreach($groups as $groupid) {
		$schedule = $DB->get_record('trainingpath_schedule', array('cmid'=>$cm->id, 'context_type'=>EATPL_ITEM_TYPE_PATH, 'group_id'=>$groupid));
		if ($schedule) break;
	}
	if (!$schedule) trainingpath_print_error('permission_denied_view_no_schedule', $course);
	
	// Check schedule access
	switch ($schedule->access) {
		case EATPL_ACCESS_CLOSED :
		case EATPL_ACCESS_OPEN :
		case EATPL_ACCESS_ON_DATES :
		case EATPL_ACCESS_ON_COMPLETION :
		case EATPL_ACCESS_AS_REMEDIAL :
			return (object)array('permission'=>'view', 'schedule'=>$schedule, 'group_id'=>$groupid);
			break;
		case EATPL_ACCESS_HIDDEN :
			trainingpath_print_error('permission_denied_view_hidden', $course);
			break;
	}
}


/*************************************************************************************************
 *                                             Scheduling                                         
 *************************************************************************************************/

 
function trainingpath_get_schedule_access_info($type, $schedule, $withIcon = false, $item = null) {
	switch ($schedule->access) {
		case EATPL_ACCESS_CLOSED :
			$schedule->display = get_string('access_currently_closed', 'trainingpath');
			$schedule->status = 'closed';
			break;
		case EATPL_ACCESS_OPEN :
			$schedule->display = get_string('access_currently_open', 'trainingpath');
			$schedule->status = 'open';
			break;
		case EATPL_ACCESS_ON_DATES :
			$schedule->status = trainingpath_get_dates_access_status($schedule, $type);
			$schedule->display = trainingpath_get_dates_access_info($schedule, $type);
			break;
		case EATPL_ACCESS_ON_COMPLETION :
			$schedule->status = trainingpath_get_completion_access_status($item);
			$schedule->display = trainingpath_get_completion_access_info($schedule->status);
			break;
		case EATPL_ACCESS_AS_REMEDIAL :
			$schedule->status = trainingpath_get_remedial_access_status($item);
			$schedule->display = trainingpath_get_remedial_access_info($schedule->status);
			break;
		case EATPL_ACCESS_HIDDEN :
			$schedule->display = get_string('access_currently_hidden', 'trainingpath');
			$schedule->status = 'hidden';
			break;
	}
	if ($withIcon) $schedule->display = trainingpath_text_icon($schedule->display, 'schedule');
	return $schedule;
}

function trainingpath_get_default_schedule_access_info($type, $withIcon = false, $item = null, $activityType = null, $complementary = false, $remedial = false) {
	$schedule = trainingpath_get_default_schedule($type, $activityType, $complementary, $remedial);
	return trainingpath_get_schedule_access_info($type, $schedule, $withIcon, $item);
}

function trainingpath_get_default_schedule($type, $activityType = null, $complementary = false, $remedial = false) {
	$schedule = new stdClass();
	switch($type) {
		case EATPL_ITEM_TYPE_PATH :
			$schedule->access = EATPL_ACCESS_CLOSED;
			break;
		case EATPL_ITEM_TYPE_BATCH :
		case EATPL_ITEM_TYPE_SEQUENCE :
			$schedule->access = EATPL_ACCESS_OPEN;
			break;
		case EATPL_ITEM_TYPE_ACTIVITY :
			$schedule = trainingpath_get_default_activity_schedule($activityType, $complementary, $remedial);
			break;
	}
	return $schedule;
}

function trainingpath_get_default_activity_schedule($type, $complementary = false, $remedial = false) {
	$schedule = new stdClass();
	switch($type) {
		case EATPL_ACTIVITY_TYPE_CONTENT :
			$schedule->access = get_config('trainingpath', 'prefered_activity_access');
			break;
		case EATPL_ACTIVITY_TYPE_EVAL :
			if ($remedial) $schedule->access = EATPL_ACCESS_AS_REMEDIAL;
			else $schedule->access = get_config('trainingpath', 'prefered_activity_access');
			break;
		case EATPL_ACTIVITY_TYPE_CLASSROOM :
		case EATPL_ACTIVITY_TYPE_VIRTUAL :
			$schedule->access = EATPL_ACCESS_CLOSED;
			break;
		case EATPL_ACTIVITY_TYPE_FILES :
		case EATPL_ACTIVITY_TYPE_RICHTEXT :
			$schedule->access = EATPL_ACCESS_HIDDEN;
			break;
	}
	if ($complementary) $schedule->access = EATPL_ACCESS_HIDDEN;
	return $schedule;
}

function trainingpath_get_dates_access_times($schedule, $type) {
	$schedule->time_open ? $timeOpen = $schedule->time_open : $timeOpen = false;
	$schedule->time_close ? $timeClose = $schedule->time_close : $timeClose = false;
	switch($type) {
		case EATPL_ITEM_TYPE_PATH :
		case EATPL_ITEM_TYPE_BATCH :
			if ($schedule->time_close) $timeClose += (24 * 60 * 60);
			break;
		case EATPL_ITEM_TYPE_SEQUENCE :
			if ($schedule->period_open == EATPL_ACCESS_AFTERNOON && $schedule->time_open) $timeOpen += (12 * 60 * 60);
			if ($schedule->time_close) {
				if ($schedule->period_close == EATPL_ACCESS_MORNING) $timeClose += (12 * 60 * 60);
				else $timeClose += (24 * 60 * 60);
			}
			break;
		case EATPL_ITEM_TYPE_ACTIVITY :
			break;
	}
	return array($timeOpen, $timeClose);
}

function trainingpath_get_dates_access_status($schedule, $type) {
	$timenow = time();
	list($timeOpen, $timeClose) = trainingpath_get_dates_access_times($schedule, $type);
	if ($timeOpen && $timeOpen > $timenow) return 'closed';
	if ($timeClose && $timenow > $timeClose) return 'closed';
	return 'open';
}

function trainingpath_get_dates_access_info($schedule, $type) {
	global $DB, $USER;
	
	// Timezone
	$user = $DB->get_record('user', array('id'=>$USER->id));
	$timezone = $user->timezone;
	
	// Format
	switch($type) {
		case EATPL_ITEM_TYPE_PATH :
		case EATPL_ITEM_TYPE_BATCH :
			$format = 'strftimedate';
			break;
		case EATPL_ITEM_TYPE_SEQUENCE :
		case EATPL_ITEM_TYPE_ACTIVITY :
			$format = 'strftimedatetime';
			break;
	}
	
	// Times
	list($timeOpen, $timeClose) = trainingpath_get_dates_access_times($schedule, $type);
	if ($timeClose) $timeClose -= 60;
	
	if ($timeOpen && $timeClose) {
		$dates = new stdClass();
		$dates->from = userdate($timeOpen, get_string($format, 'langconfig'), $timezone);
		$dates->to = userdate($timeClose, get_string($format, 'langconfig'), $timezone);
		return get_string('access_from_to', 'trainingpath', $dates);
	} else if ($timeOpen) {
		$from = userdate($timeOpen, get_string($format, 'langconfig'), $timezone);
		return get_string('access_from', 'trainingpath', $from);
	} else if ($timeClose) {
		$to = userdate($timeClose, get_string($format, 'langconfig'), $timezone);
		return get_string('access_to', 'trainingpath', $to);
	}
}

function trainingpath_get_completion_access_status($item) {
	global $DB, $USER;
	if (!$item->previous_id) return 'open';
	$previous = $DB->get_record('trainingpath_item', array('id'=>$item->previous_id));
	$tracks = $DB->get_records('trainingpath_tracks', array('context_id'=>$previous->id, 'context_type'=>$previous->type, 'user_id'=>$USER->id));
	if (!$tracks or empty($tracks)) return 'closed';
	$tracks = array_values($tracks);
	$last = $tracks[count($tracks)-1];
	if ($last->completion == EATPL_COMPLETION_COMPLETED && $last->time_status != EATPL_TIME_STATUS_CRITICAL) return 'open';
	return 'closed';  
}

function trainingpath_get_completion_access_info($status) {
	if ($status == 'open') return get_string('access_open_completion', 'trainingpath');
	else return get_string('access_closed_completion', 'trainingpath');
}

function trainingpath_get_remedial_access_status($item) {
	global $DB, $USER;
	if (!$item->previous_id) return 'hidden';
	$previous = $DB->get_record('trainingpath_item', array('id'=>$item->previous_id));
	$tracks = $DB->get_records('trainingpath_tracks', array('context_id'=>$previous->id, 'context_type'=>$previous->type, 'user_id'=>$USER->id));
	if (!$tracks or empty($tracks)) return 'hidden';
	$tracks = array_values($tracks);
	$last = $tracks[count($tracks)-1];
	if ($last->success == EATPL_SUCCESS_FAILED) return 'open';
	return 'hidden';  
}

function trainingpath_get_remedial_access_info($status) {
	if ($status == 'open') return get_string('access_open_remedial', 'trainingpath');
	else return get_string('access_closed_remedial', 'trainingpath');
}

function trainingpath_check_edit_permission($course, $cm) {
	
	// e-ATPL edit permission
	if (has_capability('mod/trainingpath:addinstance', context_module::instance($cm->id))) return 'addinstance';

	// No permission at all
	if (!has_capability('mod/trainingpath:editschedule', context_module::instance($cm->id))) {
		print_error('permission_denied_edit_schedule', 'trainingpath');
	}
		
	// Scheduling permission: check if belongs to a group
	if (count(trainingpath_get_groups($course->id)) > 0) return 'editschedule';

	// No group assigned
	trainingpath_print_error('permission_denied_edit_schedule_no_group', $course);
}
	
function trainingpath_check_tutor_permission($course, $cm, $groupId = null) {
	$permission = trainingpath_check_edit_permission($course, $cm);
	if (!isset($groupId) || !$groupId) return $permission;
	$allowedGroupIds = trainingpath_get_groups($course->id, $permission);
	if (!in_array($groupId, $allowedGroupIds)) trainingpath_print_error('permission_denied_tutor_group_not_allowed', $course);
	return $permission;
}

function trainingpath_get_groups($courseid, $permission = 'editschedule', $userId = null) {
	$res = array();
	if ($permission == 'editschedule') {
		$groupings = groups_get_user_groups($courseid, $userId);
		foreach($groupings as $grouping) {
			foreach($grouping as $group) {
				$res[] =$group;
			}
		}
	} else if ($permission == 'addinstance') {
		$groups = groups_get_all_groups($courseid);
		foreach($groups as $group) {
			$res[] =$group->id;
		}
	}
	return $res;
}

function trainingpath_get_scheduled_group_id($course, $cm, $userId = null) {
	global $DB;
	
	// Returns 0 for managers and tutors because they can not be associated with a single group
	if (has_capability('mod/trainingpath:addinstance', context_module::instance($cm->id), $userId)) return 0;
	if (has_capability('mod/trainingpath:editschedule', context_module::instance($cm->id), $userId)) return 0;

	// For learners
	$groups = trainingpath_get_groups($course->id, 'editschedule', $userId);
	foreach($groups as $group) {
		$schedule = $DB->get_record('trainingpath_schedule', array('cmid'=>$cm->id, 'context_type'=>EATPL_ITEM_TYPE_PATH, 'group_id'=>$group));
		if ($schedule) return $group;
	}
	return false;
}

function trainingpath_print_error($code, $course = null) {
	global $OUTPUT, $PAGE;
    if (isset($course)) $PAGE->set_heading($course->fullname);
    echo $OUTPUT->header();
	echo '<p>'.get_string($code, 'trainingpath').'</p>';
	echo $OUTPUT->footer();
	die;
}


/*************************************************************************************************
 *                                             Colors                                          
 *************************************************************************************************/

// Get colors config

function trainingpath_get_config_colors($prop) {
	$jcolors = get_config('trainingpath', $prop);
	$colors = json_decode($jcolors);
	if ($prop == 'time_colors') return $colors;
	usort($colors, 'trainingpath_compare_colors');
	return $colors;
}
function trainingpath_compare_colors($c1, $c2) {
	return $c1->lt > $c2->lt;
}

// Get colors config

function trainingpath_parse_colors_thresholds($thresholds_as_string) {
	if (is_array($thresholds_as_string)) {
		// In case the colors are not a string, but a JSON struct
		$thresholds = array();
		foreach($thresholds_as_string as $threshold) {
			$thresholds[] = $threshold->lt;
		}
	} else {
		// If colors are a string
		$thresholds = explode(',', $thresholds_as_string);
	}
	sort($thresholds);
	return $thresholds;
}


/*************************************************************************************************
 *                                             Files activity                                         
 *************************************************************************************************/
	
function trainingpath_files_save($data, $form, $cmid, $file_fieldname) {
	global $DB;
	// Don't save if no file
	$filename = $form->get_new_filename($file_fieldname);
	if ($filename === false) return false;
	if ($data->id == 0) $data->id = $DB->insert_record('trainingpath_files', $data);
	trainingpath_files_parse_package($data, $form, $cmid, $file_fieldname);
	$DB->update_record('trainingpath_files', $data);
	return $data->id;
}

function trainingpath_files_parse_package(&$data, $form, $cmid, $file_fieldname) {
	$itemid = $data->id;
	$filename = $form->get_new_filename($file_fieldname);
	if ($filename !== false) {
		$fs = get_file_storage();
		$context = context_module::instance($cmid);
		$modulename = 'mod_trainingpath';
		
		// Upload the new package
		$fs->delete_area_files($context->id, $modulename, 'files_package', $itemid);
		$res = $form->save_stored_file($file_fieldname, $context->id, $modulename, 'files_package', $itemid, '/', $filename);
		if ($packagefile = $fs->get_file($context->id, $modulename, 'files_package', $itemid, '/', $filename)) {
		
			// If the package is the same, do nothing
			$newhash = $packagefile->get_contenthash();
			if ($data->sha1hash == $newhash) return;
			$fs->delete_area_files($context->id, $modulename, 'files_content', $itemid);
            
            // Extract files only if it is a ZIP file. Otherwize, copy the file.
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            if ($ext == 'zip') {
                // Extract files
                $packer = get_file_packer('application/zip');
                $packagefile->extract_to_storage($packer, $context->id, $modulename, 'files_content', $itemid, '/');
				
            } else {
        		$form->save_stored_file($file_fieldname, $context->id, $modulename, 'files_content', $itemid, '/', $filename);                
				$data->launch_file = $filename;
            }
            
			// Update data for DB
			$data->reference = $filename;
			$data->revision++;
			$data->sha1hash = $newhash;
		}
	}
}


/*************************************************************************************************
 *                                             Scheduling Files                                         
 *************************************************************************************************/
	
function trainingpath_schedule_files_save(&$data, $form, $cmid, $file_fieldname) {
	global $DB;
	
	// Don't save if no file
	$filename = $form->get_new_filename($file_fieldname);
	if ($filename === false) return false;
	
	// Prepare data
	$itemid = $data->id;
	$fs = get_file_storage();
	$context = context_module::instance($cmid);
	$modulename = 'mod_trainingpath';
	
	// Upload the new package
	$fs->delete_area_files($context->id, $modulename, 'schedule_package', $itemid);
	$res = $form->save_stored_file($file_fieldname, $context->id, $modulename, 'schedule_package', $itemid, '/', $filename);
	$data->file_reference = $filename;
}


/*************************************************************************************************
 *                                             Files management                                        
 *************************************************************************************************/

require_once($CFG->libdir.'/filelib.php');

class trainingpath_content_file_info extends file_info_stored {
	
	public function get_parent() {
		if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
			return $this->browser->get_file_info($this->context);
		}
		return parent::get_parent();
	}
	
	public function get_visible_name() {
		if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
			return $this->topvisiblename;
		}
		return parent::get_visible_name();
	}
	
	public function is_empty_area() {
		if ($this->lf->get_filepath() === '/' and $this->lf->get_filename() === '.') {
			$fs = get_file_storage();
			$empty = $fs->is_area_empty($this->lf->get_contextid(), $this->lf->get_component(), $this->lf->get_filearea(), false); // Do not take into account the item id which is 0
		} else {
			$empty = false;
		}
		return $empty;
	}
}

