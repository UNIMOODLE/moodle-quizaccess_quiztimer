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

 namespace quizaccess_quiztimer;

 use core\persistent;

 /**
  * slot settings class for backups.
  */
class slots_settings extends persistent {

        /** Table name for the persistent. */
        const TABLE = 'quizaccess_timedslots';

        /** @var property_list $plist The quiztimer config represented as a Property List object. */
        private $plist;


        /**
         * Return the definition of the properties of this model.
         *
         * @return array
         */
    protected static function define_properties(): array {
        return [
            'quizid' => [
                'type' => PARAM_INT,
            ],
            'slot' => [
                'type' => PARAM_INT,
            ],
            'timeunit' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'timevalue' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }

    /**
     * Return an instance by quiz id.
     *
     * This method gets data from cache before doing any DB calls.
     *
     * @param int $quizid Quiz id.
     * @return false|\quizaccess_seb\quiz_settings
     */
    public static function get_by_quiz_id(int $quizid) {
        return self::get_record(['quizid' => $quizid]);
    }

    /**
     * Return cached SEB config represented as a string by quiz ID.
     *
     * @param int $quizid Quiz id.
     * @return string|null
     */
    public static function get_config_by_quiz_id(int $quizid): ?string {
        $config = self::get_config_cache()->get($quizid);

        if ($config !== false) {
            return $config;
        }

        $config = null;
        if ($settings = self::get_by_quiz_id($quizid)) {
            $config = $settings->get_config();
            self::get_config_cache()->set($quizid, $config);
        }

        return $config;
    }

}
