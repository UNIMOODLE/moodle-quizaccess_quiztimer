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

namespace quizaccess_quiztimer;
/**
 * Helper class for question to activity plugin which includes all the associated methods.
 *
 * @package     quizaccess_quiztimer
 */
class quiz_options {

    /**
     * Gets the quiz id from db and returns code
     *
     * @param int $quizid
     * @return string
     */
    public function get_quiz_option($quizid) : string {
        global $CFG, $DB;

        $sql = "SELECT quiz_mode FROM {quizaccess_quiztimer} WHERE quiz = :quizid";
        $params = ['quizid' => $quizid];
        $option = $DB->get_record_sql($sql, $params);
        if (!$option) {
            return 'section';
        }
        switch ($option->quiz_mode) {
            case 1:
                return 'equitative';
                break;
            case 2:
                return 'section';
                break;
            case 3:
                return 'slots';
                break;
            default:
                return 'section';
                break;
        }
    }

    /**
     * Sets the quiz id from db and returns code
     *
     * @param int $quizid
     * @param string $selected
     * @return int
     */
    public function set_quiz_option($quizid, $selected) {
        global $CFG, $DB, $USER;

        switch ($selected) {
            case 'equitative':
                $option = 1;
                break;
            case 'section':
                $option = 2;
                break;
            case 'slots':
                $option = 3;
                break;
            default:
                $option = false;
                break;
        }
        if ($option) {

            $sql = "SELECT id FROM {quizaccess_quiztimer} WHERE quiz = :quizid";
            $params = ['quizid' => $quizid];
            $id = $DB->get_record_sql($sql, $params);

            $quizmode = new \stdClass();
            $timenow = (new \DateTime('now', \core_date::get_server_timezone_object()))->getTimestamp();
            $id ? $quizmode->id = $id->id : $quizmode->timecreated = $timenow;
            $quizmode->quiz = $quizid;
            $quizmode->quizmode = $option;
            $quizmode->timemodified = $timenow;
            $quizmode->usermodified = $USER->id;
            $id ? $DB->update_record('quizaccess_quiztimer', $quizmode) : $DB->insert_record('quizaccess_quiztimer', $quizmode);
        }

    }
}
