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

 
// Completion status

define('EATPL_COMPLETION_NOT_ATTEMPTED', 0);
define('EATPL_COMPLETION_INCOMPLETE', 1);
define('EATPL_COMPLETION_COMPLETED', 2);

function trainingpath_report_get_completion($completion) {
	switch($completion) {
		case 'notattempted' : return EATPL_COMPLETION_NOT_ATTEMPTED;
		case 'incomplete' : return EATPL_COMPLETION_INCOMPLETE;
		case 'completed' : return EATPL_COMPLETION_COMPLETED;
	}
}

function trainingpath_report_get_completion_class($completion) {
	switch($completion) {
		case EATPL_COMPLETION_NOT_ATTEMPTED : return 'notattempted';
		case EATPL_COMPLETION_INCOMPLETE : return 'incomplete';
		case EATPL_COMPLETION_COMPLETED : return 'completed';
	}
}

// Success status

define('EATPL_SUCCESS_UNKNOWN', 0);
define('EATPL_SUCCESS_PASSED', 1);
define('EATPL_SUCCESS_FAILED', 2);

function trainingpath_report_get_success($status) {
	switch($status) {
		case 'unknown' : return EATPL_SUCCESS_UNKNOWN;
		case 'passed' : return EATPL_SUCCESS_PASSED;
		case 'failed' : return EATPL_SUCCESS_FAILED;
	}
}

function trainingpath_report_get_success_class($status) {
	switch($status) {
		case EATPL_SUCCESS_UNKNOWN : return 'unknown';
		case EATPL_SUCCESS_PASSED : return 'passed';
		case EATPL_SUCCESS_FAILED : return 'failed';
	}
}

function trainingpath_report_get_score_class($score, $colors) {
	usort($colors, 'trainingpath_report_get_score_color_cmp');
	$level = 1;
	foreach($colors as $item) {
		if (floatval($score) < floatval($item->lt)) break;
		$level++;
	}
	switch($level) {
		case 1 : return 'critical';
		case 2 : return 'minimal';
		case 3 : return 'nominal';
		case 4 : return 'optimal';
	}
}
function trainingpath_report_get_score_color($score, $colors) {
	usort($colors, 'trainingpath_report_get_score_color_cmp');
	foreach($colors as $item) {
		if (floatval($score) < floatval($item->lt)) break;
	}
	return $item->color;
}
function trainingpath_report_get_score_color_cmp($a, $b) { return ($a->lt < $b->lt) ? -1 : 1; }


// Time status

define('EATPL_TIME_STATUS_CRITICAL', 0);
define('EATPL_TIME_STATUS_MINIMAL', 1);
define('EATPL_TIME_STATUS_NOMINAL', 2);
define('EATPL_TIME_STATUS_OPTIMAL', 3);

function trainingpath_report_get_time_status($time, $minimum, $nominal = null, $learningpath = null) {
	if ($time < $minimum) return EATPL_TIME_STATUS_CRITICAL;
	if (!isset($nominal)) return EATPL_TIME_STATUS_OPTIMAL;
	if ($time < $nominal) return EATPL_TIME_STATUS_MINIMAL;
	if (!isset($learningpath)) {
		$config = json_decode(get_config('trainingpath')->time_colors);
		$threshold = $config->threshold;
	} else {
		$threshold = $learningpath->time_optimum_threshold;
	}
	$optimal = $nominal + ($threshold * $nominal / 100);
	if ($time < $optimal) return EATPL_TIME_STATUS_NOMINAL;
	return EATPL_TIME_STATUS_OPTIMAL;
}

function trainingpath_report_get_time_status_class($status) {
	switch($status) {
		case EATPL_TIME_STATUS_CRITICAL : return 'critical';
		case EATPL_TIME_STATUS_MINIMAL : return 'minimal';
		case EATPL_TIME_STATUS_NOMINAL : return 'nominal';
		case EATPL_TIME_STATUS_OPTIMAL : return 'optimal';
	}
}

function trainingpath_report_get_time_status_color($status) {
	$config = json_decode(get_config('trainingpath')->time_colors);
	switch($status) {
		case EATPL_TIME_STATUS_CRITICAL : return $config->lt_min;
		case EATPL_TIME_STATUS_MINIMAL : return $config->lt_nominal;
		case EATPL_TIME_STATUS_NOMINAL : return $config->lt_threshold;
		case EATPL_TIME_STATUS_OPTIMAL : return $config->lt_max;
	}
}

function trainingpath_report_get_worst_time_status($status1, $status2) {
	return min($status1, $status2);
}

function trainingpath_report_get_best_time_status($status1, $status2) {
	return max($status1, $status2);
}


// Progress units

define('EATPL_PROGRESS_UNIT_PERCENT', 0);
define('EATPL_PROGRESS_UNIT_COUNT', 1);
define('EATPL_PROGRESS_UNIT_STATUS', 2);


/*************************************************************************************************
 *                                             Tracking                                        
 *************************************************************************************************/
 
// Force content completion

function trainingpath_report_force_content_completion($learningpath, $userId, $force, $item) {
	global $DB;
	if (!$force) return;
	
	// Get existing track
	$currentTrack = $DB->get_record('trainingpath_tracks', array('context_id'=>$item->id, 'context_type'=>EATPL_ITEM_TYPE_ACTIVITY, 'user_id'=>$userId));
	if (!$currentTrack) $currentTrack = new stdClass();
	
	// Forced time
	$forced_time = isset($item->duration_down) && $item->duration_down ? $item->duration_down : $item->duration;
	
	// Update track
	$currentTrack->context_id = $item->id;
	$currentTrack->context_type = EATPL_ITEM_TYPE_ACTIVITY;
	$currentTrack->user_id = $userId;
	$currentTrack->attempt = 1;
	$currentTrack->completion = EATPL_COMPLETION_COMPLETED;
	if (!isset($currentTrack->time_spent)) $currentTrack->time_spent = $forced_time;
	$currentTrack->time_status = EATPL_TIME_STATUS_OPTIMAL;
	$currentTrack->last_attempt = true;
	$currentTrack->progress_unit = EATPL_PROGRESS_UNIT_STATUS;
	$currentTrack->progress_value = $currentTrack->completion;
	$currentTrack->time_passing = $item->duration;

	// Save track
	if (!isset($currentTrack->id)) $currentTrack->id = $DB->insert_record("trainingpath_tracks", $currentTrack);
	else $DB->update_record('trainingpath_tracks', $currentTrack);
	
	// Update ScormLite tracks
	$DB->delete_records('scormlite_scoes_track', array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>1));
	$scoTrack = (object)array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>1, 'element'=>'x.start.time', 'value'=>time(), 'timemodified'=>time());
	$DB->insert_record("scormlite_scoes_track", $scoTrack);
	$scoTrack = (object)array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>1, 'element'=>'cmi.total_time', 'value'=>trainingpath_report_get_time_scorm2004($forced_time), 'timemodified'=>time());
	$DB->insert_record("scormlite_scoes_track", $scoTrack);
	$scoTrack = (object)array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>1, 'element'=>'cmi.completion_status', 'value'=>'completed', 'timemodified'=>time());
	$DB->insert_record("scormlite_scoes_track", $scoTrack);
	$scoTrack = (object)array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>1, 'element'=>'cmi.exit', 'value'=>'suspend', 'timemodified'=>time());
	$DB->insert_record("scormlite_scoes_track", $scoTrack);
	
	// Rollup
	trainingpath_report_rollup_track($learningpath, $item->parent_id, EATPL_ITEM_TYPE_SEQUENCE, $userId);
}

// Force eval completion

function trainingpath_report_force_eval_score($learningpath, $userId, $score, $item) {
	global $DB;
	if ($score == '' || $score != intval($score) || $score < 0 || $score > 100) return;
	
	// Get activity and sco
	$sco = $DB->get_record('scormlite_scoes', array('id'=>$item->ref_id));

	// Get existing track
	$currentTracks = array_values($DB->get_records('trainingpath_tracks', array('context_id'=>$item->id, 'context_type'=>EATPL_ITEM_TYPE_ACTIVITY, 'user_id'=>$userId)));
	if (count($currentTracks) == 0) $currentTrack = new stdClass();
	else $currentTrack = $currentTracks[count($currentTracks)-1];
	
	// Update track
	$currentTrack->context_id = $item->id;
	$currentTrack->context_type = EATPL_ITEM_TYPE_ACTIVITY;
	$currentTrack->user_id = $userId;
	if (!isset($currentTrack->attempt)) $currentTrack->attempt = 1;
	$currentTrack->completion = EATPL_COMPLETION_COMPLETED;
	$currentTrack->time_spent = 0;
	$currentTrack->time_status = EATPL_TIME_STATUS_OPTIMAL;
	$currentTrack->last_attempt = true;
	$currentTrack->progress_unit = EATPL_PROGRESS_UNIT_STATUS;
	$currentTrack->progress_value = $currentTrack->completion;
	$currentTrack->time_passing = $item->duration;
	if (!$item->remedial) {
		$currentTrack->score = $score;
		if ($score >= $sco->passingscore) {
			$currentTrack->success = EATPL_SUCCESS_PASSED;
			$success = 'passed';
		} else {
			$currentTrack->success = EATPL_SUCCESS_FAILED;
			$success = 'failed';
		}
		
	} else {
		$currentTrack->score_remedial = $score;
		if ($score >= $sco->passingscore) {
			$currentTrack->success_remedial = EATPL_SUCCESS_PASSED;
			$success = 'passed';
		} else {
			$currentTrack->success_remedial = EATPL_SUCCESS_FAILED;			
			$success = 'failed';
		}
	}

	// Save track
	if (!isset($currentTrack->id)) $currentTrack->id = $DB->insert_record("trainingpath_tracks", $currentTrack);
	else $DB->update_record('trainingpath_tracks', $currentTrack);
	
	// Update ScormLite tracks
	$DB->delete_records('scormlite_scoes_track', array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>$currentTrack->attempt));
	$scoTrack = (object)array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>$currentTrack->attempt, 'element'=>'x.start.time', 'value'=>time(), 'timemodified'=>time());
	$DB->insert_record("scormlite_scoes_track", $scoTrack);
	$scoTrack = (object)array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>$currentTrack->attempt, 'element'=>'cmi.total_time', 'value'=>'PT00H00M00S', 'timemodified'=>time());
	$DB->insert_record("scormlite_scoes_track", $scoTrack);
	$scoTrack = (object)array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>$currentTrack->attempt, 'element'=>'cmi.completion_status', 'value'=>'completed', 'timemodified'=>time());
	$DB->insert_record("scormlite_scoes_track", $scoTrack);
	$scoTrack = (object)array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>$currentTrack->attempt, 'element'=>'cmi.exit', 'value'=>'suspend', 'timemodified'=>time());
	$DB->insert_record("scormlite_scoes_track", $scoTrack);
	$scoTrack = (object)array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>$currentTrack->attempt, 'element'=>'cmi.success_status', 'value'=>$success, 'timemodified'=>time());
	$DB->insert_record("scormlite_scoes_track", $scoTrack);
	$scoTrack = (object)array('userid'=>$userId, 'scoid'=>$item->ref_id, 'attempt'=>$currentTrack->attempt, 'element'=>'cmi.score.scaled', 'value'=>$score/100, 'timemodified'=>time());
	$DB->insert_record("scormlite_scoes_track", $scoTrack);
	
	// Rollup
	trainingpath_report_rollup_track($learningpath, $item->parent_id, EATPL_ITEM_TYPE_SEQUENCE, $userId);
}

// Record session track

function trainingpath_report_record_session_track($learningpath, $userId, $participation, $item, $schedule) {
	global $DB;

	// Get existing track
	$currentTrack = $DB->get_record('trainingpath_tracks', array('context_id'=>$item->id, 'context_type'=>EATPL_ITEM_TYPE_ACTIVITY, 'user_id'=>$userId));
	if (!$currentTrack) $currentTrack = new stdClass();
	
	// Update track
	$currentTrack->context_id = $item->id;
	$currentTrack->context_type = EATPL_ITEM_TYPE_ACTIVITY;
	$currentTrack->user_id = $userId;
	$currentTrack->attempt = 1;
	$currentTrack->time_passing = $item->duration;
	if ($participation) {
		$currentTrack->completion = EATPL_COMPLETION_COMPLETED;
		$currentTrack->time_spent = $schedule->duration;
		$currentTrack->time_status = trainingpath_report_get_time_status($currentTrack->time_spent, $currentTrack->time_passing, null, $learningpath);
	} else {
		$currentTrack->completion = EATPL_COMPLETION_NOT_ATTEMPTED;
		$currentTrack->time_spent = 0;
		$currentTrack->time_status = EATPL_TIME_STATUS_CRITICAL;
	}
	$currentTrack->last_attempt = true;
	$currentTrack->progress_unit = EATPL_PROGRESS_UNIT_STATUS;
	$currentTrack->progress_value = $currentTrack->completion;

	// Save track
	if (!isset($currentTrack->id)) $currentTrack->id = $DB->insert_record("trainingpath_tracks", $currentTrack);
	else $DB->update_record('trainingpath_tracks', $currentTrack);
	
	// Rollup
	trainingpath_report_rollup_track($learningpath, $item->parent_id, EATPL_ITEM_TYPE_SEQUENCE, $userId);
}

// Record ScormLite track

function trainingpath_report_record_scormlite_track($trackdata, $item, $learningpath, $eval = false) {
	global $DB;
	
	if ($trackdata->status == 'notattempted') return;

	// Use transaction !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	
	// Get existing track
	$currentTrack = $DB->get_record('trainingpath_tracks', array('context_id'=>$item->id, 'context_type'=>EATPL_ITEM_TYPE_ACTIVITY, 'user_id'=>$trackdata->userid, 'attempt'=>$trackdata->attemptnb));
	if (!$currentTrack) $currentTrack = new stdClass();
	
	// Update track
	$currentTrack->context_id = $item->id;
	$currentTrack->context_type = EATPL_ITEM_TYPE_ACTIVITY;
	$currentTrack->user_id = $trackdata->userid;
	$currentTrack->attempt = 1;
	$currentTrack->completion = trainingpath_report_get_completion($trackdata->completion_status);
	$currentTrack->success = EATPL_SUCCESS_UNKNOWN;
	$currentTrack->success_remedial = EATPL_SUCCESS_UNKNOWN;
	$currentTrack->last_attempt = true;
	$currentTrack->progress_unit = EATPL_PROGRESS_UNIT_STATUS;
	/* NOT APPLICABLE AT THE ACTIVITY LEVEL
	$currentTrack->progress_value
	$currentTrack->progress_max
	*/
	
	// Eval or not
	if ($eval) {
		if (!trainingpath_report_must_record_scormlite_track($trackdata, $item)) return;
		$newScore = intval($trackdata->score_scaled * 100);
		$currentTrack->attempt = $trackdata->attempt;
		if ($item->remedial) {
			$currentTrack->success_remedial = trainingpath_report_get_success($trackdata->success_status);
			$currentTrack->score_remedial = $newScore;
		} else {
			$currentTrack->success = trainingpath_report_get_success($trackdata->success_status);
			$currentTrack->score = $newScore;
		}
	} else {
		$seconds = trainingpath_report_get_time_seconds($trackdata->total_time);
		if ($seconds > $item->duration * $learningpath->time_max_factor) $seconds = $item->duration * $learningpath->time_max_factor;
		$currentTrack->time_spent = $seconds;
		$currentTrack->time_passing = $item->duration;
		$currentTrack->time_status = trainingpath_report_get_time_status($currentTrack->time_spent, $currentTrack->time_passing, null, $learningpath);
	}

	// Save track
	if (!isset($currentTrack->id)) $currentTrack->id = $DB->insert_record("trainingpath_tracks", $currentTrack);
	else $DB->update_record('trainingpath_tracks', $currentTrack);
	
	// Get and update previous track
	if ($eval && $trackdata->attemptnb > 1) {
		$previousTrack = $DB->get_record('trainingpath_tracks', array('context_id'=>$item->id, 'context_type'=>EATPL_ITEM_TYPE_ACTIVITY, 'user_id'=>$trackdata->userid, 'attempt'=>$trackdata->attemptnb-1));
		if ($previousTrack && $previousTrack->last_attempt) {
			$previousTrack->last_attempt = false;
			$DB->update_record('trainingpath_tracks', $previousTrack);
		}
	}
	
	// Rollup
	trainingpath_report_rollup_track($learningpath, $item->parent_id);
}

function trainingpath_report_must_record_scormlite_track($trackdata, $item) {
	global $DB;
	$sco = $DB->get_record('scormlite_scoes', array('id'=>$item->ref_id));
	if ($trackdata->attempt == 1) return true;
	if ($sco->whatgrade == 2) return true;
	if ($sco->whatgrade == 1) return false;
	$newScore = intval($trackdata->score_scaled * 100);
	$previousTrack = $DB->get_record('trainingpath_tracks', array('context_id'=>$item->id, 'context_type'=>EATPL_ITEM_TYPE_ACTIVITY, 'user_id'=>$trackdata->userid, 'attempt'=>$trackdata->attemptnb-1));
	if (!$previousTrack) return true;
	if ($item->remedial) return ($newScore > $previousTrack->score_remedial);
	return ($newScore > $previousTrack->score);
}

// Rollup

function trainingpath_report_rollup_track($learningpath, $itemId, $itemType = EATPL_ITEM_TYPE_SEQUENCE, $userId = null, $rollup = true) {
	global $DB, $USER;
	
	// Select user
	if (!isset($userId)) $userId = $USER->id;
	
	// Get item
	$item = $DB->get_record('trainingpath_item', array('id'=>$itemId, 'type'=>$itemType));
	
	// Get track
	$currentTrack = $DB->get_record('trainingpath_tracks', array('context_id'=>$itemId, 'context_type'=>$itemType, 'user_id'=>$userId));
	if (!$currentTrack) $currentTrack = new stdClass();

	// Init track: default values
	$currentTrack->context_id = $itemId;
	$currentTrack->context_type = $itemType;
	$currentTrack->user_id = $userId;
	$currentTrack->attempt = 1;
	$currentTrack->last_attempt = true;
	$currentTrack->completion = EATPL_COMPLETION_NOT_ATTEMPTED;
	$currentTrack->success = EATPL_SUCCESS_UNKNOWN;
	$currentTrack->success_remedial = EATPL_SUCCESS_UNKNOWN;
	$currentTrack->time_spent = 0;
	$currentTrack->time_status = EATPL_TIME_STATUS_CRITICAL;
	$currentTrack->progress_unit = EATPL_PROGRESS_UNIT_PERCENT;
	$currentTrack->progress_max = 0;
	$currentTrack->progress_value = 0;

	// Get children
	$children = trainingpath_report_get_items_and_tracks($itemId, $itemType, $userId);
	
	// Progress unit & max
	if ($itemType == EATPL_ITEM_TYPE_SEQUENCE) {
		$currentTrack->progress_unit = EATPL_PROGRESS_UNIT_COUNT;
		$currentTrack->progress_max = count($children);
	}
	$remedialFound = false;

	// Score average
	$score_total = 0;
	$score_count = 0;
	$score_remedial_total = 0;
	$score_remedial_count = 0;
	
	// Time
	if ($itemType == EATPL_ITEM_TYPE_SEQUENCE) $currentTrack->time_passing = $item->duration_down;
	else if ($itemType == EATPL_ITEM_TYPE_CERTIFICATE) $currentTrack->time_passing = $item->duration;
	else if ($itemType == EATPL_ITEM_TYPE_PATH || $itemType == EATPL_ITEM_TYPE_BATCH) $currentTrack->time_status = EATPL_TIME_STATUS_OPTIMAL;

	// Parse children
	foreach($children as $child) {
		
		if (isset($child->track)) {
			
			// Progress
			if ($itemType == EATPL_ITEM_TYPE_SEQUENCE) {
				if ($child->track->completion == EATPL_COMPLETION_COMPLETED) $currentTrack->progress_value++;
			} else if ($itemType == EATPL_ITEM_TYPE_BATCH || $itemType == EATPL_ITEM_TYPE_CERTIFICATE) {
				$currentTrack->progress_value += $child->track->progress_value;
				$currentTrack->progress_max += $child->track->progress_max;
			} else if ($itemType == EATPL_ITEM_TYPE_PATH) {
				$currentTrack->progress_max += $child->duration;
				$currentTrack->progress_value += $child->duration * $child->track->progress_value / 100;
			}
	
			// Time
			if ($itemType == EATPL_ITEM_TYPE_SEQUENCE) {
				if (!$item->complementary) $currentTrack->time_spent += $child->track->time_spent;
			} else {
				$currentTrack->time_spent += $child->track->time_spent;
			}
			if ($itemType == EATPL_ITEM_TYPE_PATH || $itemType == EATPL_ITEM_TYPE_BATCH) {
				$currentTrack->time_status = trainingpath_report_get_worst_time_status($currentTrack->time_status, $child->track->time_status);
			}
			
			// Score and success
			if ($itemType == EATPL_ITEM_TYPE_SEQUENCE) {
				// Keep the last one
				if ($child->track->success != EATPL_SUCCESS_UNKNOWN) {
					$currentTrack->success = $child->track->success;
					$currentTrack->score = $child->track->score;
				} else if ($child->track->success_remedial != EATPL_SUCCESS_UNKNOWN) {
					$remedialFound = true;
					$currentTrack->success_remedial = $child->track->success_remedial;
					$currentTrack->score_remedial = $child->track->score_remedial;
				}
			} else if ($itemType == EATPL_ITEM_TYPE_CERTIFICATE || $itemType == EATPL_ITEM_TYPE_PATH) {
				if ($child->track->success != EATPL_SUCCESS_UNKNOWN) {
					$score_total += $child->track->score;
					$score_count++;
				}
				if ($child->track->success_remedial != EATPL_SUCCESS_UNKNOWN) {
					$score_remedial_total += $child->track->score_remedial;
					$score_remedial_count++;
				} else if ($child->track->success != EATPL_SUCCESS_UNKNOWN) {
					$score_remedial_total += $child->track->score;
					$score_remedial_count++;
				}
			}
			
		} else {
			
			// Progress max
			if ($itemType == EATPL_ITEM_TYPE_BATCH || $itemType == EATPL_ITEM_TYPE_CERTIFICATE) {
				$activities = $DB->get_records('trainingpath_item', array('type'=>EATPL_ITEM_TYPE_ACTIVITY, 'parent_id'=>$child->id));
				foreach($activities as $activity) {
					if (!$activity->complementary && !$activity->remedial) $currentTrack->progress_max++;
				}
	
			} else if ($itemType == EATPL_ITEM_TYPE_PATH) {
				$currentTrack->progress_max += $child->duration;
			}
	
			// Time
			if ($itemType == EATPL_ITEM_TYPE_PATH || $itemType == EATPL_ITEM_TYPE_BATCH) $currentTrack->time_status = EATPL_TIME_STATUS_CRITICAL;
			
			// Search remedial
			if ($child->remedial) $remedialFound = true;
		}
	}
	
	// Progress
	if ($itemType == EATPL_ITEM_TYPE_SEQUENCE && $remedialFound && $currentTrack->success_remedial == EATPL_SUCCESS_UNKNOWN && $currentTrack->success != EATPL_SUCCESS_FAILED) {
		// Dont't count remediation test if it is not relevant
		$currentTrack->progress_max--;
	}
	if ($currentTrack->progress_unit == EATPL_PROGRESS_UNIT_PERCENT && $currentTrack->progress_max > 0) {
		$currentTrack->progress_value = intval($currentTrack->progress_value * 100 / $currentTrack->progress_max);
	}

	// Completion
	if ($currentTrack->progress_unit == EATPL_PROGRESS_UNIT_PERCENT) $currentTrack->progress_max = 100;
	if ($currentTrack->progress_value == 0) $currentTrack->completion = EATPL_COMPLETION_NOT_ATTEMPTED;
	else if ($currentTrack->progress_value == $currentTrack->progress_max) $currentTrack->completion = EATPL_COMPLETION_COMPLETED;
	else $currentTrack->completion = EATPL_COMPLETION_INCOMPLETE;
	
	// Time
	if ($itemType == EATPL_ITEM_TYPE_SEQUENCE) {
		$currentTrack->time_status = trainingpath_report_get_time_status($currentTrack->time_spent, $item->duration_up, $item->duration_down, $learningpath);
	} else if ($itemType == EATPL_ITEM_TYPE_CERTIFICATE) {
		$currentTrack->time_status = trainingpath_report_get_time_status($currentTrack->time_spent, $item->duration, null, $learningpath);
	}

	// Score and success
	if ($itemType == EATPL_ITEM_TYPE_CERTIFICATE || $itemType == EATPL_ITEM_TYPE_PATH) {
		if ($score_count > 0) {
			$currentTrack->score = intval($score_total / $score_count);
			$currentTrack->score >= get_config('trainingpath')->passing_score ? $currentTrack->success = EATPL_SUCCESS_PASSED : $currentTrack->success = EATPL_SUCCESS_FAILED;
		}
		if ($score_remedial_count > 0) {
			$currentTrack->score_remedial = intval($score_remedial_total / $score_remedial_count);
			$currentTrack->score_remedial >= get_config('trainingpath')->passing_score ? $currentTrack->success_remedial = EATPL_SUCCESS_PASSED : $currentTrack->success_remedial = EATPL_SUCCESS_FAILED;
		}
	}

	// Save track
	if (!isset($currentTrack->id)) $currentTrack->id = $DB->insert_record("trainingpath_tracks", $currentTrack);
	else $DB->update_record('trainingpath_tracks', $currentTrack);
	
	// Rollup
	if (!$rollup) return;
	if ($itemType == EATPL_ITEM_TYPE_SEQUENCE) {
		trainingpath_report_rollup_track($learningpath, $item->parent_id, EATPL_ITEM_TYPE_BATCH, $userId);
		trainingpath_report_rollup_track($learningpath, $item->grouping_id, EATPL_ITEM_TYPE_CERTIFICATE, $userId);
	} else if ($itemType == EATPL_ITEM_TYPE_CERTIFICATE) {
		trainingpath_report_rollup_track($learningpath, $item->parent_id, EATPL_ITEM_TYPE_PATH, $userId);		
	}
}


/*************************************************************************************************
 *                                             Getting data (tracks)                                       
 *************************************************************************************************/
 
 // My status
 
function trainingpath_report_get_my_status($itemId, $itemType = EATPL_ITEM_TYPE_PATH) {
	global $USER;
	return trainingpath_report_get_user_status($USER->id, $itemId, $itemType);
}

// User status

function trainingpath_report_get_user_status($userId, $itemId, $itemType = EATPL_ITEM_TYPE_PATH) {
	global $DB;
	
	// Get data
	if ($itemType == EATPL_ITEM_TYPE_PATH) {
		$itemAndTrack = $DB->get_record('trainingpath_item', array('id'=>$itemId));
		$currentTracks = array_values($DB->get_records('trainingpath_tracks', array('context_id'=>$itemId, 'context_type'=>$itemType, 'user_id'=>$userId)));
		if (count($currentTracks) > 0) $itemAndTrack->track = $currentTracks[count($currentTracks) - 1];
	} else {
		$item = $DB->get_record('trainingpath_item', array('id'=>$itemId, 'type'=>$itemType));
		$itemAndTrack = trainingpath_report_get_item_and_track($item, $userId);
	}
	return $itemAndTrack;
}

// Items and tracks
 
function trainingpath_report_get_items_and_tracks($itemId, $itemType = EATPL_ITEM_TYPE_SEQUENCE, $userId = null) {
	global $DB;
	$res = array();
	if ($itemType == EATPL_ITEM_TYPE_CERTIFICATE) $items = $DB->get_records('trainingpath_item', array('grouping_id'=>$itemId, 'type'=>EATPL_ITEM_TYPE_SEQUENCE));
	else $items = $DB->get_records('trainingpath_item', array('parent_id'=>$itemId, 'type'=>trainingpath_item_child_type($itemType)));
	foreach($items as $item) {
		$itemAndTrack = trainingpath_report_get_item_and_track($item, $userId);
		if (!$itemAndTrack) continue;
		$res[] = $itemAndTrack;
	}
	return $res;
}

 // Item and track

function trainingpath_report_get_item_and_track($item, $userId = null) {
	global $DB, $USER;
	if (!isset($userId)) $userId = $USER->id;
	if ($item->complementary) return false;
	$currentTracks = array_values($DB->get_records('trainingpath_tracks', array('context_id'=>$item->id, 'context_type'=>$item->type, 'user_id'=>$userId)));
	if (count($currentTracks) > 0) $item->track = $currentTracks[count($currentTracks) - 1];
	return $item;
}

// Users and tracks

function trainingpath_report_get_users_and_tracks($groupId, $context_module, $itemId, $itemType = EATPL_ITEM_TYPE_ACTIVITY) {
	global $DB;
	$res = array();
	$users = groups_get_members($groupId, 'u.id, u.firstname, u.lastname, u.picture, u.imagealt, u.email', 'lastname ASC, firstname ASC');
	foreach($users as $user) {
		$userAndTrack = trainingpath_report_get_user_and_track($user, $context_module, $itemId, $itemType);
		if (!$userAndTrack) continue;
		$res[$user->id] = $userAndTrack;
	}
	return $res;
}

 // User and track

function trainingpath_report_get_user_and_track($user, $context_module, $itemId, $itemType) {
	global $DB;
	
	// Check permissions: only learning, not tutors and editors
	if (has_capability('mod/trainingpath:addinstance', $context_module, $user)) return false;
	if (has_capability('mod/trainingpath:editschedule', $context_module, $user)) return false;

	// Track
	$currentTracks = array_values($DB->get_records('trainingpath_tracks', array('context_id'=>$itemId, 'context_type'=>$itemType, 'user_id'=>$user->id)));
	if (count($currentTracks) > 0) $user->track = $currentTracks[count($currentTracks) - 1];
	
	// Needed to use $OUTPUT->user_picture($user)
	$user->firstnamephonetic = '';
	$user->lastnamephonetic = '';
	$user->middlename = '';
	$user->alternatename = '';
	
	return $user;
}

 // User track

function trainingpath_report_get_user_track($userId, $itemId, $itemType) {
	global $DB;
	$currentTracks = array_values($DB->get_records('trainingpath_tracks', array('context_id'=>$itemId, 'context_type'=>$itemType, 'user_id'=>$userId)));
	if (count($currentTracks) > 0) return $currentTracks[count($currentTracks) - 1];
	return false;
}

// Get group status

function trainingpath_report_get_group_status($groupId, $context_module, $itemId, $itemType = EATPL_ITEM_TYPE_CERTIFICATE) {
	global $DB;
	$users = trainingpath_report_get_users_and_tracks($groupId, $context_module, $itemId, $itemType);
	$completed = 0;
	$count = count($users);
	foreach($users as $user) {
		if (isset($user->track) && $user->track->completion == EATPL_COMPLETION_COMPLETED) $completed++;
	}
	$track = trainingpath_report_get_progress_track($completed, $count);
	if ($itemType == EATPL_ITEM_TYPE_ACTIVITY) {
		$item = $DB->get_record('trainingpath_item', array('id'=>$itemId));
		if ($item->activity_type == EATPL_ACTIVITY_TYPE_EVAL) {
			$schedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$item->id, 'context_type'=>EATPL_ITEM_TYPE_ACTIVITY, 'group_id'=>$groupId));
			if ($schedule && $schedule->access == EATPL_ACCESS_CLOSED && $track->progress_value > 0) {
				$track->completion = EATPL_COMPLETION_COMPLETED;
			} else if ($schedule && $schedule->access == EATPL_ACCESS_ON_DATES && $schedule->time_close && time() > $schedule->time_close) {
				$track->completion = EATPL_COMPLETION_COMPLETED;
			}
		}
	} else if ($itemType == EATPL_ITEM_TYPE_SEQUENCE) {
		$evals = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$itemId, 'type'=>EATPL_ITEM_TYPE_ACTIVITY, 'evaluation'=>1, 'remedial'=>0)));
		if (count($evals) > 0) {
			$eval = $evals[count($evals)-1];
			$schedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$eval->id, 'context_type'=>EATPL_ITEM_TYPE_ACTIVITY, 'group_id'=>$groupId));
			if ($schedule && $schedule->access == EATPL_ACCESS_CLOSED && $track->progress_value > 0) {
				$track->completion = EATPL_COMPLETION_COMPLETED;
			} else if ($schedule && $schedule->access == EATPL_ACCESS_ON_DATES && $schedule->time_close && time() > $schedule->time_close) {
				$track->completion = EATPL_COMPLETION_COMPLETED;
			}
		}
	}
	return $track;
}

// Get group status

function trainingpath_report_get_group_recursive_status($groupId, $context_module, $parentId, $evalOnly = false) {
	global $DB;
	$res = new stdClass();
	$totalCount = 0;
	$totalCompleted = 0;
	$certificates = $DB->get_records('trainingpath_item', array('parent_id'=>$parentId, 'type'=>EATPL_ITEM_TYPE_CERTIFICATE), 'parent_position');
	foreach($certificates as $certificate) {
		$certificate->sequences = array();
		$sequences = $DB->get_records('trainingpath_item', array('grouping_id'=>$certificate->id, 'type'=>EATPL_ITEM_TYPE_SEQUENCE), 'parent_position');
		$certificateCompleted = 0;
		$certificateCount = 0;
		foreach($sequences as $sequence) {
			if ($evalOnly && !$sequence->evaluation) continue;
			$certificateCount++;
			$sequence->track = trainingpath_report_get_group_status($groupId, $context_module, $sequence->id, EATPL_ITEM_TYPE_SEQUENCE);
			if ($sequence->track->completion == EATPL_COMPLETION_COMPLETED) {
				$totalCompleted++;
				$certificateCompleted++;
			}
			$certificate->sequences[$sequence->id] = $sequence;
		}
		$totalCount += $certificateCount;
		$certificate->track = trainingpath_report_get_progress_track($certificateCompleted, $certificateCount);
	}
	$res->certificates = $certificates;
	$res->track = trainingpath_report_get_progress_track($totalCompleted, $totalCount);
	return $res;
}

// Get progress track

function trainingpath_report_get_progress_track($completed, $count) {
	$res = new stdClass();
	$res->progress_unit = EATPL_PROGRESS_UNIT_PERCENT;
	if ($count == 0) $res->progress_value = 0;
	else $res->progress_value = intval($completed * 100 / $count);
	if ($res->progress_value == 0) $res->completion = EATPL_COMPLETION_NOT_ATTEMPTED;
	else if ($res->progress_value == 100) $res->completion = EATPL_COMPLETION_COMPLETED;
	else $res->completion = EATPL_COMPLETION_INCOMPLETE;
	return $res;
}

// Get average status

function trainingpath_report_get_average_status($users, $addToObject = false, $globalTracks = null, $avgForRemedial = false, $singleValue = false) {
	$score_total = 0;
	$score_count = 0;
	$score_remedial_total = 0;
	$score_remedial_count = 0;
	if (isset($globalTracks) && !$avgForRemedial) {

		// Based on global tracks
		foreach($globalTracks as $userId => $user) {
			if ($userId == 'avg') continue;
			if (!isset($user->track)) continue;
			if ($globalTracks[$userId]->track->success_remedial != EATPL_SUCCESS_UNKNOWN) {
				$score_remedial_total += $globalTracks[$userId]->track->score_remedial;
				$score_remedial_count++;
			} else if ($globalTracks[$userId]->track->success != EATPL_SUCCESS_UNKNOWN) {
				$score_remedial_total += $globalTracks[$userId]->track->score;
				$score_remedial_count++;
			}
		}
	} else {

		// Based on either initial or remedial
		foreach($users as $userId => $user) {

			// Skip
			if ($userId == 'avg') continue;
			if (!isset($user->track)) continue;
			if ($user->track->completion != EATPL_COMPLETION_COMPLETED) continue;
			if ($avgForRemedial && $globalTracks[$userId]->track->success_remedial == EATPL_SUCCESS_UNKNOWN) continue;
			
			// Average of initial tests
			if ($user->track->success != EATPL_SUCCESS_UNKNOWN) {
				$score_total += $user->track->score;
				$score_count++;
			}
			
			// Average after remedial
			if ($user->track->success_remedial != EATPL_SUCCESS_UNKNOWN) {
				$score_remedial_total += $user->track->score_remedial;
				$score_remedial_count++;
			} else if ($user->track->success != EATPL_SUCCESS_UNKNOWN && !$singleValue) {
				$score_remedial_total += $user->track->score;
				$score_remedial_count++;
			}
		}
	}
	$track = new stdClass();
	if ($score_count > 0) {
		$track->score = intval($score_total / $score_count);
		$track->score >= get_config('trainingpath')->passing_score ? $track->success = EATPL_SUCCESS_PASSED : $track->success = EATPL_SUCCESS_FAILED;
	}
	if ($score_remedial_count > 0) {
		$track->score_remedial = intval($score_remedial_total / $score_remedial_count);
		$track->score_remedial >= get_config('trainingpath')->passing_score ? $track->success_remedial = EATPL_SUCCESS_PASSED : $track->success_remedial = EATPL_SUCCESS_FAILED;
	}
	if (!$addToObject) return $track;
	$userTrack = new stdClass();
	$userTrack->track = $track;
	return $userTrack;
}
	

/*************************************************************************************************
 *                                             Getting data (indicator)                                       
 *************************************************************************************************/
 
 
// ------------------------------------ Aggregations & selections -------------------------------------------- //


// Get user indicator

function trainingpath_report_get_user_indicator_data($userId, $itemId, $itemType = EATPL_ITEM_TYPE_PATH, $learningpath = null) {
	$itemAndTrack = trainingpath_report_get_user_status($userId, $itemId, $itemType);
	if (!$itemAndTrack || !isset($itemAndTrack->track)) return false;
	return trainingpath_report_get_indicator_data($itemAndTrack->track, $learningpath);
}

// Basic track status (3 cells)

function trainingpath_report_get_indicator_data($track, $learningpath = null) {
	$scoreColors = trainingpath_report_get_score_colors($learningpath);
	$data = new stdClass();
	$data->success = trainingpath_report_get_success_indicator_data($track, $scoreColors);
	$data->time = trainingpath_report_get_time_indicator_data($track);
	$data->progress = trainingpath_report_get_progress_indicator_data($track);
	if (!$data->success && !$data->time && !$data->progress) return false;
	return $data;
}

// Initial score & success (1 cell)

function trainingpath_report_get_success_initial_indicator_data($track, $learningpath = null) {
	$scoreColors = trainingpath_report_get_score_colors($learningpath);
	return trainingpath_report_get_success_single_indicator_data($track, $scoreColors, false);
}

// Remedial score & success (1 cell)

function trainingpath_report_get_success_remedial_indicator_data($track, $learningpath = null) {
	$scoreColors = trainingpath_report_get_score_colors($learningpath);
	return trainingpath_report_get_success_single_indicator_data($track, $scoreColors, true);
}

// Learning path

function trainingpath_report_get_score_colors($learningpath = null) {
	if (!isset($learningpath)) return null;
	$levels = explode(',', $learningpath->score_colors);
	$config = json_decode(get_config('trainingpath')->score_colors);
	foreach($config as $rg => $configItem) {
		$config[$rg]->lt = intval($levels[$rg]);
	}
	return $config;
}


// ------------------------------------ Score & success -------------------------------------------- //


// Initial and/or remedial score & Success

function trainingpath_report_get_success_indicator_data($track, $colors = null) {
	$initial = isset($track->success) && $track->success != EATPL_SUCCESS_UNKNOWN && isset($track->score);
	$remedial = isset($track->success_remedial) && $track->success_remedial != EATPL_SUCCESS_UNKNOWN && isset($track->score_remedial);

	// Nothing to return
	if (!$initial && !$remedial) return false;

	// Prepare colors 	
	if (!isset($colors)) $colors = json_decode(get_config('trainingpath')->score_colors);

	// Data init
	$data = new stdClass();
	if ($initial) $data->initial = trainingpath_report_get_success_single_indicator_data($track, $colors, false);
	if ($remedial) $data->remedial = trainingpath_report_get_success_single_indicator_data($track, $colors, true);
	return $data;
}

// Single score & success, initial or remedial

function trainingpath_report_get_success_single_indicator_data($track, $colors = null, $wantRemedial = false) {
	$initial = isset($track->success) && $track->success != EATPL_SUCCESS_UNKNOWN && isset($track->score);
	$remedial = isset($track->success_remedial) && $track->success_remedial != EATPL_SUCCESS_UNKNOWN && isset($track->score_remedial);
	
	// Nothing to display
	if (!$initial && !$wantRemedial) return false;
	if (!$remedial && $wantRemedial) return false;

	// Data
	$data = new stdClass();
	
	// Prepare colors 	
	if (!isset($colors)) $colors = json_decode(get_config('trainingpath')->score_colors);

	// Single score
	if (!$wantRemedial) {
		$data->score = $track->score;
		$data->success = $track->success;
		$data->class = trainingpath_report_get_score_class($track->score, $colors);
		$data->color = trainingpath_report_get_score_color($track->score, $colors);
	} else {
		$data->score = $track->score_remedial;
		$data->success = $track->success_remedial;
		$data->class = trainingpath_report_get_score_class($track->score_remedial, $colors);
		$data->color = trainingpath_report_get_score_color($track->score_remedial, $colors);
	}
	
	return $data;
}


// ------------------------------------ Time -------------------------------------------- //

 
 function trainingpath_report_get_time_indicator_data($track) {

	// Nothing to display
	if (!isset($track->time_status) || $track->time_spent == 0) return false;

	// Data
	$data = new stdClass();
	$data->status = $track->time_status;
	
	// Styling
	$data->class = trainingpath_report_get_time_status_class($track->time_status);
	$data->color = trainingpath_report_get_time_status_color($track->time_status);
	
	// Value
	if (isset($track->time_spent)) {
		$data->spent = trainingpath_report_get_readable_duration($track->time_spent);
		if ($data->spent == '') $data->spent = '0'; 
	} else {
		$data->spent = '0';
	}
	
	// Passing time
	$data->passing = '';
	if (isset($track->time_passing) && $track->time_passing > 0) {
		$data->passing = '/ '.trainingpath_report_get_readable_duration($track->time_passing);
	}
	return $data;
}


// ------------------------------------ Completion & Progress -------------------------------------------- //

 
 function trainingpath_report_get_progress_indicator_data($track) {

	// Nothing to display
	if (!isset($track->completion) || !isset($track->progress_unit)) return false;
	if ($track->progress_unit == EATPL_PROGRESS_UNIT_STATUS && $track->completion == EATPL_COMPLETION_NOT_ATTEMPTED) return false;

	// Data
	$data = new stdClass();
	$data->completion = $track->completion;

	// Styling
	$data->class = trainingpath_report_get_completion_class($track->completion);
	
	// Value
	if ($track->progress_unit == EATPL_PROGRESS_UNIT_PERCENT) {
		$data->label = $track->progress_value.'%';
		$data->value = intval($track->progress_value);
	} else if ($track->progress_unit == EATPL_PROGRESS_UNIT_COUNT) {
		if ($track->progress_max == 0) return false;
		$data->label = $track->progress_value.'/'.$track->progress_max;
		$data->value = intval($track->progress_value * 100 / $track->progress_max);
	} else if ($track->progress_unit == EATPL_PROGRESS_UNIT_STATUS) {
		$data->label = get_string('status_completion_'.$data->class, 'trainingpath');
		if ($track->completion == EATPL_COMPLETION_COMPLETED) $data->value = 100;
		else if ($track->completion == EATPL_COMPLETION_INCOMPLETE) $data->value = 50;
		else $data->value = 0;
	}
	if ($data->value == 0) return false;
	return $data;
}


 
/*************************************************************************************************
 *                                             Aggregating indicators (HTML)                                        
 *************************************************************************************************/
 

// ------------------------------------ My status & User status -------------------------------------------- //


function trainingpath_report_get_my_indicator_html($itemId, $itemType = EATPL_ITEM_TYPE_PATH, $format = 'default', $learningpath = null) {
	global $USER;
	$data = trainingpath_report_get_user_indicator_data($USER->id, $itemId, $itemType, $learningpath);
	return trainingpath_report_get_indicator_html($data, $format);
}

function trainingpath_report_get_user_combined_indicator_html($userId, $itemId, $itemType = EATPL_ITEM_TYPE_PATH, $learningpath = null) {
	$data = trainingpath_report_get_user_indicator_data($userId, $itemId, $itemType, $learningpath);
	return trainingpath_report_get_combined_indicator_html($data);
}


// ------------------------------------ Combined status -------------------------------------------- //


// Basic track status (3 cells)

function trainingpath_report_get_indicator_html($data, $format = 'default') {
	if (!$data) return '';
	$success = trainingpath_report_get_success_indicator_html($data->success);
	$time = trainingpath_report_get_time_indicator_html($data->time);
	$progress = trainingpath_report_get_progress_indicator_html($data->progress);
	$html = '';
	if ($format == 'right-align') {
		$nb = 0;
		if (empty($success)) $nb++;
		if (empty($time)) $nb++;
		if (empty($progress)) $nb++;
		for($i=0; $i<$nb; $i++) {
			$html .= '<div class="trainingpath-fake-indicator"></div>';
		}	
	}
	$html .= $success.$time.$progress;
	if (empty($html)) return '';
	return trainingpath_get_div($html, 'indicators');
}

// Combined track status (1 cell)

function trainingpath_report_get_combined_indicator_html($data, $reviewUrl = null) {
	if (!$data) return '';
	if ($data->progress && $data->progress->completion == EATPL_COMPLETION_INCOMPLETE)
		return trainingpath_report_get_progress_indicator_html($data->progress);
	if ($data->time && !$data->success && $data->progress && $data->progress->completion == EATPL_COMPLETION_COMPLETED)
		return trainingpath_report_get_time_indicator_html($data->time);
	if ($data->success && isset($data->success->initial) && $data->time && $data->time->status == EATPL_TIME_STATUS_CRITICAL) 
		return trainingpath_report_get_success_with_time_alert_indicator_html($data->success->initial);
	if ($data->success && isset($data->success->initial) && !isset($data->success->remedial))
		return trainingpath_report_get_success_single_indicator_html($data->success->initial, $reviewUrl);
	if ($data->success && isset($data->success->remedial))
		return trainingpath_report_get_success_indicator_html($data->success, $reviewUrl);
	return '';
}

// Combined track status without score/success (1 cell)

function trainingpath_report_get_progress_combined_indicator_html($data) {
	if (!$data) return '';
	if ($data->progress && $data->progress->completion == EATPL_COMPLETION_INCOMPLETE) return trainingpath_report_get_progress_indicator_html($data->progress);
	if ($data->time) return trainingpath_report_get_time_indicator_html($data->time);
	return '';
}


/*************************************************************************************************
 *                                             Formating indicators (HTML primitives)                                        
 *************************************************************************************************/
 

// ------------------------------------ Score & success -------------------------------------------- //


// Initial and/or remedial score & Success

function trainingpath_report_get_success_indicator_html($data, $reviewUrl = null) {
	if (!$data) return '';
	if (isset($data->initial) && isset($data->remedial)) {
		return trainingpath_report_get_success_dual_indicator_html($data);
	} else if (isset($data->initial)) {
		return trainingpath_report_get_success_single_indicator_html($data->initial, $reviewUrl);
	} else if (isset($data->remedial)) {
		return trainingpath_report_get_success_single_indicator_html($data->remedial, $reviewUrl);
	}
	return '';
}

// Dual score & success, initial and remedial (1 cell)

function trainingpath_report_get_success_dual_indicator_html($data) {
	if (!$data) return '';
	$html = '
			<div class="trainingpath-score trainingpath-dual-score" style="background-color:'.$data->initial->color.';border-color:'.$data->initial->color.';">
				<div class="trainingpath-value">'.$data->initial->score.'</div>
				<div class="trainingpath-remedial" style="background-color:'.$data->remedial->color.';border-color:'.$data->remedial->color.';">'.$data->remedial->score.'</div>
			</div>';
	return $html;
}

// Single score & success, initial or remedial (1 cell)

function trainingpath_report_get_success_single_indicator_html($data, $reviewUrl = null) {
	if (!$data) return '';
	
	// Class
	$class = 'trainingpath-score trainingpath-single-score';
	if (isset($reviewUrl)) $class .= ' trainingpath-with-review';
	
	// HTML
	$html = '<div class="'.$class.'" style="background-color:'.$data->color.';border-color:'.$data->color.';">
				<div class="trainingpath-value">'.$data->score.'</div>';

	// SF2017 - Icons
	// if (isset($reviewUrl)) $html .= '<div class="trainingpath-review"><a href="'.$reviewUrl.'"><img src="'.trainingpath_get_icon('review').'"></a></div>';
	if (isset($reviewUrl)) $html .= '<div class="trainingpath-review"><a href="'.$reviewUrl.'">'.trainingpath_get_icon('review', '', 'icon-reverse').'</a></div>';

	$html .= '</div>';
	return $html;
}


// ------------------------------------ Initial score and time alert -------------------------------------------- //


function trainingpath_report_get_success_with_time_alert_indicator_html($data) {
	if (!$data) return '';
	$html = '
			<div class="trainingpath-score trainingpath-single-score trainingpath-with-alert" style="background-color:'.$data->color.';border-color:'.$data->color.';">
				<div class="trainingpath-value">'.$data->score.'</div>
	';
	
	// SF2017 - Icons
	//$html .= '<div class="trainingpath-time-alert"><img src="'.trainingpath_get_icon('duration').'"></div>';
	$html .= '<div class="trainingpath-time-alert">'.trainingpath_get_icon('duration', '', 'icon-reverse').'</div>';
	
	$html .= '</div>';
	return $html;
}


// ------------------------------------ Time -------------------------------------------- //


function trainingpath_report_get_time_indicator_html($data) {
	if (!$data) return '';
	$class = $data->class;
	if ($data->passing != '') $class .= ' trainingpath-with-passing';
	$html = '
			<div class="trainingpath-time trainingpath-'.$class.'" style="border-color:'.$data->color.';">
				<div class="trainingpath-time-spent" style="color:'.$data->color.';">'.$data->spent.'</div>
				<div class="trainingpath-time-passing">'.$data->passing.'</div>
			</div>';
	return $html;
}


// ------------------------------------ Completion & Progress -------------------------------------------- //


function trainingpath_report_get_progress_indicator_html($data) {
	if (!$data) return '';
	$html = '
			<div class="trainingpath-progress trainingpath-'.$data->class.'">
				<div class="trainingpath-progress-frame">
					<div class="trainingpath-progress-value" style="width:'.$data->value.'%;"></div>
				</div>
				<div class="trainingpath-progress-label">'.$data->label.'</div>
			</div>';
	return $html;
}


/*************************************************************************************************
 *                                             Formating values                                        
 *************************************************************************************************/

 
// Get readable duration from seconds

function trainingpath_report_get_readable_duration($seconds) {
    $hours = strval(intval($seconds / 3600));
    $rest = $seconds % 3600;
    $minutes = strval(intval($rest / 60));
    $seconds = strval($rest % 60);
	
	$hours2 = $hours;
	$minutes2 = $minutes;
	if (strlen($hours) == 1) $hours2 = '0'.$hours;
	if (strlen($minutes) == 1) $minutes2 = '0'.$minutes;
	
	if (intval($hours) == 0 && intval($minutes) == 0) return $seconds.'s';
	if (intval($hours) == 0) return $minutes.'m';
    return $hours2.':'.$minutes2;
}

// Get time in seconds from SCORM 2004

function trainingpath_report_get_time_seconds($scormTime) {
	$chunks = explode('PT', $scormTime);
	$rest = $chunks[1];
	
	// Hours
	$chunks = explode('H', $rest);
	if (count($chunks) == 1) {
		$hours = 0;
		$rest = $chunks[0];
	} else {
		$hours = intval($chunks[0]);
		$rest = $chunks[1];
	}
	// Minutes
	$chunks = explode('M', $rest);
	if (count($chunks) == 1) {
		$minutes = 0;
		$rest = $chunks[0];
	} else {
		$minutes = intval($chunks[0]);
		$rest = $chunks[1];
	}
	// Seconds
	$chunks = explode('S', $rest);
	if (count($chunks) == 1) {
		$seconds = 0;
	} else {
		$seconds = floatval($chunks[0]);
	}
	// Result
	return intval($seconds + (60 * $minutes) + (60 * 60 * $hours));
}

// Get SCORM 2004 duration from time in seconds from

function trainingpath_report_get_time_scorm2004($seconds) {
	$hours = intval($seconds / 3600);
	if ($hours < 10) $hours = '0'.$hours;
	$rest = $seconds % 3600;
	$minutes = intval($rest / 60);
	if ($minutes < 10) $minutes = '0'.$minutes;
	$seconds = $rest % 60;
	if ($seconds < 10) $seconds = '0'.$seconds;
	return "PT".$hours."H".$minutes."M".$seconds."S";
}



