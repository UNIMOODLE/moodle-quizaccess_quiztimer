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

use quizaccess_quiztimer\quiz_settings;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/restore_mod_quiz_access_subplugin.class.php');

/**
 * Restore instructions for the quiztimer (Safe Exam Browser) quiz access subplugin.
 *
 * @copyright  2023 isyc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_quizaccess_quiztimer_subplugin extends restore_mod_quiz_access_subplugin {

    /**
     * Provides path structure required to restore data for seb quiz access plugin.
     *
     * @return array
     */
    protected function define_quiz_subplugin_structure() {
        $paths = [];
        // Quiz settings.
        $path = $this->get_pathfor('/quizaccess_quiztimer'); // Subplugin root path.
        $paths[] = new restore_path_element('quizaccess_quiztimer', $path);
        // Quiz sections.
        $path = $this->get_pathfor('/quizaccess_timedsections'); // Subplugin sections times path.
        $paths[] = new restore_path_element('quizaccess_timedsections', $path);

        // Quiz slots.
        $path = $this->get_pathfor('/quizaccess_timedslots'); // Subplugin slots times path.
        $paths[] = new restore_path_element('quizaccess_timedslots', $path);
        return $paths;
    }

    /**
     * Process the restored data for the quizaccess_quiztimer_template table.
     *
     * @param stdClass $data Data for quizaccess_quiztimer_template retrieved from backup xml.
     */
    public function process_quizaccess_quiztimer($data) {
        global $DB, $USER;
        $data = (object) $data;
        $data->quiz = $this->get_new_parentid('quiz'); // Update quizid with new reference.

        unset($data->id);
        $data->timecreated = $data->timemodified = time();
        $data->usermodified = $USER->id;
        $DB->insert_record(quizaccess_quiztimer\quiztimer::TABLE, $data);
        // Process attached files.
        $this->add_related_files('quizaccess_quiztimer', 'filemanager_quiztimerconfigfile', null);
    }

    /**
     * Process the restored data for the quizaccess_seb_quizsettings table.
     *
     * @param stdClass $data Data for quizaccess_seb_quizsettings retrieved from backup xml.
     */
    public function process_quizaccess_timedsections($data) {
        global $DB, $USER;
        // Process quizsettings.
        $data = (object) $data;
        $data->quizid = $this->get_new_parentid('quiz'); // Update quizid with new reference.
        unset($data->id);
        $data->timecreated = $data->timemodified = time();
        $data->usermodified = $USER->id;
        $DB->insert_record(quizaccess_quiztimer\sections_settings::TABLE, $data);
        // Process attached files.
        $this->add_related_files('quizaccess_timedsections', 'filemanager_quiztimerconfigfile', null);
    }

    /**
     * Process the restored data for the quizaccess_seb_quizsettings table.
     *
     * @param stdClass $data Data for quizaccess_seb_quizsettings retrieved from backup xml.
     */
    public function process_quizaccess_timedslots($data) {
        global $DB, $USER;
        // Process quizsettings.
        $data = (object) $data;
        $data->quizid = $this->get_new_parentid('quiz'); // Update quizid with new reference.
        unset($data->id);
        $data->timecreated = $data->timemodified = time();
        $data->usermodified = $USER->id;
        $DB->insert_record(quizaccess_quiztimer\slots_settings::TABLE, $data);
        // Process attached files.
        $this->add_related_files('quizaccess_timedslots', 'filemanager_quiztimerconfigfile', null);
    }

    /**
     * Assigns the created sections and slots ids of the new restored quiz
     * to them timed sections and slots.
     */
    protected function after_execute_quiz() {
        global $DB;
        $quizid = $this->task->get_activityid();
        $newquizsectionsids = $DB->get_records('quiz_sections', ['quizid' => $quizid], 'id ASC', 'id');
        $timedsections = $DB->get_records('quizaccess_timedsections', ['quizid' => $quizid], 'id ASC', '*');
        foreach ($timedsections as $section) {
            next($newquizsectionsids) ? next($newquizsectionsids) : '';
            $sectionid = array_shift($newquizsectionsids)->id;
            $DB->set_field('quizaccess_timedsections', 'sectionid', $sectionid, ['id' => $section->id]);
        }
        $newquizslotsids = $DB->get_records('quiz_slots', ['quizid' => $quizid], 'id ASC', 'id');
        $timedslots = $DB->get_records('quizaccess_timedslots', ['quizid' => $quizid], 'id ASC', '*');
        foreach ($timedslots as $slot) {
            next($newquizslotsids) ? next($newquizslotsids) : '';
            $slotid = array_shift($newquizslotsids)->id;
            $DB->set_field('quizaccess_timedslots', 'slot', $slotid, ['id' => $slot->id]);
        }
    }
}
