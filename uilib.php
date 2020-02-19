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

require_once($CFG->dirroot.'/mod/trainingpath/locallib.php');


//------------------------------------------- Page Setup -------------------------------------------//

function trainingpath_setup_page($course, $browserTitle, $pageTitle = null, $icon = false, $layout = null, $navbar = null, $tabsHtml = null, $breadcrumb = null, $heading = null) {
    global $PAGE, $OUTPUT;
    if (isset($layout)) $PAGE->set_pagelayout($layout);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_title($browserTitle);
    if (isset($navbar)) $PAGE->navbar->add($navbar);
    echo $OUTPUT->header();
    if (isset($pageTitle)) {
        if ($icon) echo $OUTPUT->heading_with_help($pageTitle, 'modulename', 'trainingpath', 'icon');
        else echo $OUTPUT->heading($pageTitle);
    }
	
	// Tabs
	if (isset($tabsHtml)) echo $tabsHtml;
	// Breadcumb
	if (isset($breadcrumb)) echo trainingpath_get_breadcrumb($breadcrumb);
	// Title
	if (isset($heading)) echo '<h3>'.$heading.'</h3>';
}


//------------------------------------------- Title with status -------------------------------------------//

function trainingpath_get_title_with_status($title, $status) {
	$html = '';
	$html .= '<div class="trainingpath-title">';
	$html .= '	<div class="trainingpath-title-status">'.$status.'</div>';
	$html .= '	<h3>'.$title.'</h3>';
	$html .= '</div>';
	return $html;
}

function trainingpath_get_content_with_status($content, $status) {
	$html = '';
	$html .= '<div class="trainingpath-title">';
	$html .= '	<div class="trainingpath-title-status">'.$status.'</div>';
	$html .= $content;
	$html .= '</div>';
	return $html;
}


//------------------------------------------- Breadcumb -------------------------------------------//
 
function trainingpath_get_breadcrumb($breadcrumb) {
	$res = '<ol class="breadcrumb">';
	$num = 1;
	$active = '';
	foreach($breadcrumb as $item) {
		if (count($breadcrumb) == $num) $active = ' active';
		if (isset($item['url'])) {
			$res .= '<li class="breadcrumb-item'.$active.'"><a href="'.$item['url'].'">'.$item['label'].'</a></li>';
		} else {
			$res .= '<li class="breadcrumb-item'.$active.'">'.$item['label'].'</li>';
		}
		$num++;
	}
	$res .= '</ol>';
	return $res;
}


//------------------------------------------- Tabs -------------------------------------------//
 
function trainingpath_get_tabs($tabs, $marginTop = false, $marginBottom = false, $activeTab = null) {
	
    // Margin
    $margin = '';
    if ($marginTop) $margin .= 'margin-top:20px;';
    if ($marginBottom) $margin .= 'margin-bottom:20px;';
	
    // Tabs
	$tabsHtml = '<ul class="nav nav-tabs" style="'.$margin.'">';
    foreach($tabs as $name => $tab) {
        $active = '';
        if ($name == $activeTab) $active = ' active';
    	$tabsHtml .= '<li class="nav-item"><a class="nav-link'.$active.'" href="'.$tab['url'].'">'.$tab['title'].'</a></li>';
    }
	$tabsHtml .= '</ul>';
	return $tabsHtml;
}


//------------------------------------------- Links -------------------------------------------// 


function trainingpath_get_activity_open_command($cmid, $item, $via) {
	global $DB, $CFG;
	$res = new stdClass();
	switch($item->activity_type) {
		case EATPL_ACTIVITY_TYPE_FILES :
			$files = $DB->get_record('trainingpath_files', array('id'=>$item->ref_id));
			$res->url = $CFG->wwwroot.'/pluginfile.php/'.context_module::instance($cmid)->id.'/mod_trainingpath/files_content/'.$item->ref_id.'/'.$files->revision.'/'.$files->launch_file;
			$res->target = '_blank';
			break;
		case EATPL_ACTIVITY_TYPE_CONTENT :
		case EATPL_ACTIVITY_TYPE_EVAL :
		case EATPL_ACTIVITY_TYPE_CLASSROOM :
		case EATPL_ACTIVITY_TYPE_VIRTUAL :
		case EATPL_ACTIVITY_TYPE_RICHTEXT :
			$res->url = (new moodle_url('/mod/trainingpath/view/activity_'.trainingpath_activity_type_name($item->activity_type).'.php', array('cmid'=>$cmid, 'via'=>$via, 'activity_id'=>$item->id)))->out();
			$res->target = '_self';
			break;
	}
	return $res;
}


//------------------------------------------- Layout -------------------------------------------//


function trainingpath_get_div($content, $class = '', $style = '', $title = '') {
	$classnames = '';
	if (is_array($class)) {
		foreach($class as $cl) {
			$classnames .= ' trainingpath-'.$cl;
		}
	} else if (!empty($class)) {
		$classnames = 'trainingpath-'.$class;
	}
	if (!empty($title)) {
		$content = '<p style="margin-top:-10px;margin-bottom:5px;"><small>'. $title . '</small></p>' . $content;
	}
	return '<div class="'.$classnames.'" style="'.$style.'">'.$content.'</div>';
}

function trainingpath_get_export_div($exports, $title = '') {
    $commands = array();
	foreach($exports as $export) {
		$params = $export->params;
		$params['format'] = $export->format;
		$url = new moodle_url($export->url, $params);
		$commands[] = (object)array('title'=>$export->title, 'href'=>$url->out(), 'target'=>'_blank', 'class'=>'secondary');
	}
	return trainingpath_get_commands_div($commands, 'export', $title);
}

function trainingpath_get_commands_div($commands, $divClass = '', $title = '') {
	if (is_array($divClass)) $divClass = implode(' ', $divClass);
	if (!empty($divClass)) $divClass .= ' ';
	$divClass .= 'commands';
	$divClass = explode(' ', $divClass);
	$commandsDiv = '';
	if (!empty($commands)) {
		foreach($commands as $command) {
			if (isset($command->html)) {
				$commandsDiv .= $command->html;
			} else {
				$class = 'btn ';
				if (isset($command->class)) $class .= 'btn-'.$command->class.' ';
				if (isset($command->size)) $class .= 'btn-'.$command->size.' ';
				if (isset($command->target)) $target = $command->target;
				else $target = '_self';
				$commandsDiv .= '<a href="'.$command->href.'" target="'.$target.'" class="'.$class.'" role="button">'.$command->title.'</a>';
			}
		}
		$commandsDiv = trainingpath_get_div($commandsDiv, $divClass, '', $title);
	}
	return $commandsDiv;
}

function trainingpath_get_card($content, $class = '', $style = '', $title = '', $tools = array(), $commands = array(), $leftZone = null, $rightZone = null, $highlight = false) {
	
	// Styling
	$classnames = '';
	if (is_array($class)) {
		foreach($class as $cl) {
			$classnames .= ' trainingpath-'.$cl;
		}
	} else if (!empty($class)) {
		$classnames = 'trainingpath-'.$class;
	}
	
	// Title and tools
	$header = '';
	if ($title != '') {
		if (!empty($tools)) {
			$header .= '<div class="card-header with-tools">
							<div class="card-tools">';
			foreach($tools as $tool) {
				$header .= '	<a href="'.$tool->href.'"><img src="'.$tool->img.'" title="'.$tool->title.'"></a>';
			}
			$header .= '	</div>'; 
		} else {
			$header .= '<div class="card-header">'; 
		}
		$header .= '		<div class="trainingpath-card-space"></div>
							<h4 class="card-title">'.$title.'</h4>
						</div>'; 
	}
	
	// Wrapper class
	$wrapperClass = '';
	if (isset($leftZone) && isset($rightZone)) $wrapperClass = 'trainingpath-left-right-columns';
	else if (isset($leftZone)) $wrapperClass = 'trainingpath-left-column';
	else if (isset($rightZone)) $wrapperClass = 'trainingpath-right-column';
	if ($highlight) $wrapperClass .= ' trainingpath-highlight';
	
	// Card
	$res = '<div class="trainingpath-card-wrapper '.$wrapperClass.'">';
	if (isset($leftZone)) $res.= '	<div class="trainingpath-card-left">'.$leftZone.'</div>';
	if (isset($rightZone)) $res.= '	<div class="trainingpath-card-right">'.$rightZone.'</div>';
	$res .= '	<div class="card trainingpath-card '.$classnames.'" style="'.$style.'">';
	$res.= $header;
	$res.= '		<div class="card-block">
						'.$content.trainingpath_get_commands_div($commands).'
						<div class="trainingpath-card-space"></div>
					</div>';
	$res.= '	</div>';
	$res.= '</div>';
	return $res;
}

function trainingpath_get_modal_confirm($title, $content) {
	$res = '
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">' . $title . '</h4>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">'.$content.'</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary confirm">'.get_string('confirm', 'trainingpath').'</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">'.get_string('cancel', 'trainingpath').'</button>
				</div>
			</div>
		</div>
	';
	return $res;
}

function trainingpath_get_table($rows, $head = null, $collapsible = false, $collapseId = null) {
	$res = '<table class="table trainingpath-table">';
	if (isset($head)) {
		if (isset($head->class)) $res .= '<thead class="'.$head->class.'"><tr>';
		else $res .= '<thead><tr>';
		foreach($head->cells as $cell) {
			$class = '';
			if (isset($cell->class)) $class = ' class="trainingpath-cell-'.$cell->class.'"';
			$res .= '<th'.$class.'>'.$cell->content.'</th>';
		}
		$res .= '</tr></thead>';
	}
	if ($collapsible) $res .= '<tbody class="collapse" id="table-body-'.$collapseId.'">';
	else $res .= '<tbody>';
	foreach($rows as $row) {
		$res .= '<tr>';
		foreach($row->cells as $cell) {
			$class = '';
			if (isset($cell->class)) $class = ' class="trainingpath-cell-'.$cell->class.'"';
			$res .= '<td'.$class.'>'.$cell->content.'</td>';
		}
		$res .= '</tr>';
	}
	$res .= '</tbody></table>';
	return $res;
}


//------------------------------------------- Icons -------------------------------------------// 


function trainingpath_text_icon($text, $type) {
	// SF2017 - Replace <img> by Font Awesome
    global $OUTPUT;
	return $OUTPUT->pix_icon($type, '', 'mod_trainingpath').$text;
	//return '<img src="'.trainingpath_get_icon($type).'" class="trainingpath-text-icon">'.$text;
}

function trainingpath_get_icon($type, $alt = '', $class = '') {
	// SF2017 - Replace <img> by Font Awesome
    global $OUTPUT;
	return '<span class="'.$class.'">'.$OUTPUT->pix_icon($type, $alt, 'mod_trainingpath').'</span>';
	/*
    switch($type) {
		
		// Moodle pix
        case 'schedule' : return $OUTPUT->pix_url('e/insert_date')->out();
        case 'dragdrop' : return $OUTPUT->pix_url('i/dragdrop')->out();
        case 'edit' : return $OUTPUT->pix_url('i/edit')->out();
        case 'delete' : return $OUTPUT->pix_url('i/delete')->out();
		case 'children' : return $OUTPUT->pix_url('i/withsubcat')->out();
			
		// Plugin pix
		case 'content' : return $OUTPUT->pix_url('content', 'trainingpath')->out();
		case 'eval' : return $OUTPUT->pix_url('eval', 'trainingpath')->out();
		case 'classroom' : return $OUTPUT->pix_url('classroom', 'trainingpath')->out();
		case 'virtual' : return $OUTPUT->pix_url('virtual', 'trainingpath')->out();
		case 'files' : return $OUTPUT->pix_url('files', 'trainingpath')->out();
		case 'richtext' : return $OUTPUT->pix_url('richtext', 'trainingpath')->out();
		case 'alert' : return $OUTPUT->pix_url('alert', 'trainingpath')->out();
		case 'review' : return $OUTPUT->pix_url('review', 'trainingpath')->out();
		case 'duration' : return $OUTPUT->pix_url('duration_white', 'trainingpath')->out();
		case 'comments' : return $OUTPUT->pix_url('comments', 'trainingpath')->out();
    }
    */
}

