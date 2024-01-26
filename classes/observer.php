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

defined('MOODLE_INTERNAL') || die();

class quizaccess_quiztimer_observer {
    public static function slot_deleted(\mod_quiz\event\slot_deleted $event) {
        global $DB;
        if ($DB->get_record('quizaccess_timedslots', ['slot' => $event->objectid], 'id', IGNORE_MISSING)) {
            $DB->delete_records('quizaccess_timedslots', ['slot' => $event->objectid]);
        }
    }

    public static function section_break_deleted(\mod_quiz\event\section_break_deleted $event) {
        global $DB;
        if ($DB->get_record('quizaccess_timedsections', ['sectionid' => $event->objectid], 'id', IGNORE_MISSING)) {
            $DB->delete_records('quizaccess_timedsections', ['sectionid' => $event->objectid]);
        }
    }
}
