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
defined('MOODLE_INTERNAL') || die();

$functions = [

    'quizaccess_quiztimer_get_quiz_id' => [
        'classname' => 'quizaccess_quiztimer_external',
        'methodname' => 'get_quiz_id',
        'classpath' => '/mod/quiz/accessrule/quiztimer/externallib.php',
        'description' => 'gets the quiz id from the cm id',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '',
    ],
    'quizaccess_quiztimer_set_question_time' => [
        'classname' => 'quizaccess_quiztimer_external',
        'methodname' => 'set_question_time',
        'classpath' => '/mod/quiz/accessrule/quiztimer/externallib.php',
        'description' => 'sets the question time and saves it in bdd',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '',
    ],
    'quizaccess_quiztimer_get_question_time' => [
        'classname' => 'quizaccess_quiztimer_external',
        'methodname' => 'get_question_time',
        'classpath' => '/mod/quiz/accessrule/quiztimer/externallib.php',
        'description' => 'gets the question time from the bdd',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '',
    ],
    'quizaccess_quiztimer_set_section_time' => [
        'classname' => 'quizaccess_quiztimer_external',
        'methodname' => 'set_section_time',
        'classpath' => '/mod/quiz/accessrule/quiztimer/externallib.php',
        'description' => 'sets the section time and saves it in bdd',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '',
    ],
    'quizaccess_quiztimer_get_section_time' => [
        'classname' => 'quizaccess_quiztimer_external',
        'methodname' => 'get_section_time',
        'classpath' => '/mod/quiz/accessrule/quiztimer/externallib.php',
        'description' => 'gets the section time and saves it in bdd',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '',
    ],
    'quizaccess_quiztimer_repaginate_slots' => [
        'classname' => 'quizaccess_quiztimer_external',
        'methodname' => 'repaginate_slots',
        'classpath' => '/mod/quiz/accessrule/quiztimer/externallib.php',
        'description' => 'gets the section time and saves it in bdd',
        'type' => 'write',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '',
    ],
    'quizaccess_quiztimer_get_quiz_time' => [
        'classname' => 'quizaccess_quiztimer_external',
        'methodname' => 'get_quiz_time',
        'classpath' => '/mod/quiz/accessrule/quiztimer/externallib.php',
        'description' => 'gets the quiz time',
        'type' => 'read',
        'ajax' => true,
        'loginrequired' => true,
        'capabilities' => '',
    ],
];
