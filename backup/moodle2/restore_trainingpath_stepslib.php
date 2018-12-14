<?php

class restore_trainingpath_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $paths[] = new restore_path_element('trainingpath', '/activity/trainingpath');
        $paths[] = new restore_path_element('calendar', '/activity/trainingpath/calendars/calendar');
        $paths[] = new restore_path_element('item', '/activity/trainingpath/items/item');
        $paths[] = new restore_path_element('file', '/activity/trainingpath/items/item/files/file');
        $paths[] = new restore_path_element('sco', '/activity/trainingpath/items/item/scoes/sco');
        
		$userinfo = $this->get_setting_value('userinfo');
		if ($userinfo) {
			$paths[] = new restore_path_element('schedule', '/activity/trainingpath/items/item/schedules/schedule');
			$paths[] = new restore_path_element('comment', '/activity/trainingpath/items/item/comments/comment');
			$paths[] = new restore_path_element('track', '/activity/trainingpath/items/item/tracks/track');
			$paths[] = new restore_path_element('sco_track', '/activity/trainingpath/items/item/scoes/sco/scoes_tracks/sco_track');
		}

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_trainingpath($data) {
        global $DB;

        // Prepare
        $data = (object)$data;
        $oldId = $data->id;
        
        // Restore jointures
        $data->course = $this->get_courseid();

        // Insert record
        $newId = $DB->insert_record('trainingpath', $data);
        
        // Apply
        $this->apply_activity_instance($newId);
    }

    protected function process_calendar($data) {
        global $DB;

        // Prepare
        $data = (object)$data;
        $oldId = $data->id;
        
        // Restore jointures
        $data->path_id = $this->get_new_parentid('trainingpath');

        // Insert record
        $newId = $DB->insert_record('trainingpath_calendar', $data);
        
        // Set id mapping
        $this->set_mapping('calendar', $oldId, $newId, true);
    }

	protected function process_sco($data) {
		global $DB;

        // Prepare
        $data = (object)$data;
        $oldId = $data->id;

        // Restore jointures
        
        // Insert record
        $newId = $DB->insert_record('scormlite_scoes', $data);

        // Update parent relation
		$itemId = $this->get_new_parentid('item');
		$DB->execute("UPDATE {trainingpath_item} SET ref_id=$newId WHERE id=$itemId");

        // Set id mapping
		$this->set_mapping('sco', $oldId, $newId, true);
	}

	protected function process_file($data) {
		global $DB;

        // Prepare
        $data = (object)$data;
        $oldId = $data->id;

        // Restore jointures
        
        // Insert record
        $newId = $DB->insert_record('trainingpath_files', $data);

        // Update parent relation
		$itemId = $this->get_new_parentid('item');
		$DB->execute("UPDATE {trainingpath_item} SET ref_id=$newId WHERE id=$itemId");

        // Set id mapping
		$this->set_mapping('file', $oldId, $newId, true);
	}

    protected function process_item($data) {
        global $DB;

        // Prepare
        $data = (object)$data;
        $oldId = $data->id;
        
        // Restore jointures
        $data->path_id = $this->get_new_parentid('trainingpath');

        // Insert record
        $newId = $DB->insert_record('trainingpath_item', $data);
        
        // Set id mapping
        $this->set_mapping('item', $oldId, $newId, true);
    }

    protected function process_schedule($data) {
        global $DB;

        // Prepare
        $data = (object)$data;
        $oldId = $data->id;
        
        // Restore jointures
		$data->cmid = $this->get_mappingid('course_module', $data->cmid);
        $data->context_id = $this->get_new_parentid('item');
		if ($data->calendar_id) $data->calendar_id = $this->get_mappingid('calendar', $data->calendar_id);
		$data->group_id = $this->get_mappingid('group', $data->group_id);

        // Insert record
        $newId = $DB->insert_record('trainingpath_schedule', $data);

        // Set id mapping
		$this->set_mapping('schedule', $oldId, $newId, true);
    }

    protected function process_comment($data) {
        global $DB;

        // Prepare
        $data = (object)$data;
        $oldId = $data->id;
        
        // Restore jointures
        $data->context_id = $this->get_new_parentid('item');
		if ($data->user_id) $data->user_id = $this->get_mappingid('user', $data->user_id);
		if ($data->group_id) $data->group_id = $this->get_mappingid('group', $data->group_id);

        // Insert record
        $DB->insert_record('trainingpath_comments', $data);
    }

    protected function process_track($data) {
        global $DB;

        // Prepare
        $data = (object)$data;
        $oldId = $data->id;
        
        // Restore jointures
        $data->context_id = $this->get_new_parentid('item');
		$data->user_id = $this->get_mappingid('user', $data->user_id);

        // Insert record
        $DB->insert_record('trainingpath_tracks', $data);
    }

    protected function process_sco_track($data) {
        global $DB;

        // Prepare
        $data = (object)$data;
        $oldId = $data->id;
        
        // Restore jointures
        $data->scoid = $this->get_new_parentid('sco');
		$data->userid = $this->get_mappingid('user', $data->userid);

        // Insert record
        $newId = $DB->insert_record('scormlite_scoes_track', $data);
        
        // Set id mapping
        $this->set_mapping('sco_track', $oldId, $newId, true);
    }

    protected function after_execute() {
        global $DB;
        
        // Adapt item to item ids (parent_id, grouping_id, previous_id)
        $path_id = $this->task->get_activityid();
        $items = $DB->get_records('trainingpath_item', array('path_id'=>$path_id));
        foreach($items as $item) {
            if ($item->parent_id) $item->parent_id = $this->get_mappingid('item', $item->parent_id);
            if ($item->grouping_id) $item->grouping_id = $this->get_mappingid('item', $item->grouping_id);
            if ($item->previous_id) $item->previous_id = $this->get_mappingid('item', $item->previous_id);
            $DB->update_record('trainingpath_item', $item);
        }
        
        // Add related files
        $this->add_related_files('mod_trainingpath', 'intro', null);
		$this->add_related_files('mod_trainingpath', 'richtext', 'item');

		$this->add_related_files('mod_trainingpath', 'files_content', 'file');
		$this->add_related_files('mod_trainingpath', 'files_package', 'file');

		$this->add_related_files('mod_trainingpath', 'content', 'sco');
		$this->add_related_files('mod_trainingpath', 'package', 'sco');
		
		$userinfo = $this->get_setting_value('userinfo');
        if ($userinfo) {
    		$this->add_related_files('mod_trainingpath', 'schedule_package', 'schedule');        
        }
    }
}
