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

require_once($CFG->dirroot . '/mod/quiz/backup/moodle2/backup_mod_quiz_access_subplugin.class.php');

/**
 * Backup instructions for the quiztimer (Safe Exam Browser) quiz access subplugin.
 *
 * @copyright  2023 isyc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_quizaccess_quiztimer_subplugin extends backup_mod_quiz_access_subplugin {

    /**
     * Stores the data related to the Safe Exam Browser quiz settings and management for a particular quiz.
     *
     * @return backup_subplugin_element
     */
    protected function define_quiz_subplugin_structure() {
        parent::define_quiz_subplugin_structure();
        $quizid = backup::VAR_ACTIVITYID;

        // Create XML elements.
        $subplugin = $this->get_subplugin_element();
        $subpluginwrapper = new backup_nested_element($this->get_recommended_name());

        // Timed sections.
        $timedsections = new \quizaccess_quiztimer\sections_settings();
        $blanktimedsectionsarray = (array) $timedsections->to_record();
        unset($blanktimedsectionsarray['usermodified']);
        unset($blanktimedsectionsarray['timemodified']);

        $timedsectionskeys = array_keys($blanktimedsectionsarray);

        $subplugintimedsectionssettings = new backup_nested_element('quizaccess_timedsections', null, $timedsectionskeys);

        // Timed slots.
        $timedslots = new \quizaccess_quiztimer\slots_settings();
        $blanktimedslotsarray = (array) $timedslots->to_record();
        unset($blanktimedslotsarray['usermodified']);
        unset($blanktimedslotsarray['timemodified']);

        $timedslotskeys = array_keys($blanktimedslotsarray);

        $subplugintimedslotssettings = new backup_nested_element('quizaccess_timedslots', null, $timedslotskeys);

        // Get quiz settings keys to save.
        $settings = new \quizaccess_quiztimer\quiztimer();
        $blanksettingsarray = (array) $settings->to_record();
        unset($blanksettingsarray['id']); // We don't need to save reference to settings record in current instance.
        // We don't need to save the data about who last modified the settings as they will be overwritten on restore. Also
        // means we don't have to think about user data for the backup.
        unset($blanksettingsarray['usermodified']);
        unset($blanksettingsarray['timemodified']);

        $settingskeys = array_keys($blanksettingsarray);

        // Save the settings.
        $subpluginquizsettings = new backup_nested_element('quizaccess_quiztimer', null, $settingskeys);

        // Connect XML elements into the tree.
        $subplugin->add_child($subpluginwrapper);
        $subpluginwrapper->add_child($subpluginquizsettings);
        $subpluginwrapper->add_child($subplugintimedsectionssettings);
        $subpluginwrapper->add_child($subplugintimedslotssettings);

        // Set source to populate the settings data by referencing the ID of quiz being backed up.
        $params = ['quizid' => $quizid];
        $subpluginquizsettings->set_source_table(\quizaccess_quiztimer\quiztimer::TABLE, ['quiz' => $quizid]);

        $subpluginquizsettings->annotate_files('quizaccess_quiztimer', 'filemanager_quiztimerconfigfile', null);

        $subplugintimedsectionssettings->set_source_table(\quizaccess_quiztimer\sections_settings::TABLE, $params);
        $subplugintimedslotssettings->set_source_table(\quizaccess_quiztimer\slots_settings::TABLE, $params);

        return $subplugin;
    }
}
