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

use logstore_trax\src\utils\module_context;
use logstore_trax\src\statements\base_statement;

/**
 * xAPI transformation of an trainingpath event.
 *
 * @package    mod_trainingpath
 * @copyright  2019 Sébastien Fraysse {@link http://fraysse.eu}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class item_statement extends base_statement {

    use module_context, statement_utils;


    /**
     * Get the base Statement.
     *
     * @return array
     */
    protected function statement_base() {
        return [
            'context' => $this->statement_context(),
            'timestamp' => date('c', $this->event->timecreated),
        ];
    }

    /**
     * Get the object.
     *
     * @return array
     */
    protected function statement_object() {

        switch ($this->item->type) {
            case EATPL_ITEM_TYPE_ACTIVITY:
                return $this->xapi_activity(true);
            case EATPL_ITEM_TYPE_SEQUENCE:
                return $this->xapi_sequence(true);
            case EATPL_ITEM_TYPE_BATCH:
                return $this->xapi_phase(true);
            case EATPL_ITEM_TYPE_CERTIFICATE:
                return $this->xapi_theme(true);
            case EATPL_ITEM_TYPE_PATH:
                return $this->activities->get('trainingpath', $this->cm->instance, true, 'module', 'trainingpath', 'mod_trainingpath');
        }
    }

    /**
     * Get the context.
     *
     * @return array
     */
    protected function statement_context() {
        $context = $this->base_context('assessmentpath', true, 'assessmentpath', 'mod_assessmentpath');
        switch ($this->item->type) {

            case EATPL_ITEM_TYPE_ACTIVITY:
                $context['contextActivities']['parent'][0] = $this->xapi_sequence();
                $context['contextActivities']['grouping'][] = $this->xapi_phase();
                $context['contextActivities']['grouping'][] = $this->xapi_theme();
                $context['contextActivities']['grouping'][] = $this->xapimodule;
                break;

            case EATPL_ITEM_TYPE_SEQUENCE:
                $context['contextActivities']['parent'][0] = $this->xapi_phase();
                $context['contextActivities']['grouping'][] = $this->xapi_theme();
                $context['contextActivities']['grouping'][] = $this->xapimodule;
                break;

            case EATPL_ITEM_TYPE_BATCH:
                $context['contextActivities']['parent'][0] = $this->xapimodule;
                break;

            case EATPL_ITEM_TYPE_CERTIFICATE:
                $context['contextActivities']['parent'][0] = $this->xapimodule;
                break;

            case EATPL_ITEM_TYPE_PATH:

        }

        // Moodle module profile.
        foreach ($context['contextActivities']['category'] as &$category) {
            if ($category['id'] == 'http://vocab.xapi.fr/categories/moodle/scormlite'
                || $category['id'] == 'http://vocab.xapi.fr/categories/moodle/assessmentpath') {
                    
                $category['id'] = 'http://vocab.xapi.fr/categories/moodle/trainingpath';
                break;
            }
        }
        
        // Change context to "inside module".
        if ($this->item->type != EATPL_ITEM_TYPE_PATH) {

            // Add course in grouping context.
            $course = $this->activities->get('course', $this->event->courseid, false);
            $context['contextActivities']['grouping'][] = $course;

            // Change granularity level to "inside-learning-unit".
            foreach ($context['contextActivities']['category'] as &$category) {
                if ($category['definition']['type'] == 'http://vocab.xapi.fr/activities/granularity-level') {
                    $category['id'] = 'http://vocab.xapi.fr/categories/inside-learning-unit';
                    break;
                }
            }
        }
        
        return $context;
    }

}
