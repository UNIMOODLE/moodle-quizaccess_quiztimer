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

namespace quizaccess_quiztimer\helpers;


/**
 * Class with helper methods to work with dates.
 *
 */
class dateshelper {


    /**
     * Get the time in seconds based on the time unit and value provided.
     *
     * @param mixed $timeunit The unit of time (1, 2, or 3)
     * @param mixed $timevalue The value corresponding to the time unit
     * @return string The time in seconds calculated based on the unit and value
     */
    public static function get_quiz_time($timeunit, $timevalue): string {
        switch ($timeunit) {
            case 1:
                $timeinseconds = $timevalue;
                break;
            case 2:
                $timeinseconds = $timevalue * 60;
                break;
            case 3:
                $timeinseconds = $timevalue * 3600;
                break;
            default:
                $timeinseconds = '';
                break;

        }
        return $timeinseconds;
    }
}
