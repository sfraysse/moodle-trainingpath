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

namespace mod_trainingpath\privacy;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/trainingpath/locallib.php');

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;


/**
 * Privacy class for requesting user data.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider
{

    /**
     * Return the fields which contain personal data.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection
    {
        $collection->add_database_table('scormlite_scoes_track', [
            'userid' => 'privacy:metadata:scoes_track:userid',
            'attempt' => 'privacy:metadata:scoes_track:attempt',
            'element' => 'privacy:metadata:scoes_track:element',
            'value' => 'privacy:metadata:scoes_track:value',
            'timemodified' => 'privacy:metadata:scoes_track:timemodified'
        ], 'privacy:metadata:trainingpath_scoes_track');

        $collection->add_database_table('trainingpath_tracks', [
            'context_type' => 'privacy:metadata:tracks:context_type',
            'context_id' => 'privacy:metadata:tracks:context_id',
            'user_id' => 'privacy:metadata:tracks:user_id',
            'attempt' => 'privacy:metadata:tracks:attempt',
            'last_attempt' => 'privacy:metadata:tracks:last_attempt',
            'completion' => 'privacy:metadata:tracks:completion',
            'success' => 'privacy:metadata:tracks:success',
            'success_remedial' => 'privacy:metadata:tracks:success_remedial',
            'score' => 'privacy:metadata:tracks:score',
            'score_remedial' => 'privacy:metadata:tracks:score_remedial',
            'progress_value' => 'privacy:metadata:tracks:progress_value',
            'progress_max' => 'privacy:metadata:tracks:progress_max',
            'progress_unit' => 'privacy:metadata:tracks:progress_unit',
            'time_spent' => 'privacy:metadata:tracks:time_spent',
            'time_status' => 'privacy:metadata:tracks:time_status',
            'time_passing' => 'privacy:metadata:tracks:time_passing',
        ], 'privacy:metadata:trainingpath_tracks');

        $collection->add_database_table('trainingpath_comments', [
            'context_type' => 'privacy:metadata:comments:contexttype',
            'context_id' => 'privacy:metadata:comments:contextid',
            'user_id' => 'privacy:metadata:comments:userid',
            'group_id' => 'privacy:metadata:comments:groupid',
            'comment' => 'privacy:metadata:comments:comment'
        ], 'privacy:metadata:trainingpath_comments');

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist
    {
        $contextlist = new contextlist();

        // Select from Training Path tracks & comments
        $sql = "SELECT ctx.id
            FROM {%s} track
            JOIN {trainingpath_item} item
                ON item.id = track.context_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            JOIN {context} ctx
                ON ctx.instanceid = cm.id
                AND ctx.contextlevel = :modlevel
            WHERE track.user_id = :userid";

        $params = ['modlevel' => CONTEXT_MODULE, 'userid' => $userid];
        $contextlist->add_from_sql(sprintf($sql, 'trainingpath_tracks'), $params);
        $contextlist->add_from_sql(sprintf($sql, 'trainingpath_comments'), $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist    $userlist   The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!is_a($context, \context_module::class)) {
            return;
        }

        // Select from Training Path tracks & comments
        $sql = "SELECT track.user_id
            FROM {%s} track
            JOIN {trainingpath_item} item
                ON item.id = track.context_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            JOIN {context} ctx
                ON ctx.instanceid = cm.id
                AND ctx.contextlevel = :modlevel
            WHERE ctx.id = :contextid";
                 
        $params = ['modlevel' => CONTEXT_MODULE, 'contextid' => $context->id];
        $userlist->add_from_sql('user_id', sprintf($sql, 'trainingpath_tracks'), $params);
        $userlist->add_from_sql('user_id', sprintf($sql, 'trainingpath_comments'), $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist)
    {
        global $DB;
        $userid = $contextlist->get_user()->id;

        // Remove contexts different from CONTEXT_MODULE.
        $contexts = array_reduce($contextlist->get_contexts(), function ($carry, $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $carry[] = $context->id;
            }
            return $carry;
        }, []);

        if (empty($contexts)) {
            return;
        }
        list($insql, $inparams) = $DB->get_in_or_equal($contexts, SQL_PARAMS_NAMED);
        $params = array_merge($inparams, ['userid' => $userid]);

        // Get from Training Path tracks

        $sql = "SELECT track.*,
                       item.id as itemid,
                       item.code as itemcode,
                       item.title as itemtitle,
                       item.type as itemtype,
                       ctx.id as contextid
            FROM {trainingpath_tracks} track
            JOIN {trainingpath_item} item
                ON item.id = track.context_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            JOIN {context} ctx
                ON ctx.instanceid = cm.id
            WHERE ctx.id $insql
                AND track.user_id = :userid";

        $alldata = [];
        $tracks = $DB->get_recordset_sql($sql, $params);
        foreach ($tracks as $track) {
            $alldata[$track->contextid][$track->itemid] = (object) [
                'code' => $track->itemcode,
                'title' => $track->itemtitle,
                'type' => $track->itemtype,
                'attempt' => $track->attempt,
                'last_attempt' => $track->last_attempt,
                'completion' => $track->completion,
                'success' => $track->success,
                'success_remedial' => $track->success_remedial,
                'score' => $track->score,
                'score_remedial' => $track->score_remedial,
                'progress_value' => $track->progress_value,
                'progress_max' => $track->progress_max,
                'progress_unit' => $track->progress_unit,
                'time_spent' => $track->time_spent,
                'time_status' => $track->time_status,
                'time_passing' => $track->time_passing,
            ];
        }
        $tracks->close();

        // Push in folders
        array_walk($alldata, function ($pathdata, $contextid) {
            $context = \context::instance_by_id($contextid);
            array_walk($pathdata, function ($itemdata, $itemid) use ($context) {

                // Item description
                $subcontext = [
                    get_string('items', 'trainingpath'),
                    get_string('item', 'trainingpath') . ' ' . $itemid,
                    get_string('itemdescr', 'trainingpath'),
                ];
                $itemdescr = (object)[
                    'type' => static::item_type($itemdata->type),
                    'code' => $itemdata->code,
                    'title' => $itemdata->title,
                ];
                writer::with_context($context)->export_data(
                    $subcontext,
                    (object) ['item' => $itemdescr]
                );

                // My status
                $subcontext = [
                    get_string('items', 'trainingpath'),
                    get_string('item', 'trainingpath') . ' ' . $itemid,
                    get_string('mystatus', 'trainingpath'),
                ];
                unset($itemdata->code);
                unset($itemdata->title);
                unset($itemdata->type);
                writer::with_context($context)->export_data(
                    $subcontext,
                    (object) ['status' => $itemdata]
                );
            });
        });

        // Get from SCORM Lite tracks

        $sql = "SELECT sst.id,
                       sst.attempt,
                       sst.element,
                       sst.value,
                       sst.timemodified,
                       item.id as itemid,
                       ctx.id as contextid
            FROM {scormlite_scoes_track} sst
            JOIN {trainingpath_item} item
                ON sst.scoid = item.ref_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            JOIN {context} ctx
                ON ctx.instanceid = cm.id
            WHERE ctx.id $insql
                AND sst.userid = :userid";

        $alldata = [];
        $tracks = $DB->get_recordset_sql($sql, $params);
        foreach ($tracks as $track) {
            $alldata[$track->contextid][$track->itemid][$track->attempt][] = (object) [
                'element' => $track->element,
                'value' => $track->value,
                'timemodified' => transform::datetime($track->timemodified),
            ];
        }
        $tracks->close();

        // Push in folders
        array_walk($alldata, function ($pathdata, $contextid) {
            $context = \context::instance_by_id($contextid);
            array_walk($pathdata, function ($itemdata, $itemid) use ($context) {
                array_walk($itemdata, function ($attemptdata, $attempt) use ($context, $itemid) {
                    $subcontext = [
                        get_string('items', 'trainingpath'),
                        get_string('item', 'trainingpath') . ' ' . $itemid,
                        get_string('myattempts', 'scorm'),
                        get_string('attempt', 'scorm') . " $attempt"
                    ];
                    writer::with_context($context)->export_data(
                        $subcontext,
                        (object) ['scoestrack' => $attemptdata]
                    );
                });
            });
        });

        // Get from Training Path comments

        $sql = "SELECT track.*,
                       item.id as itemid,
                       ctx.id as contextid
            FROM {trainingpath_comments} track
            JOIN {trainingpath_item} item
                ON item.id = track.context_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            JOIN {context} ctx
                ON ctx.instanceid = cm.id
            WHERE ctx.id $insql
                AND track.user_id = :userid";

        $alldata = [];
        $tracks = $DB->get_recordset_sql($sql, $params);
        foreach ($tracks as $track) {
            $alldata[$track->contextid][$track->itemid] = (object) [
                'comment' => $track->comment,
            ];
        }
        $tracks->close();

        // Push in folders
        array_walk($alldata, function ($pathdata, $contextid) {
            $context = \context::instance_by_id($contextid);
            array_walk($pathdata, function ($itemdata, $itemid) use ($context) {

                // My comments
                $subcontext = [
                    get_string('items', 'trainingpath'),
                    get_string('item', 'trainingpath') . ' ' . $itemid,
                    get_string('comments', 'trainingpath'),
                ];
                writer::with_context($context)->export_data(
                    $subcontext,
                    (object) ['comments' => $itemdata]
                );
            });
        });
    }

    /**
     * Delete all user data which matches the specified context.
     *
     * @param context $context A user context.
     */
    public static function delete_data_for_all_users_in_context(\context $context)
    {
        // This should not happen, but just in case.
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        // Delete from SCORM Lite tracks

        $sql = "SELECT sst.id
            FROM {%s} sst
            JOIN {trainingpath_item} item
                ON sst.scoid = item.ref_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            WHERE cm.id = :cmid";

        $params = ['cmid' => $context->instanceid];
        static::delete_data('scormlite_scoes_track', $sql, $params);
        
        // Delete from Training Path tracks & comments
        
        $sql = "SELECT track.id
            FROM {%s} track
            JOIN {trainingpath_item} item
                ON item.id = track.context_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            WHERE cm.id = :cmid";

        $params = ['cmid' => $context->instanceid];
        static::delete_data('trainingpath_tracks', $sql, $params);
        static::delete_data('trainingpath_comments', $sql, $params);
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist)
    {
        global $DB;
        $userid = $contextlist->get_user()->id;

        // Remove contexts different from CONTEXT_MODULE.
        $contextids = array_reduce($contextlist->get_contexts(), function ($carry, $context) {
            if ($context->contextlevel == CONTEXT_MODULE) {
                $carry[] = $context->id;
            }
            return $carry;
        }, []);

        if (empty($contextids)) {
            return;
        }
        list($insql, $inparams) = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED);

        // Delete from SCORM Lite tracks

        $sql = "SELECT sst.id
            FROM {%s} sst
            JOIN {trainingpath_item} item
                ON sst.scoid = item.ref_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            JOIN {context} ctx
                ON ctx.instanceid = cm.id
            WHERE sst.userid = :userid
                AND ctx.id $insql";

        $params = array_merge($inparams, ['userid' => $userid]);
        static::delete_data('scormlite_scoes_track', $sql, $params);

        // Delete from Training Path tracks & comments

        $sql = "SELECT track.id
            FROM {%s} track
            JOIN {trainingpath_item} item
                ON item.id = track.context_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            JOIN {context} ctx
                ON ctx.instanceid = cm.id
            WHERE track.user_id = :userid
                AND ctx.id $insql";

        $params = array_merge($inparams, ['userid' => $userid]);
        static::delete_data('trainingpath_tracks', $sql, $params);
        static::delete_data('trainingpath_comments', $sql, $params);
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist       $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        $userids = $userlist->get_userids();

        if (!is_a($context, \context_module::class)) {
            return;
        }
        list($insql, $inparams) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);

        // Delete from SCORM Lite tracks

        $sql = "SELECT sst.id
            FROM {%s} sst
            JOIN {trainingpath_item} item
                ON sst.scoid = item.ref_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            JOIN {context} ctx
                ON ctx.instanceid = cm.id
            WHERE ctx.id = :contextid
                AND sst.userid $insql";

        $params = array_merge($inparams, ['contextid' => $context->id]);
        static::delete_data('scormlite_scoes_track', $sql, $params);

        // Delete from Training Path tracks & comments

        $sql = "SELECT track.id
            FROM {%s} track
            JOIN {trainingpath_item} item
                ON item.id = track.context_id
            JOIN {modules} m
                ON m.name = 'trainingpath'
            JOIN {course_modules} cm
                ON cm.instance = item.path_id
                AND cm.module = m.id
            JOIN {context} ctx
                ON ctx.instanceid = cm.id
            WHERE ctx.id = :contextid
                AND track.user_id $insql";

        $params = array_merge($inparams, ['contextid' => $context->id]);
        static::delete_data('trainingpath_tracks', $sql, $params);
        static::delete_data('trainingpath_comments', $sql, $params);
    }

    /**
     * Delete data from $tablename with the IDs returned by $sql query.
     *
     * @param  string $tablename  Table name where executing the SQL query.
     * @param  string $sql    SQL query for getting the IDs of the scoestrack entries to delete.
     * @param  array  $params SQL params for the query.
     */
    protected static function delete_data(string $tablename, string $sql, array $params)
    {
        global $DB;
        $ids = $DB->get_fieldset_sql(sprintf($sql, $tablename), $params);
        if (!empty($ids)) {
            list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
            $DB->delete_records_select($tablename, "id $insql", $inparams);
        }
    }

    /**
     * Get the item type.
     *
     * @param  int $type  Type code.
     * @return string
     */
    protected static function item_type(int $code)
    {
        switch($code) {
            case EATPL_ITEM_TYPE_PATH:
                return 'Training Path';
            case EATPL_ITEM_TYPE_BATCH:
                return 'Phase';
            case EATPL_ITEM_TYPE_SEQUENCE:
                return 'Sequence';
            case EATPL_ITEM_TYPE_ACTIVITY:
                return 'Activity';
            case EATPL_ITEM_TYPE_CERTIFICATE:
                return 'Theme';
        }
    }
}
