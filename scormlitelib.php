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

// Each module using scormlite must provide a file such as this one, providing the following functions

function trainingpath_get_activity_from_scoid($scoid) {
	global $DB;
	$item = $DB->get_record('trainingpath_item', array('ref_id'=>$scoid), '*', MUST_EXIST);
	$trainingpath = $DB->get_record('trainingpath', array('id'=>$item->path_id), '*', MUST_EXIST);
	return $trainingpath;
}

// Returns the activity completion

function trainingpath_is_activity_completed($userid, $activity) {
	return false;
}

// Returns the user grade for this activity or NULL if there is no grade to record

function trainingpath_get_grade($userid, $activity) {
	return false;
}

// Returns the grades for this activity

function trainingpath_get_grades($activity) {
	return array();	
}


