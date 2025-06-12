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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.
// Project implemented by the \"Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU\".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * Version details
 *
 * @package    quizaccess_quiztimer
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 namespace quizaccess_quiztimer\privacy;

 use core_privacy\local\metadata\collection;
 use core_privacy\local\request\contextlist;
 use core_privacy\local\request\approved_contextlist;
 use core_privacy\local\request\userlist;
 use core_privacy\local\request\approved_userlist;
 use core_privacy\local\request\writer;

 defined('MOODLE_INTERNAL') || die();

 class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\core_userlist_provider,
        \core_privacy\local\request\plugin\provider {

    /**
     * Retrieve the user metadata stored by plugin.
     *
     * @param collection $collection Collection of metadata.
     * @return collection Collection of metadata.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('quizaccess_quiztimer', [
            'quiz' => 'privacy:metadata:quiz',
            'quiz_mode' => 'privacy:metadata:quiz_mode',
            'usermodified' => 'privacy:metadata:usermodified',
            'timecreated' => 'privacy:metadata:timecreated',
            'timemodified' => 'privacy:metadata:timemodified',
        ], 'privacy:metadata:quizaccess_quiztimer', 'quizaccess_quiztimer');

        $collection->add_database_table('quizaccess_usertimedslots', [
            'quizid' => 'privacy:metadata:quizid',
            'slot' => 'privacy:metadata:slot',
            'userid' => 'privacy:metadata:userid',
            'attempt' => 'privacy:metadata:attempt',
            'timestart' => 'privacy:metadata:timestart',
            'timefinish' => 'privacy:metadata:timefinish',
        ], 'privacy:metadata:quizaccess_usertimedslots', 'quizaccess_quiztimer');

        $collection->add_database_table('quizaccess_usertimedsections', [
            'quizid' => 'privacy:metadata:quizid',
            'section' => 'privacy:metadata:section',
            'userid' => 'privacy:metadata:userid',
            'attempt' => 'privacy:metadata:attempt',
            'timestart' => 'privacy:metadata:timestart',
            'timefinish' => 'privacy:metadata:timefinish',
        ], 'privacy:metadata:quizaccess_usertimedsections', 'quizaccess_quiztimer');

        return $collection;
    }



    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist A list of contexts used in this plugin.
     */
     public static function get_contexts_for_userid(int $userid): contextlist {
         $contextlist = new contextlist();

         $sql = "SELECT ctx.id
                   FROM {context} ctx
                   JOIN {quizaccess_usertimedslots} uts ON uts.userid = :userid
                   JOIN {quiz} q ON q.id = uts.quizid
                   JOIN {course_modules} cm ON cm.instance = q.id
                   JOIN {modules} m ON cm.module = m.id AND m.name = :modulename
                   WHERE ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
                   GROUP BY ctx.id";

         $params = [
             'userid' => $userid,
             'contextlevel' => CONTEXT_MODULE,
             'modulename' => 'quiz',
         ];

         $contextlist->add_from_sql($sql, $params);


         $sql = "SELECT ctx.id
                   FROM {context} ctx
                   JOIN {quizaccess_usertimedsections} uts ON uts.userid = :userid
                   JOIN {quiz} q ON q.id = uts.quizid
                   JOIN {course_modules} cm ON cm.instance = q.id
                   JOIN {modules} m ON cm.module = m.id AND m.name = :modulename
                   WHERE ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
                   GROUP BY ctx.id";

         $params = [
             'userid' => $userid,
             'contextlevel' => CONTEXT_MODULE,
             'modulename' => 'quiz',
         ];

         $contextlist->add_from_sql($sql, $params);


         $sql = "SELECT ctx.id
                   FROM {context} ctx
                   JOIN {quizaccess_quiztimer} qt ON qt.userid = :userid
                   JOIN {quiz} q ON q.id = qt.quizid
                   JOIN {course_modules} cm ON cm.instance = q.id
                   JOIN {modules} m ON cm.module = m.id AND m.name = :modulename
                   WHERE ctx.contextlevel = :contextlevel AND ctx.instanceid = cm.id
                   GROUP BY ctx.id";

         $params = [
             'userid' => $userid,
             'contextlevel' => CONTEXT_MODULE,
             'modulename' => 'quiz',
         ];

         $contextlist->add_from_sql($sql, $params);

         return $contextlist;
     }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {

            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $cm = get_coursemodule_from_id('quiz', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $quizid = $cm->instance;

            $data = $DB->get_records('quizaccess_quiztimer', [
                'quiz' => $quizid,
                'usermodified' => $contextlist->get_user()->id,
            ]);

            if (!empty($data)) {
                writer::with_context($context)->export_data(
                    ['quizaccess_quiztimer'],
                    (object)[
                        'timersettings' => array_values($data),
                    ]
                );
            }


            $data = $DB->get_records('quizaccess_usertimedslots', [
                'quizid' => $quizid,
                'userid' => $contextlist->get_user()->id,
            ]);

            if (!empty($data)) {
                writer::with_context($context)->export_data(
                    ['quizaccess_usertimedslots'],
                    (object)[
                        'slots' => array_values($data),
                    ]
                );
            }

            $data = $DB->get_records('quizaccess_usertimedsections', [
                'quizid' => $quizid,
                'userid' => $contextlist->get_user()->id,
            ]);

            if (!empty($data)) {
                writer::with_context($context)->export_data(
                    ['quizaccess_usertimedsections'],
                    (object)[
                        'sections' => array_values($data),
                    ]
                );
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('quiz', $context->instanceid);
        if (!$cm) {
            return;
        }

        $quizid = $DB->get_field('quiz', 'id', ['id' => $cm->instance]);

        if ($quizid) {
            $DB->delete_records('quizaccess_usertimedslots', ['quizid' => $quizid]);
            $DB->delete_records('quizaccess_usertimedsections', ['quizid' => $quizid]);
            $DB->delete_records('quizaccess_quiztimer', ['quiz' => $quizid]);
        }
    }


    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */

     public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $cm = get_coursemodule_from_id('quiz', $context->instanceid);
            if (!$cm) {
                continue;
            }

            $quizid = $DB->get_field('quiz', 'id', ['id' => $cm->instance]);
            if (!$quizid) {
                continue;
            }

            $DB->delete_records('quizaccess_usertimedslots', [
                'quizid' => $quizid,
                'userid' => $userid,
            ]);

            $DB->delete_records('quizaccess_usertimedsections', [
                'quizid' => $quizid,
                'userid' => $userid,
            ]);

            $DB->delete_records('quizaccess_quiztimer', [
                'quiz' => $quizid,
                'usermodified' => $userid,
            ]);
        }
    }



    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $cm = get_coursemodule_from_id('quiz', $context->instanceid);
        if (!$cm) {
            return;
        }

        $quizid = $DB->get_field('quiz', 'id', ['id' => $cm->instance]);
        if (!$quizid) {
            return;
        }

        $params = ['quizid' => $quizid];

        $userids = $DB->get_fieldset_select('quizaccess_usertimedslots', 'DISTINCT userid', 'quizid = :quizid', $params);
        foreach ($userids as $userid) {
            $userlist->add_user($userid);
        }

        $userids = $DB->get_fieldset_select('quizaccess_usertimedsections', 'DISTINCT userid', 'quizid = :quizid', $params);
        foreach ($userids as $userid) {
            $userlist->add_user($userid);
        }

        $userids = $DB->get_fieldset_select('quizaccess_quiztimer', 'DISTINCT usermodified', 'quiz = :quizid', $params);
        foreach ($userids as $userid) {
            $userlist->add_user($userid);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
     public static function delete_data_for_users(approved_userlist $userlist) {
         global $DB;
         $context = $userlist->get_context();

         if ($context->contextlevel != CONTEXT_MODULE) {
             return;
         }


         foreach ($userlist->get_userids() as $userid) {
            $DB->delete_records('quizaccess_usertimedslots', ['userid' => $userid]);
            $DB->delete_records('quizaccess_usertimedslots', ['userid' => $userid]);
            $DB->delete_records('quizaccess_quiztimer', ['usermodified' => $userid]);
         }
     }


 }