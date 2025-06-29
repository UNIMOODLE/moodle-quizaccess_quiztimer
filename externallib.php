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
defined('MOODLE_INTERNAL') || die;
global $CFG;
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
require_once($CFG->dirroot . '/mod/quiz/accessmanager.php');
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;

/**
 * quiztime external functions
 *
 * @package    quizaccess_quiztimer
 * @category   external
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 4.1
 */
class quizaccess_quiztimer_external extends external_api {

    /**
     * Checks that the cmid passed is of type int.
     *
     * @return external_function_parameters function.
     */
    public static function get_quiz_id_parameters() {
        return new external_function_parameters(
            ["cmid" => new external_value(PARAM_INT, "cmid")]
        );
    }

    /**
     * Gets the quizid from the course module id.
     *
     * @param int $cmid
     * @return string json encoded quizid for current course module
     */
    public static function get_quiz_id($cmid) {
        global $DB;
        $params = self::validate_parameters(
            self::get_quiz_id_parameters(),
                ["cmid" => $cmid]
        );
        $cm = get_coursemodule_from_id('quiz', $params['cmid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:manage', $context);

        $sql = "SELECT instance quizid FROM {course_modules} WHERE id = :id";
        $params = ['id' => $cmid];
        $quizid = $DB->get_record_sql($sql, $params);
        return json_encode($quizid);
    }

    /**
     * info about the returned object
     */
    public static function get_quiz_id_returns() {
        return null;
    }

    /**
     * Params check for setting a question time.
     *
     * @return external_function_parameters function.
     */
    public static function set_question_time_parameters() {
        return new external_function_parameters(
            ["quizid" => new external_value(PARAM_INT, "quizid"),
            "questionid" => new external_value(PARAM_INT, "questionid"),
            "timedata" => new external_value(PARAM_RAW, "timedata"),
            ]);
    }

    /**
     * Inserts or updates the time of a question in the
     * questions times table.
     *
     * @param int $quizid
     * @param int $questionid
     * @param array $timedata contains time unit and value
     * @return string json encoded info of the time inserted | updated in the db
     */
    public static function set_question_time($quizid, $questionid, $timedata) {
        global $DB, $USER;
        $params = self::validate_parameters(
            self::set_question_time_parameters(),
                ["quizid" => $quizid, "questionid" => $questionid, "timedata" => $timedata]
        );
        $cm = get_coursemodule_from_instance('quiz', $params['quizid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:manage', $context);


        $sql = "SELECT id, slot, timeunit, timevalue FROM {quizaccess_timedslots} WHERE slot = :slot AND quizid = :quizid";
        $params = ['slot' => $questionid, 'quizid' => $quizid];
        $timedslot = $DB->get_record_sql($sql, $params);
        $timedata = json_decode($timedata);
        if ($timedata->value == 0 || $timedata->unit == 0) {
            return json_encode($timedata);
        }
        $timenow = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        if ($timedslot === false) {
            $timedslot = new stdClass();
            $timedslot->slot = $questionid;
            $timedslot->quizid = $quizid;
            $timedslot->timeunit = $timedata->unit;
            $timedslot->timevalue = $timedata->value;
            $timedslot->timecreated = $timenow;
            $timedslot->timemodified = $timenow;
            $DB->insert_record('quizaccess_timedslots', $timedslot);
        } else {
            $timedslot->timeunit = $timedata->unit;
            $timedslot->timevalue = $timedata->value;
            $timedslot->timemodified = $timenow;
            $DB->update_record('quizaccess_timedslots', $timedslot);
        }
        switch($timedslot->timeunit) {
            case 1:
                $timeunit = get_string('seconds');
                $timevalue = $timedslot->timevalue;
                break;
            case 2:
                $timeunit = get_string('minutes');
                $timevalue = $timedslot->timevalue / 60;
                break;
            case 3:
                $timeunit = get_string('hours');
                $timevalue = $timedslot->timevalue / 3600;
                break;
            default:
                $timeunit = '';
                $timevalue = 0;
                break;
        }
        list($course, $cm) = get_course_and_cm_from_instance($quizid, 'quiz');
        $event = \quizaccess_quiztimer\event\slot_timer_updated::create([
            'objectid' => $timedslot->slot,
            'context' => \context_module::instance($cm->id),
            'relateduserid' => $USER->id,
            'other' => [
                'userid' => $USER->id,
                'slot' => $timedslot->slot,
                'timevalue' => $timevalue,
                'timeunit' => $timeunit,
            ],
        ]);

        $event->trigger();

        return json_encode($timedslot);
    }

    /**
     * info about the returned object
     */
    public static function set_question_time_returns() {
        return null;
    }

    /**
     * Checks that the questionid is of type int
     *
     * @return external_function_parameters function.
     */
    public static function get_question_time_parameters() {
        return new external_function_parameters(
            ["questionid" => new external_value(PARAM_INT, "questionid"),
            ]);
    }

    /**
     * Gets the information about a question time unit and value
     * from the questions db.
     *
     * @param int $questionid
     * @return string json encoded unit and value from a question
     */
    public static function get_question_time($questionid) {
        global $DB, $CFG;
        $params = self::validate_parameters(
            self::get_question_time_parameters(),
                ["questionid" => $questionid]
        );
        $question = $DB->get_record('question', ['id' => $params['questionid']], '*', MUST_EXIST);
        $context = context_module::instance($question->contextid);
        self::validate_context($context);
        require_capability('mod/quiz:manage', $context);

        $params = ['slot' => $questionid];
        $slottime = $DB->get_record('quizaccess_timedslots', $params, 'timeunit, timevalue', IGNORE_MISSING);
        if (!$slottime) {
            $slottime = new stdClass();
            $slottime->quizid = $DB->get_field('quiz_slots', 'quizid', ['id' => $questionid], IGNORE_MISSING);
            $slottime->slot = $questionid;
            $slottime->timecreated = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
            get_config('quizaccess_quiztimer', 'timedslotsunit') ? $slottime->timeunit =
            get_config('quizaccess_quiztimer', 'timedslotsunit') : $slottime->timeunit = 1;
            get_config('quizaccess_quiztimer', 'timedslots') ? $slottime->timevalue =
            get_config('quizaccess_quiztimer', 'timedslots') : $slottime->timevalue = 60;
            switch ($slottime->timeunit) {
                case 3:
                    $slottime->timevalue *= 3600;
                    break;
                case 2:
                    $slottime->timevalue *= 60;
                    break;
                default:
                    break;
            }
            $DB->insert_record('quizaccess_timedslots', $slottime);
        }

        return json_encode($slottime);
    }
    /**
     * info about the returned object.
     */
    public static function get_question_time_returns() {
        return null;
    }

    /**
     * Validates the params for setting a section time.
     *
     * @return external_function_parameters function
     */
    public static function set_section_time_parameters() {
        return new external_function_parameters(
            ["quizid" => new external_value(PARAM_INT, "quizid"),
            "sectionid" => new external_value(PARAM_INT, "sectionid"),
            "timedata" => new external_value(PARAM_RAW, "timedata"),
            ]);
    }

    /**
     * Inserts or updates a section time in the section times db.
     *
     * @param int $quizid
     * @param int $sectionid
     * @param array $timedata
     * @return string json encoded information about setted section
     */
    public static function set_section_time($quizid, $sectionid, $timedata) {
        global $DB, $USER;
        $params = self::validate_parameters(
            self::set_section_time_parameters(),
                ["quizid" => $quizid, "sectionid" => $sectionid, "timedata" => $timedata]
        );
        $cm = get_coursemodule_from_instance('quiz', $params['quizid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:manage', $context);

        $sql = "SELECT id, sectionid, timeunit, timevalue FROM {quizaccess_timedsections} WHERE
                 sectionid = :section AND quizid = :quizid";
        $params = ['section' => $sectionid, 'quizid' => $quizid];
        $timedsection = $DB->get_record_sql($sql, $params);
        $timedata = json_decode($timedata);
        if ($timedata->value == 0 || $timedata->unit == 0) {
            return json_encode($timedata);
        }
        $timenow = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
        if ($timedsection === false) {
            $timedsection = new stdClass();
            $timedsection->sectionid = $sectionid;
            $timedsection->quizid = $quizid;
            $timedsection->timeunit = $timedata->unit;
            $timedsection->timevalue = $timedata->value;
            $timedsection->timecreated = $timenow;
            $timedsection->timemodified = $timenow;
            $DB->insert_record('quizaccess_timedsections', $timedsection);
        } else {
            $timedsection->timeunit = $timedata->unit;
            $timedsection->timevalue = $timedata->value;
            $timedsection->timemodified = $timenow;
            $DB->update_record('quizaccess_timedsections', $timedsection);
        }
        switch($timedsection->timeunit) {
            case '1':
                $timeunit = get_string('seconds');
                $timevalue = $timedsection->timevalue;
                break;
            case '2':
                $timeunit = get_string('minutes');
                $timevalue = $timedsection->timevalue / 60;
                break;
            case '3':
                $timeunit = get_string('hours');
                $timevalue = $timedsection->timevalue / 3600;
                break;
            default:
                $timeunit = '';
                $timevalue = 0;
                break;
        }
        list($cm) = get_course_and_cm_from_instance($quizid, 'quiz');
        $event = \quizaccess_quiztimer\event\section_timer_updated::create([
            'objectid' => $timedsection->sectionid,
            'context' => \context_module::instance($cm->id),
            'relateduserid' => $USER->id,
            'other' => [
                'userid' => $USER->id,
                'section' => $timedsection->sectionid,
                'timevalue' => $timevalue,
                'timeunit' => $timeunit,
            ],
        ]);

        $event->trigger();

        return json_encode($timedsection);
    }

    /**
     * info about the returned object.
     */
    public static function set_section_time_returns() {
        return null;
    }

    /**
     * Validates the params for getting a section time.
     *
     * @return external_function_parameters function
     */
    public static function get_section_time_parameters() {
        return new external_function_parameters(
            ["quizid" => new external_value(PARAM_INT, "quizid"),
                "sectionid" => new external_value(PARAM_INT, "sectionid"),
            ]);
    }

    /**
     * Gets the info about a section time value and unit
     * from the sections time db.
     *
     * @param int $quizid
     * @param int $sectionid
     * @return string json encoded section time unit and value
     */
    public static function get_section_time($quizid, $sectionid) {
        global $DB;
        $params = self::validate_parameters(
            self::get_section_time_parameters(),
                ["quizid" => $quizid, "sectionid" => $sectionid]
        );
        $cm = get_coursemodule_from_instance('quiz', $params['quizid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:manage', $context);

        $sectiontime = $DB->get_record('quizaccess_timedsections', $params, 'timeunit, timevalue', IGNORE_MISSING);
        if (!$sectiontime) {
            $sectiontime = new stdClass();
            $sectiontime->quizid = $quizid;
            $sectiontime->sectionid = $sectionid;
            $sectiontime->timecreated = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
            get_config('quizaccess_quiztimer', 'timedsectionsunit') ? $sectiontime->timeunit =
                get_config('quizaccess_quiztimer', 'timedsectionsunit') : $sectiontime->timeunit = 2;
            get_config('quizaccess_quiztimer', 'timedsections') ? $sectiontime->timevalue =
                get_config('quizaccess_quiztimer', 'timedsections') : $sectiontime->timevalue = 10;
            switch ($sectiontime->timeunit) {
                case 3:
                    $sectiontime->timevalue *= 3600;
                    break;
                case 2:
                    $sectiontime->timevalue *= 60;
                    break;
                default:
                    break;
            }
            $DB->insert_record('quizaccess_timedsections', $sectiontime);
        }
        return json_encode($sectiontime);
    }

    /**
     * info about the returned object.
     */
    public static function get_section_time_returns() {
        return null;
    }


    /**
     * Params check for repaginating a quiz according to editmethod.
     *
     * @return external_function_parameters function.
     */
    public static function repaginate_slots_parameters() {
        return new external_function_parameters(
            ["quizid" => new external_value(PARAM_INT, "quizid"),
            "editmethod" => new external_value(PARAM_RAW, "editmethod"),
            ]);
    }

    /**
     * Repaginates the quiz in order to follow the editmehtod chosen.
     *
     * @param int $quizid
     * @param string $editmethod
     * @return string json encoded info of the time inserted | updated in the db
     */
    public static function repaginate_slots($quizid, $editmethod) {
        global $DB, $DB;
        $params = self::validate_parameters(
            self::repaginate_slots_parameters(),
                ["quizid" => $quizid, "editmethod" => $editmethod]
        );
        $cm = get_coursemodule_from_instance('quiz', $params['quizid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:manage', $context);
        $timetype = null;
        switch ($editmethod) {
            case 'section':
                $timetype = 0;
                $quizoption = 2;
                break;
            case 'slots':
                $timetype = 1;
                $quizoption = 3;
                break;
            case 'equitative':
                $quizoption = 4;
                break;
            default:
                $quizoption = 1;
                break;
        }
        $DB->set_field('quizaccess_quiztimer', 'quiz_mode', $quizoption, ['quiz' => $quizid]);
        if ($timetype !== null) {
            quiz_repaginate_questions($quizid, $timetype);

            $quiznav = $DB->get_field('quiz', 'navmethod', ['id' => $quizid], IGNORE_MISSING);
            $quiznav != QUIZ_NAVMETHOD_SEQ ? $navmethod = QUIZ_NAVMETHOD_SEQ : $navmethod = false;
            if ($navmethod) {
                $DB->set_field('quiz', 'navmethod', $navmethod, ['id' => $quizid]);
            }
        }
        return json_encode($timetype);
    }

    /**
     * info about the returned object
     */
    public static function repaginate_slots_returns() {
        return null;
    }

    /**
     * Params check for getting a quiz time.
     *
     * @return external_function_parameters function.
     */
    public static function get_quiz_time_parameters() {
        return new external_function_parameters(
            ["quizid" => new external_value(PARAM_INT, "quizid"),
            "editmethod" => new external_value(PARAM_RAW, "editmethod"),
            ]);
    }

    /**
     * Gets the time of the quiz.
     *
     * @param int $quizid
     * @param string $editmethod
     * @return string json encoded info of the time inserted | updated in the db
     */
    public static function get_quiz_time($quizid, $editmethod) {
        global $DB, $DB;
        $params = self::validate_parameters(
            self::get_quiz_time_parameters(),
                ["quizid" => $quizid, "editmethod" => $editmethod]
        );
        $cm = get_coursemodule_from_instance('quiz', $params['quizid'], 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/quiz:manage', $context);
        if ($editmethod == 'timelimit') {
            return 0;
        }
        $param = ['quizid' => $quizid];
        $quiztime = new stdClass();
        $quiztime->time = 0;
        if ($editmethod != 'slots') {
            $sql = "SELECT sectionid, timeunit, timevalue FROM {quizaccess_timedsections} WHERE quizid = :quizid";
            $sections = $DB->get_records_sql($sql, $param);
            $sql = "SELECT id FROM {quiz_sections} WHERE quizid = :quizid";
            $activesections = $DB->get_records_sql($sql, $param);
            foreach ($activesections as $activesection) {
                $activeids[] = (int)$activesection->id;
            }
            foreach ($sections as $id => $timevalue) {
                if (in_array($id, $activeids, true)) {
                    $quiztime->time += $timevalue->timevalue;
                }
            }
            $totalquiztime = array_sum(array_column($sections, 'timevalue'));
        } else {
            $slots = $DB->get_records('quizaccess_timedslots', $param, 'slot, timeunit, timevalue');
            $activeslots = $DB->get_records('quiz_slots', $param, 'id');
            foreach ($activeslots as $activeslot) {
                $activeids[] = (int)$activeslot->id;
            }
            foreach ($slots as $slot) {
                $quiztime->time += $slot->timevalue;
            }
            $totalquiztime = array_sum(array_column($slots, 'timevalue'));
        }
        if ($DB->get_field('quiz', 'timelimit', ['id' => $quizid], IGNORE_MISSING) != $totalquiztime) {
            $DB->set_field('quiz', 'timelimit', $totalquiztime, ['id' => $quizid]);
        }
        return json_encode($quiztime);
    }

    /**
     * info about the returned object
     */
    public static function get_quiz_time_returns() {
        return null;
    }

}
