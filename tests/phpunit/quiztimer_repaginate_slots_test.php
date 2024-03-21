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
 * Test case for repaginate section. *
 * @package    quizaccess_quiztimer
 * @copyright  2023 Proyecto UNIMOODLE
 * @author     UNIMOODLE Group (Coordinator) <direccion.area.estrategia.digital@uva.es>
 * @author     ISYC <soporte@isyc.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/config.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/quiztimer/externallib.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/quiztimer/classes/quiz_options.php');

use quizaccess_quiztimer\slots_settings;
class quiztimer_repaginate_slots_test extends \advanced_testcase {

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
     * Repaginate slots
     *
     * Repaginate slots of a quiz
     *
     * @package    quizaccess_quiztimer
     * @copyright  2023 Proyecto UNIMOODLE
     * @covers \quiztimer_repaginate_slots_test::repaginate_slots
     * @dataProvider dataprovider
     * @param string $param Parameters (timevalue and timeunit)
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_repaginate_slots($editmethod) {
        global $DB, $SITE;

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        // Generate quiz.
        self::$quiz = $quizgenerator->create_instance(['course' => self::$course->id,
            'seb_program_autocomplete_program_quiz' => [1],
            'questionsperpage' => 1,
            ]);

        $cm = get_coursemodule_from_instance('quiz', self::$quiz->id, self::$course->id);
        $this->assertNotNull($cm);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        // Generate questions.
        for ($i = 0; $i < 4; $i++) {
            $truefalse = $questiongenerator->create_question('truefalse', null, ['category' => $cat->id]);
            quiz_add_quiz_question($truefalse->id, self::$quiz);
        }
        $quizobj = \quiz::create(self::$quiz->id, self::$user->id);

        // Create quiz_slots_settiongs persistent.
        $slotssettings = new slots_settings();
        $reflectionmethod = new \ReflectionMethod(slots_settings::class, 'define_properties');
        $reflectionmethod->setAccessible(true);
        $properties = $reflectionmethod->invoke($slotssettings);
        // $properties->create();
        // $slotssettings->define_properties();
        $slotssettings->get_by_quiz_id(self::$quiz->id);

        $quizaccess = new \quizaccess_quiztimer_external();
        // Repaginate slots.
        $repaginatedslots = $quizaccess->repaginate_slots(self::$quiz->id, $editmethod);
        $quizaccess->repaginate_slots_returns();
        $this->assertNotNull($repaginatedslots);
    }
    public static function dataprovider(): array {
        return [
            ['section'],
            ['slots'],
            ['equitative'], // Error with equitative in phpunit, it's ok in moodle.
            ['nonvalidoption'],
        ];
    }

}
