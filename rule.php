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


use mod_quiz\quiz_settings;
use mod_quiz\quiz_attempt;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for inserting timers for questions and sections.
 */
class quizaccess_quiztimer extends \mod_quiz\local\access_rule_base {


    /**
     * Generate the form fields for adding quiz settings.
     *
     * @param mod_quiz_mod_form $quizform The form object for the quiz settings.
     * @param MoodleQuickForm $mform The Moodle QuickForm object.
     */
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform): void {
        global $DB, $PAGE, $CFG;

        $quizid = $quizform->get_instance();
        $addparam = optional_param('add', '', PARAM_ALPHA);
        if ($addparam !== 'quiz') {
            $arrayofoptions = [
                'limit' => get_string('timelimit', 'quizaccess_quiztimer'),
                'section' => get_string('sectiontime', 'quizaccess_quiztimer'),
                'question' => get_string('questiontime', 'quizaccess_quiztimer'),
                'page' => get_string('pagetime', 'quizaccess_quiztimer'),
            ];

            $element = $mform->createElement('select', 'timequestion',
            get_string('subtimes', 'quizaccess_quiztimer'),
            $arrayofoptions, ['onchange' => 'updateQuizSettingsOnChange();']);
            $mform->insertElementBefore($element, 'overduehandling');
            $mform->addHelpButton('timequestion', 'subtimes', 'quizaccess_quiztimer');
        } else {
            $arrayofoptions = [
                'limit' => get_string('timelimit', 'quizaccess_quiztimer'),
                'section' => get_string('sectiontime', 'quizaccess_quiztimer'),
                'question' => get_string('questiontime', 'quizaccess_quiztimer'),
                'page' => get_string('pagetime', 'quizaccess_quiztimer'),
            ];

            $element = $mform->createElement('select', 'timequestion',
            get_string('subtimes', 'quizaccess_quiztimer'),
            $arrayofoptions, ['onchange' => 'updateQuizSettingsOnChange();']);
            $element->updateAttributes(['disabled' => 'disabled']);
            $mform->insertElementBefore($element, 'overduehandling');
            $mform->addHelpButton('timequestion', 'subtimes', 'quizaccess_quiztimer');
        }
        if ($quizid !== null && $quizid !== "") {
            $quiz = $DB->get_record('quizaccess_quiztimer', ['quiz' => $quizid]);

            $totaltimesection = $DB->get_field('quizaccess_timedsections', 'SUM(timevalue)', ['quizid' => $quizid]);
            if ($totaltimesection >= 60) {
                $totaltimesection = $totaltimesection / 60;
                $timeunit = 3;
            } else {
                $timeunit = 4;
            }
            if (!empty($quiz)) {
                if (quiz_has_attempts($quizid)) {
                    $attempts = $DB->count_records('quiz_attempts', ['quiz' => $quizid, 'preview' => 0]);
                    $url = $CFG->wwwroot . '/mod/quiz/report.php?id=' . $quizform->get_coursemodule()->id . '&mode=overview';
                    $PAGE->requires->js_call_amd('quizaccess_quiztimer/mod_form', 'init',
                        [$url, get_string('canteditquiztype', 'quizaccess_quiztimer'),
                        $attempts, get_string('disabledbycustomtimer', 'quizaccess_quiztimer'), ]);
                } else {
                    $PAGE->requires->js_call_amd('quizaccess_quiztimer/mod_form', 'init', [0, 0, 0,
                        get_string('disabledbycustomtimer', 'quizaccess_quiztimer'), ]);
                }
                if ($quiz->quiz_mode == 1) {
                    $mform->setDefault('timequestion', 'limit');

                } else if ($quiz->quiz_mode == 2) {
                    $mform->setDefault('timequestion', 'section');
                    $mform->addElement('html',
                        '<script type="text/javascript">
                        document.getElementById("id_timelimit_number").disabled = true;
                        document.getElementById("id_timelimit_number").value = '. $totaltimesection . ';
                        document.getElementById("id_timelimit_timeunit").disabled = true;
                        document.getElementById("id_timelimit_timeunit").selectedIndex = '. $timeunit . ';
                        document.getElementById("id_timelimit_enabled").disabled = true;
                        document.getElementById("id_navmethod").value = "sequential";
                        document.getElementById("id_navmethod").disabled = true;
                        document.getElementById("id_questionsperpage").value = 0;
                        document.getElementById("id_questionsperpage").disabled = true;
                        document.getElementById("id_repaginatenow").disabled = true;
                        document.getElementById("id_repaginatenow").checked = 1;
                        </script>'
                    );
                } else if ($quiz->quiz_mode == 3) {
                    $totaltime = $DB->get_field('quizaccess_timedslots', 'SUM(timevalue)', ['quizid' => $quizid]);
                    if ($totaltime >= 60) {
                        $totaltime = $totaltime / 60;
                        $timeunit = 3;
                    } else {
                        $timeunit = 4;
                    }
                    $mform->setDefault('timequestion', 'question');
                    $mform->addElement(
                        'html',
                        '<script type="text/javascript">
                        document.getElementById("id_timelimit_number").disabled = true;
                        document.getElementById("id_timelimit_number").value = '. $totaltime . ';
                        document.getElementById("id_timelimit_timeunit").disabled = true;
                        document.getElementById("id_timelimit_timeunit").selectedIndex = '. $timeunit . ';
                        document.getElementById("id_timelimit_enabled").disabled = true;
                        document.getElementById("id_navmethod").value = "sequential";
                        document.getElementById("id_navmethod").disabled = true;
                        document.getElementById("id_questionsperpage").value = 1;
                        document.getElementById("id_questionsperpage").disabled = true;
                        document.getElementById("id_repaginatenow").disabled = true;
                        document.getElementById("id_repaginatenow").checked = 1;
                        </script>'
                    );
                } else if ($quiz->quiz_mode == 4) {
                    $mform->setDefault('timequestion', 'page');
                    $mform->addElement(
                        'html',
                        '<script type="text/javascript">
                        document.getElementById("id_timelimit_number").disabled = true;
                        document.getElementById("id_timelimit_number").value = '. $totaltimesection . ';
                        document.getElementById("id_timelimit_timeunit").selectedIndex = '. $timeunit . ';
                        document.getElementById("id_timelimit_timeunit").disabled = true;
                        document.getElementById("id_timelimit_enabled").disabled = true;
                        document.getElementById("id_navmethod").value = "sequential";
                        document.getElementById("id_navmethod").disabled = true;
                        document.getElementById("id_questionsperpage").value = 1;
                        document.getElementById("id_questionsperpage").disabled = true;
                        document.getElementById("id_repaginatenow").disabled = true;
                        document.getElementById("id_repaginatenow").checked = 1;
                        </script>'
                    );
                }
            }
        }

        // Define the JavaScript function.
        $mform->addElement(
            'html',
            '<script type="text/javascript">
                // Function to update the quiz navigation method.
                function quizaccess_quiztimer_updatequiznavmethod(quizid, optionnavigation) {
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function() {
                        if (this.readyState == 4 && this.status == 200) {
                            console.log("Quiz navmethod updated");
                        }
                    };
                    xhttp.open("POST", "", true);
                    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhttp.send("quizid=" + quizid + "&optionnavigation=" + optionnavigation);
                }
                // Function to perform some action when the selection changes.
                function updateQuizSettingsOnChange() {
                    var selectedValue = document.getElementById("id_timequestion").value;
                    // Call the quizaccess_quiztimer_updatequiznavmethod function based on the selected value.
                    if (selectedValue === "limit") {
                        quizaccess_quiztimer_updatequiznavmethod(' . $quizid . ', 1);
                        document.getElementById("id_timelimit_number").disabled = false;
                        document.getElementById("id_timelimit_timeunit").disabled = false;
                        document.getElementById("id_timelimit_enabled").disabled = false;
                        document.getElementById("id_navmethod").disabled = false;
                        document.getElementById("id_questionsperpage").disabled = false;
                        document.getElementById("id_repaginatenow").disabled = false;
                        document.getElementById("id_repaginatenow").checked = 0;
                    }
                    if (selectedValue === "section") {
                        quizaccess_quiztimer_updatequiznavmethod(' . $quizid . ', 2);
                        document.getElementById("id_timelimit_number").disabled = true;
                        document.getElementById("id_timelimit_number").value = "";
                        document.getElementById("id_timelimit_timeunit").disabled = true;
                        document.getElementById("id_timelimit_enabled").disabled = true;
                        document.getElementById("id_navmethod").value = "sequential";
                        document.getElementById("id_navmethod").disabled = true;
                        document.getElementById("id_questionsperpage").value = 0;
                        document.getElementById("id_questionsperpage").disabled = true;
                        document.getElementById("id_repaginatenow").disabled = true;
                        document.getElementById("id_repaginatenow").checked = 1;
                    }
                    if (selectedValue === "question") {
                        quizaccess_quiztimer_updatequiznavmethod(' . $quizid . ', 3);
                        document.getElementById("id_timelimit_number").disabled = true;
                        document.getElementById("id_timelimit_number").value = "";
                        document.getElementById("id_timelimit_timeunit").disabled = true;
                        document.getElementById("id_timelimit_enabled").disabled = true;
                        document.getElementById("id_navmethod").value = "sequential";
                        document.getElementById("id_navmethod").disabled = true;
                        document.getElementById("id_questionsperpage").value = 1;
                        document.getElementById("id_questionsperpage").disabled = true;
                        document.getElementById("id_repaginatenow").disabled = true;
                        document.getElementById("id_repaginatenow").checked = 1;
                    }
                    if (selectedValue === "page") {
                        quizaccess_quiztimer_updatequiznavmethod(' . $quizid . ', 4);
                        document.getElementById("id_timelimit_number").disabled = true;
                        document.getElementById("id_timelimit_number").value = "";
                        document.getElementById("id_timelimit_timeunit").disabled = true;
                        document.getElementById("id_timelimit_enabled").disabled = true;
                        document.getElementById("id_navmethod").value = "sequential";
                        document.getElementById("id_navmethod").disabled = true;
                        document.getElementById("id_questionsperpage").value = 1;
                        document.getElementById("id_questionsperpage").disabled = true;
                        document.getElementById("id_repaginatenow").disabled = true;
                        document.getElementById("id_repaginatenow").checked = 1;
                    }
                }
            </script>'
        );
    }

    /**
     * Generate a new instance of the class based on certain conditions.
     *
     * @param quiz $quizobj The quiz object to be used
     * @param mixed $timenow The current time
     * @param bool $canignoretimelimits Flag to indicate if time limits can be ignored
     * @return self|null A new instance of the class or null based on conditions
     */
    public static function make(quiz_settings $quizobj, $timenow, $canignoretimelimits) {
        global $DB;
        if (!empty($quizobj->get_quiz()->timelimit)) {
            $quizmode = $DB->get_field('quizaccess_quiztimer', 'quiz_mode', ['quiz' => $quizobj->get_quiz()->id]);
            if ($quizmode == 1 || !$quizmode) {
                return null;
            }
        }
        return new self($quizobj, $timenow);
    }

    /**
     * Get the description.
     *
     */
    public function description() {
        return get_string('requirequiztimermessage', 'quizaccess_quiztimer');
    }


    /**
     * Check if preflight check is required.
     *
     * @param datatype $attemptid description
     * @return int
     */
    public function is_preflight_check_required($attemptid) {
        return $attemptid === null;
    }

    /**
     * Adds preflight check form fields for the quiz module.
     *
     * @param mod_quiz\form\preflight_check_form $quizform The quiz preflight check form object.
     * @param MoodleQuickForm $mform The Moodle quick form object.
     * @param int $attemptid The ID of the quiz attempt.
     * @throws Exception If there is an error.
     */
    public function add_preflight_check_form_fields(mod_quiz\form\preflight_check_form $quizform,
            MoodleQuickForm $mform, $attemptid) {
        global $DB, $PAGE, $USER;

        $context = context_module::instance($PAGE->cm->id);
        $quiztimeserrors = get_preflight_errors();
        $url = new moodle_url('mod/quiz/accesrule/quiztimer/edit.php', ['cmid' => $PAGE->cm->id, "editmethod" => 'time']);
        if (has_capability('mod/quiz:manage', $context, $USER)) {
            $mform->addElement('header', 'quiztimerheader', get_string('quiztimer', 'quizaccess_quiztimer'));
            $mform->addElement('static', 'quiztimermessage', '',
            get_string('requirequiztimermessage', 'quizaccess_quiztimer'));
            if ($quiztimeserrors) {
                $url = new moodle_url('/mod/quiz/accessrule/quiztimer/edit.php', ['cmid' => $PAGE->cm->id, 'editmethod' => 'time']);
                $mform->addElement('static', 'quiztimer_errors', get_string('quiztimererrors', 'quizaccess_quiztimer'));
                foreach ($quiztimeserrors as $errorkey => $errortext) {
                    $mform->addElement('static', $errorkey, '<a class="quiztimererror text-primary" href="' . $url . '"
                        target="_blank">' . $errortext . '</a>');
                }
            }
        }

    }

    /**
     * Validate preflight check function.
     *
     * @param datatype $data description
     * @param datatype $files description
     * @param datatype $errors description
     * @param datatype $attemptid description
     * @return Some_Return_Value
     */
    public function validate_preflight_check($data, $files, $errors, $attemptid) {

        $quiztimeserrors = get_preflight_errors();
        if ($quiztimeserrors) {
            $errors['quiztimermessage'] = '';
            foreach ($quiztimeserrors as $errorkey => $errortext) {
                $errors['quiztimermessage'] .= $errortext . ' ' . get_string('warningtime', 'quizaccess_quiztimer') . '.<br>';
            }
        }
        return $errors;
    }

    /**
     * Function to handle the event when the preflight check is passed.
     *
     * @param int $attemptid The ID of the quiz attempt
     * @return void
     */
    public function notify_preflight_check_passed($attemptid) {
    }

    /**
     * Function to handle the event when the current attempt is finished.
     *
     * @return void
     */
    public function current_attempt_finished() {
    }
}

$quizidparam = optional_param('quizid', null, PARAM_INT);
$optionnavigationparam = optional_param('optionnavigation', null, PARAM_INT);

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the quizid and optionnavigation parameters are set in the POST request.
    if ($quizidparam !== null && $optionnavigationparam !== null) {

        $quizid = $quizidparam;
        $optionnavigation = $optionnavigationparam;

        // Call the quizaccess_quiztimer_updatequiznavmethod function with the retrieved parameters.
        quizaccess_quiztimer_updatequiznavmethod($quizid, $optionnavigation);
    }
}



/**
 * Function to update the navmethod field in the quiz.
 *
 * @param  mixed $quizid
 * @param  mixed $optionnavigation
 * @return void
 */
function quizaccess_quiztimer_updatequiznavmethod($quizid, $optionnavigation) {
    global $DB;
    $quiz = $DB->get_record('quiz', ['id' => $quizid]);

    if ($quiz) {
        $data = new stdClass;
        $data->id = $quizid;

        if ($optionnavigation > 1) {
            $data->navmethod = 'sequential';
            $DB->update_record('quiz', $data);
        } else {
            $data->navmethod = 'free';
            $DB->update_record('quiz', $data);
        }

        // Repaginate.
        if ($optionnavigation == 2) {
            $data = new stdClass;
            $data->id = $quizid;
            $data->questionsperpage = 0;

            if ($DB !== null) {
                $DB->update_record('quiz', $data);
                quiz_repaginate_questions($quizid, 0);
            }
        } else if ($optionnavigation == 3) {
            $data = new stdClass;
            $data->id = $quizid;
            $data->questionsperpage = 1;

            if ($DB !== null) {
                $DB->update_record('quiz', $data);
                quiz_repaginate_questions($quizid, 1);
            }
        }
        quizoptions($quizid, $optionnavigation);
    }
}


// -------------------------------------------------------------------------------------------

/**
 * Function that saves or updates in the database which option has been chosen in the quiz.
 *
 * @param  mixed $quizid
 * @param  mixed $optionnavigation
 * @return void
 */
function quizoptions($quizid, $optionnavigation) {
    global $DB;
    $quizoptions = $DB->get_record('quizaccess_quiztimer', ['quiz' => $quizid]);

    if ($quizoptions) {
        $data = new stdClass;
        $data->id = $quizoptions->id;
        $data->quiz = $quizid;
        $data->quiz_mode = $optionnavigation;
        $DB->update_record('quizaccess_quiztimer', $data);
    } else {
        $data = new stdClass;
        $data->id = $quizoptions->id;
        $data->quiz = $quizid;
        $data->quiz_mode = $optionnavigation;
        $DB->insert_record('quizaccess_quiztimer', $data);
    }
}


/**
 * Function that is in charge of selecting if the time of the quiz will be by questions, section, etc.
 *
 * @param  mixed $option
 * @return void
 */
function show_timer_based_on_option($option) {
    $attemptparam = optional_param('attempt', '', PARAM_INT);
    // OPTION 2 = SECTIONS.
    if ($option === 2) {
        // Timer inside the quiz
        // Check if the 'attempt' parameter is present in the URL.
        if ($attemptparam !== '') {
            global $DB, $quiz, $PAGE, $USER;

            $attemptid = required_param('attempt', PARAM_INT);
            $attempt = quiz_attempt::create($attemptid);
            $quizid = $attempt->get_quiz();
            $id = $quizid->id;

            $quizid = $id;

            $sql = "SELECT * FROM {quizaccess_timedsections} WHERE quizid = :quizid ORDER BY sectionid ASC";
            $params = ['quizid' => $quizid];
            $quiz = $DB->get_records_sql($sql, $params);
            $totalquiztime = array_sum(array_column($quiz, 'timevalue'));
            $originaltime = $totalquiztime;

            $quizoverride = false;

            if ($DB->get_record('quiz_overrides', ['quiz' => $quizid, 'userid' => $USER->id], 'timelimit', IGNORE_MISSING)) {
                $quizoverride = $DB->get_record('quiz_overrides',
                ['quiz' => $quizid, 'userid' => $USER->id], 'timelimit', IGNORE_MISSING);
            } else {
                $cm = $attempt->get_cm();
                $currentgroupid = groups_get_activity_group($cm);
                $quizoverride = $DB->get_record('quiz_overrides',
                ['quiz' => $quizid, 'groupid' => $currentgroupid], 'timelimit', IGNORE_MISSING);
            }
            if ($quizoverride) {
                $timefraction = $quizoverride->timelimit / $totalquiztime;
                foreach ($quiz as $quizdata) {
                    $quizdata->timevalue = round($quizdata->timevalue * $timefraction, 0);
                }
            }

            $tiempos = array_column($quiz, 'timevalue');
            $tiempos = array_map('intval', $tiempos);



            // Check if the 'page' parameter is present in the URL.
            $currentpage = optional_param('page', $attempt->get_currentpage(), PARAM_INT);
            $sectionkey = $currentpage;
            $sectiontime = $tiempos[$currentpage];



            $data = new stdClass;
            $data->id = $id;
            $data->timelimit = 0;
            $data->questionsperpage = 0;
            if ($DB !== null) {
                $DB->update_record('quiz', $data);
                // Repaginate the questions.
                quiz_repaginate_questions($quizid, 0);
            }

            if (!(basename($_SERVER['PHP_SELF']) == 'summary.php' || basename($_SERVER['PHP_SELF']) == 'review.php')) {
                // Get the current attempt instance.

                // Check if the attempt is valid.
                if ($attempt) {

                $record = $DB->get_record('quizaccess_usertimedsections', [
                    'quizid'   => $quizid,
                    'section'  => $sectionkey,
                    'userid'   => $USER->id,
                    'attempt'  => $attemptid,
                ]);

                if (!$record) {
                    $record = new stdClass();
                    $record->quizid = $quizid;
                    $record->section = $sectionkey;
                    $record->userid = $USER->id;
                    $record->attempt = $attemptid;
                    $record->timestart = time();
                    $record->timefinish = time() + $sectiontime;
                    $DB->insert_record('quizaccess_usertimedsections', $record);
                }

                    $data = [
                        'attemptid' => $attemptid,
                        'tiempos' => $tiempos,
                        'section' => $sectionkey,
                        'secondsleft'  => $record->timefinish - time()
                    ];
                    $PAGE->requires->js_call_amd('quizaccess_quiztimer/section', 'init', [$data]);

                }
            } else {
                $data = new stdClass;
                $data->id = $id;
                $data->timelimit = $originaltime;
                $DB->update_record('quiz', $data);
            }
        }
    }

    // OPTION 3 = QUESTIONS.
    if ($option === 3) {
        // Timer inside the quiz
        // Check if the 'attempt' parameter is present in the URL.
        if ($attemptparam !== '') {
            global $DB, $quiz, $PAGE, $USER;

            $attemptid = required_param('attempt', PARAM_INT);
            $attempt = quiz_attempt::create($attemptid);
            $quizid = $attempt->get_quiz();
            $id = $quizid->id;
            $quizid = $id;
            $sql = "SELECT * FROM {quizaccess_timedslots} WHERE quizid = :quizid ORDER BY slot ASC";
            $params = ['quizid' => $quizid];
            $quiz = $DB->get_records_sql($sql, $params);
            $quizoverride = false;
            $totalquiztime = array_sum(array_column($quiz, 'timevalue'));
            $originaltime = $totalquiztime;

            if ($DB->get_record('quiz_overrides', ['quiz' => $quizid, 'userid' => $USER->id], 'timelimit', IGNORE_MISSING)) {
                $quizoverride = $DB->get_record('quiz_overrides',
                ['quiz' => $quizid, 'userid' => $USER->id], 'timelimit', IGNORE_MISSING);
            } else {
                $cm = $attempt->get_cm();
                $currentgroupid = groups_get_activity_group($cm);
                $quizoverride = $DB->get_record('quiz_overrides',
                ['quiz' => $quizid, 'groupid' => $currentgroupid], 'timelimit', IGNORE_MISSING);
            }
            if ($quizoverride) {
                $timefraction = $quizoverride->timelimit / $totalquiztime;
                foreach ($quiz as $quizdata) {
                    $quizdata->timevalue = round($quizdata->timevalue * $timefraction, 0);
                }
            }
            $tiempos = array_column($quiz, 'timevalue');
            $tiempos = array_map('intval', $tiempos);

            // Check if the 'page' parameter is present in the URL.
            $currentpage = optional_param('page', $attempt->get_currentpage(), PARAM_INT);
            // Get the questions of the current page.
            $questionid = $attempt->get_slots($currentpage);
            $questionid = reset($questionid);


            $allSlots = $attempt->get_slots();
            $slotIndex = array_search($questionid, $allSlots);
            $slotTime = $tiempos[$slotIndex];


            $data = new stdClass;
            $data->id = $id;
            $data->timelimit = 0;
            $data->questionsperpage = 1;
            if ($DB !== null) {
                $DB->update_record('quiz', $data);
                // Repaginate the questions.
                quiz_repaginate_questions($quizid, 1);
            }

            if (!(basename($_SERVER['PHP_SELF']) == 'summary.php' || basename($_SERVER['PHP_SELF']) == 'review.php')) {
                // Get the current attempt instance.

                // Check if the attempt is valid.
                if ($attempt) {
                    $record = $DB->get_record('quizaccess_usertimedslots', [
                        'quizid'  => $quizid,
                        'slot'    => $questionid,
                        'userid'  => $USER->id,
                        'attempt' => $attemptid,
                    ]);

                    if (!$record) {
                        $record = new stdClass();
                        $record->quizid = $quizid;
                        $record->slot = $questionid;
                        $record->userid = $USER->id;
                        $record->attempt = $attemptid;
                        $record->timestart = time();
                        $record->timefinish = time() + $slotTime;
                        $DB->insert_record('quizaccess_usertimedslots', $record);
                    }

                    $data = [
                        'attemptid' => $attemptid,
                        'slot' => $questionid,
                        'secondsleft'  => $record->timefinish - time()
                    ];

                    $PAGE->requires->js_call_amd('quizaccess_quiztimer/question', 'init', [$data]);
                }
            } else {
                $data = new stdClass;
                $data->id = $id;
                $data->timelimit = $originaltime;
                $DB->update_record('quiz', $data);
            }
        }
    }

    // OPTION 4 = PAGE.
    if ($option === 4) {
        // Timer inside the quiz
        // Check if the 'attempt' parameter is present in the URL.
        if ($attemptparam !== '') {
            global $DB, $quiz, $PAGE, $USER;

            $attemptid = required_param('attempt', PARAM_INT);
            $attempt = quiz_attempt::create($attemptid);
            $quizid = $attempt->get_quiz();
            $id = $quizid->id;

            $quizid = $id;
            $quiz = $DB->get_records('quizaccess_timedsections', ['quizid' => $quizid]);
            $quizslots = $DB->get_records('quiz_slots', ['quizid' => $quizid]);
            $quizsections = $DB->get_records('quiz_sections', ['quizid' => $quizid]);

            $tiempos = [];

            // Create an array to store the pages and time values of each section.
            $sectioninfo = [];

            // Iterate over the sections.
            foreach ($quizsections as $section) {
                $sectioninfo[$section->id] = [
                    'pages' => [],
                    'timevalue' => 0,
                ];

                // Find the next section's firstslot.
                $nextsectionfirstslot = PHP_INT_MAX;
                foreach ($quizsections as $nextsection) {
                    if ($nextsection->firstslot > $section->firstslot && $nextsection->firstslot < $nextsectionfirstslot) {
                        $nextsectionfirstslot = $nextsection->firstslot;
                    }
                }

                // Iterate over the slots and assign them to the corresponding pages.
                foreach ($quizslots as $slot) {
                    if ($slot->slot >= $section->firstslot && $slot->slot < $nextsectionfirstslot) {
                        $sectioninfo[$section->id]['pages'][$slot->page][] = $slot->slot;
                    }
                }

                // Calculate the total time value for the section, considering the number of pages.
                foreach ($quiz as $timedsection) {
                    if ($timedsection->sectionid == $section->id) {
                        $sectioninfo[$section->id]['timevalue'] +=
                        ($timedsection->timevalue / count($sectioninfo[$section->id]['pages']));
                    }
                }
            }

            // Output the result.
            foreach ($sectioninfo as $sectionid => $info) {
                $numpages = count($info['pages']);
                $timevalue = $info['timevalue'];
                for ($i = 0; $i < $numpages; $i++) {
                    $tiempos[] = $timevalue;
                }
            }

            $quizoverride = false;
            $totalquiztime = array_sum($tiempos);
            $originaltime = $totalquiztime;

            if ($DB->get_record('quiz_overrides', ['quiz' => $quizid, 'userid' => $USER->id], 'timelimit', IGNORE_MISSING)) {
                $quizoverride = $DB->get_record('quiz_overrides',
                ['quiz' => $quizid, 'userid' => $USER->id], 'timelimit', IGNORE_MISSING);
            } else {
                $cm = $attempt->get_cm();
                $currentgroupid = groups_get_activity_group($cm);
                $quizoverride = $DB->get_record('quiz_overrides',
                ['quiz' => $quizid, 'groupid' => $currentgroupid], 'timelimit', IGNORE_MISSING);
            }
            if ($quizoverride) {
                $timefraction = $quizoverride->timelimit / $totalquiztime;
                foreach ($tiempos as $key => $value) {
                    $tiempos[$key] = round($value * $timefraction, 0);
                }
            }

            // Check if the 'page' parameter is present in the URL.
            $currentpage = optional_param('page', $attempt->get_currentpage(), PARAM_INT);
            $sectionkey = $currentpage;
            $sectiontime = $tiempos[$currentpage];

            $data = new stdClass;
            $data->id = $id;
            $data->timelimit = 0;
            if ($DB !== null) {
                $DB->update_record('quiz', $data);
            }

            if (!(basename($_SERVER['PHP_SELF']) == 'summary.php' || basename($_SERVER['PHP_SELF']) == 'review.php')) {
                // Get the current attempt instance.

                // Check if the attempt is valid.
                if ($attempt) {

                    $record = $DB->get_record('quizaccess_usertimedsections', [
                        'quizid'   => $quizid,
                        'section'  => $sectionkey,
                        'userid'   => $USER->id,
                        'attempt'  => $attemptid,
                    ]);

                    if (!$record) {
                        $record = new stdClass();
                        $record->quizid = $quizid;
                        $record->section = $sectionkey;
                        $record->userid = $USER->id;
                        $record->attempt = $attemptid;
                        $record->timestart = time();
                        $record->timefinish = time() + $sectiontime;
                        $DB->insert_record('quizaccess_usertimedsections', $record);
                    }


                    $data = [
                        'attemptid' => $attemptid,
                        'tiempos' => $tiempos,
                        'section' => $sectionkey,
                        'secondsleft'  => $record->timefinish - time()
                    ];
                    $PAGE->requires->js_call_amd('quizaccess_quiztimer/page', 'init', [$data]);

                }
            } else {
                $data = new stdClass;
                $data->id = $id;
                $data->timelimit = $originaltime;
                $DB->update_record('quiz', $data);
            }
        }
    }

    // Send quiz results after finish.
    if (basename($_SERVER['PHP_SELF']) == 'summary.php') {
        if($option === 2 || $option === 3 || $option === 4) {
            echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
            var button = document.getElementsByClassName("btn btn-primary")[0];
            button.click();
            localStorage.clear();
            });
            </script>';
        }
    }
}


/**
 * Function that returns an int with the selected option.
 *
 * @return integer option choosed
 */
function get_quizoptions() {
    global $DB, $quiz;
    $attemptparam = optional_param('attempt', '', PARAM_INT);
    if ($attemptparam !== '') {

        $attemptid = required_param('attempt', PARAM_INT);
        $attempt = quiz_attempt::create($attemptid);
        $quizid = $attempt->get_quiz();
        $id = $quizid->id;
        $quizid = $id;
        $quiz = $DB->get_record('quizaccess_quiztimer', ['quiz' => $quizid]);
        if ($quiz && isset($quiz->quiz_mode)) {
            return intval($quiz->quiz_mode);
        } else {
            return 0;
        }
    }
}

/**
 * Retrieves preflight errors for a quiz based on the quiz mode.
 *
 * @return array|null An array of quiz times errors or null if no quiz timer is found.
 */
function get_preflight_errors() {
    global $DB, $PAGE, $USER;

    $cm = $PAGE->cm->id;
    $context = context_module::instance($PAGE->cm->id);
    $quiz = $DB->get_record('quiz', ['id' => $DB->get_field('course_modules', 'instance',
        ['id' => $cm], IGNORE_MISSING)]);
    $quiztimer = $DB->get_record('quizaccess_quiztimer', ['quiz' => $quiz->id]);
    if (!$quiztimer) {
        return null;
    }
    $quizmode = $quiztimer->quiz_mode;
    $quiztimeserrors = [];

    if ($quizmode) {
        $sections = $DB->get_records('quizaccess_timedsections', ['quizid' => $quiz->id], 'id ASC');
        $slots = $DB->get_records('quizaccess_timedslots', ['quizid' => $quiz->id], 'id ASC');
        switch($quizmode) {
            case 2:
                foreach ($sections as $section) {
                    if ($section->timevalue <= 0) {
                        $sectime = $section->timevalue;
                        $sectime >= 3600 ? $sectime = gmdate('H:i:s', $sectime) : $sectime = gmdate('i:s', $sectime);
                        $secdata = $DB->get_record('quiz_sections', ['id' => $section->sectionid]);
                        empty($secdata->heading) ? $secdata->heading = get_string('section') . $secdata->id : '';
                        $quiztimeserrors['sectime' . $secdata->id] = get_string('section') . ': ' . $secdata->heading;
                    }
                }
                if (has_capability('mod/quiz:manage', $context, $USER)) {
                    !$quiztimeserrors ? quiz_repaginate_questions($quiz->id, 0) : '';
                }
                break;
            case 3:
                foreach ($slots as $slot) {
                    if ($slot->timevalue <= 0) {
                        $slotdata = $DB->get_record('question', ['id' => $DB->get_field('quiz_slots', 'slot',
                        ['id' => $slot->slot])]);
                        $quiztimeserrors['slot' . $slotdata->id] = get_string('question') . ': ' . $slotdata->name;
                    }
                }
                if (has_capability('mod/quiz:manage', $context, $USER)) {
                    !$quiztimeserrors ? quiz_repaginate_questions($quiz->id, 1) : '';
                }
                break;
            case 4:
                foreach ($sections as $section) {
                    if ($section->timevalue <= 0) {
                        $sectime = $section->timevalue;
                        $sectime >= 3600 ? $sectime = gmdate('H:i:s', $sectime) : $sectime = gmdate('i:s', $sectime);
                        $secdata = $DB->get_record('quiz_sections', ['id' => $section->sectionid]);
                        empty($secdata->heading) ? $secdata->heading = get_string('section') . $secdata->id : '';
                        $quiztimeserrors['sectime' . $secdata->id] = get_string('section') . ': ' . $secdata->heading;
                    }
                }
                break;
            default:
                break;
        }
        return $quiztimeserrors;
    }
}



echo show_timer_based_on_option(get_quizoptions());
