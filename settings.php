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

global $ADMIN;

if ($hassiteconfig) {

    $units = [ 3 => get_string('hours', 'quizaccess_quiztimer'),
    2 => get_string('minutes', 'quizaccess_quiztimer'), 1 => get_string('seconds', 'quizaccess_quiztimer'), ];


    $settings->add(new admin_setting_heading(
        'quizaccess_quiztimer/quiztimedsection',
        get_string('setting:timedsections', 'quizaccess_quiztimer'),
        ''
    ));
    $settings->add(new admin_setting_configtext('quizaccess_quiztimer/timedsections',
        get_string('setting:timedsections', 'quizaccess_quiztimer'),
        get_string('setting:timedsections_desc', 'quizaccess_quiztimer'),
        10, PARAM_INT));
    $settings->add(new admin_setting_configselect('quizaccess_quiztimer/timedsectionsunit',
        get_string('unitsections', 'quizaccess_quiztimer'), '',
        2, $units));

    $settings->add(new admin_setting_heading(
        'quizaccess_quiztimer/quiztimedslot',
        get_string('setting:timedslots', 'quizaccess_quiztimer'),
        ''
    ));
    $settings->add(new admin_setting_configtext('quizaccess_quiztimer/timedslots',
        get_string('setting:timedslots', 'quizaccess_quiztimer'),
        get_string('setting:timedslots_desc', 'quizaccess_quiztimer'),
        60, PARAM_INT));
    $settings->add(new admin_setting_configselect('quizaccess_quiztimer/timedslotsunit',
        get_string('unitslots', 'quizaccess_quiztimer'), '',
        1, $units));
}
