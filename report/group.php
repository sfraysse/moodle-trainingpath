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
require_once($CFG->dirroot.'/mod/trainingpath/report/uilib.php');

// Params
$cmid = required_param('cmid', PARAM_INT); 
$group_id = required_param('group_id', PARAM_INT); 

// Useful objects and vars
$cm = get_coursemodule_from_id('trainingpath', $cmid, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
$learningpath = $DB->get_record('trainingpath', array('id'=>$cm->instance), '*', MUST_EXIST);
$topItem = $DB->get_record('trainingpath_item', array('path_id'=>$learningpath->id, 'type'=>EATPL_ITEM_TYPE_PATH), '*', MUST_EXIST);
$group = $DB->get_record('groups', array('id'=>$group_id), '*', MUST_EXIST);

// Page URL
$url = new moodle_url('/mod/trainingpath/edit/group_progress.php', array('cmid'=>$cmid, 'group_id'=>$group_id));
$PAGE->set_url($url);

// Check permissions
$context_module = context_module::instance($cmid);
$context_course = context_course::instance($course->id);
require_login($course, false, $cm);
$permission = trainingpath_check_tutor_permission($course, $cm, $group_id);

// Page setup
$breadcrumb = array();
$breadcrumb[] = array('label'=>get_string('reporting', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/report/groups.php', array('cmid'=>$cmid)))->out());
$breadcrumb[] = array('label'=>$group->name);
$breadcrumb[] = array('label'=>get_string('group_progress', 'trainingpath'));
trainingpath_tutor_setup_page($course, 'reporting', $breadcrumb, null, $permission);


//------------------------------------------- Display groups -------------------------------------------//

// Prepare data
$data = trainingpath_report_get_group_recursive_status($group_id, $context_module, $topItem->id, false);

// Title
$statusData = trainingpath_report_get_indicator_data($data->track, $learningpath);
$status = trainingpath_report_get_indicator_html($statusData, 'right-align');
echo trainingpath_get_title_with_status($group->name, $status);

// Content
echo trainingpath_report_get_group_html($cmid, $learningpath, $group_id, $data);

// End
echo $OUTPUT->footer();


//------------------------------------------- Scripts -------------------------------------------//
?>
