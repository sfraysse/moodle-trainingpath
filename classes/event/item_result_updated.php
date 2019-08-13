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

namespace mod_trainingpath\event;

defined('MOODLE_INTERNAL') || die();

class item_result_updated extends item_event {
    
    /**
     * Return localised event name.
     */
    public static function get_name() {
        return get_string('event:item_result_updated', 'trainingpath');
    }

    /**
     * Returns description of what happened.
     */
    public function get_description()  {
        return "The result of user with id '$this->relateduserid' 
            on the trainingpath item with the id '$this->objectid' 
            changed in the trainingpath activity with the course module id '$this->contextinstanceid'.";
    }

}
