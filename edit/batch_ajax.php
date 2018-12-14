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
require_once($CFG->dirroot.'/mod/trainingpath/locallib.php');
require_once($CFG->dirroot.'/mod/trainingpath/edit/ajaxlib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 
$action = optional_param('action', '', PARAM_RAW); 
$id = optional_param('id', 0, PARAM_INT); 
$ids = optional_param('ids', '', PARAM_RAW); 
if ($ids) $ids = explode(',', $ids);

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$topitem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
if (trainingpath_check_edit_permission($course, $cm) == 'editschedule') { http_response_code(403); die; }


//------------------------------------------- Delete -------------------------------------------//

if ($action == 'delete' && $id) {
    
    trainingpath_db_delete_item($id);
    
//------------------------------------------- Reorder -------------------------------------------//

} else if ($action == 'reorder' && $ids) {
    
    trainingpath_db_reorder_item($ids);
    
}

trainingpath_json_response_edit_items($cmid, $topitem->id, EATPL_ITEM_TYPE_PATH, 'path', EATPL_ITEM_TYPE_BATCH, 'batch', EATPL_ITEM_TYPE_SEQUENCE, 'sequences');

?>

