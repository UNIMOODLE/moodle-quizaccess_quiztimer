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
 * @package    local_quiztimer
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\navigation\views\view;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/mod/quiz/accessrule/accessrulebase.php');
require_once($CFG->dirroot . '/mod/quiz/accessmanager.php');

/**
 * Class for inserting timers for questions and sections.
 */
class quizaccess_quiztimer extends quiz_access_rule_base {

    public function end_time($attempt) {
        $timedue = /*$attempt->timestart + */ $this->quiz->timelimit;
        if ($this->quiz->timeclose) {
            $timedue = min($timedue, $this->quiz->timeclose);
        }
        return $timedue;
    }

    /**
     * Function that adds the new field in the question bank etc.
     *
     * @param  mixed $quizform
     * @param  mixed $mform
     * @return void
     */
    public static function add_settings_form_fields(mod_quiz_mod_form $quizform, MoodleQuickForm $mform): void {
        global $DB;

        $context = $quizform->get_context();
        $canedit = 1;

        $currentvalue = null;
        $quizid = $quizform->get_instance();

        $arrayofoptions = [
            'opcion1' => 'Time limit',
            'opcion2' => 'Time for section',
            'opcion3' => 'Time for question',
        ];

        $element = $mform->addElement('select', 'timequestion',
        get_string('subtimes', 'quizaccess_quiztimer'),
        $arrayofoptions, ['onchange' => 'myFunctionToDoSomething();']);
        $mform->insertElementBefore($element, 'overduehandling');
        $mform->addHelpButton('timequestion', 'subtimes', 'quizaccess_quiztimer');

        if ($quizid !== null && $quizid !== "") {
            $quiz = $DB->get_record('quizaccess_quiztimer', ['quiz' => $quizid]);
            if (!empty($quiz)) {
                if ($quiz->quiz_mode == 1) {
                    $mform->setDefault('timequestion', 'opcion1');

                } else if ($quiz->quiz_mode == 2) {
                    $mform->setDefault('timequestion', 'opcion2');
                    $mform->addElement('html',
                        '<script type="text/javascript">
                        document.getElementById("id_timelimit_number").disabled = true;
                        document.getElementById("id_timelimit_timeunit").disabled = true;
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
                    $mform->setDefault('timequestion', 'opcion3');
                    $mform->addElement(
                        'html',
                        '<script type="text/javascript">
                        document.getElementById("id_timelimit_number").disabled = true;
                        document.getElementById("id_timelimit_timeunit").disabled = true;
                        document.getElementById("id_timelimit_enabled").disabled = true;
                        document.getElementById("id_navmethod").value = "free";
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
                function updatequiznavmethod(quizid, optionnavigation) {
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
                function myFunctionToDoSomething() {
                    var selectedValue = document.getElementById("id_timequestion").value;
                    // Call the updatequiznavmethod function based on the selected value.
                    if (selectedValue === "opcion1") {
                        updatequiznavmethod(' . $quizid . ', 1);
                        document.getElementById("id_timelimit_number").disabled = false;
                        document.getElementById("id_timelimit_timeunit").disabled = false;
                        document.getElementById("id_timelimit_enabled").disabled = false;
                        document.getElementById("id_navmethod").disabled = false;
                        document.getElementById("id_questionsperpage").disabled = false;
                        document.getElementById("id_repaginatenow").disabled = false;
                        document.getElementById("id_repaginatenow").checked = 0;
                    }
                    if (selectedValue === "opcion2") {
                        updatequiznavmethod(' . $quizid . ', 2);
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
                    if (selectedValue === "opcion3") {
                        updatequiznavmethod(' . $quizid . ', 3);
                        document.getElementById("id_timelimit_number").disabled = true;
                        document.getElementById("id_timelimit_number").value = "";
                        document.getElementById("id_timelimit_timeunit").disabled = true;
                        document.getElementById("id_timelimit_enabled").disabled = true;
                        document.getElementById("id_navmethod").value = "free";
                        document.getElementById("id_navmethod").disabled = true;
                        document.getElementById("id_questionsperpage").value = 1;
                        document.getElementById("id_questionsperpage").disabled = true;
                        document.getElementById("id_repaginatenow").disabled = true;
                        document.getElementById("id_repaginatenow").checked = 1;
                    }
                }
            </script>'
        );

        if (!$currentvalue) {
            $mform->setAdvanced('timequestion');
        }

    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the quizid and optionnavigation parameters are set in the POST request.
    if (isset($_POST['quizid']) && isset($_POST['optionnavigation'])) {

        $quizid = $_POST['quizid'];
        $optionnavigation = $_POST['optionnavigation'];
        // Call the updatequiznavmethod function with the retrieved parameters.
        updatequiznavmethod($quizid, $optionnavigation);
    }
}



/**
 * Function to update the navmethod field in the quiz.
 *
 * @param  mixed $quizid
 * @param  mixed $optionnavigation
 * @return void
 */
function updatequiznavmethod($quizid, $optionnavigation) {
    global $DB;
    $quiz = $DB->get_record('quiz', ['id' => $quizid]);

    if ($quiz) {
        $data = new stdClass;
        $data->id = $quizid;

        if ($optionnavigation == 3) {
            $data->navmethod = 'sequential';
            $DB->update_record('quiz', $data);
        } else {
            $data->navmethod = 'free';
            $DB->update_record('quiz', $data);
        }

        // Repaginado.
        if ($optionnavigation == 1) {
        } else if ($optionnavigation == 2) {
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
function showtime($option) {

    if ($option === 1) {
    }

    // OPTION 2 = SECTIONS.
    if ($option === 2) {
        // Timer inside the quiz
        // Check if the 'attempt' parameter is present in the URL.
        if (isset($_GET['attempt'])) {
            global $DB, $quiz;

            $attemptid = required_param('attempt', PARAM_INT);
            $attempt = quiz_attempt::create($attemptid);
            $quizid = $attempt->get_quiz();
            $id = $quizid->id;

            $quizid = $id;
            $quiz = $DB->get_records('quizaccess_timedsections', ['quizid' => $quizid]);
            $counttime = count($quiz);

            $tiempos = array_column($quiz, 'timevalue');
            $tiempos = array_map('intval', $tiempos);
            $sumatorio = array_sum($tiempos);

            $existingdata = $DB->get_records('quiz');
            $data = new stdClass;
            $data->id = $id;
            $data->timelimit = $sumatorio;

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
                    // Get the quiz ID associated with the attempt.
                    $quizid = $attempt->get_quiz();
                    // Create an instance of the quiz class.
                    $quiz = new quiz($quizid, null, null);
                    // Create an instance of the quiz timer access rule.
                    $quiztimer = new quizaccess_quiztimer($attempt, $quiz);
                    // Get the end time of the attempt in seconds.
                    $endtime = $quiztimer->end_time($attempt);
                    $endtime = $endtime + time();
                    // Print the countdown timers.
                    for ($i = 0; $i < $counttime; $i++) {

                        echo '<br><span class="countdown-section" id="countdown' . $i . '"></span>';
                    }

                    echo '<script type="text/javascript">

            document.addEventListener("DOMContentLoaded", function() {
            const backgroundColors = ["#ca3120", "#d73422", "#dd3d2b", "#e04938",
             "#e25546", "#e46153", "#e66d60", "#e8796d", "#ea867a", "#ec9288",
             "#ee9e95", "#f0aaa2", "#f2b6af", "#f4c2bc", "#f7ceca", "#f9dad7", "#fbe6e4"];

            const textColors = ["#fff", "#fff", "#fff", "#fff", "#fff", "#fff",
             "#fff", "#1d2125", "#1d2125", "#1d2125", "#1d2125",
             "#1d2125", "#1d2125", "#1d2125", "#1d2125", "#1d2125", "#1d2125"];

            //Disable original timer
            var divElement = document.getElementById("quiz-timer");
            divElement.disabled = true;
            divElement.innerHTML = "Temporizador desactivado";

            var currentPageURL = window.location.href;
            var urlParams = new URLSearchParams(currentPageURL);
            var pageID = urlParams.get("page");

            if(pageID === null){

            if(localStorage.getItem("attempt") != ' . $attemptid . '){
                localStorage.clear();
            }

                pageID = 0;
            }

            var headingElement = document.querySelectorAll(".mod_quiz-section-heading");

            /**
             * Updates the countdown timer based on the end time.
             *
             * @param {number} endTime - The end time of the countdown timer in seconds.
             */
            function updateCountdownTimer(endTime) {

                var countdownElement = document.getElementById("countdown" + (pageID));
                var countdownInterval = setInterval(function() {
                    var currentTime = Math.floor(Date.now() / 1000);
                    var timeRemaining =  (endTime - 1) - currentTime;

                    function getColorBackgroundByPercentage(percentage) {

                        var index = percentage / 10;

                        // Devolver el color correspondiente
                        return backgroundColors[index];
                    }
                    function getColorTextByPercentage(percentage) {
                        var index = percentage / 10;

                        // Devolver el color correspondiente
                        return textColors[index];
                    }


                    // Calcular el porcentaje actual en relación con la duración total
                    var totalDuration = endTime - (endTime - 1);
                    var currentPercentage = (timeRemaining / totalDuration) * 10;
                    countdownElement.style.backgroundColor = getColorBackgroundByPercentage(currentPercentage);
                    countdownElement.style.color = getColorTextByPercentage(currentPercentage);




                    if (timeRemaining <= -1 ) {
                        clearInterval(countdownInterval);
                        var button = document.getElementById("mod_quiz-next-nav");
                        localStorage.setItem("countdown" + pageID, 0);
                        countdownElement.innerHTML = "00:00:00";
                        button.click();
                        return;
                    } else {
                        var button = document.getElementById("mod_quiz-next-nav");
                        if (button) {
                            button.addEventListener("click", function() {
                                localStorage.setItem("countdown" + pageID, timeRemaining);
                            });
                        }
                    }

                    // Function to save the countdown time before leaving the page
                    function saveCountdownTime() {
                        var countdownElement = document.getElementById("countdown" + pageID);
                        localStorage.setItem("countdown" + pageID, timeRemaining);
                        localStorage.setItem("attempt", ' . json_encode($attemptid) . ');
                    }

                    // Attach the saveCountdownTime function to the beforeunload event
                    window.addEventListener("beforeunload", saveCountdownTime);

                    var hours = Math.floor(timeRemaining / 3600);
                    var minutes = Math.floor((timeRemaining % 3600) / 60);
                    var seconds = timeRemaining % 60;

                    var formattedTime = hours.toString().padStart(2, "0") + ":" +
                                        minutes.toString().padStart(2, "0") + ":" +
                                        seconds.toString().padStart(2, "0");
                    countdownElement.innerHTML = formattedTime;
                    }, 1);

            }




            // Get the end time from your server-side variable.
            var endTime = ' . json_encode($tiempos) . '[pageID] + ' . time() . ';


            var storedTime = localStorage.getItem("countdown" + pageID);
            if (storedTime !== null) {
                // Use the stored time as the end time
                endTime = parseInt(storedTime) + ' . time() . ';
                // Remove the stored time from localStorage
                localStorage.removeItem("countdown" + pageID);
            }



            // Start the countdown timer.
            updateCountdownTimer(endTime + 1,5);

            // Insert countdown elements after the respective heading elements.
            headingElement.forEach(function(headingElement, index) {

            var textoElement = document.getElementById("countdown" + (index));
            if (headingElement && textoElement) {
                //headingElement.parentNode.insertBefore(textoElement, headingElement.nextSibling);
                headingElement.appendChild(textoElement);
            }
        });
    });

    </script>';
                }
            }
        }
    }

    // OPTION 3 = QUESTIONS.
    if ($option === 3) {
        // Timer inside the quiz
        // Check if the 'attempt' parameter is present in the URL.
        if (isset($_GET['attempt'])) {
            global $DB, $quiz;

            $attemptid = required_param('attempt', PARAM_INT);
            $attempt = quiz_attempt::create($attemptid);
            $quizid = $attempt->get_quiz();
            $id = $quizid->id;

            $quizid = $id;
            $quiz = $DB->get_records('quizaccess_timedslots', ['quizid' => $quizid]);
            $counttime = count($quiz);

            $tiempos = array_column($quiz, 'timevalue');
            $tiempos = array_map('intval', $tiempos);
            $sumatorio = array_sum($tiempos);

            $existingdata = $DB->get_records('quiz');
            $data = new stdClass;
            $data->id = $id;
            $data->timelimit = $sumatorio;

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
                    // Get the quiz ID associated with the attempt.
                    $quizid = $attempt->get_quiz();
                    // Create an instance of the quiz class.
                    $quiz = new quiz($quizid, null, null);
                    // Create an instance of the quiz timer access rule.
                    $quiztimer = new quizaccess_quiztimer($attempt, $quiz);
                    // Get the end time of the attempt in seconds.
                    $endtime = $quiztimer->end_time($attempt);
                    $endtime = $endtime + time();

                    // Print the countdown timers.
                    for ($i = 0; $i < $counttime; $i++) {

                        echo '<span class="countdown-question" id="countdown0"></span>';
                    }

                    echo '<script type="text/javascript">

            document.addEventListener("DOMContentLoaded", function() {
            const backgroundColors = ["#ca3120", "#d73422", "#dd3d2b",
            "#e04938", "#e25546", "#e46153", "#e66d60", "#e8796d",
            "#ea867a", "#ec9288", "#ee9e95", "#f0aaa2", "#f2b6af",
            "#f4c2bc", "#f7ceca", "#f9dad7", "#fbe6e4"];

            const textColors = ["#fff", "#fff", "#fff", "#fff", "#fff",
            "#fff", "#fff", "#1d2125", "#1d2125", "#1d2125", "#1d2125",
            "#1d2125", "#1d2125", "#1d2125", "#1d2125", "#1d2125", "#1d2125"];


            var currentPageURL = window.location.href;
            var urlParams = new URLSearchParams(currentPageURL);
            var pageID = urlParams.get("page");

            //Disable original timer
            var divElement = document.getElementById("quiz-timer");
            divElement.disabled = true;
            divElement.innerHTML = "Temporizador desactivado";

            if(pageID === null){

                if(localStorage.getItem("attempt") != ' . $attemptid . '){
                    localStorage.clear();
                }

                pageID = 0;
            }

            var headingElement = document.querySelectorAll(".no");

            /**
             * Updates the countdown timer based on the end time.
             *
             * @param {number} endTime - The end time of the countdown timer in seconds.
             */
            function updateCountdownTimer(endTime) {

                var countdownElement = document.getElementById("countdown0");
                var countdownInterval = setInterval(function() {
                    var currentTime = Math.floor(Date.now() / 1000);
                    var timeRemaining =  (endTime - 1) - currentTime;

                    function getColorBackgroundByPercentage(percentage) {

                        var index = percentage / 10;

                        //Return the corresponding color
                        return backgroundColors[index];
                    }
                    function getColorTextByPercentage(percentage) {
                        var index = percentage / 10;

                        //Return the corresponding color
                        return textColors[index];
                    }

                    // Calculate the current percentage in relation to the total duration
                    var totalDuration = endTime - (endTime - 1);
                    var currentPercentage = (timeRemaining / totalDuration) * 10;
                    countdownElement.style.backgroundColor = getColorBackgroundByPercentage(currentPercentage);
                    countdownElement.style.color = getColorTextByPercentage(currentPercentage);



                    if (timeRemaining <= -1 ) {
                        clearInterval(countdownInterval);
                        var button = document.getElementById("mod_quiz-next-nav");
                        localStorage.setItem("countdown" + pageID, 0);
                        countdownElement.innerHTML = "00:00:00";
                        button.click();
                        return;
                    } else {
                        var button = document.getElementById("mod_quiz-next-nav");
                        if (button) {
                            button.addEventListener("click", function() {
                                localStorage.setItem("countdown" + pageID, timeRemaining);
                            });
                        }
                    }



                    // Function to save the countdown time before leaving the page
                    function saveCountdownTime() {
                        var countdownElement = document.getElementById("countdown" + pageID);
                        localStorage.setItem("countdown" + pageID, timeRemaining);
                        localStorage.setItem("attempt", ' . json_encode($attemptid) . ');
                    }

                    // Attach the saveCountdownTime function to the beforeunload event
                    window.addEventListener("beforeunload", saveCountdownTime);



                    var hours = Math.floor(timeRemaining / 3600);
                    var minutes = Math.floor((timeRemaining % 3600) / 60);
                    var seconds = timeRemaining % 60;

                    var formattedTime = hours.toString().padStart(2, "0") + ":" +
                                        minutes.toString().padStart(2, "0") + ":" +
                                        seconds.toString().padStart(2, "0");
                    countdownElement.innerHTML = formattedTime;
                    }, 1);



            }


            // Get the end time from your server-side variable.
            var endTime = ' . json_encode($tiempos) . '[pageID] + ' . time() . ';


            var storedTime = localStorage.getItem("countdown" + pageID);
            if (storedTime !== null) {
                // Use the stored time as the end time
                endTime = parseInt(storedTime) + ' . time() . ';
                // Remove the stored time from localStorage
                localStorage.removeItem("countdown" + pageID);
            }


                // Start the countdown timer.
                updateCountdownTimer(endTime + 1,5);

            // Insert countdown elements after the respective heading elements.
            headingElement.forEach(function(headingElement, index) {
            var textoElement = document.getElementById("countdown0");
                if (headingElement && textoElement) {
                    headingElement.parentNode.insertBefore(textoElement, headingElement.nextSibling);
                }
        });


    });

    </script>';
                }
            }
        }
    }

    // Send quiz results after finish.
    if (basename($_SERVER['PHP_SELF']) == 'summary.php') {
        echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
            var button = document.getElementsByClassName("btn btn-primary")[0];
            button.click();
            localStorage.clear();
            });
        </script>';
    }
}


/**
 * Function that returns an int with the selected option.
 *
 * @return integer option choosed
 */
function get_quizoptions() {
    global $DB, $quiz;
    if (isset($_GET['attempt'])) {

        $attemptid = required_param('attempt', PARAM_INT);
        $attempt = quiz_attempt::create($attemptid);
        $quizid = $attempt->get_quiz();
        $id = $quizid->id;
        $quizid = $id;
        $quiz = $DB->get_record('quizaccess_quiztimer', ['quiz' => $quizid]);
        return intval($quiz->quiz_mode);
    }
}




echo showtime(get_quizoptions());
