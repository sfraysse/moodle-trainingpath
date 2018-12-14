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

require_once('../../../config.php');
require_once($CFG->dirroot.'/mod/trainingpath/uilib.php');
require_once($CFG->dirroot.'/mod/trainingpath/ajaxlib.php');


/*************************************************************************************************
 *                                             UI                                         
 *************************************************************************************************/

 // Get calendars
 
function trainingpath_calendars_get($cmid) {
	$res = '
		<div id="trainingpath-cards" data-url="calendar_ajax.php?cmid='.$cmid.'">
			<p><img src="../pix/loading.gif" height="16" width="16"></p>
		</div>
		<div id="trainingpath-cards-confirm" class="modal fade">
			'.trainingpath_get_modal_confirm(get_string('delete_calendar', 'trainingpath'), get_string('delete_calendar_confirm', 'trainingpath')).'
		</div>
	';
	return $res;
}


/*************************************************************************************************
 *                                             Utilities                                         
 *************************************************************************************************/

// Day number

define('EATPL_DAY_SUNDAY', 0);
define('EATPL_DAY_MONDAY', 1);
define('EATPL_DAY_TUESDAY', 2);
define('EATPL_DAY_WEDNESDAY', 3);
define('EATPL_DAY_THURSDAY', 4);
define('EATPL_DAY_FRIDAY', 5);
define('EATPL_DAY_SATURDAY', 6);

// Get calendars select

function trainingpath_calendar_select($pathId) {
	global $DB;
	$res = array();
	$res[0] = get_string('none', 'trainingpath');
	$calendars = array_values($DB->get_records('trainingpath_calendar', array('path_id'=>$pathId), 'position'));
	foreach($calendars as $calendar) {
		$res[$calendar->id] = $calendar->title;
	}
	return $res;
}

// Leap year

function trainingpath_calendar_leap($year) {
	return (bool)date('L', strtotime("$year-01-01"));
}

// Day number from date

function trainingpath_calendar_day_number($dateStr) {
	
	// Day num on the date year
	$items = explode('/', $dateStr);
	$american = $items[1].'/'.$items[0].'/'.$items[2];
	$time = strtotime($american);
	$daynum = intval(date('z', $time));
	$targetYear = intval(date('Y', $time));
	
	// Current year
	$currentYear = intval(date('Y', time()));
	
	// Target year
	for ($i = $currentYear; $i < $targetYear; $i++) {
		if (trainingpath_calendar_leap($i)) $daynum += 366;
		else $daynum += 365;
	}
	return $daynum;
}

// Check yearly closed text validity

function trainingpath_calendar_yearly_closed_valid($text) {
	$lines = explode("\n", trim($text));
	foreach($lines as $line) {
		$line = trim($line);
		if (empty($line)) continue;
		$dates = explode(';', $line);
		foreach($dates as $date) {
			$date = trim($date);
			if (empty($date)) continue;
			$ranges = explode('-', $date);
			foreach($ranges as $range) {
				$range = trim($range);
				$d = DateTime::createFromFormat('d/m/Y', $range);
				$valid = ($d && $d->format('d/m/Y') === $range);
				if (!$valid) return false;
			}
		}
	}
	return true;
}

// Serialize yearly closed text

function trainingpath_calendar_get_yearly_closed($text) {
	$days = array();
	$lines = explode("\n", trim($text));
	foreach($lines as $line) {
		$line = trim($line);
		if (empty($line)) continue;
		$dates = explode(';', $line);
		foreach($dates as $date) {
			$date = trim($date);
			if (empty($date)) continue;
			$ranges = explode('-', $date);
			if (count($ranges) == 2) {
				$begin = trainingpath_calendar_day_number(trim($ranges[0]));
				$end = trainingpath_calendar_day_number(trim($ranges[1]));
				for ($i = $begin; $i <= $end; $i++) {
					$days[] = $i;
				}
			} else {
				$days[] = trainingpath_calendar_day_number(trim($ranges[0]));
			}
		}
	}
	$days = array_unique($days);
	return $days;
}

// Add weekly closed days

function trainingpath_calendar_get_weekly_closed($closedDays, $weeklyClosed, $starttime) {
	$time = $starttime;
	$startDate = date('d/m/Y', $starttime);
	$startDayNum = trainingpath_calendar_day_number($startDate);
	for ($i=$startDayNum; $i<365*3+$startDayNum; $i++) {
		$day = date('w', $time);
		if (in_array($day, $weeklyClosed)) $closedDays[] = $i;
		$time += (24*60*60);
	}
	return array_unique($closedDays);
}

// Generate schedules

function trainingpath_calendar_generate_schedules($cmid, $group_id, $sequences, $calendar, $starttime) {
	global $DB;
	
	// List closed days
	$closedDays = trainingpath_calendar_get_yearly_closed($calendar->yearly_closed);
	$weeklyClosed = explode(',', $calendar->weekly_closed);
	$closedDays = trainingpath_calendar_get_weekly_closed($closedDays, $weeklyClosed, $starttime);
	
	// Parse sequences
	$time = $starttime;
	$startDate = date('d/m/Y', $starttime);
	$dayNum = trainingpath_calendar_day_number($startDate);
	$period = EATPL_ACCESS_MORNING;
	foreach($sequences as $sequence) {
		
		// Seach for a free day to start the sequence
		while(in_array($dayNum, $closedDays)) {
			$dayNum++;
			$time += (24*60*60);
		}

		// Change the schedule
		$schedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$sequence->id, 'context_type'=>EATPL_ITEM_TYPE_SEQUENCE, 'group_id'=>$group_id));
		if (!$schedule) $schedule = (object)array('cmid'=>$cmid, 'context_type'=>$sequence->type, 'context_id'=>$sequence->id, 'group_id'=>$group_id, 'description'=>'', 'information'=>'');
		$schedule->access = EATPL_ACCESS_ON_DATES;
		$schedule->time_open = $time;
		$schedule->time_close = 0;
		$schedule->period_open = $period;
		$schedule->period_close = 0;
		if (isset($schedule->id)) $DB->update_record('trainingpath_schedule', $schedule);
		else $DB->insert_record('trainingpath_schedule', $schedule);
		
		// To be placed
		$duration = $sequence->duration / 86400.0;
		if (intval($duration) == $duration) {
			if ($period == EATPL_ACCESS_MORNING) $incr = intval($duration);
			else $incr = intval($duration);
		} else {
			if ($period == EATPL_ACCESS_MORNING) $incr = intval($duration);
			else $incr = intval($duration)+1;
		}
		$incrDone = 0;
		
		// Next day
		while($incrDone < $incr) {
			if (!in_array($dayNum, $closedDays)) {
				$incrDone++;
			}
			$dayNum++;
			$time += (24*60*60);
		}
		
		// Next period
		if (intval($duration) != $duration) {
			$period == EATPL_ACCESS_MORNING ? $period = EATPL_ACCESS_AFTERNOON : $period = EATPL_ACCESS_MORNING;
		}
	}
	
}


/*************************************************************************************************
 *                                             DB Ops on Calendars                                         
 *************************************************************************************************/


//------------------------------------------- Delete -------------------------------------------//


function trainingpath_db_delete_calendar($id) {
   global $DB, $CFG;
   
   // Test if it exists
   $calendar = $DB->get_record('trainingpath_calendar', array('id'=>$id));
   if (!$calendar) trainingpath_error_response(404);
   
   // De-assign schedules
   $schedules = $DB->get_records('trainingpath_schedule', array('calendar_id'=>$id));
   foreach($schedules as $schedule) {
	  $schedule->calendar_id = 0;
	  $DB->update_record("trainingpath_schedule", $schedule);
   }

   // Delete the calendar
   $res = $DB->delete_records('trainingpath_calendar', array('id'=>$id));
   if (!$res) trainingpath_error_response(500);
}
 
 
//------------------------------------------- Reorder -------------------------------------------//


function trainingpath_db_reorder_calendar($ids) {
	global $DB;
    $position = 1;
    foreach($ids as $id) {
        $record = $DB->get_record('trainingpath_calendar', array('id'=>$id));
        if (!$record) trainingpath_error_response(404);
        $record->position = $position;
        $res = $DB->update_record('trainingpath_calendar', $record);
	    if (!$res) trainingpath_error_response(500);
        $position++;
    }
}


/*************************************************************************************************
 *                                             JSON  Responses                                        
 *************************************************************************************************/
 

function trainingpath_json_response_edit_calendars($cmid, $pathId) {
   global $DB, $OUTPUT;
   $calendars = array_values($DB->get_records('trainingpath_calendar', array('path_id'=>$pathId), 'position'));
   
   // SF2017 - Icons
   $lang = array(
	  'no_description'=>get_string('no_description', 'trainingpath'),
	  'empty'=>get_string('no_calendar', 'trainingpath'),
   );
   $icon = array(
	  'dragdrop'=>trainingpath_get_icon('dragdrop', ''),
	  'edit'=>trainingpath_get_icon('edit', get_string('edit', 'trainingpath')),
	  'delete'=>trainingpath_get_icon('delete', get_string('delete', 'trainingpath'))
   );
   
   $url = array(
	  'edit'=>'edit.php?cmid='.$cmid.'&id='
   );
   $data = array('items'=>$calendars, 'lang'=>$lang, 'icon'=>$icon, 'url'=>$url);
   trainingpath_json_response($data);
}
 
