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

require_once($CFG->dirroot.'/mod/trainingpath/uilib.php');
require_once($CFG->dirroot.'/mod/trainingpath/view/uilib.php');
require_once($CFG->dirroot.'/mod/trainingpath/edit/uilib.php');
require_once($CFG->dirroot.'/mod/trainingpath/report/lib.php');



/*************************************************************************************************
 *                                             Reports data                                          
 *************************************************************************************************/

 
//------------------------------------------- Learners -------------------------------------------//

// Exportable data
  
function trainingpath_report_get_learners_progress_data($cmid, $learningpath, $groupId, $itemId, $itemType, $context_module, $evalOnly = false, $backurl = null) {
	global $DB, $OUTPUT;
	
	// Prepare data: for each child
	$childType = trainingpath_item_child_type($itemType);
	if ($evalOnly) {
		if ($itemType == EATPL_ITEM_TYPE_CERTIFICATE) $children = $DB->get_records('trainingpath_item', array('grouping_id'=>$itemId, 'type'=>$childType, 'evaluation'=>1, 'complementary'=>0), 'parent_position');
		else $children = $DB->get_records('trainingpath_item', array('parent_id'=>$itemId, 'type'=>$childType, 'evaluation'=>1, 'complementary'=>0), 'parent_position');
	} else {
		if ($itemType == EATPL_ITEM_TYPE_CERTIFICATE) $children = $DB->get_records('trainingpath_item', array('grouping_id'=>$itemId, 'type'=>$childType, 'complementary'=>0), 'parent_position');
		else $children = $DB->get_records('trainingpath_item', array('parent_id'=>$itemId, 'type'=>$childType, 'complementary'=>0), 'parent_position');
	}
	if (count($children) == 0) return false;
	$usersChildren = array();
	foreach($children as $child) {
		$usersChildren[$child->id] = trainingpath_report_get_users_and_tracks($groupId, $context_module, $child->id, $childType);
		$usersChildren[$child->id]['avg'] = trainingpath_report_get_average_status($usersChildren[$child->id], true);
	}

	// Prepare data: globally
	$usersGlobal = trainingpath_report_get_users_and_tracks($groupId, $context_module, $itemId, $itemType);
	if (count($usersGlobal) == 0) return false;
	$usersGlobal['avg'] = trainingpath_report_get_average_status($usersGlobal, true);

	// Sequence specific
	if ($itemType == EATPL_ITEM_TYPE_SEQUENCE) {

		foreach($children as $child) {

			// Calculate avg for remediated learners
			if ($child->remedial) {
				$usersChildren[$child->id]['avg'] = trainingpath_report_get_average_status($usersChildren[$child->id], true, $usersGlobal, false, true);
			} else {
				$usersChildren[$child->id]['avg'] = trainingpath_report_get_average_status($usersChildren[$child->id], true, null, false, true);
			}

			// Calculate avg for remediated learners
			$usersChildren[$child->id]['avg_remedial'] = trainingpath_report_get_average_status($usersChildren[$child->id], true, $usersGlobal, true, true);
		}
		
		// No global column for Sequence
		$usersGlobal['avg'] = false;
		$usersGlobal['avg_remedial'] = false;
	}
	
	// Header
	$cells = array();
	$cells[] = (object)array('content'=>'', 'content_pure'=>'');
	if ($childType == EATPL_ITEM_TYPE_ACTIVITY && $evalOnly) {
		$cells[] = (object)array('content'=>'', 'content_pure'=>'');
	}
	foreach($children as $child) {
		if ($childType == EATPL_ITEM_TYPE_ACTIVITY) {
			$typeName = trainingpath_activity_type_name($child->activity_type);
			$url = (new moodle_url('/mod/trainingpath/report/'.$typeName.'.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'activity_id'=>$child->id, 'eval_only'=>$evalOnly)))->out();
		} else {
			$typeName = trainingpath_item_type_name($childType);
			$url = (new moodle_url('/mod/trainingpath/report/'.$typeName.'.php', array('cmid'=>$cmid, 'group_id'=>$groupId, $typeName.'_id'=>$child->id, 'eval_only'=>$evalOnly)))->out();
		}
		$cells[] = (object)array('content'=>'<a href="'.$url.'">'.$child->code.'</a>', 'content_pure'=>$child->code);
	}
	if ($childType != EATPL_ITEM_TYPE_ACTIVITY) {
		$cells[] = (object)array('content'=>get_string('global', 'trainingpath'), 'content_pure'=>get_string('global', 'trainingpath'));
	}
	$header = (object)array('cells'=>$cells);
			
	// Rows
	$rows = array();
			
	foreach($usersGlobal as $userId => $user) {
		
		// Row
		$cells = array();
			
		// Title
		if ($userId == 'avg') {
			$rowTitle = get_string('average', 'trainingpath');
			$rowTitlePure = $rowTitle;
			$rowClass = 'bold';
		} else if ($userId == 'avg_remedial') {
			$rowTitle = get_string('average_remedial', 'trainingpath');
			$rowTitlePure = $rowTitle;
			$rowClass = 'bold';
		} else {
			$url = (new moodle_url('/mod/trainingpath/report/learner.php', array('cmid'=>$cmid, 'user_id'=>$userId, 'eval_only'=>$evalOnly)))->out();
			$rowTitle = $OUTPUT->user_picture($user).'<a href="'.$url.'">'.$user->firstname.' '.$user->lastname.'</a>';
			$rowTitlePure = $user->firstname.' '.$user->lastname;
			$rowClass = '';
		}
		$cells[] = (object)array('content'=>$rowTitle, 'content_pure'=>$rowTitlePure, 'class_xls'=>$rowClass);
		
		// Add a progress column for activities 
		if ($childType == EATPL_ITEM_TYPE_ACTIVITY && $evalOnly) {
			if (!$usersGlobal[$userId]) {
				$cells[] = (object)array('content'=>'', 'class'=>'status');
			} else {
				$userAndTrack = $usersGlobal[$userId];
				if (!isset($userAndTrack->track)) {
					$cells[] = (object)array('content'=>'', 'class'=>'status');
				} else {
					$statusData = trainingpath_report_get_indicator_data($userAndTrack->track, $learningpath);
					$statusHtml = trainingpath_report_get_progress_combined_indicator_html($statusData);
					$cells[] = (object)array('content'=>$statusHtml, 'data'=>$statusData, 'class'=>'status');
				}
			}
		}
		
		// Children
		foreach($children as $child) {
			
			// Get track
			if (!isset($usersChildren[$child->id][$userId])) {
				$cells[] = (object)array('content'=>'', 'class'=>'status');
				continue;
			}
			$userAndTrack = $usersChildren[$child->id][$userId];
			if (!isset($userAndTrack->track)) {
				$cells[] = (object)array('content'=>'', 'class'=>'status');
				continue;
			}
			
			// Link to review mose
			$reviewUrl = null;
			if ($childType == EATPL_ITEM_TYPE_ACTIVITY) {
				if ($userId != 'avg' && $userId != 'avg_remedial') {
					if (isset($userAndTrack->track)) {
						if ((isset($userAndTrack->track->success) && $userAndTrack->track->success != EATPL_SUCCESS_UNKNOWN)
							|| (isset($userAndTrack->track->success_remedial) && $userAndTrack->track->success_remedial != EATPL_SUCCESS_UNKNOWN)) {
							$reviewUrl = new moodle_url('/mod/scormlite/player.php', array('scoid'=>$child->ref_id, 'userid'=>$userId, 'attempt'=>$userAndTrack->track->last_attempt, 'backurl'=>$backurl->out()));
						}
					}
				}
			}
			
			// Print status
			$statusData = trainingpath_report_get_indicator_data($userAndTrack->track, $learningpath);
			$statusHtml = trainingpath_report_get_combined_indicator_html($statusData, $reviewUrl);
			$cells[] = (object)array('content'=>$statusHtml, 'data'=>$statusData, 'class'=>'status');			
		}
		
		// Get global score
		if ($childType != EATPL_ITEM_TYPE_ACTIVITY) {
			if (!$usersGlobal[$userId]) {
				$cells[] = (object)array('content'=>'', 'class'=>'status', 'class_xls'=>'bold');
			} else {
				$userAndTrack = $usersGlobal[$userId];
				if (!isset($userAndTrack->track)) {
					$cells[] = (object)array('content'=>'', 'class'=>'status', 'class_xls'=>'bold');
				} else {
					$statusData = trainingpath_report_get_indicator_data($userAndTrack->track, $learningpath);
					$statusHtml = trainingpath_report_get_combined_indicator_html($statusData);
					$cells[] = (object)array('content'=>$statusHtml, 'data'=>$statusData, 'class'=>'status', 'class_xls'=>'bold');
				}
			}
		}
			
		// Row
		$rows[] = (object)array('cells'=>$cells);
	}

	// Table
	return (object)array('header'=>$header, 'rows'=>$rows);
}


//------------------------------------------- Items (2 levels) -------------------------------------------//


function trainingpath_report_get_my_items_data($type, $course, $cm, $learningpath, $parentId, $context_module, $evalOnly = false, $children = false, $childrenGrouping = false, $backurl = null) {
	global $USER;
	return trainingpath_report_get_items_data($USER->id, $type, $course, $cm, $learningpath, $parentId, $context_module, $evalOnly, $children, $childrenGrouping, $backurl, false);
}

function trainingpath_report_get_items_data($userId, $type, $course, $cm, $learningpath, $parentId, $context_module, $evalOnly = false, $children = false, $childrenGrouping = false, $backurl = null, $report = true) {
	global $DB;
	
	// Get items
	$items = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$parentId, 'type'=>$type), 'parent_position'));
	
	// Get scheduled group
	$groupId = trainingpath_get_scheduled_group_id($course, $cm, $userId);
	
	// Start parsing
	$res = array();
	if (count($items) > 0) {
		foreach($items as $item) {

			// Title
			if ($item->code) $item->title = '['.$item->code.'] '.$item->title;
			
			// URL
			if ($report) {
				if ($item->type == EATPL_ITEM_TYPE_ACTIVITY) {
					$typeName = trainingpath_activity_type_name($item->activity_type);
					$titleUrl = (new moodle_url('/mod/trainingpath/report/'.$typeName.'.php', array('cmid'=>$cm->id, 'group_id'=>$groupId, 'activity_id'=>$item->id, 'eval_only'=>$evalOnly)))->out();
				} else {
					$typeName = trainingpath_item_type_name($item->type);
					$titleUrl = (new moodle_url('/mod/trainingpath/report/'.$typeName.'.php', array('cmid'=>$cm->id, 'group_id'=>$groupId, $typeName.'_id'=>$item->id, 'eval_only'=>$evalOnly)))->out();
				}
			} else {
				$childType = trainingpath_item_child_type($item->type);
				$typeName = trainingpath_item_type_name($item->type);
				$typeNames = trainingpath_item_type_name($childType, true);
				$titleUrl = (new moodle_url('/mod/trainingpath/view/'.$typeNames.'.php', array('cmid'=>$cm->id, $typeName.'_id'=>$item->id, 'via'=>'certificates')))->out();
			}

			// Status
			$itemAndTrack = trainingpath_report_get_user_status($userId, $item->id, $item->type);
			$statusInitialHtml = '';
			$statusRemedialHtml = '';
			$statusInitialData = false;
			$statusRemedialData = false;
			if ($itemAndTrack && isset($itemAndTrack->track)) {
				$statusInitialData = trainingpath_report_get_success_initial_indicator_data($itemAndTrack->track, $learningpath);
				$statusInitialHtml = trainingpath_report_get_success_single_indicator_html($statusInitialData);
				$statusRemedialData = trainingpath_report_get_success_remedial_indicator_data($itemAndTrack->track, $learningpath);
				$statusRemedialHtml = trainingpath_report_get_success_single_indicator_html($statusRemedialData);
			}

			// Header
			$cells = array();
			$titleUrl = '#'; // Collasible table
			$cells[] = (object)array('content'=>'<a href="'.$titleUrl.'" class="collapsed trainingpath-custom" data-toggle="collapse" data-target="#table-body-'.$item->id.'"><i class="fa"></i>'.$item->title.'</a>', 'content_pure'=>$item->title);
			$cells[] = (object)array('content'=>$statusInitialHtml, 'data'=>(object)array('success'=>(object)array('initial'=>$statusInitialData)), 'class'=>'status');
			$cells[] = (object)array('content'=>$statusRemedialHtml, 'data'=>(object)array('success'=>(object)array('remedial'=>$statusRemedialData)), 'class'=>'status');
			if ($itemAndTrack && isset($itemAndTrack->track)) {
				$statusData = trainingpath_report_get_indicator_data($itemAndTrack->track, $learningpath);
				$statusHtml = trainingpath_report_get_progress_combined_indicator_html($statusData);
				$cells[] = (object)array('content'=>$statusHtml, 'data'=>$statusData, 'class'=>'status');
			} else {
				$cells[] = (object)array('content'=>'', 'class'=>'status');
			}
			$header = (object)array('cells'=>$cells, 'class'=>'thead-default');
			
			// Rows
			$rows = array();
			if ($children) $rows = trainingpath_report_get_children_items_rows($userId, $groupId, trainingpath_item_child_type($item->type), $cm, $learningpath, $item->id, $context_module, $childrenGrouping, $evalOnly, $backurl, $report);

			// Table
			$res[] = (object)array('header'=>$header, 'rows'=>$rows, 'item'=>$item);
		}
	}
	return $res;
}
function trainingpath_report_get_children_items_rows($userId, $groupId, $type, $cm, $learningpath, $parentId, $context_module, $grouping, $evalOnly = false, $backurl = null, $report = true) {
	global $DB, $USER;
	$rows = array();
	
	// Get items
	if ($evalOnly) {
		if ($grouping) $items = array_values($DB->get_records('trainingpath_item', array('grouping_id'=>$parentId, 'type'=>$type, 'evaluation'=>1, 'complementary'=>0), 'parent_position'));
		else $items = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$parentId, 'type'=>$type, 'evaluation'=>1, 'complementary'=>0), 'parent_position'));
	} else {
		if ($grouping) $items = array_values($DB->get_records('trainingpath_item', array('grouping_id'=>$parentId, 'type'=>$type, 'complementary'=>0), 'parent_position'));
		else $items = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$parentId, 'type'=>$type, 'complementary'=>0), 'parent_position'));
	}
			
	if (count($items) > 0) {
		foreach($items as $item) {

			// Title
			if ($item->code) $item->title = '['.$item->code.'] '.$item->title;

			// URL
			if ($report) {
				if ($item->type == EATPL_ITEM_TYPE_ACTIVITY) {
					$typeName = trainingpath_activity_type_name($item->activity_type);
					$titleUrl = (new moodle_url('/mod/trainingpath/report/'.$typeName.'.php', array('cmid'=>$cm->id, 'group_id'=>$groupId, 'activity_id'=>$item->id, 'eval_only'=>$evalOnly)))->out();
				} else {
					$typeName = trainingpath_item_type_name($item->type);
					$titleUrl = (new moodle_url('/mod/trainingpath/report/'.$typeName.'.php', array('cmid'=>$cm->id, 'group_id'=>$groupId, $typeName.'_id'=>$item->id, 'eval_only'=>$evalOnly)))->out();
				}
			} else {
				$childType = trainingpath_item_child_type($item->type);
				$typeName = trainingpath_item_type_name($item->type);
				$typeNames = trainingpath_item_type_name($childType, true);
				$titleUrl = (new moodle_url('/mod/trainingpath/view/'.$typeNames.'.php', array('cmid'=>$cm->id, $typeName.'_id'=>$item->id, 'via'=>'certificates')))->out();
			}

			// Review links
			$reviewInitialUrl = null;
			$reviewRemedialUrl = null;
			if ($item->type == EATPL_ITEM_TYPE_SEQUENCE) {
				$sequenceTrack = trainingpath_report_get_user_track($userId, $item->id, $item->type);
				if ($sequenceTrack) {
					if (isset($sequenceTrack->success) && $sequenceTrack->success != EATPL_SUCCESS_UNKNOWN) {
						$evals = array_values($DB->get_records('trainingpath_item', array('type'=>EATPL_ITEM_TYPE_ACTIVITY, 'parent_id'=>$item->id, 'evaluation'=>1, 'remedial'=>0), 'parent_position'));
						if (count($evals) > 0) {
							$eval = $evals[count($evals)-1];
							$allowed = true;
							if ($userId == $USER->id) {
								$groupTrack = trainingpath_report_get_group_status($groupId, $context_module, $eval->id, $eval->type);
								$allowed = ($groupTrack->completion == EATPL_COMPLETION_COMPLETED);
							}
							if ($allowed) {
								$evalTrack = trainingpath_report_get_user_track($userId, $eval->id, $eval->type);
								$reviewInitialUrl = new moodle_url('/mod/scormlite/player.php', array('scoid'=>$eval->ref_id, 'userid'=>$userId, 'attempt'=>$evalTrack->last_attempt, 'backurl'=>$backurl->full));
							}
						}
					}
					if (isset($sequenceTrack->success_remedial) && $sequenceTrack->success_remedial != EATPL_SUCCESS_UNKNOWN) {
						$evals = array_values($DB->get_records('trainingpath_item', array('type'=>EATPL_ITEM_TYPE_ACTIVITY, 'parent_id'=>$item->id, 'evaluation'=>1, 'remedial'=>1), 'parent_position'));
						if (count($evals) > 0) {
							$eval = $evals[count($evals)-1];
							$allowed = true;
							if ($userId == $USER->id) {
								$groupTrack = trainingpath_report_get_group_status($groupId, $context_module, $eval->id, $eval->type);
								$allowed = ($groupTrack->completion == EATPL_COMPLETION_COMPLETED);
							}
							if ($allowed) {
								$evalTrack = trainingpath_report_get_user_track($userId, $eval->id, $eval->type);
								$reviewRemedialUrl = new moodle_url('/mod/scormlite/player.php', array('scoid'=>$eval->ref_id, 'userid'=>$userId, 'attempt'=>$evalTrack->last_attempt, 'backurl'=>$backurl->full));
							}
						}
					}
				}
			}

			// Status
			$itemAndTrack = trainingpath_report_get_user_status($userId, $item->id, $item->type);
			$statusInitialHtml = '';
			$statusRemedialHtml = '';
			$statusInitialData = false;
			$statusRemedialData = false;
			if ($itemAndTrack && isset($itemAndTrack->track)) {
				$statusInitialData = trainingpath_report_get_success_initial_indicator_data($itemAndTrack->track, $learningpath);
				$statusInitialHtml = trainingpath_report_get_success_single_indicator_html($statusInitialData, $reviewInitialUrl);
				$statusRemedialData = trainingpath_report_get_success_remedial_indicator_data($itemAndTrack->track, $learningpath);
				$statusRemedialHtml = trainingpath_report_get_success_single_indicator_html($statusRemedialData, $reviewRemedialUrl);
			}

			// Row
			$cells = array();
			$cells[] = (object)array('content'=>'<a href="'.$titleUrl.'">'.$item->title.'</a>', 'content_pure'=>$item->title);
			$cells[] = (object)array('content'=>$statusInitialHtml, 'data'=>(object)array('success'=>(object)array('initial'=>$statusInitialData)), 'class'=>'status');
			$cells[] = (object)array('content'=>$statusRemedialHtml, 'data'=>(object)array('success'=>(object)array('remedial'=>$statusRemedialData)), 'class'=>'status');
			if ($itemAndTrack && isset($itemAndTrack->track)) {
				$statusData = trainingpath_report_get_indicator_data($itemAndTrack->track, $learningpath);
				$statusHtml = trainingpath_report_get_progress_combined_indicator_html($statusData);
				$cells[] = (object)array('content'=>$statusHtml, 'data'=>$statusData, 'class'=>'status');
			} else {
				$cells[] = (object)array('content'=>'', 'class'=>'status');
			}
			$rows[] = (object)array('cells'=>$cells);
		}
	}
	return $rows;
}


//------------------------------------------- Items (2 levels, Excel) -------------------------------------------//


function trainingpath_report_get_items_data_xls($userId, $type, $course, $cm, $learningpath, $parentId, $context_module, $evalOnly = false, $children = false, $childrenGrouping = false, $backurl = null, $report = true) {
	global $DB;
	
	// Get items
	$items = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$parentId, 'type'=>$type), 'parent_position'));
	
	// Get scheduled group
	$groupId = trainingpath_get_scheduled_group_id($course, $cm, $userId);
	
	// Start parsing
	$res = array();
	if (count($items) > 0) {
		foreach($items as $item) {

			// Title
			if ($item->code) $item->title = '['.$item->code.'] '.$item->title;
			
			// Header
			$cells = array();
			$cells[] = (object)array();
			$cells[] = (object)array();
			$header = (object)array('cells'=>$cells);
			
			// Rows
			$rows = array();
			if ($children) $rows = trainingpath_report_get_children_items_rows_xls($userId, $groupId, trainingpath_item_child_type($item->type), $cm, $learningpath, $item->id, $context_module, $childrenGrouping, $evalOnly, $backurl, $report);

			// Status
			$itemAndTrack = trainingpath_report_get_user_status($userId, $item->id, $item->type);

			// Last line (average)
			$cells = array();
			$cells[] = (object)array('content_pure'=>$item->title, 'class_xls'=>'bold');
			if ($itemAndTrack && isset($itemAndTrack->track)) {
				$statusData = trainingpath_report_get_indicator_data($itemAndTrack->track, $learningpath);
				$cells[] = (object)array('data'=>$statusData, 'class_xls'=>'bold');
			} else {
				$cells[] = (object)array();
			}
			$rows[] = (object)array('cells'=>$cells);
			
			// Table
			$res[] = (object)array('header'=>$header, 'rows'=>$rows, 'item'=>$item);
		}
	}
	return $res;
}
function trainingpath_report_get_children_items_rows_xls($userId, $groupId, $type, $cm, $learningpath, $parentId, $context_module, $grouping, $evalOnly = false, $backurl = null, $report = true) {
	global $DB, $USER;
	$rows = array();
	
	// Get items
	if ($evalOnly) {
		if ($grouping) $items = array_values($DB->get_records('trainingpath_item', array('grouping_id'=>$parentId, 'type'=>$type, 'evaluation'=>1, 'complementary'=>0), 'parent_position'));
		else $items = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$parentId, 'type'=>$type, 'evaluation'=>1, 'complementary'=>0), 'parent_position'));
	} else {
		if ($grouping) $items = array_values($DB->get_records('trainingpath_item', array('grouping_id'=>$parentId, 'type'=>$type, 'complementary'=>0), 'parent_position'));
		else $items = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$parentId, 'type'=>$type, 'complementary'=>0), 'parent_position'));
	}
			
	if (count($items) > 0) {
		foreach($items as $item) {

			// Title
			if ($item->code) $item->title = '['.$item->code.'] '.$item->title;

			// Status
			$itemAndTrack = trainingpath_report_get_user_status($userId, $item->id, $item->type);

			// Row
			$cells = array();
			$cells[] = (object)array('content_pure'=>$item->title);
			if ($itemAndTrack && isset($itemAndTrack->track)) {
				$statusData = trainingpath_report_get_indicator_data($itemAndTrack->track, $learningpath);
				$cells[] = (object)array('data'=>$statusData);
			} else {
				$cells[] = (object)array();
			}
			$rows[] = (object)array('cells'=>$cells);
		}
	}
	return $rows;
}


/*************************************************************************************************
 *                                             Reports HTML                                          
 *************************************************************************************************/
 
 
//------------------------------------------- Group -------------------------------------------//

  
function trainingpath_report_get_group_html($cmid, $learningpath, $groupId, $data) {
	global $DB;

	// Certificates
	$res = '';
	foreach($data->certificates as $certificate) {
		
		// Certificate & status
		$url = (new moodle_url('/mod/trainingpath/report/certificate.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'certificate_id'=>$certificate->id)))->out();
		$content = '<a href="'.$url.'"><h4>['.$certificate->code.'] '.$certificate->title.'</h4><a>';
		$statusData = trainingpath_report_get_indicator_data($certificate->track, $learningpath);
		$status = trainingpath_report_get_indicator_html($statusData, 'right-align');
		
		// 	Sequences
		$content .= '<div class="trainingpath-items-bar">';
		foreach($certificate->sequences as $sequence) {
			
			// 	Sequence
			$url = (new moodle_url('/mod/trainingpath/report/sequence.php', array('cmid'=>$cmid, 'group_id'=>$groupId, 'sequence_id'=>$sequence->id)))->out();
			if (isset($sequence->track) && isset($sequence->track->completion)) {
				$class = 'trainingpath-'.trainingpath_report_get_completion_class($sequence->track->completion);
			} else {
				$class = 'trainingpath-notattempted';
			}
			$content .= '	<div class="trainingpath-bar-item '.$class.'"><a href="'.$url.'">'.$sequence->code.'</a></div>';
		}
		$content .= '</div>';
		$res .= trainingpath_get_content_with_status($content, $status);
	}

	return $res;	
}


//------------------------------------------- Groups -------------------------------------------//

  
function trainingpath_report_get_groups_html($course, $cmid, $context_id, $context_module, $permission) {
	global $DB;
	
	// Prepare data
	$groupIds = trainingpath_get_groups($course->id, $permission);
	$schedules = array_values($DB->get_records('trainingpath_schedule', array('cmid'=>$cmid, 'context_id'=>$context_id, 'context_type'=>EATPL_ITEM_TYPE_PATH), 'position'));
	$finalSchedules = array();
	foreach($schedules as $schedule) {
		if (!in_array($schedule->group_id, $groupIds)) continue;
		$group = $DB->get_record('groups', array('id'=>$schedule->group_id));
		if ($group) $schedule->title = $group->name;
		else $schedule->title = get_string('no_matching_group', 'trainingpath');
		$finalSchedules[] = $schedule;
	}
	
	// Prepare display
	$res = '<div id="trainingpath-cards">';
	foreach($finalSchedules as $schedule) {
		
		// Get status
		$status = trainingpath_report_get_group_recursive_status($schedule->group_id, $context_module, $schedule->context_id, false);
		$statusData = trainingpath_report_get_progress_indicator_data($status->track);
		$statusHtml = trainingpath_report_get_progress_indicator_html($statusData);

		// Get links
		$learnersUrl = (new moodle_url('/mod/trainingpath/report/learners.php', array('cmid'=>$cmid, 'group_id'=>$schedule->group_id)))->out();
		$pathUrl = (new moodle_url('/mod/trainingpath/report/group.php', array('cmid'=>$cmid, 'group_id'=>$schedule->group_id)))->out();

		// Commands
		$commands = array();
		$commands[] = (object)array('href'=>$learnersUrl, 'class'=>'primary', 'size'=>'sm', 'title'=>get_string('learners_progress', 'trainingpath'));
		$commands[] = (object)array('href'=>$pathUrl, 'class'=>'primary', 'size'=>'sm', 'title'=>get_string('group_progress', 'trainingpath'));
		
		// Card
		$res .= trainingpath_get_card($schedule->description, null, null, $schedule->title, null, $commands, null, $statusHtml);

	}
	$res .= '</div>';
	return $res;
}



/*************************************************************************************************
 *                                             Excel output                                          
 *************************************************************************************************/
  
function trainingpath_report_excel_get_workbook() {
	global $CFG;
	require_once($CFG->libdir . '/excellib.class.php');
    $workbook = new MoodleExcelWorkbook('export.xlsx', 'Excel2007');
	return $workbook;
}

function trainingpath_report_excel_add_worksheet($workbook, $pageTitles, $indicators = array(), $cols = array(), $sheetTitle = '') {
	$worksheet = $workbook->add_worksheet($sheetTitle);
	
	// Columns width
	for($i=0; $i<count($cols); $i++) {
		$worksheet->set_column($i, $i, $cols[$i]);
	}
	
	// Page titles
	$line = 0;
	foreach($pageTitles as $pageTitle) {
		$style = array('size'=>'12', 'bg_color'=>'silver', 'align'=>'center');
		if (isset($pageTitle->italic) && $pageTitle->italic) $style['italic'] = 1;
		if (isset($pageTitle->bold) && $pageTitle->bold) $style['bold'] = 1;
		if (isset($pageTitle->size)) $style['size'] = $pageTitle->size;
		$worksheet->merge_cells($line, 0, $line, 5);
		$worksheet->write_string($line, 0, $pageTitle->content, $workbook->add_format($style));
		$line++;
	}
	$line += 2;
	return (object)array('worksheet'=>$worksheet, 'line'=>$line, 'indicators'=>$indicators);
}

function trainingpath_report_excel_add_comment($workbook, &$sheet, $title, $comment) {
	$sheet->worksheet->write_string($sheet->line, 0, $title, $workbook->add_format(array('size'=>'10', 'bold'=>1)));
	$sheet->line++;
	$sheet->worksheet->write_string($sheet->line, 0, $comment, $workbook->add_format(array('size'=>'10')));
	$sheet->line += 2;
}

function trainingpath_report_excel_add_table($workbook, &$sheet, $rows, $head = null, $includeRemedial = false, $hideHead = false) {
	
	// Header
	if (isset($head) && !$hideHead) {
		$col = 0;
		$format = array('bold'=>1, 'color'=>'white');
		foreach($head->cells as $cell) {
			if ($col > 0) $format['bg_color'] = 'grey';
			trainingpath_report_excel_write_header_cell($workbook, $sheet, $col, $cell, $format, $includeRemedial);
			$col++;
		}
	}
	$sheet->line++;
	
	// Sub Header
	if (isset($head) && count($sheet->indicators) > 1) {
		$col = 1;
		$format = array('bold'=>1, 'color'=>'black', 'bg_color'=>'silver');
		for ($i = 1; $i < count($head->cells); $i++) {
			foreach($sheet->indicators as $indicator) {
				$subcell = (object)array('content_pure'=>get_string('xls_'.$indicator, 'trainingpath'));
				trainingpath_report_excel_write_simple_cell($workbook, $sheet, $col, $subcell, $format, $includeRemedial);
				$col++;
			}
			if ($includeRemedial) {
				$subcell = (object)array('content_pure'=>get_string('xls_remedial', 'trainingpath'));
				trainingpath_report_excel_write_simple_cell($workbook, $sheet, $col, $subcell, $format, $includeRemedial);
				$col++;
			}
		}
	}
	$sheet->line++;
	
	// Rows
	foreach($rows as $row) {
		$col = 0;
		$format = array('border'=>1);
		foreach($row->cells as $cell) {
			if ($col == 0) trainingpath_report_excel_write_simple_cell($workbook, $sheet, $col, $cell, $format, $includeRemedial);
			else trainingpath_report_excel_write_data_cell($workbook, $sheet, $col, $cell, $format, $includeRemedial);
			$col++;
		}
		$sheet->line++;
	}
	$sheet->line += 2;
}

function trainingpath_report_excel_write_header_cell($workbook, &$sheet, $col, $cell, $format = array(), $includeRemedial = false) {
	$format['size'] = 10;
	$mergeCount = count($sheet->indicators)-1;
	if ($includeRemedial) $mergeCount++;
	isset($cell->content_pure) ? $content = $cell->content_pure : $content = '';
	if ($col > 0) {
		$col = ($col-1) * count($sheet->indicators) + 1;
		$sheet->worksheet->merge_cells($sheet->line, $col, $sheet->line, $col+$mergeCount);
	}
	$sheet->worksheet->write_string($sheet->line, $col, $content, $workbook->add_format($format));
}

function trainingpath_report_excel_write_simple_cell($workbook, &$sheet, $col, $cell, $format = array(), $includeRemedial = false) {
	$format['size'] = 10;
	if (isset($cell->class_xls) && $cell->class_xls == 'bold') $format['bold'] = 1;
	isset($cell->content_pure) ? $content = $cell->content_pure : $content = '';
	$sheet->worksheet->write_string($sheet->line, $col, $content, $workbook->add_format($format));
}

function trainingpath_report_excel_write_data_cell($workbook, &$sheet, $col, $cell, $format = array(), $includeRemedial = false) {
	$format['size'] = 10;
	$format['align'] = 'center';
	if (isset($cell->class_xls) && $cell->class_xls == 'bold') $format['bold'] = 1;
	if ($col > 0) $col = ($col-1) * count($sheet->indicators) + 1;
	foreach($sheet->indicators as $indicator) {
		if (isset($cell->data) && $cell->data && isset($cell->data->$indicator) && $cell->data->$indicator) {
			if ($indicator == 'progress') {
				trainingpath_report_excel_write_progress_cell($workbook, $sheet, $col, $cell->data->$indicator, $format);
			} else if ($indicator == 'time') {
				trainingpath_report_excel_write_time_cell($workbook, $sheet, $col, $cell->data->$indicator, $format);
			} else if ($indicator == 'success') {
				trainingpath_report_excel_write_success_cell($workbook, $sheet, $col, $cell->data->$indicator, $format, $includeRemedial);
				if ($includeRemedial) $col++;
			} else {
				$sheet->worksheet->write_string($sheet->line, $col, '', $workbook->add_format($format));
			}
		} else {
			$sheet->worksheet->write_string($sheet->line, $col, '', $workbook->add_format($format));
			if ($includeRemedial && $indicator == 'success') {
				$col++;
				$sheet->worksheet->write_string($sheet->line, $col, '', $workbook->add_format($format));
			}
		}
		$col++;
	}
}

function trainingpath_report_excel_write_progress_cell($workbook, &$sheet, $col, $data, $format) {
	$content = '';
	if ($data) {
		$content = $data->label;
		if ($data->class == 'notattempted') { $format['color'] = 'white'; $format['bg_color'] = 'white'; }
		else if ($data->class == 'incomplete') $format['bg_color'] = 'orange';
		else if ($data->class == 'completed') $format['bg_color'] = 'green';
	}
	$sheet->worksheet->write_string($sheet->line, $col, $content, $workbook->add_format($format));
}

function trainingpath_report_excel_write_time_cell($workbook, &$sheet, $col, $data, $format) {
	$content = '';
	if ($data) {
		$content = $data->spent;
		$format['bg_color'] = trainingpath_report_excel_get_cell_color($data->class);
	}
	$sheet->worksheet->write_string($sheet->line, $col, $content, $workbook->add_format($format));
}

function trainingpath_report_excel_write_success_cell($workbook, &$sheet, $col, $data, $format, $remedial = false) {
	$content = '';
	$class = '';
	$content_remedial = '';
	$class_remedial = '';
	if ($data) {
		if ($remedial) {
			if (isset($data->initial) && $data->initial)  {
				$content = $data->initial->score;
				$class = $data->initial->class;
			}
			if (isset($data->remedial) && $data->remedial)  {
				$content_remedial = $data->remedial->score;
				$class_remedial = $data->remedial->class;
			}
		} else {
			if (isset($data->initial) && $data->initial) {
				$content = $data->initial->score;
				$class = $data->initial->class;
			} else if (isset($data->remedial) && $data->remedial) {
				$content = $data->remedial->score;
				$class = $data->remedial->class;
			}
		}
		$format['bg_color'] = trainingpath_report_excel_get_cell_color($class);
		$format_remedial = $format;
		$format_remedial['bg_color'] = trainingpath_report_excel_get_cell_color($class_remedial);
	}
	$sheet->worksheet->write_string($sheet->line, $col, $content, $workbook->add_format($format));
	if ($remedial) {
		$col++;
		$sheet->worksheet->write_string($sheet->line, $col, $content_remedial, $workbook->add_format($format_remedial));
	}
}

function trainingpath_report_excel_get_cell_color($class) {
	if ($class == 'critical') return 'red';
	else if ($class == 'minimal') return 'orange';
	else if ($class == 'nominal') return 'yellow';		
	else if ($class == 'optimal') return 'green';
	else return 'white';
}



/*************************************************************************************************
 *                                             Comments                                          
 *************************************************************************************************/

  
// Get comment

function trainingpath_report_comments_get_record($itemId, $itemType, $userId = null, $groupId = null) {
	global $DB;
	if (!isset($userId) && !isset($groupId)) return false;
	if (isset($userId)) $record = $DB->get_record('trainingpath_comments', array('context_id'=>$itemId, 'context_type'=>$itemType, 'user_id'=>$userId));
	else $record = $DB->get_record('trainingpath_comments', array('context_id'=>$itemId, 'context_type'=>$itemType, 'group_id'=>$groupId));
	return $record;
}
						
// Generate comment div

function trainingpath_report_comments_get_div($itemId, $itemType, $userId = null, $groupId = null) {
	global $DB;
	
	// Get existing comment
	$record = trainingpath_report_comments_get_record($itemId, $itemType, $userId, $groupId);
	if (!$record) return '';
	
	// Return div
	return trainingpath_get_div(trainingpath_get_div($record->comment, 'comment'), 'comments');
}
						
// Generate comment form

function trainingpath_report_comments_get_form($url, $urlParams, $itemId, $itemType, $userId = null, $groupId = null) {
	global $DB;
	
	// Get existing comment
	$record = trainingpath_report_comments_get_record($itemId, $itemType, $userId, $groupId);
	$record ? $comment = $record->comment : $comment = '';
	
	// Record comment if it is passed
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$postComment = optional_param('comment', '', PARAM_RAW);
		$postContextId = optional_param('comment_context_id', 0, PARAM_INT);
		$postContextType = optional_param('comment_context_type', 0, PARAM_INT);
		if ($postContextId == $itemId && $postContextType == $itemType) {
			if ($record) {
				$record->comment = $postComment;
				$DB->update_record("trainingpath_comments", $record);
			} else {
				$record = new stdClass();
				$record->context_id = $itemId;
				$record->context_type = $itemType;
				$record->comment = $postComment;
				if (isset($userId)) $record->user_id = $userId;
				else $record->group_id = $groupId;
				$DB->insert_record("trainingpath_comments", $record);
			}
			$comment = $record->comment;
		}
	}	
	
	// Generate form
	$label = get_string('comments', 'trainingpath');
	$html = '<div class="trainingpath-comments">
				<form action="'.$url.'" method="POST">
					<div class="form-group">
						<textarea class="form-control" name="comment" placeholder="'.$label.'" rows="3">'.$comment.'</textarea>
					</div>';
	foreach($urlParams as $id => $val) {
		$html .= '		<input type="hidden" name="'.$id.'" value="'.$val.'">';
	}
	$html .= '			<input type="hidden" name="comment_context_id" value="'.$itemId.'">
						<input type="hidden" name="comment_context_type" value="'.$itemType.'">
					<button type="submit" class="btn btn-primary">'.get_string('save', 'trainingpath').'</button>
				</form>
			</div>';
	return $html;
}
						

