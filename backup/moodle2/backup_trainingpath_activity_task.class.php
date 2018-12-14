<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/trainingpath/backup/moodle2/backup_trainingpath_stepslib.php');

/**
 * Provides all the settings and steps to perform one complete backup of the activity
 */
class backup_trainingpath_activity_task extends backup_activity_task {

    /**
     * No specific settings for this activity
     */
    protected function define_my_settings() {
    }

    /**
     * Defines a backup step to store the instance data in the trainingpath.xml file
     */
    protected function define_my_steps() {
        $this->add_step(new backup_trainingpath_activity_structure_step('trainingpath_structure', 'trainingpath.xml'));
    }

    /**
     * No content encoding needed for this activity
     *
     * @param string $content some HTML text that eventually contains URLs to the activity instance scripts
     * @return string the same content with no changes
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of trainingpaths
        $search="/(".$base."\/mod\/trainingpath\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@trainingpathINDEX*$2@$', $content);

        // Link to trainingpath view by moduleid
        $search="/(".$base."\/mod\/trainingpath\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@trainingpathVIEWBYID*$2@$', $content);

        return $content;
    }
}
