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

/**
 * DB uninstall for quiztimer
 *
 * @copyright   2023 isyc
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function xmldb_quizaccess_quiztimer_uninstall() {

    global $DB;

    $dbman = $DB->get_manager();

    // Define table quizaccess_quiztimer to be dropped.
    $table = new xmldb_table('quizaccess_quiztimer');

    // Conditionally launch drop table for quizaccess_quiztimer.
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table);
    }

    // Define table quizaccess_timedslots to be dropped.
    $table = new xmldb_table('quizaccess_timedslots');

    // Conditionally launch drop table for quizaccess_timedslots.
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table);
    }

    // Define table quizaccess_timedslots to be dropped.
    $table = new xmldb_table('quizaccess_timedslots');

    // Conditionally launch drop table for quizaccess_timedslots.
    if ($dbman->table_exists($table)) {
        $dbman->drop_table($table);
    }
}

