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
 * Quiz timer access rule  - Hook: Allows elements to the page <head> html tag.
 *
 * @package    quizaccess_quiztimer
 * @copyright  2025 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     Enrique castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


namespace quizaccess_quiztimer\hooks\output;
use quizaccess_quiztimer\quiz_options;
use context_module;

/**
 * Hook to add elements to the page <head> html tag.
 *
 * @package    quizaccess_quiztimer
 * @copyright  2025 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     Enrique castro @ULPGC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class before_standard_head_html_generation {
    /**
     * Callback to add head elements.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook
     */
    public static function callback(\core\hook\output\before_standard_head_html_generation $hook): void {
        global $CFG, $PAGE, $DB, $USER;

        if ($PAGE->pagetype == "mod-quiz-edit" && has_capability('quizaccess/quiztimer:manage',
            context_module::instance($PAGE->cm->id), $USER->id)) {
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
}
