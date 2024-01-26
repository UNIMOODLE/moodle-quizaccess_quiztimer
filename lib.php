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

// Adds the question time option in the edit quiz view with an amd module.
use quizaccess_quiztimer\quiz_options;
/**
 * Hook to add the times edit form
 */
function quizaccess_quiztimer_before_standard_html_head() {
    global $CFG, $PAGE, $DB;
    if ($PAGE->pagetype == "mod-quiz-edit") {
        $e = optional_param('editmethod', null, PARAM_TEXT);
        if ($e === null) {
            $quizopt = new quiz_options();
            $instance = $DB->get_field('course_modules', 'instance', ['id' => $PAGE->cm->id], IGNORE_MISSING);
            $e = $quizopt->get_quiz_option($instance);
        }
        $PAGE->requires->js_call_amd('quizaccess_quiztimer/preflightcheck', 'init', ['cmid' => $PAGE->cm->id,
         'editmethod' => $e, 'webroot' => $CFG->wwwroot, ]);
        $PAGE->requires->strings_for_js(['quiztime', 'hours', 'minutes', 'seconds'], 'quizaccess_quiztimer');
    }
}
