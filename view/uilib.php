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
require_once($CFG->dirroot.'/mod/trainingpath/report/lib.php');


/*************************************************************************************************
 *                                             UI                                          
 *************************************************************************************************/
 

//------------------------------------------- Page Setup -------------------------------------------//

function trainingpath_view_setup_page($course, $tab = null, $breadcrumb = null, $heading = null) {
	if (isset($tab)) $tabsHtml = trainingpath_view_get_tabs($tab, $breadcrumb);
	else $tabsHtml = '';
	trainingpath_setup_page($course, get_string('viewing_trainingpath', 'trainingpath'), null, null, null, null, $tabsHtml, $breadcrumb, $heading);
}


//------------------------------------------- Tabs -------------------------------------------//
 
function trainingpath_view_get_tabs($activeTab = null, $hasBreadcrumb = false) {
	
	// Get URL params
	$cmid  = optional_param('id', 0, PARAM_INT);
	if (!$cmid) $cmid  = required_param('cmid', PARAM_INT);
	
	// Define tabs
	$tabs = array();
	$tabs['home'] = array('title'=>get_string('home', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view.php', array('id'=>$cmid)))->out());
	$tabs['certificates'] = array('title'=>get_string('certificates', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view/certificates.php', array('cmid'=>$cmid)))->out());
	$tabs['batches'] = array('title'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view/batches.php', array('cmid'=>$cmid)))->out());
	$tabs['gradebook'] = array('title'=>get_string('gradebook', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/report/gradebook.php', array('cmid'=>$cmid)))->out());
	//$tabs['gradebook'] = array('title'=>get_string('gradebook', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/view/gradebook.php', array('cmid'=>$cmid)))->out());

	// Active tab
	if (!isset($activeTab)) $activeTab = 'home';

	// Margin
	$marginTop = true;
	$marginBottom = !$hasBreadcrumb;
	
	return trainingpath_get_tabs($tabs, $marginTop, $marginBottom, $activeTab);
}


//------------------------------------------- Current item -------------------------------------------//
  
function trainingpath_view_get_current_item($course, $cm, $learningpath, $parentId, $openBatchUrl, $openSequenceUrl) {
	global $OUTPUT;
	$res = '';
	
	// Get next batch
	$currentBatch = trainingpath_view_get_current_item_data(EATPL_ITEM_TYPE_BATCH, $course, $cm, $learningpath, $parentId);
	if (isset($currentBatch->next->item_and_track)) {

		// Title
		$res = '<h4>'.get_string('next_step', 'trainingpath').'</h4>';
		
		// Batch HTML
		$res .= trainingpath_view_get_current_item_html($cm, $currentBatch->next->item_and_track, $openBatchUrl, $currentBatch->next->status_data, $currentBatch->next->schedule_info);

		// Get next sequence
		$currentSequence = trainingpath_view_get_current_item_data(EATPL_ITEM_TYPE_SEQUENCE, $course, $cm, $learningpath, $currentBatch->next->item_and_track->id);
		if (isset($currentSequence->next->item_and_track)) {

			// Sequence HTML
			$sequenceHtml = trainingpath_view_get_current_item_html($cm, $currentSequence->next->item_and_track, $openSequenceUrl, $currentSequence->next->status_data, $currentSequence->next->schedule_info);
			$sequenceHtml = trainingpath_get_div($sequenceHtml, 'padding-left');
			$res .= $sequenceHtml;
		}
	}
	return $res;
}

function trainingpath_view_get_current_item_data($type, $course, $cm, $learningpath, $parentId) {
	global $DB;
	$res = new stdClass();
	
	// Get scheduled group
	$groupId = trainingpath_get_scheduled_group_id($course, $cm);
	
	// Items
	$items = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$parentId, 'type'=>$type), 'parent_position'));
	if (count($items) > 0) {

		$allCompleted = true;
		foreach($items as $item) {
			
			// Get track
			$itemAndTrack = trainingpath_report_get_my_status($item->id, $item->type);
			
			// Status
			$myStatusData = false;
			if (isset($itemAndTrack->track)) {
				$myStatusData = trainingpath_report_get_indicator_data($itemAndTrack->track, $learningpath);
			}
	
			// Determine if it is the current status
			$nextActivity = false;
			if ($allCompleted && !$item->complementary && (!$itemAndTrack || !isset($itemAndTrack->track) || $itemAndTrack->track->completion != EATPL_COMPLETION_COMPLETED)) {
				$allCompleted = false;
				$nextActivity = true;
			}
			
			// Schedule
			$scheduleInfo = false;
			if ($groupId) {
				$schedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$item->id, 'context_type'=>$type, 'group_id'=>$groupId));
				if ($schedule) {
					$scheduleInfo = trainingpath_get_schedule_access_info($type, $schedule, true, $item);
				} else {
					$scheduleInfo = trainingpath_get_default_schedule_access_info($type, true, $item);
				}
			}
			
			// Next item found
			if ($nextActivity) {
				$res->next = new stdClass();
				$res->next->item_and_track = $itemAndTrack;
				$res->next->status_data = $myStatusData;
				$res->next->schedule_info = $scheduleInfo;
				
			} else {
				// Last item found
				$res->last = new stdClass();
				$res->last->item_and_track = $itemAndTrack;
				$res->last->status_data = $myStatusData;
				$res->last->schedule_info = $scheduleInfo;
			}
		}
	}
	return $res;
}

function trainingpath_view_get_current_item_html($cm, $itemAndTrack, $openUrl, $myStatusData = false, $scheduleInfo = false) {
	$res = '';
	
	// Batch status
	$myStatusHtml = '';
	if ($myStatusData) $myStatusHtml = trainingpath_report_get_indicator_html($myStatusData, 'default');
	$myStatusHtml = trainingpath_get_div($myStatusHtml, 'mystatus');

	// Batch content
	$content = '';
	if ($scheduleInfo) $content = trainingpath_get_div($scheduleInfo->display, 'schedule');
	$content .= $itemAndTrack->description;

	// Batch title		
	$batchTitle = '['.$itemAndTrack->code.'] '.$itemAndTrack->title;

	// Commands
	$commands = array();
	if (!$scheduleInfo || $scheduleInfo->status == 'open') $commands = trainingpath_view_get_item_commands($cm->id, $itemAndTrack, $openUrl);
	
	// Batch card
	$res .= '<div id="trainingpath-cards">';
	$res .= trainingpath_get_card($content, null, null, $batchTitle, null, $commands, null, $myStatusHtml);
	$res .= '</div>';
	
	return $res;
}


//------------------------------------------- Items -------------------------------------------//
  
function trainingpath_view_get_items($type, $course, $cm, $learningpath, $parentId, $openUrl, $editUrl, $grouping = false) {
	global $DB, $OUTPUT;

	// Get items
	if ($grouping) $items = array_values($DB->get_records('trainingpath_item', array('grouping_id'=>$parentId, 'type'=>$type), 'grouping_position'));
	else $items = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$parentId, 'type'=>$type), 'parent_position'));
	
	// Get scheduled group
	$groupId = trainingpath_get_scheduled_group_id($course, $cm);
	
	// Done sequences grouping
	$doneFirst = true;
	$doneStarted = false;
	
	// Start parsing
	$res = '<div id="trainingpath-cards">';
	
	if (count($items) > 0) {

		$allCompleted = true;
		foreach($items as $item) {

			// Title
			if ($item->code) $item->title = '['.$item->code.'] '.$item->title;

			// My Status
			$itemAndTrack = trainingpath_report_get_my_status($item->id, $item->type);
			if (!$itemAndTrack || !isset($itemAndTrack->track)) $myStatusHtml = '';
			else {
				$myStatusData = trainingpath_report_get_indicator_data($itemAndTrack->track, $learningpath);
				$myStatusHtml = trainingpath_report_get_indicator_html($myStatusData, 'default');
			}
			$myStatusHtml = trainingpath_get_div($myStatusHtml, 'mystatus');

			// Current activity
			$currentActivity = false;
			if ($allCompleted && !$item->complementary && $item->type != EATPL_ITEM_TYPE_CERTIFICATE && (!$itemAndTrack || !isset($itemAndTrack->track) || $itemAndTrack->track->completion != EATPL_COMPLETION_COMPLETED)) {
				$allCompleted = false;
				$currentActivity = true;
			}
			
			// Schedule
			$scheduleHtml = '';
			if ($groupId && $type != EATPL_ITEM_TYPE_CERTIFICATE) {
				$schedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$item->id, 'context_type'=>$type, 'group_id'=>$groupId));
				if ($schedule) {
					$scheduleInfo = trainingpath_get_schedule_access_info($type, $schedule, true, $item);
				} else {
					$scheduleInfo = trainingpath_get_default_schedule_access_info($type, true, $item);
				}
				$scheduleHtml = trainingpath_get_div($scheduleInfo->display, 'schedule');
			}

			// Content
			$content = $scheduleHtml.$item->description;
			
			// Commands
			$commands = array();
			if (empty($scheduleHtml) || $scheduleInfo->status == 'open') $commands = trainingpath_view_get_item_commands($cm->id, $item, $openUrl, $editUrl, $learningpath->locked);

			// Done opening
			if ($type == EATPL_ITEM_TYPE_SEQUENCE && $doneFirst && !$currentActivity) {
				$res .= '
					<p><button class="btn btn-secondary collapsed trainingpath-custom" type="button" data-toggle="collapse" data-target="#trainingpath-cards-done">
						<i class="fa"></i>'.get_string('show_hide_acheived_sequences', 'trainingpath').'
					</button></p>
					<div id="trainingpath-cards-done" class="collapse">';
				$doneStarted = true;
			}
			$doneFirst = false;
			
			// Done closing
			if ($type == EATPL_ITEM_TYPE_SEQUENCE && $doneStarted && $currentActivity) {
				$res .= '</div>';
			}
			
			// Card
			$res .= trainingpath_get_card($content, null, null, $item->title, null, $commands, null, $myStatusHtml, $currentActivity);
		}
	} else {
		$res .= '<p>'.get_string('no_'.trainingpath_item_type_name($type), 'trainingpath').'</p>';		
	}
	$res .= '</div>';
	return $res;
}


//------------------------------------------- Activities -------------------------------------------//
  
function trainingpath_view_get_activities($course, $cm, $learningpath, $sequenceId, $via) {
	global $DB;
	
	// Get items
	$items = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$sequenceId, 'type'=>EATPL_ITEM_TYPE_ACTIVITY), 'parent_position'));
	
	// Get scheduled group
	$groupId = trainingpath_get_scheduled_group_id($course, $cm);	

	// Start parsing
	$res = '<div id="trainingpath-cards">';
	if (count($items) > 0) {
		
		$allCompleted = true;
		foreach($items as $item) {

			// Title
			if ($item->code) $item->title = '['.$item->code.'] '.$item->title;
			
			// My Status
			$itemAndTrack = trainingpath_report_get_my_status($item->id, $item->type);
			if (!$itemAndTrack || !isset($itemAndTrack->track)) $myStatusHtml = '';
			else {
				$myStatusData = trainingpath_report_get_indicator_data($itemAndTrack->track, $learningpath);
				$myStatusHtml = trainingpath_report_get_indicator_html($myStatusData, 'default');
			}
			
			// SF2017 - Icons
			// if (empty($myStatusHtml) && !$item->complementary) $myStatusHtml = '<img src="'.trainingpath_get_icon('alert').'">';
			if (empty($myStatusHtml) && !$item->complementary) $myStatusHtml = trainingpath_get_icon('alert');

			$myStatusHtml = trainingpath_get_div($myStatusHtml, 'mystatus');

			// Current activity
			$currentActivity = false;
			if ($allCompleted && !$item->complementary && (!$itemAndTrack || !isset($itemAndTrack->track) || $itemAndTrack->track->completion != EATPL_COMPLETION_COMPLETED)) {
				$allCompleted = false;
				$currentActivity = true;
			}
			
			// Schedule
			$scheduleHtml = '';
			if ($groupId) {
				$schedule = $DB->get_record('trainingpath_schedule', array('context_id'=>$item->id, 'context_type'=>EATPL_ITEM_TYPE_ACTIVITY, 'group_id'=>$groupId));
				if ($schedule) {
					$scheduleInfo = trainingpath_get_schedule_access_info(EATPL_ITEM_TYPE_ACTIVITY, $schedule, true, $item);
				} else {
					$scheduleInfo = trainingpath_get_default_schedule_access_info(EATPL_ITEM_TYPE_ACTIVITY, true, $item, $item->activity_type, $item->complementary, $item->remedial);
				}
				if ($scheduleInfo->status == 'hidden') continue;
				$scheduleHtml = trainingpath_get_div($scheduleInfo->display, 'schedule');
			}

			// Content
			$content = $scheduleHtml.$item->description;
			
			// Commands
			$commands = array();
			if (empty($scheduleHtml) || $scheduleInfo->status == 'open') $commands = trainingpath_view_get_open_activity_commands($cm->id, $item, $via, $learningpath->locked);

			// Type
			
			// SF2017 - Icons
			// $typeHtml = trainingpath_get_div('<img src="'.trainingpath_get_icon(trainingpath_activity_type_name($item->activity_type)).'">', 'activity-type');
			$icon = trainingpath_get_icon(trainingpath_activity_type_name($item->activity_type));
			$typeHtml = trainingpath_get_div($icon, 'activity-type');
			
			// Card
			$res .= trainingpath_get_card($content, null, null, $item->title, null, $commands, $typeHtml, $myStatusHtml, $currentActivity);
		}
	} else {
		$res .= '<p>'.get_string('no_'.trainingpath_item_type_name(EATPL_ITEM_TYPE_ACTIVITY), 'trainingpath').'</p>';		
	}
	$res .= '</div>';
	return $res;
}


//------------------------------------------- Commands -------------------------------------------//

function trainingpath_view_get_item_commands($cmid, $item, $openUrl, $editUrl = null, $locked = false) {
	$commands = array();
	$commands[] = (object)array('href'=>$openUrl.$item->id, 'class'=>'primary', 'title'=>get_string('open', 'trainingpath'));
	if (!$locked && isset($editUrl) && has_capability('mod/trainingpath:addinstance', context_module::instance($cmid))) {
		$commands[] = (object)array('href'=>$editUrl.$item->id, 'class'=>'secondary', 'title'=>get_string('edit', 'trainingpath'));
	}
	return $commands;
}
							
function trainingpath_view_get_open_activity_commands($cmid, $item, $via, $locked) {
	$open = trainingpath_get_activity_open_command($cmid, $item, $via);
	$editUrl = new moodle_url('/mod/trainingpath/edit/activity.php', array('cmid'=>$cmid, 'via'=>$via, 'activity_id'=>$item->id, 'sequence_id'=>$item->parent_id));
	$commands = array();
	$commands[] = (object)array('href'=>$open->url, 'target'=>$open->target, 'class'=>'primary', 'title'=>get_string('open', 'trainingpath'));
	if (!$locked && has_capability('mod/trainingpath:addinstance', context_module::instance($cmid))) {
		$commands[] = (object)array('href'=>$editUrl, 'class'=>'secondary', 'title'=>get_string('edit', 'trainingpath'));
	}
	return $commands;
}

function trainingpath_view_get_backfrom_activity_commands($cmid, $backLabel, $backUrl, $editLabel, $editUrl, $locked) {
	$commands = array();
	$commands[] = (object)array('href'=>$backUrl, 'class'=>'secondary', 'title'=>$backLabel);
	if (has_capability('mod/trainingpath:addinstance', context_module::instance($cmid)) && !$locked)
		$commands[] = (object)array('href'=>$editUrl, 'class'=>'secondary', 'title'=>$editLabel);
	return $commands;
}

function trainingpath_view_get_edit_commands($cmid, $label, $url, $commands = array()) {
	if (!has_capability('mod/trainingpath:addinstance', context_module::instance($cmid))) return $commands;
	$commands[] = (object)array('href'=>$url, 'class'=>'secondary', 'title'=>$label);
	return $commands;
}


