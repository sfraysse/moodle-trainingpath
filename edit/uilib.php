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


/*************************************************************************************************
 *                                             UI                                          
 *************************************************************************************************/
 

//------------------------------------------- Page Setup -------------------------------------------//


function trainingpath_edit_setup_page($course, $tab = null, $breadcrumb = null, $heading = null, $permission = 'addinstance') {
    $fullmodulename = get_string('modulename', 'trainingpath');
    $streditinga = get_string('editinga', 'moodle', $fullmodulename);
    $pageheading = get_string('updatinga', 'moodle', $fullmodulename);
	if (isset($tab)) $tabsHtml = trainingpath_edit_get_tabs($tab, $breadcrumb, $permission);
	else $tabsHtml = '';
	trainingpath_setup_page($course, $streditinga, $pageheading, true, 'admin', get_string('settings', 'trainingpath'), $tabsHtml, $breadcrumb, $heading);
}

function trainingpath_schedule_setup_page($course, $tab = null, $breadcrumb = null, $heading = null, $permission = 'addinstance') {
    $fullmodulename = get_string('modulename', 'trainingpath');
    $pageheading = get_string('scheduling_', 'trainingpath', $fullmodulename);
	if (isset($tab)) $tabsHtml = trainingpath_edit_get_tabs($tab, $breadcrumb, $permission);
	else $tabsHtml = '';
	trainingpath_setup_page($course, $pageheading, $pageheading, true, 'admin', get_string('settings', 'trainingpath'), $tabsHtml, $breadcrumb, $heading);
}

function trainingpath_tutor_setup_page($course, $tab = null, $breadcrumb = null, $heading = null, $permission = 'addinstance') {
    $fullmodulename = get_string('modulename', 'trainingpath');
    $pageheading = get_string('tutoring', 'trainingpath', $fullmodulename);
	if (isset($tab)) $tabsHtml = trainingpath_edit_get_tabs($tab, $breadcrumb, $permission);
	else $tabsHtml = '';
	trainingpath_setup_page($course, $pageheading, $pageheading, true, 'admin', get_string('settings', 'trainingpath'), $tabsHtml, $breadcrumb, $heading);
}


//------------------------------------------- Tabs -------------------------------------------//
 
function trainingpath_edit_get_tabs($activeTab = null, $hasBreadcrumb = false, $permission = 'addinstance') {
	
	// Get URL params
	$add  = optional_param('add', '', PARAM_ALPHA);
	$update  = optional_param('update', 0, PARAM_INT);
	$cmid  = optional_param('cmid', $update, PARAM_INT);
	
	// No tab for new activities
	if ($add) return '';
	
	// Define tabs
	$tabs = array();
	if ($permission == 'addinstance') {
		$tabs['path'] = array('title'=>get_string('path', 'trainingpath'), 'url'=>(new moodle_url('/course/modedit.php', array('update'=>$cmid)))->out());
		$tabs['certificates'] = array('title'=>get_string('certificates', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/certificates.php', array('cmid'=>$cmid)))->out());
		$tabs['batches'] = array('title'=>get_string('batches', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/batches.php', array('cmid'=>$cmid)))->out());
	}
	if ($permission == 'addinstance' || $permission == 'editschedule') {
		$tabs['schedules'] = array('title'=>get_string('schedules', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/edit/schedules.php', array('cmid'=>$cmid)))->out());
		$tabs['reporting'] = array('title'=>get_string('reporting', 'trainingpath'), 'url'=>(new moodle_url('/mod/trainingpath/report/groups.php', array('cmid'=>$cmid)))->out());
	}
	
	// Active tab
	if ($update) $activeTab = 'path';

	// Margin
	$marginTop = (!$update && !$add);
	$marginBottom = !$hasBreadcrumb;

	return trainingpath_get_tabs($tabs, $marginTop, $marginBottom, $activeTab);
}


//------------------------------------------- Items -------------------------------------------//
  
function trainingpath_edit_get_items($type, $cmid, $parentId = null, $grouping = false, $via = '') {
	$parentStr = '';
	if (isset($parentId)) {
		if ($grouping) $parentStr = '&grouping_id='.$parentId;
		else $parentStr = '&parent_id='.$parentId;
	}
	$res = '
		<div id="trainingpath-cards" data-url="'.$type.'_ajax.php?cmid='.$cmid.'&via='.$via.$parentStr.'">
			<p><img src="../pix/loading.gif" height="16" width="16"></p>
		</div>
		<div id="trainingpath-cards-confirm" class="modal fade">
			'.trainingpath_get_modal_confirm(get_string('delete_'.$type, 'trainingpath'), get_string('delete_'.$type.'_confirm', 'trainingpath')).'
		</div>
	';
	return $res;
}


//------------------------------------------- Schedules -------------------------------------------//
  
function trainingpath_edit_get_schedules($cmid) {
	$res = '
		<div id="trainingpath-cards" data-url="schedule_ajax.php?cmid='.$cmid.'">
			<p><img src="../pix/loading.gif" height="16" width="16"></p>
		</div>
		<div id="trainingpath-cards-confirm" class="modal fade">
			'.trainingpath_get_modal_confirm(get_string('delete_schedule', 'trainingpath'), get_string('delete_schedule_confirm', 'trainingpath')).'
		</div>
	';
	return $res;
}


//------------------------------------------- Buttons -------------------------------------------//
  
function trainingpath_edit_get_commands($commands) {
	$res = '<div class="trainingpath-commands">';
	foreach($commands as $code => $params) {
		if ($code == 'submit') {
			$res .= '<input type="submit" class="btn btn-'.$params['class'].'" href="'.$params['href'].'" name="submitbutton" id="id_submitbutton" value="'.$params['label'].'">';		
		} else {
			$res .= '<a class="btn btn-'.$params['class'].'" href="'.$params['href'].'" role="button">'.$params['label'].'</a> ';		
		}
	}
	$res .= '</div>';
	return $res;
}



/*************************************************************************************************
 *                                             FORMS                                          
 *************************************************************************************************/

 
//------------------------------------------- Colors -------------------------------------------//
  
// Add colors settings

function trainingpath_form_add_score_colors(&$form) {
	$colors = trainingpath_get_config_colors('score_colors');
	// All colors except the last one
	for ($i=0; $i < count($colors)-1; $i++) {
		$color = $colors[$i];
		$attributes = 'maxlength="3" size="3" style="background-color:'.$color->color.'"';
		$form->addElement('text', "score_colors[$i]", get_string('score_lessthan', 'trainingpath'), $attributes);
		$form->setDefault("score_colors[$i]", $color->lt);
		$form->setType("score_colors[$i]", PARAM_INT);
		$form->addRule("score_colors[$i]", null, 'numeric', null, 'client');
	}
	// The last one
	$color = $colors[$i];
	$attributes = 'disabled maxlength="3" size="3" style="background-color:'.$color->color.'"';
	$form->addElement('text', "score_colors[$i]", get_string('score_upto', 'trainingpath'), $attributes);
	$form->setDefault("score_colors[$i]", 100);
	$form->setType("score_colors[$i]", PARAM_INT);
	$form->addRule("score_colors[$i]", null, 'numeric', null, 'client');
}

function trainingpath_form_add_time_settings(&$form) {
	
	// Optimal time threeshold
	$colors = trainingpath_get_config_colors('time_colors');
	$attributes = 'maxlength="3" size="3"';
	$form->addElement('text', "time_optimum_threshold", get_string('time_optimum_threshold', 'trainingpath'), $attributes);
	$form->setDefault("time_optimum_threshold", $colors->threshold);
	$form->setType("time_optimum_threshold", PARAM_INT);
	$form->addHelpButton('time_optimum_threshold', 'time_optimum_threshold', 'trainingpath');
	$form->addRule("time_optimum_threshold", null, 'numeric', null, 'client');
	
	// Maximum activity time factor
	$attributes = 'maxlength="3" size="3"';
	$form->addElement('text', "time_max_factor", get_string('time_max_factor', 'trainingpath'), $attributes);
	$form->setDefault("time_max_factor", 2);
	$form->setType("time_max_factor", PARAM_RAW);
	$form->addHelpButton('time_max_factor', 'time_max_factor', 'trainingpath');
	$form->addRule("time_max_factor", null, 'numeric', null, 'client');
	$form->addRule('time_max_factor', get_string('time_max_factor_notvalid', 'trainingpath'), 'regex', '/^[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$/', 'client');
}


// Pre-process colors

function trainingpath_form_preprocess_score_colors(&$default_values) {	
	$thresholds = null;
	if (array_key_exists('score_colors', $default_values) && $default_values['score_colors'] != null) {
		$thresholds = trainingpath_parse_colors_thresholds($default_values['score_colors']);
		unset($default_values['score_colors']);		
		foreach ($thresholds as $i => $threshold) {
			$default_values["score_colors[$i]"] = $threshold;
		}
	}
}

function trainingpath_form_preprocess_time_settings(&$default_values) {
}


// Check colors validity

function trainingpath_form_check_score_colors($data, &$errors) {
	$colors = trainingpath_get_config_colors('score_colors');
	for ($i=0; $i < count($colors); $i++) {
		$value = $data["score_colors"][$i];
		if ($value < 0 || $value > 101) {
			$errors["score_colors[$i]"] = get_string('score_color_notvalid', 'trainingpath');
		}
	}
}

function trainingpath_form_check_time_settings($data, &$errors) {
	
	// Time threshold
	$value = $data["time_optimum_threshold"];
	if ($value < 0 || $value > 100) {
		$errors["time_optimum_threshold"] = get_string('time_optimum_threshold_notvalid', 'trainingpath');
	}
	
	// Time threshold
	$value = $data["time_max_factor"];
	if ($value < 1 || $value > 10) {
		$errors["time_max_factor"] = get_string('time_max_factor_notvalid', 'trainingpath');
	}
}

// Add action buttons

function trainingpath_form_get_buttons($buttons) {
	$html = '';
	$html .= '<div class="form-group row fitem">
				<div class="col-md-3"></div>
				<div class="col-md-9">
	';
	foreach($buttons as $type => $button) {
		if ($type == 'submit') {
			$html .= '<input type="submit" class="btn btn-primary" name="submitbutton" id="id_submitbutton" style="margin-right:5px;" value="'.$button->label.'">';
		} else if ($type == 'cancel') {
			$html .= '<a href="'.$button->url.'" class="btn btn-secondary" style="margin-right:5px;">'.$button->label.'</a>';
		}
	}
	$html .= '	</div>
			</div>';
	return $html;
}



