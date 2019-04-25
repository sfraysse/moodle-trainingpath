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

require_once($CFG->dirroot.'/mod/trainingpath/ajaxlib.php');
require_once($CFG->dirroot.'/mod/trainingpath/uilib.php');


/*************************************************************************************************
 *                                             DB Ops on Items                                         
 *************************************************************************************************/

 
 //------------------------------------------- Delete -------------------------------------------//
 
 
function trainingpath_db_delete_item($id, $recurs = false) {
   global $DB, $CFG;
   
   // Load item
   $item = $DB->get_record('trainingpath_item', array('id'=>$id));
   if (!$item) trainingpath_error_response(404);
   
   // De-assign grouped items
   $grouped = $DB->get_records('trainingpath_item', array('grouping_id'=>$id, 'type'=>trainingpath_item_child_type($item->type)));
   foreach($grouped as $groupedItem) {
	  $groupedItem->grouping_id = 0;
	  $groupedItem->grouping_position = 0;
	  $DB->update_record("trainingpath_item", $groupedItem);
   }
   
   // Delete children
   $children = $DB->get_records('trainingpath_item', array('parent_id'=>$id, 'type'=>trainingpath_item_child_type($item->type)));
   foreach($children as $child) {
	  trainingpath_db_delete_item($child->id, true);
   }
   
   // Delete bacthes if we delete the top item
   if ($item->type == EATPL_ITEM_TYPE_PATH) {
	  $children = $DB->get_records('trainingpath_item', array('parent_id'=>$id, 'type'=>EATPL_ITEM_TYPE_BATCH));
	  foreach($children as $child) {
		 trainingpath_db_delete_item($child->id, true);
	  }
   }
   
   // If it is an activity
   if ($item->ref_id) {
	  
	  // ScormLite 
	  if ($item->activity_type == EATPL_ACTIVITY_TYPE_CONTENT || $item->activity_type == EATPL_ACTIVITY_TYPE_EVAL) {
		 require_once($CFG->dirroot.'/mod/scormlite/sharedlib.php');
		 scormlite_delete_sco($item->ref_id);
		 
	  // Files 
	  } else if ($item->activity_type == EATPL_ACTIVITY_TYPE_FILES) {
		 $res = $DB->delete_records('trainingpath_files', array('id'=>$item->ref_id));
		 if (!$res) trainingpath_error_response(500);
	  }
   }
   
   // Delete the item
   $res = $DB->delete_records('trainingpath_item', array('id'=>$id));
   if (!$res) trainingpath_error_response(500);
   
   // Delete schedule
   $res = $DB->delete_records('trainingpath_schedule', array('context_id'=>$id, 'context_type'=>$item->type));
   if (!$res) trainingpath_error_response(500);
	  
   // Delete tracks
   $res = $DB->delete_records('trainingpath_tracks', array('context_id'=>$id, 'context_type'=>$item->type));
   if (!$res) trainingpath_error_response(500);
	  
   // Delete comments
   $res = $DB->delete_records('trainingpath_comments', array('context_id'=>$id, 'context_type'=>$item->type));
   if (!$res) trainingpath_error_response(500);
	  
   // Update rolldown information
   if (!$recurs) {
	  if ($item->type == EATPL_ITEM_TYPE_ACTIVITY) trainingpath_db_update_rolldown($item->parent_id, EATPL_ITEM_TYPE_SEQUENCE);
	  else if ($item->type == EATPL_ITEM_TYPE_SEQUENCE) trainingpath_db_update_rolldown($id, EATPL_ITEM_TYPE_SEQUENCE, $item->parent_id, $item->grouping_id);
   }
}
 
 
//------------------------------------------- Reorder -------------------------------------------//

 
function trainingpath_db_reorder_item($ids, $grouping = false) {
	global $DB;
    $position = 1;
	
	// Save data - Should use transactions !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    foreach($ids as $id) {
        $record = $DB->get_record('trainingpath_item', array('id'=>$id));
        if (!$record) trainingpath_error_response(404);
        if ($grouping) $record->grouping_position = $position;
		else $record->parent_position = $position;
        $res = $DB->update_record('trainingpath_item', $record);
	    if (!$res) trainingpath_error_response(500);
        $position++;
    }
	
   // Update rolldown information
   if ($record->type == EATPL_ITEM_TYPE_ACTIVITY) trainingpath_db_update_rolldown($record->parent_id, EATPL_ITEM_TYPE_SEQUENCE);
   else if ($record->type == EATPL_ITEM_TYPE_SEQUENCE) trainingpath_db_update_rolldown($record->id, EATPL_ITEM_TYPE_SEQUENCE);
   else if ($record->type == EATPL_ITEM_TYPE_BATCH) trainingpath_db_update_rolldown($record->id, EATPL_ITEM_TYPE_BATCH);
}


//------------------------------------------- Rolldown -------------------------------------------//


// Rolldown dispatch

function trainingpath_db_update_rolldown($itemId, $itemType, $parentId = null, $groupingId = null) {
	global $DB;
	switch($itemType) {
	  case EATPL_ITEM_TYPE_CERTIFICATE :
		 trainingpath_db_update_rolldown_certificate($itemId);
		 break;
	  case EATPL_ITEM_TYPE_BATCH :
		 trainingpath_db_update_rolldown_batch($itemId);
		 break;
	  case EATPL_ITEM_TYPE_SEQUENCE :
		 if (isset($parentId) || isset($groupingId)) {
			if (isset($groupingId)) trainingpath_db_update_rolldown_certificate($groupingId);
			if (isset($parentId)) trainingpath_db_update_rolldown_batch($parentId);
		 } else {
     		$sequence = $DB->get_record('trainingpath_item', array('id'=>$itemId, 'type'=>EATPL_ITEM_TYPE_SEQUENCE));
			trainingpath_db_update_rolldown_certificate($sequence->grouping_id);
			trainingpath_db_update_rolldown_batch($sequence->parent_id);
		 }
		 break;
	}
}

// Rolldown certificate: impact on calculated durations only

function trainingpath_db_update_rolldown_certificate($itemId) {
   global $DB;
   $certificate = $DB->get_record('trainingpath_item', array('id'=>$itemId, 'type'=>EATPL_ITEM_TYPE_CERTIFICATE));
	
   // Parse down
   $data = array();
   $total = 0;
   $sequences = $DB->get_records('trainingpath_item', array('grouping_id'=>$certificate->id, 'type'=>EATPL_ITEM_TYPE_SEQUENCE));
   foreach($sequences as $sequence) {
	  $seqData = new stdClass();
	  $seqData->total = 0;
	  $seqData->evaluation = 0;
	  $activities = $DB->get_records('trainingpath_item', array('parent_id'=>$sequence->id, 'type'=>EATPL_ITEM_TYPE_ACTIVITY));
	  foreach($activities as $activity) {
		 if (isset($activity->duration)) $seqData->total += $activity->duration;
		 $seqData->evaluation = $seqData->evaluation || $activity->evaluation;
	  }
	  $total += $seqData->total;
	  $data[$sequence->id] = $seqData;
   }	  
	
   // Use transactions !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
   
    // Calculate parts
    foreach($data as $sequenceId => $sequenceData) {
        if ($total > 0) $sequenceData->percent = $sequenceData->total * 100 / $total;
        else $sequenceData->percent = 0;
        $nominal = intVal($sequenceData->percent * $certificate->duration / 100);
        $sequence = $DB->get_record('trainingpath_item', array('id'=>$sequenceId, 'type'=>EATPL_ITEM_TYPE_SEQUENCE));
        $sequence->duration_down = $nominal;
        $sequence->duration_up = $sequenceData->total;
        $sequence->evaluation = $sequenceData->evaluation;
        $DB->update_record("trainingpath_item", $sequence);
      
        // Calculate the duration_down for each activity
        $activities = $DB->get_records('trainingpath_item', array('parent_id' => $sequenceId, 'type' => EATPL_ITEM_TYPE_ACTIVITY));
        foreach ($activities as $activity) {
            if (!isset($activity->duration) || !$sequence->duration_up) continue;
            $activity->duration_down = intval($activity->duration * $sequence->duration_down / $sequence->duration_up);
            $DB->update_record("trainingpath_item", $activity);
        }
    }
}

// Rolldown batch: impact on calculated durations only
// No relation between batches

function trainingpath_db_update_rolldown_batch($itemId) {
   global $DB;

   // Use transactions !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
   
   $previousId = 0;
   $batch = $DB->get_record('trainingpath_item', array('id'=>$itemId, 'type'=>EATPL_ITEM_TYPE_BATCH));
   $sequences = $DB->get_records('trainingpath_item', array('parent_id'=>$batch->id, 'type'=>EATPL_ITEM_TYPE_SEQUENCE), 'parent_position');
   foreach($sequences as $sequence) {
	  $activities = $DB->get_records('trainingpath_item', array('parent_id'=>$sequence->id, 'type'=>EATPL_ITEM_TYPE_ACTIVITY), 'parent_position');
	  foreach($activities as $activity) {
		 $activity->previous_id = $previousId;
		 $DB->update_record("trainingpath_item", $activity);
		 $previousId = $activity->id;
	  }
	  $previousId = $sequence->id;
   }
}


/*************************************************************************************************
 *                                             DB Ops on Schedules                                         
 *************************************************************************************************/


//------------------------------------------- Delete -------------------------------------------//


function trainingpath_db_delete_schedule($id) {
   global $DB, $CFG;
   
   // Check it exists
   $schedule = $DB->get_record('trainingpath_schedule', array('id'=>$id));
   if (!$schedule) trainingpath_error_response(404);
   
   // Delete the schedule and children schedule
   trainingpath_db_delete_item_schedule($schedule->context_id, $schedule->group_id);
}

function trainingpath_db_delete_item_schedule($itemId, $groupId) {
   global $DB;
   
   // Delete attached schedules
   $res = $DB->delete_records('trainingpath_schedule', array('context_id'=>$itemId, 'group_id'=>$groupId));
   if (!$res) trainingpath_error_response(500);
   
   // Do the same with item children
   $children = $DB->get_records('trainingpath_item', array('parent_id'=>$itemId));
   foreach($children as $child) {
	  trainingpath_db_delete_item_schedule($child->id, $groupId);
   }
} 
 
//------------------------------------------- Reorder -------------------------------------------//


function trainingpath_db_reorder_schedule($ids) {
	global $DB;
    $position = 1;
	// Save data - Should use transactions !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
    foreach($ids as $id) {
        $record = $DB->get_record('trainingpath_schedule', array('id'=>$id));
        if (!$record) trainingpath_error_response(404);
        $record->position = $position;
        $res = $DB->update_record('trainingpath_schedule', $record);
	    if (!$res) trainingpath_error_response(500);
        $position++;
    }
}
 

/*************************************************************************************************
 *                                             JSON  Responses                                        
 *************************************************************************************************/
 

 //------------------------------------------- Items -------------------------------------------//

 
function trainingpath_json_response_edit_items($cmid, $parent_id, $parent_type, $parent_type_name, $type, $type_name, $children_type = null, $children_type_name = null, $grouping = false, $via = '') {
   global $DB, $OUTPUT, $CFG;
   require_once($CFG->dirroot.'/mod/trainingpath/uilib.php');
   if ($grouping) $items = array_values($DB->get_records('trainingpath_item', array('grouping_id'=>$parent_id, 'type'=>$type), 'grouping_position'));
   else $items = array_values($DB->get_records('trainingpath_item', array('parent_id'=>$parent_id, 'type'=>$type), 'parent_position'));
   if (!isset($children_type_name)) {
	  foreach($items as $item) {
		 $item->open = trainingpath_get_activity_open_command($cmid, $item, $via);
	  }
   }

   // SF2017 - Icons
   $lang = array(
	  'no_description'=>get_string('no_description', 'trainingpath'),
	  'empty'=>get_string('no_'.$type_name, 'trainingpath'),
   );
   $icon = array(
	  'dragdrop'=>trainingpath_get_icon('dragdrop'),
	  'edit'=>trainingpath_get_icon('edit', get_string('edit', 'trainingpath')),
	  'delete'=>trainingpath_get_icon('delete', get_string('delete', 'trainingpath'))
   );
   
   $url = array(
	  'edit'=>$type_name.'.php?cmid='.$cmid.'&'.$parent_type_name.'_id='.$parent_id.'&via='.$via.'&'.$type_name.'_id='
   );
   if (isset($children_type_name)) {

	  // SF2017 - Icons
	  //$lang['children'] = get_string('edit_'.$children_type_name, 'trainingpath');
	  $icon['children'] = trainingpath_get_icon('children', get_string('edit_'.$children_type_name, 'trainingpath'));

	  $url['children'] = $children_type_name.'.php?cmid='.$cmid.'&via='.$via.'&'.$type_name.'_id=';
   } else {	  
	  $lang['open'] = get_string('preview', 'trainingpath');
   }
   $data = array('items'=>$items, 'lang'=>$lang, 'icon'=>$icon, 'url'=>$url);
   trainingpath_json_response($data);
}
 

//------------------------------------------- Schedules -------------------------------------------//


function trainingpath_json_response_edit_top_schedules($cmid, $context_id, $groupIds) {
   global $DB, $OUTPUT;
   $schedules = array_values($DB->get_records('trainingpath_schedule', array('cmid'=>$cmid, 'context_id'=>$context_id, 'context_type'=>EATPL_ITEM_TYPE_PATH), 'position'));
   $finalSchedules = array();
   foreach($schedules as $schedule) {
	  if (!in_array($schedule->group_id, $groupIds)) continue;
      $group = $DB->get_record('groups', array('id'=>$schedule->group_id));
	  if ($group) $schedule->title = get_string('schedule', 'trainingpath').': '.$group->name;
	  else $schedule->title = get_string('no_matching_group', 'trainingpath');
	  $finalSchedules[] = $schedule;
   }
   
   // SF2017 - Icons
   $lang = array(
	  'no_description'=>get_string('no_description', 'trainingpath'),
	  'empty'=>get_string('no_schedule', 'trainingpath'),
   );
   $icon = array(
	  'dragdrop'=>trainingpath_get_icon('dragdrop'),
	  'edit'=>trainingpath_get_icon('edit', get_string('edit', 'trainingpath')),
	  'delete'=>trainingpath_get_icon('delete', get_string('delete', 'trainingpath')),
	  'batches'=>trainingpath_get_icon('children', get_string('schedule_batches', 'trainingpath'))
   );
   
   $url = array(
	  'edit'=>'schedule.php?cmid='.$cmid.'&group_id=',
	  'batches'=>'schedule_batches.php?cmid='.$cmid.'&group_id='
   );
   $data = array('schedules'=>$finalSchedules, 'lang'=>$lang, 'icon'=>$icon, 'url'=>$url);
   trainingpath_json_response($data);
}
 

