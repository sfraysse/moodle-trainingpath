<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * xAPI transformation of an trainingpath event.
 *
 * @package    mod_trainingpath
 * @copyright  2019 Sébastien Fraysse {@link http://fraysse.eu}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_trainingpath\xapi\statements;

defined('MOODLE_INTERNAL') || die();

use logstore_trax\src\utils as logstore_utils;

/**
 * xAPI transformation of an trainingpath event.
 *
 * @package    mod_trainingpath
 * @copyright  2019 Sébastien Fraysse {@link http://fraysse.eu}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait statement_utils {
    

    /**
     * Course.
     *
     * @var \stdClass $course
     */
    protected $course;

    /**
     * Course module.
     *
     * @var stdClass $cm
     */
    protected $cm;

    /**
     * Item.
     *
     * @var stdClass $item
     */
    protected $item;

    /**
     * Theme.
     *
     * @var stdClass $theme
     */
    protected $theme;

    /**
     * Phase.
     *
     * @var stdClass $phase
     */
    protected $phase;

    /**
     * Sequence.
     *
     * @var stdClass $sequence
     */
    protected $sequence;

    /**
     * Activity.
     *
     * @var stdClass $activity
     */
    protected $activity;

    /**
     * SCO.
     *
     * @var stdClass $sco
     */
    protected $sco;

    /**
     * xAPI module.
     *
     * @var array $xapimodule
     */
    protected $xapimodule;


    /**
     * Build the Statement.
     *
     * @return array
     */
    protected function statement() {
        $this->init_data();
        return parent::statement();
    }

    /**
     * Init data.
     *
     * @return void
     */
    protected function init_data() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/mod/trainingpath/locallib.php');
        $this->cm = $DB->get_record('course_modules', ['id' => $this->event->contextinstanceid], '*', MUST_EXIST);
        $this->course = $DB->get_record('course', ['id' => $this->cm->course], '*', MUST_EXIST);
        $this->xapimodule = $this->activities->get('trainingpath', $this->cm->instance, false, 'module', 'trainingpath', 'mod_trainingpath');

        if ($this->event->objecttable == 'scormlite_scoes') {

            // SCO
            $this->sco = $DB->get_record('scormlite_scoes', ['id' => $this->event->objectid], '*', MUST_EXIST);
            $content = $DB->get_record('trainingpath_item', ['activity_type' => EATPL_ACTIVITY_TYPE_CONTENT, 'ref_id' => $this->sco->id]);
            $eval = $DB->get_record('trainingpath_item', ['activity_type' => EATPL_ACTIVITY_TYPE_EVAL, 'ref_id' => $this->sco->id]);
            $this->activity = $content ? $content : $eval;
            $this->sequence = $DB->get_record('trainingpath_item', ['id' => $this->activity->parent_id], '*', MUST_EXIST);
            $this->phase = $DB->get_record('trainingpath_item', ['id' => $this->sequence->parent_id], '*', MUST_EXIST);
            $this->theme = $DB->get_record('trainingpath_item', ['id' => $this->sequence->grouping_id], '*', MUST_EXIST);
            return;

        } else if ($this->event->objecttable == 'trainingpath_item') {
            
            $this->item = $DB->get_record('trainingpath_item', ['id' => $this->event->objectid], '*', MUST_EXIST);

            if ($this->item->type == EATPL_ITEM_TYPE_ACTIVITY) {

                // Activity
                $this->activity = $this->item;
                $this->sequence = $DB->get_record('trainingpath_item', ['id' => $this->activity->parent_id], '*', MUST_EXIST);
                $this->phase = $DB->get_record('trainingpath_item', ['id' => $this->sequence->parent_id], '*', MUST_EXIST);
                $this->theme = $DB->get_record('trainingpath_item', ['id' => $this->sequence->grouping_id], '*', MUST_EXIST);
            
            } else if ($this->item->type == EATPL_ITEM_TYPE_SEQUENCE) {

                // Sequence
                $this->sequence = $this->item;
                $this->phase = $DB->get_record('trainingpath_item', ['id' => $this->sequence->parent_id], '*', MUST_EXIST);
                $this->theme = $DB->get_record('trainingpath_item', ['id' => $this->sequence->grouping_id], '*', MUST_EXIST);
                
            } else if ($this->item->type == EATPL_ITEM_TYPE_BATCH) {

                // Phase
                $this->phase = $this->item;
                
            } else if ($this->item->type == EATPL_ITEM_TYPE_CERTIFICATE) {

                // Theme
                $this->theme = $this->item;
            }
        }
    }

    /**
     * Get the theme.
     *
     * @return array
     */
    protected function xapi_theme($fulldef = false) {

        // Base.
        $xapi = [
            'objectType' => 'Activity',
            'id' => $this->xapimodule['id'] . '/theme/' . $this->uuid($this->theme),
            'definition' => [
                'type' => 'http://vocab.xapi.fr/activities/training-module'
            ]
        ];

        // Full definition.
        if ($fulldef) {
            if (!empty($this->theme->code)) {
                $xapi['definition']['name'] = logstore_utils::lang_string($this->theme->code, $this->course);
            }
            if (!empty($this->theme->title)) {
                $xapi['definition']['description'] = logstore_utils::lang_string($this->theme->title, $this->course);
            }
            $xapi['definition']['extensions'] = [];
            $xapi['definition']['extensions']['http://id.tincanapi.com/extension/position'] = $this->position($this->theme);
            $xapi['definition']['extensions']['http://id.tincanapi.com/extension/duration'] = logstore_utils::iso8601_duration($this->theme->duration);
        }
        return $xapi;
    }

    /**
     * Get the phase.
     *
     * @return array
     */
    protected function xapi_phase($fulldef = false) {

        // Base.
        $xapi = [
            'objectType' => 'Activity',
            'id' => $this->xapimodule['id'] . '/phase/' . $this->uuid($this->phase),
            'definition' => [
                'type' => 'http://vocab.xapi.fr/activities/training-phase'
            ]
        ];

        // Full definition.
        if ($fulldef) {
            if (!empty($this->phase->code)) {
                $xapi['definition']['name'] = logstore_utils::lang_string($this->phase->code, $this->course);
            }
            if (!empty($this->phase->title)) {
                $xapi['definition']['description'] = logstore_utils::lang_string($this->phase->title, $this->course);
            }
            $xapi['definition']['extensions'] = [];
            $xapi['definition']['extensions']['http://id.tincanapi.com/extension/position'] = $this->position($this->phase);
        }
        return $xapi;
    }

    /**
     * Get the sequence.
     *
     * @return array
     */
    protected function xapi_sequence($fulldef = false) {

        // Base.
        $xapi = [
            'objectType' => 'Activity',
            'id' => $this->xapimodule['id'] . '/sequence/' . $this->uuid($this->sequence),
            'definition' => [
                'type' => 'http://vocab.xapi.fr/activities/training-sequence'
            ]
        ];

        // Full definition.
        if ($fulldef) {
            if (!empty($this->sequence->code)) {
                $xapi['definition']['name'] = logstore_utils::lang_string($this->sequence->code, $this->course);
            }
            if (!empty($this->sequence->title)) {
                $xapi['definition']['description'] = logstore_utils::lang_string($this->sequence->title, $this->course);
            }
            $xapi['definition']['extensions'] = [];
            $xapi['definition']['extensions']['http://id.tincanapi.com/extension/position'] = $this->position($this->sequence);
        }
        return $xapi;
    }

    /**
     * Get the activity.
     *
     * @return array
     */
    protected function xapi_activity($fulldef = false) {

        // Define the type and other things.
        global $CFG;
        require_once($CFG->dirroot.'/mod/trainingpath/locallib.php');
        $remedial = null;
        $duration = null;
        switch ($this->activity->activity_type) {
            case EATPL_ACTIVITY_TYPE_CONTENT:
                $type = 'http://vocab.xapi.fr/activities/web-content';
                $duration = logstore_utils::iso8601_duration($this->activity->duration);
                break;
            case EATPL_ACTIVITY_TYPE_EVAL:
                $type = 'http://vocab.xapi.fr/activities/quiz';
                $remedial = $this->activity->remedial ? true : false;
                break;
            case EATPL_ACTIVITY_TYPE_CLASSROOM:
                $type = 'http://vocab.xapi.fr/activities/face-to-face';
                $duration = logstore_utils::iso8601_duration($this->activity->duration);
                break;
            case EATPL_ACTIVITY_TYPE_VIRTUAL:
                $type = 'http://vocab.xapi.fr/activities/live-session';
                $duration = logstore_utils::iso8601_duration($this->activity->duration);
                break;
            case EATPL_ACTIVITY_TYPE_FILES:
                $type = 'http://adlnet.gov/expapi/activities/file';
                break;
            case EATPL_ACTIVITY_TYPE_RICHTEXT:
                $type = 'http://vocab.xapi.fr/activities/web-page';
                break;
        }

        // Base.
        $xapi = [
            'objectType' => 'Activity',
            'id' => $this->xapimodule['id'] . '/activity/' . $this->uuid($this->activity),
            'definition' => [
                'type' => $type
            ]
        ];

        // Full definition.
        if ($fulldef) {
            if (!empty($this->activity->code)) {
                $xapi['definition']['name'] = logstore_utils::lang_string($this->activity->code, $this->course);
                if (!empty($this->activity->title)) {
                    $xapi['definition']['description'] = logstore_utils::lang_string($this->activity->title, $this->course);
                }
            } else if (!empty($this->activity->title)) {
                $xapi['definition']['name'] = logstore_utils::lang_string($this->activity->title, $this->course);
            }
            $xapi['definition']['extensions'] = [];
            $xapi['definition']['extensions']['http://id.tincanapi.com/extension/position'] = $this->position($this->activity);
            $xapi['definition']['extensions']['http://vocab.xapi.fr/extensions/mandatory'] = $this->activity->complementary ? false : true;
            if (!is_null($duration)) {
                $xapi['definition']['extensions']['http://id.tincanapi.com/extension/duration'] = $duration;
            }
            if (!is_null($remedial)) {
                $xapi['definition']['extensions']['http://vocab.xapi.fr/extensions/remedial'] = $remedial;
            }
        }
        return $xapi;
    }

    /**
     * Get the sco.
     *
     * @return array
     */
    protected function xapi_sco() {

        return [
            'objectType' => 'Activity',
            'id' => $this->xapi_activity()['id'] . '/sco',
            'definition' => [
                'type' => $this->activities->types->type('sco'),
                'extensions' => [
                    'http://vocab.xapi.fr/extensions/standard' => $this->activities->types->standard('sco')
                ]
            ]
        ];
    }

    /**
     * Get the item position.
     *
     * @param stdClass $item item
     * @return int
     */
    protected function position($item) {
        return $item->parent_position == 1000 ? 1 : intval($item->parent_position);
    }

    /**
     * Get the item UUID.
     *
     * @param stdClass $item item
     * @return string
     */
    protected function uuid(&$item) {
        global $DB;
        if (isset($item->uuid) && $item->uuid) return $item->uuid;
        $item->uuid = logstore_utils::uuid();
        $DB->update_record('trainingpath_item', $item);
        return $item->uuid;
    }

}
