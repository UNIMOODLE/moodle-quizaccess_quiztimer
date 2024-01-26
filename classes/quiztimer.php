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

use core\persistent;

/**
 * Entity model representing template settings for the quiztimer plugin.
 *
 * @copyright  2020 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class quiztimer extends persistent {

    /** Table name for the persistent. */
    const TABLE = 'quizaccess_quiztimer';

    /** @var property_list $plist The quiztimer config represented as a Property List object. */
    private $plist;

    /**
     * Return the definition of the properties of this model.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'quiz' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
            'quiz_mode' => [
                'type' => PARAM_INT,
                'default' => 0,
            ],
        ];
    }

    /**
     * Hook to execute before an update.
     *
     * Please note that at this stage the data has already been validated and therefore
     * any new data being set will not be validated before it is sent to the database.
     */
    protected function before_update() {
        $this->before_save();
    }

    /**
     * Hook to execute before a create.
     *
     * Please note that at this stage the data has already been validated and therefore
     * any new data being set will not be validated before it is sent to the database.
     */
    protected function before_create() {
        $this->before_save();
    }

    /**
     * As there is no hook for before both create and update, this function is called by both hooks.
     */
    private function before_save() {
        $this->plist = new property_list($this->get('content'));
        $this->set('content', $this->plist->to_xml());
    }

    /**
     * Validate template content.
     *
     * @param string $content Content string to validate.
     *
     * @return bool|\lang_string
     */
    protected function validate_content(string $content) {
        if (helper::is_valid_quiztimer_config($content)) {
            return true;
        } else {
            return new \lang_string('sectiontime', 'quizaccess_quiztimer');
        }
    }

    /**
     * Check if we can delete the template.
     *
     * @return bool
     */
    public function can_delete() : bool {
        $result = true;

        if ($this->get('quiz')) {
            $settings = quiz_settings::get_records(['quiz' => $this->get('quiz')]);
            $result = empty($settings);
        }

        return $result;
    }

}
