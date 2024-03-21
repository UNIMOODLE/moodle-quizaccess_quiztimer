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

// Project implemented by the "Recovery, Transformation and Resilience Plan.
// Funded by the European Union - Next GenerationEU".
//
// Produced by the UNIMOODLE University Group: Universities of
// Valladolid, Complutense de Madrid, UPV/EHU, León, Salamanca,
// Illes Balears, Valencia, Rey Juan Carlos, La Laguna, Zaragoza, Málaga,
// Córdoba, Extremadura, Vigo, Las Palmas de Gran Canaria y Burgos.

/**
 * The testing class.
 *
 * @package     quizaccess_quiztimer
 * @copyright   2023 Proyecto UNIMOODLE
 * @author      UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/config.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/quiztimer/externallib.php');

use quizaccess_quiztimer\quiz_options;
use quizaccess_quiztimer\sections_settings;
class quiztimer_set_quiz_config_test extends \advanced_testcase {

    // Write the tests here as public funcions.
    // Please refer to {@link https://docs.moodle.org/dev/PHPUnit} for more details on PHPUnit tests in Moodle.
    private static $course;
    private static $context;
    private static $coursecontext;
    private static $user;

    private static $reviewattempt;
    private static $timeclose;
    private static $attempts;

    private static $cm;
    private static $quiz;
    private const COURSE_START = 1706009000;
    private const COURSE_END = 1906009000;
    private const CM_DATESTART = 1706009000;
    private const CM_DATEEND = 1906009000;
    public function setUp(): void {
        global $USER;
        parent::setUp();
        $this->resetAfterTest(true);
        self::setAdminUser();
        self::$course = self::getDataGenerator()->create_course(
            ['startdate' => self::COURSE_START, 'enddate' => self::COURSE_END]
        );

        $_SERVER['REQUEST_METHOD'] = 'POST';

        self::$coursecontext = \context_course::instance(self::$course->id);
        self::$user = $USER;
        self::$reviewattempt = 0x10010;
        self::$timeclose = 0;

    }

    /**
     * Set quiz config
     *
     * Set quiz config plugin info
     *
     * @package    quizaccess_quiztimer
     * @copyright  2023 Proyecto UNIMOODLE
     * @covers \quiztimer_set_quiz_config_test::set_quiz_config
     * @dataProvider dataprovider
     * @param string $selected Selected by fields (equitative, section o slots)
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_set_quiz_config($selected, $quizmode) {
        global $DB, $SITE;

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        // Generate quiz.
        self::$quiz = $quizgenerator->create_instance(['course' => self::$course->id,
            'seb_program_autocomplete_program_quiz' => [1],
            'quiz_mode' => $quizmode,
            ]);
        $quizobj = \quiz::create(self::$quiz->id, self::$user->id);
        $cm = get_coursemodule_from_instance('quiz', self::$quiz->id, self::$course->id);
        $this->assertNotNull($cm);
        $quizoptions = new quiz_options();
        // Set quiz config.
        $quizoptions->set_quiz_option($cm->id, $selected);
        // Check it.
        $quizoption = $quizoptions->get_quiz_option(self::$quiz->id);
        // print print_r($quizoption);
        // Fail get quiz option.
        $quizoptionfail = $quizoptions->get_quiz_option(99);
        $this->assertNotNull($quizoption);
        $this->assertIsString($quizoption);
        // Persistent quiztimer
        $quiztimer = new quizaccess_quiztimer\quiztimer();
        $reflectionmethod = new ReflectionMethod(quizaccess_quiztimer\quiztimer::class, 'define_properties');
        $reflectionmethod->setAccessible(true);
        $defineproperties = $reflectionmethod->invoke($quiztimer);

        $reflectionmethod = new ReflectionMethod(quizaccess_quiztimer\quiztimer::class, 'before_update');
        $reflectionmethod->setAccessible(true);
        $defineproperties = $reflectionmethod->invoke($quiztimer);

        $reflectionmethod = new ReflectionMethod(quizaccess_quiztimer\quiztimer::class, 'before_create');
        $reflectionmethod->setAccessible(true);
        $defineproperties = $reflectionmethod->invoke($quiztimer);

        $quiztimer->before_save();
    }

    public static function dataprovider(): array {
        return [
            ['equitative', 0],
            ['section', 1],
            ['equitative', 2],
            ['section', 3],
            ['slots', 4],
            ['randomtext', 5],
        ];
    }

}
