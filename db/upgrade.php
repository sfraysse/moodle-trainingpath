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

defined('MOODLE_INTERNAL') || die;

function xmldb_trainingpath_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // Add UUID column on trainingpath_item table.

    if ($oldversion < 2018050801) {

        $table = new xmldb_table('trainingpath_item');

        // Add the column.
        $field = new xmldb_field('uuid', XMLDB_TYPE_CHAR, '36', null, null, null, null, 'ref_id');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add the index.
        $index = new xmldb_index('uuid', XMLDB_INDEX_UNIQUE, array('uuid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Savepoint.
        upgrade_mod_savepoint(true, 2018050801, 'trainingpath');
    }

    return true;
}
