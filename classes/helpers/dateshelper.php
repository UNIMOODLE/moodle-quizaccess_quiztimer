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

/*
 *
 * @package    quizaccess_quiztimer
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace quizaccess_quiztimer\helpers;


class dateshelper {

    public static function get_quiz_time($timeunit, $timevalue) : string {
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
