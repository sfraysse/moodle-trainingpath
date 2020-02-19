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

 
// ------------------------- Standard plugin terms ---------------------------

// Plugin
$string['pluginadministration'] = 'Training Path module administration';
$string['pluginname'] = 'Training Path';

// Module
$string['modulename'] = 'Training Path';
$string['modulename_help'] = 'Training Path enables to create, manage, and monitor predefined linear training paths.';
$string['modulename_link'] = 'mod/trainingpath/view';
$string['modulenameplural'] = 'Training Paths';

// Permissions
$string['trainingpath:addinstance'] = 'Create a training path';
$string['trainingpath:editschedule'] = 'Edit a training schedule';


// ------------------------- Settings ---------------------------

$string['prefered_activity_access_eval'] = 'Prefered activity access (assessment)';
$string['prefered_activity_access_eval_desc'] = 'Default value of the access setting on assessments scheduling.';
$string['prefered_activity_access'] = 'Prefered activity access (other)';
$string['prefered_activity_access_desc'] = 'Default value of the access setting on activities scheduling other than test.';


// ------------------------- Common terms ---------------------------

$string['home'] = 'Home';
$string['gradebook'] = 'Gradebook';
$string['path'] = 'Training path';
$string['certificate'] = 'Theme';
$string['batch'] = 'Phase';
$string['sequence'] = 'Sequence';
$string['activity'] = 'Activity';
$string['eval'] = 'Assessment';
$string['eval_comp'] = 'Complementary assessment';
$string['virtual'] = 'Virtual classroom';
$string['classroom'] = 'Onsite classroom';
$string['files'] = 'Files';
$string['richtext'] = 'Rich text';
$string['schedule'] = 'Schedule';
$string['certificates'] = 'Themes';
$string['batches'] = 'Phases';
$string['sequences'] = 'Sequences';
$string['activities'] = 'Activities';
$string['schedules'] = 'Schedules';
$string['settings'] = 'Settings';
$string['general'] = 'Global settings';
$string['other_settings'] = 'Other settings';
$string['title'] = 'Title';
$string['code'] = 'Code';
$string['description'] = 'Description';
$string['context'] = 'Context';
$string['content'] = 'Content';

// ------------------------- Editing ---------------------------

$string['add'] = 'Add';
$string['add_schedule'] = 'Add schedule';
$string['add_certificate'] = 'Add theme';
$string['add_batch'] = 'Add phase';
$string['add_sequence'] = 'Add sequence';
$string['add_formal_activity'] = 'Add mandatory activity';
$string['add_complementary_activity'] = 'Add complementary activity';

$string['delete'] = 'Delete';
$string['delete_schedule'] = 'Delete schedule';
$string['delete_schedule_confirm'] = 'Do you really want to delete this schedule?';
$string['delete_certificate'] = 'Delete theme';
$string['delete_certificate_confirm'] = 'Do you really want to delete this theme?';
$string['delete_batch'] = 'Delete phase';
$string['delete_batch_confirm'] = 'Do you really want to delete this phase? All related sequences and activities will also be deleted!';
$string['delete_sequence'] = 'Delete sequence';
$string['delete_sequence_confirm'] = 'Do you really want to delete this sequence? All related activities will also be deleted!';
$string['delete_activity'] = 'Delete activity';
$string['delete_activity_confirm'] = 'Do you really want to delete this activity?';

$string['edit'] = 'Edit';
$string['edit_certificates'] = 'Edit themes';
$string['edit_sequences'] = 'Edit sequences';
$string['edit_batches'] = 'Edit phases';
$string['edit_activities'] = 'Edit activities';
$string['editing_schedule'] = 'Updating schedule';
$string['editing_certificate'] = 'Updating theme';
$string['editing_certificates'] = 'Updating themes';
$string['editing_batch'] = 'Updating phase';
$string['editing_batches'] = 'Updating phases';
$string['editing_sequence'] = 'Updating sequence';
$string['editing_sequences'] = 'Updating sequences';
$string['editing_activity'] = 'Updating activity';
$string['editing_activities'] = 'Updating activities';
$string['editing_content'] = 'Updating content';
$string['editing_eval'] = 'Updating assessment';
$string['editing_virtual'] = 'Updating virtual classroom';
$string['editing_classroom'] = 'Updating onsite classroom';
$string['editing_files'] = 'Updating files';
$string['editing_richtext'] = 'Updating richtext';

$string['new_schedule'] = 'New schedule';
$string['new_certificate'] = 'New theme';
$string['new_batch'] = 'New phase';
$string['new_sequence'] = 'New sequence';
$string['new_content'] = 'New content';
$string['new_eval'] = 'New assessment';
$string['new_virtual'] = 'New virtual classroom';
$string['new_classroom'] = 'New onsite classroom';
$string['new_files'] = 'New files';
$string['new_richtext'] = 'New richtext';

$string['no_schedule'] = 'There is no schedule at this moment.';
$string['no_certificate'] = 'There is no theme at this moment.';
$string['no_batch'] = 'There is no phase at this moment.';
$string['no_sequence'] = 'There is no sequence at this moment.';
$string['no_activity'] = 'There is no activity at this moment.';
$string['no_matching_group'] = 'No matching group';
$string['no_description'] = 'No description';
$string['no_data'] = 'There is no data at this moment.';
$string['none'] = 'None';

$string['preview'] = 'Preview';
$string['preview_certificates'] = 'Preview themes';
$string['preview_batches'] = 'Preview phases';
$string['preview_sequences'] = 'Preview sequences';
$string['preview_activities'] = 'Preview activities';

$string['tracks_recalculate'] = 'Recalculate tracking data';
$string['tracks_recalculate_confirm'] = 'Tracking data has been recalculated.';
$string['tracks_recalculate_desc'] = '
    By clicking on the above button, the progess of all users, at any level of the training path, will be recalculated.
    This may be usefull after modifying the training path.
';
$string['tracks_recalculate_back'] = 'Back to Learning Path';

$string['save'] = 'Save';
$string['cancel'] = 'Cancel';
$string['confirm'] = 'Confirm';

// Regulatory
$string['regulatory_rules'] = 'Time constraints';
$string['certificate_duration'] = 'Minimum study time (hours)';
$string['certificate_duration_help'] = 'Minimum time the user must spend on the theme. The theme will not be validated as long as the learner has not spent this quota.';
$string['certificate_duration_error'] = 'It must be a non-zero numeric positive value. "." is used as the decimal separator.';
$string['sequence_duration'] = 'Duration of sequence (days)';
$string['sequence_duration_help'] = 'Duration that must be taken into account during planning generation. It must be a multiple of 0.5 days.';
$string['sequence_duration_error'] = 'It must be a non-zero numeric positive value. The character "." is used as the decimal separator. Full days or half days only.';
$string['content_duration'] = 'Minimum study time (minutes)';
$string['content_duration_help'] = 'Minimum time the user must spend on the activity. The activity will not be validated under this time.';
$string['content_duration_error'] = 'It must be a non-zero positive integer.';
$string['eval_duration'] = 'Alloted time (minutes)';
$string['eval_duration_help'] = 'Alloted time to take the assessment. The maximum time must be expressed in minutes (e.g. 60 for a maximum time of 1 hour). 0 means that there is no time limit.';
$string['eval_duration_error'] = 'It must be a positive integer.';
$string['virtual_duration'] = 'Duration (minutes)';
$string['virtual_duration_help'] = 'Scheduled duration of the virtual classroom session.';
$string['virtual_duration_error'] = 'It must be a non-zero positive integer.';
$string['classroom_duration'] = 'Duration (minutes)';
$string['classroom_duration_help'] = 'Scheduled duration of the onsite classroom session.';
$string['classroom_duration_error'] = 'It must be a non-zero positive integer.';
$string['session_duration_'] = 'Duration actually achieved (default: {$a} minutes)';
$string['session_duration'] = 'Duration actually achieved (minutes)';
$string['session_duration_error'] = 'It must be a non-zero positive integer.';

// Colors
$string['colors'] = 'Reporting colors';
$string['score_colors'] = 'Reporting colors for scores';
$string['score_colors_desc'] = 'Colors that should be used when displaying scores in reports. Each value indicates the score under which the color will apply.';
$string['score_colors_help'] = $string['score_colors_desc'];
$string['score_lessthan'] = 'Score <';
$string['score_upto'] = 'Score <=';

// Time
$string['time_colors'] = 'Reporting colors for study times';
$string['time_colors_desc'] = 'Colors that should be used when displaying in the reports the amount of time actually spent on learning.';
$string['time_colors_help'] = $string['time_colors_desc'];
$string['time_settings'] = 'Study time monitoring settings';
$string['time_optimum_threshold'] = 'Tolerance threshold above the computed nominal time (%)';
$string['time_optimum_threshold_help'] = 'This setting (Threshold %) defines a threshold for display color code, with respect to study time (T) actually spent on the sequence:
    <br>- Orange if T < [nominal time]
    <br>- Yellow if [nominal time] < T < [nominal time] x Threshold %
    <br>- Green if T > [nominal time] x Threshold %';
$string['time_max_factor'] = 'Maximum time factor';
$string['time_max_factor_help'] = "Defines a coefficient in order to determine the maximum accountable time for a given activity (same factor for all path's activities):
    <br>[Max time] = [Min study time] x [Max time factor]";

// Other settings
$string['file_extensions'] = 'File extensions';
$string['file_extensions_desc'] = 'Allowed file extensions in Files activities';
$string['file_extensions_help'] = $string['file_extensions_desc'];
$string['passing_score'] = 'Passing score';
$string['passing_score_desc'] = 'Minimum score that a learner must obtain in order to pass an assessment.';
$string['passing_score_help'] = $string['passing_score_desc'];
$string['remedial'] = 'Remedial test';
$string['complementary_l'] = 'complementary';
$string['formal_l'] = 'mandatory';
$string['practical_information'] = 'Practical information';
$string['launch_file'] = 'Launch file';
$string['group'] = 'Group';
$string['of_group'] = 'of group';
$string['morning'] = 'Morning';
$string['afternoon'] = 'Afternoon';
$string['morning_l'] = 'morning';
$string['afternoon_l'] = 'afternoon';
$string['locked'] = 'Updating locked';

// Form errors
$string['score_color_notvalid'] = 'You must enter a value between 0 and 100.';
$string['time_optimum_threshold_notvalid'] = 'You must enter a value between 0 and 100.';
$string['time_max_factor_notvalid'] = 'You must enter a value between 1 and 10.';
$string['error_files_missing_launch'] = 'You must enter a launch file.';
$string['error_files_invalid_package'] = 'The package is not valid.';
$string['error_files_invalid_launch'] = 'The specified launch file was not found in the package.';
$string['error_files_invalid_extension'] = 'The file extension is not allowed.';

// Permission errors
$string['permission_denied_edit_schedule'] = 'You are not allowed to edit schedules.';
$string['permission_denied_edit_schedule_no_group'] = "You have no group of learners to manage. You first need to be associated with a group in order to manage its schedule.";
$string['permission_denied_view_no_group'] = "Currently, you don't belong to a group of learners. Please, try to come back later or contact your supervisor.";
$string['permission_denied_view_no_schedule'] = "There is currently no schedule defined for your group. Please, try to come back later or contact your supervisor.";
$string['permission_denied_view_hidden'] = "You are currently not allowed to view this. Please, try to come back later or contact your supervisor.";
$string['permission_denied_tutor_group_not_allowed'] = "You are not allowed to manage this group.";
$string['permission_denied_calendar_not_defined'] = "No calendar has been defined.";



// ------------------------- Scheduling ---------------------------

$string['scheduling'] = 'Scheduling';
$string['scheduling_'] = 'Scheduling: {$a}';
$string['schedule_already_assigned'] = 'A schedule has already been assigned to this group.';
$string['schedule_certificates'] = 'Schedule themes';
$string['schedule_batches'] = 'Schedule phases';
$string['schedule_sequences'] = 'Schedule sequences';
$string['schedule_activities'] = 'Schedule activities';
$string['scheduling_certificates'] = 'Scheduling themes';
$string['scheduling_batches'] = 'Scheduling phases';
$string['scheduling_sequences'] = 'Scheduling sequences';
$string['scheduling_activities'] = 'Scheduling activities';

// Access
$string['access'] = 'Access';
$string['access_from_date'] = 'From';
$string['access_to_date'] = 'To';
$string['access_closed'] = 'Closed';
$string['access_open'] = 'Open';
$string['access_on_dates'] = 'On dates';
$string['access_between_dates'] = 'Between dates';
$string['access_from_date'] = 'From date';
$string['access_to_date'] = 'To date';
$string['access_on_completion'] = 'On completion';
$string['access_as_remedial'] = 'Auto-remediation';
$string['access_hidden'] = 'Hidden';
$string['access_currently_closed'] = 'Access currently closed';
$string['access_currently_open'] = 'Access currently open';
$string['access_currently_hidden'] = 'Item currently hidden';
$string['access_from_to'] = 'Open from {$a->from} to {$a->to}';
$string['access_from'] = 'Open from {$a}';
$string['access_to'] = 'Open until {$a}';
$string['access_open_completion'] = 'Open';
$string['access_closed_completion'] = 'You must complete the previous activity first.';
$string['access_open_remedial'] = 'Open';
$string['access_closed_remedial'] = 'Closed';


// ------------------------- Viewing ---------------------------

$string['show_hide_acheived_sequences'] = 'Achieved sequences';
$string['viewing_trainingpath'] = 'Viewing training path';
$string['open'] = 'Open';
$string['back_to_activity'] = 'Back to activity';
$string['back_to_sequence'] = 'Back to sequence';
$string['back_to_batch'] = 'Back to phase';
$string['back_to_batches'] = 'Back to phases';
$string['back_to_certificate'] = 'Back to theme';
$string['back_to_certificates'] = 'Back to themes';

// Status
$string['status_currently_no'] = 'Currently no status';
$string['status_completion'] = 'Completion';
$string['status_completion_notattempted'] = 'Not attempted';
$string['status_completion_incomplete'] = 'Incomplete';
$string['status_completion_completed'] = 'Completed';
$string['status_success'] = 'Success';
$string['status_success_unknown'] = 'Unknown';
$string['status_success_passed'] = 'Passed';
$string['status_success_failed'] = 'Failed';
$string['status_score'] = 'Score';
$string['status_score_remedial'] = 'Score (remedial)';
$string['status_progress'] = 'Progress';
$string['status_time_spent'] = 'Time spent';
$string['status_time_passing'] = 'Minimum time';
$string['status_time_status'] = 'Time status';
$string['status_time_status_critical'] = 'Critical';
$string['status_time_status_minimal'] = 'Minimal';
$string['status_time_status_nominal'] = 'Nominal';
$string['status_time_status_optimal'] = 'Optimal';
$string['next_step'] = 'Next step';


// ------------------------- Tutoring ---------------------------

$string['gradebook'] = 'Gradebook';
$string['tutoring'] = 'Tutoring: {$a}';
$string['manage_session'] = 'Manage session';
$string['managing_session'] = 'Managing session';
$string['global_followup'] = 'Follow-up';
$string['participation'] = 'Participation';
$string['manage_tracking'] = 'Manage tracking';
$string['managing_tracking'] = 'Managing tracking';
$string['user'] = 'User';
$string['status'] = 'Status';
$string['force_completion'] = 'Force completion';
$string['force_scores'] = 'Force scores';
$string['reporting'] = 'Reporting';
$string['learners'] = 'Learners';
$string['global'] = 'Global';
$string['average'] = 'Average';
$string['average_remedial'] = 'Average (remediated learners)';
$string['group_progress'] = 'Group progress';
$string['learner_progress'] = 'Learner progress';
$string['learners_progress'] = 'Learners progress';
$string['eval_only_certificate_switch_0'] = 'Show evaluated sequences only';
$string['eval_only_certificate_switch_1'] = 'Show all sequences';
$string['eval_only_sequence_switch_0'] = 'Show assessments only';
$string['eval_only_sequence_switch_1'] = 'Show all activities';
$string['review'] = 'Review';
$string['comments'] = 'Comments';
$string['group_results_'] = 'Results of group: {$a}';
$string['xls_progress'] = 'Progress';
$string['xls_time'] = 'Time';
$string['xls_success'] = 'Score';
$string['xls_remedial'] = 'Remedial';
$string['xls_export'] = 'Export (Excel)';
$string['xls_export_global'] = 'Global export (Excel)';
$string['xls_export_certificates'] = 'Export themes (Excel)';
$string['xls_export_users'] = 'Export users (Excel)';
$string['xls_export_sequences'] = 'Export sequences (Excel)';


// ------------------------- Calendars ---------------------------

$string['calendar'] = 'Calendar';
$string['manage_calendars'] = 'Manage calendars';
$string['add_calendar'] = 'Add calendar';
$string['delete_calendar'] = 'Delete calendar';
$string['delete_calendar_confirm'] = 'Do you really want to delete this calendar?';
$string['no_calendar'] = 'There is no calendar at this moment.';
$string['new_calendar'] = 'New calendar';
$string['year'] = 'Year';
$string['weekly_closed'] = 'Weekly closed days';
$string['yearly_closed'] = 'Yearly closed days';
$string['monday'] = 'Monday';
$string['tuesday'] = 'Tuesday';
$string['wednesday'] = 'Wednesday';
$string['thursday'] = 'Thursday';
$string['friday'] = 'Friday';
$string['saturday'] = 'Saturday';
$string['sunday'] = 'Sunday';
$string['yearly_closed_help'] = "Please, enter the dates of closed days (DD/MM/YYY) separated by ';' or a new line.
                                    You can also enter closed periods (DD/MM/YYY-DD/MM/YYY).
                                    Example:<br>
                                        <br>10/01/2017
                                        <br>11/01/2017
                                        <br>14/01/2017;15/01/2017
                                        <br>20/01/2017-25/01/2017";
$string['yearly_closed_error'] = 'The provided format is not valid. Please, check the format by clicking on the above help button.';
$string['generate_schedule'] = 'Generate schedule';
$string['auto_scheduling'] = 'Auto-scheduling';
$string['generate_schedules'] = 'Generate schedules';
$string['manual_scheduling'] = 'Manual scheduling';
$string['generate_schedule_from'] = 'Generate from';


// ------------------------- Reset ---------------------------

$string['reset_tracks'] = 'Reset tracks';
$string['reset_comments'] = 'Reset comments';
$string['reset_schedules'] = 'Reset schedules';


// ------------------------- Statistics ---------------------------

$string['statistics'] = 'Statistics';


// ------------------------- Privacy ---------------------------

// SCORM Lite tracks
$string['privacy:metadata:trainingpath_scoes_track'] = 'Data tracked by the SCORM Lite activities';
$string['privacy:metadata:scoes_track:userid'] = 'The ID of the user who accessed the SCORM Lite activity';
$string['privacy:metadata:scoes_track:attempt'] = 'The attempt number';
$string['privacy:metadata:scoes_track:element'] = 'The name of the element to be tracked';
$string['privacy:metadata:scoes_track:value'] = 'The value of the given element';
$string['privacy:metadata:scoes_track:timemodified'] = 'The time when the tracked element was last modified';

// Training Path tracks
$string['privacy:metadata:trainingpath_tracks'] = 'Data tracked by Training Path';
$string['privacy:metadata:tracks:context_type'] = 'The type of the tracked context';
$string['privacy:metadata:tracks:context_id'] = 'The ID of the tracked context';
$string['privacy:metadata:tracks:user_id'] = 'The ID of the tracked user';
$string['privacy:metadata:tracks:attempt'] = 'The attempt number';
$string['privacy:metadata:tracks:last_attempt'] = 'Is this attempt the last attempt?';
$string['privacy:metadata:tracks:completion'] = 'Completion of the activity';
$string['privacy:metadata:tracks:success'] = 'Success of the initial evaluation';
$string['privacy:metadata:tracks:success_remedial'] = 'Success of the remedial evaluation';
$string['privacy:metadata:tracks:score'] = 'Score of the initial evaluation';
$string['privacy:metadata:tracks:score_remedial'] = 'Score of the remedial evaluation';
$string['privacy:metadata:tracks:progress_value'] = 'Learner progress on this unit';
$string['privacy:metadata:tracks:progress_max'] = 'Max value of the progress value';
$string['privacy:metadata:tracks:progress_unit'] = 'Unit of the progress value';
$string['privacy:metadata:tracks:time_spent'] = 'Time spent by the learner on this unit';
$string['privacy:metadata:tracks:time_status'] = 'Time status for the learner on this unit';
$string['privacy:metadata:tracks:time_passing'] = 'Minimum time to spend on this unit';

// Comments
$string['privacy:metadata:trainingpath_comments'] = 'Comments entered by the instructors on the Training Path';
$string['privacy:metadata:comments:contexttype'] = 'The type of the comment context';
$string['privacy:metadata:comments:contextid'] = 'The ID of the comment context';
$string['privacy:metadata:comments:userid'] = 'The ID of the user who performed the Assessment Path';
$string['privacy:metadata:comments:groupid'] = 'The ID of the group the user belongs to';
$string['privacy:metadata:comments:comment'] = 'The comment entered for this user';

// Exports
$string['item'] = 'Item';
$string['items'] = 'Items';
$string['mystatus'] = 'My status';
$string['comments'] = 'Comments';
$string['itemdescr'] = 'Item description';


// ------------------------- Events ---------------------------

$string['event:page_viewed'] = 'Page viewed';
$string['event:item_viewed'] = 'Item viewed';
$string['event:item_completed'] = 'Item completed';
$string['event:item_result_updated'] = 'Item result updated';
$string['event:item_completion_forced'] = 'Item completion forced';
$string['event:item_result_forced'] = 'Item result forced';


