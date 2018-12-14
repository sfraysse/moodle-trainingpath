<?php

defined('MOODLE_INTERNAL') || die;

class backup_trainingpath_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

		// To know if we are including userinfo
		$userinfo = $this->get_setting_value('userinfo');

		
        // ------------------------ Define each element separated ------------------------
        
		// e-ATPL activity
        $trainingpath = new backup_nested_element('trainingpath', array('id'), array(
            'name', 'intro', 'introformat',
            'score_colors', 'time_optimum_threshold', 'time_max_factor', 'locked',
            'timecreated', 'timemodified'
        ));
		
		// Calendars
        $calendars = new backup_nested_element('calendars');
        $calendar = new backup_nested_element('calendar', array('id'), array(
            'title', 'description', 'weekly_closed', 'yearly_closed', 'position'
        ));

		// Files
        $files = new backup_nested_element('files');
        $file = new backup_nested_element('file', array('id'), array(
            'popup', 'reference', 'launch_file', 'sha1hash', 'revision'
        ));

		// Scormlite
		$scoes = new backup_nested_element('scoes');
		$sco = new backup_nested_element('sco', array('id'), array(
			'containertype', 'scormtype', 'reference', 'sha1hash', 'md5hash', 'revision', 'timeopen', 'timeclose',
			'manualopen', 'maxtime', 'passingscore', 'displaychrono', 'colors', 'popup', 'maxattempt', 'whatgrade', 'launchfile'
		));

		// Items
        $items = new backup_nested_element('items');
        $item = new backup_nested_element('item', array('id'), array(
            'type', 'code', 'title', 'description', 'parent_id', 'parent_position',
            'grouping_id', 'grouping_position', 'previous_id', 'duration', 'duration_up', 'duration_down',
            'activity_type', 'complementary', 'evaluation', 'remedial', 'information', 'ref_id'
        ));

		if ($userinfo) {

			// Schedules
            $schedules = new backup_nested_element('schedules');
            $schedule = new backup_nested_element('schedule', array('id'), array(
                'cmid', 'context_type', 'group_id', 'calendar_id', 
                'access', 'time_open', 'time_close', 'period_open', 'period_close',
                'information', 'position', 'description', 'duration', 'file_reference'
            ));

			// Comments
            $comments = new backup_nested_element('comments');
            $comment = new backup_nested_element('comment', array('id'), array(
                'context_type', 'user_id', 'group_id', 'comment'
            ));

			// Tracks
            $tracks = new backup_nested_element('tracks');
            $track = new backup_nested_element('track', array('id'), array(
                'context_type', 'user_id', 'attempt', 'last_attempt',
                'completion', 'success', 'success_remedial', 'score', 'score_remedial',
                'progress_value', 'progress_max', 'progress_unit',
                'time_spent', 'time_status', 'time_passing'
            ));

			// Scormlite tracks
			$scotracks = new backup_nested_element('scoes_tracks');
			$scotrack = new backup_nested_element('sco_track', array('id'), array(
				'userid', 'scoid', 'attempt', 'element', 'value', 'timemodified'
			));
        }
        
        // ------------------------ Build the tree ------------------------
		
		// Calendars
		$trainingpath->add_child($calendars);
		$calendars->add_child($calendar);
		
		// Files
		$item->add_child($files);
		$files->add_child($file);
		
		// Scormlite
		$item->add_child($scoes);
		$scoes->add_child($sco);		
		
		// Items
		$trainingpath->add_child($items);
		$items->add_child($item);
		
		if ($userinfo) {

			// Schedules
			$item->add_child($schedules);
			$schedules->add_child($schedule);

			// Comments
			$item->add_child($comments);
			$comments->add_child($comment);

			// Tracks
			$item->add_child($tracks);
			$tracks->add_child($track);
			
			// Scormlite tracks
			$sco->add_child($scotracks);
			$scotracks->add_child($scotrack);
		}

        // ------------------------ Define sources ------------------------

		// e-ATPL activity
        $trainingpath->set_source_table('trainingpath', array('id' => backup::VAR_ACTIVITYID));

		// Files
		$sql = '
			SELECT F.*
			FROM {trainingpath_files} F
			INNER JOIN {trainingpath_item} I ON I.ref_id=F.id
			WHERE I.activity_type=5 AND I.id=?';
		$file->set_source_sql($sql, array(backup::VAR_PARENTID));
		
		// Scormlite
		$sql = '
			SELECT SS.*
			FROM {scormlite_scoes} SS
			INNER JOIN {trainingpath_item} I ON I.ref_id=SS.id
			WHERE (I.activity_type=1 OR I.activity_type=2) AND I.id=?';
		$sco->set_source_sql($sql, array(backup::VAR_PARENTID));
		
		// Calendars
        $calendar->set_source_table('trainingpath_calendar', array('path_id' => backup::VAR_PARENTID));

		// Items
        $item->set_source_table('trainingpath_item', array('path_id' => backup::VAR_PARENTID));

		if ($userinfo) {

			// Schedules
	        $schedule->set_source_table('trainingpath_schedule', array('context_id' => backup::VAR_PARENTID, 'cmid'=>backup::VAR_MODID));

			// Comments
			$comment->set_source_table('trainingpath_comments', array('context_id' => backup::VAR_PARENTID));
			
			// Tracks
			$track->set_source_table('trainingpath_tracks', array('context_id' => backup::VAR_PARENTID));
			
			// Scormlite tracks
			$scotrack->set_source_table('scormlite_scoes_track', array('scoid' => backup::VAR_PARENTID));
		}
		
        // ------------------------ Define id annotations ------------------------
		
		if ($userinfo) {

			// Schedules
			$schedule->annotate_ids('group', 'group_id');

			// Comments
			$comment->annotate_ids('user', 'user_id');
			$comment->annotate_ids('group', 'group_id');

			// Tracks
			$track->annotate_ids('user', 'user_id');

			// Scormlite tracks
			$scotrack->annotate_ids('user', 'userid');
		}
		
        // ------------------------ Define file annotations ------------------------

		// e-ATPL activity
		$trainingpath->annotate_files('mod_trainingpath', 'intro', null); // This file area hasn't itemid
		
		// Item (for RichText)
		$item->annotate_files('mod_trainingpath', 'richtext', 'id');
		
		// Files
		$file->annotate_files('mod_trainingpath', 'files_content', 'id');
		$file->annotate_files('mod_trainingpath', 'files_package', 'id');

		// SCO
		$sco->annotate_files('mod_trainingpath', 'content', 'id');
		$sco->annotate_files('mod_trainingpath', 'package', 'id');

		if ($userinfo) {

			// Schedules
			$schedule->annotate_files('mod_trainingpath', 'schedule_package', 'id');
		}

        // Return the root element (trainingpath), wrapped into standard activity structure
        return $this->prepare_activity_structure($trainingpath);
    }
}
