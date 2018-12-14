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


/*************************************************************************************************
 *                                             HTTP Responses                                          
 *************************************************************************************************/

 function trainingpath_error_response($code) {
	http_response_code($code);
	die;
 }
 
 function trainingpath_json_response($data) {
	header('Content-Type: application/json');
	echo json_encode($data);
	die;
 }
 
 

