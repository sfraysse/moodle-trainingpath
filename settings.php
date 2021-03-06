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

if ($ADMIN->fulltree) {

	require_once($CFG->dirroot . '/mod/trainingpath/locallib.php');

	// Passing score (mainly for certificates)
    $settings->add(new admin_setting_configtext('trainingpath/passing_score', get_string('passing_score', 'trainingpath'), get_string('passing_score_desc','trainingpath'), 75, PARAM_INT));

	// Enabled extensions for File activities
	$default = 'zip,pdf,ppt,pptx,doc,docx';
	$settings->add(new admin_setting_configtext('trainingpath/file_extensions', get_string('file_extensions', 'trainingpath'), get_string('file_extensions_desc','trainingpath'), $default, PARAM_RAW, 100));

	// Score colors
	$jsoncolors = '[{"lt":50, "color":"#D53B3B"}, {"lt":65, "color":"#EF7A00"}, {"lt":75, "color":"#FDC200"}, {"lt":101,"color":"#85C440"}]';
	$settings->add(new admin_setting_configtext('trainingpath/score_colors', get_string('score_colors', 'trainingpath'), get_string('score_colors_desc','trainingpath'), $jsoncolors, PARAM_RAW, 100));

	// Time colors
	$jsoncolors = '{"lt_min":"#D53B3B", "lt_nominal":"#EF7A00", "lt_threshold":"#FDC200", "lt_max":"#85C440", "threshold":10}';
	$settings->add(new admin_setting_configtext('trainingpath/time_colors', get_string('time_colors', 'trainingpath'), get_string('time_colors_desc','trainingpath'), $jsoncolors, PARAM_RAW, 100));

    // Player close button
	$settings->add(new admin_setting_configcheckbox('trainingpath/displayclosebutton', get_string('displayclosebutton', 'scormlite'), get_string('displayclosebuttondesc', 'scormlite'), 0));

	// Prefered activity access for intiial tests
	$options = trainingpath_access_select([EATPL_ACCESS_ON_COMPLETION, EATPL_ACCESS_OPEN, EATPL_ACCESS_CLOSED]);
	$settings->add(new admin_setting_configselect('trainingpath/prefered_activity_access_eval', get_string('prefered_activity_access_eval', 'trainingpath'), get_string('prefered_activity_access_eval_desc', 'trainingpath'), EATPL_ACCESS_ON_COMPLETION, $options));

	// Prefered activity access for remedial tests
	$options = trainingpath_access_select([EATPL_ACCESS_AS_REMEDIAL, EATPL_ACCESS_OPEN, EATPL_ACCESS_CLOSED]);
	$settings->add(new admin_setting_configselect('trainingpath/prefered_activity_access_remedial', get_string('prefered_activity_access_remedial', 'trainingpath'), get_string('prefered_activity_access_remedial_desc', 'trainingpath'), EATPL_ACCESS_AS_REMEDIAL, $options));

	// Prefered activity access for others
	$options = trainingpath_access_select([EATPL_ACCESS_ON_COMPLETION, EATPL_ACCESS_OPEN]);
	$settings->add(new admin_setting_configselect('trainingpath/prefered_activity_access', get_string('prefered_activity_access', 'trainingpath'), get_string('prefered_activity_access_desc', 'trainingpath'), EATPL_ACCESS_ON_COMPLETION, $options));

}

