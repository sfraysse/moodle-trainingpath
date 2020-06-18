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

require_once($CFG->dirroot . '/mod/trainingpath/report/lib.php');
require_once($CFG->dirroot . '/mod/trainingpath/locallib.php');
require_once($CFG->dirroot . '/mod/scormlite/report/reportlib.php');


// Completion hook

function trainingpath_hook_completion($cm, $learningpath, $sco, $userid, $attempt) {
	global $DB;
	$trackdata = scormlite_get_mystatus($cm, $sco, false, false)[1];
	$item = $DB->get_record('trainingpath_item', array('ref_id' => $sco->id), '*', MUST_EXIST);
	$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
	if (!$item->complementary) trainingpath_report_record_scormlite_track($trackdata, $item, $course, $cm, $learningpath, $item->evaluation);
}
