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

require_once($CFG->dirroot.'/mod/trainingpath/report/lib.php');

use logstore_trax\src\utils as logstore_utils;

/**
 * xAPI transformation of an trainingpath event.
 *
 * @package    mod_trainingpath
 * @copyright  2019 Sébastien Fraysse {@link http://fraysse.eu}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class item_completed extends item_statement {
    

    /**
     * Build the Statement.
     *
     * @return array
     */
    protected function statement() {

        $this->init_data();

        return array_replace($this->statement_base(), [
            'actor' => $this->actors->get('user', $this->event->relateduserid),
            'verb' => $this->verbs->get('completed'),
            'result' => $this->statement_result(),
            'object' => $this->statement_object()
        ]);
    }

    /**
     * Get the result.
     *
     * @return array
     */
    protected function statement_result() {
        $res = [];
        $res['completion'] = true;
        $res['duration'] = logstore_utils::iso8601_duration($this->eventother->time_spent);

        if (isset($this->eventother->success) && $this->eventother->success && isset($this->eventother->score)) {

            // Main score.
            $res['success'] = ($this->eventother->success == EATPL_SUCCESS_PASSED);
            $res['score'] = [
                'min' => 0,
                'max' => 100,
                'raw' => intval($this->eventother->score),
                'scaled' => intval($this->eventother->score) / 100
            ];

            // Remedial score.
            if (isset($this->eventother->success_remedial) && $this->eventother->success_remedial 
                && isset($this->eventother->score_remedial) && $this->eventother->score != $this->eventother->score_remedial) {
                $res['extensions'] = [
                    'http://vocab.xapi.fr/extensions/remedial-success' => ($this->eventother->success_remedial == EATPL_SUCCESS_PASSED),
                    'http://vocab.xapi.fr/extensions/remedial-score' => [
                        'min' => 0,
                        'max' => 100,
                        'raw' => intval($this->eventother->score_remedial),
                        'scaled' => intval($this->eventother->score_remedial) / 100
                    ]
                ];
            }
        }

        return $res;
    }

    /**
     * Get the context.
     *
     * @return array
     */
    protected function statement_context() {
        $context = parent::statement_context();

        // Add instructor when required.
        if ($this->event->userid != $this->event->relateduserid) {
            $context['instructor'] = $this->actors->get('user', $this->event->userid);
        }

        return $context;
    }

}
