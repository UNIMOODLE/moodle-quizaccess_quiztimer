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
require_once($CFG->dirroot . '/mod/quiz/accessrule/quiztimer/classes/sections_settings.php');
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/quiztimer/externallib.php');
require_once($CFG->dirroot . '/mod/quiz/accessrule/quiztimer/classes/quiz_options.php');

use quizaccess_quiztimer\sections_settings;

class quiztimer_insert_section_time_test extends \advanced_testcase {

    // Write the tests here as public funcions.
    // Please refer to {@link https://docs.moodle.org/dev/PHPUnit} for more details on PHPUnit tests in Moodle.
    
    /**
     * @var \stdClass
     */
    private static $course;

    /**
     * @var \stdClass
     */
    private static $coursecontext;
    
    /**
     * @var \stdClass
     */
    private static $user;

    /**
     * @var int
     */
    private static $reviewattempt;
    
    /**
     * @var int
     */
    private static $timeclose;

    /**
     * @var \stdClass
     */
    private static $quiz;

    /**
     * Course start.
     */
    private const COURSE_START = 1706009000;

    /**
     * Course end.
     */
    private const COURSE_END = 1906009000;
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
     * Insert section timer
     *
     * Insert section time into quiz
     *
     * @package    quizaccess_quiztimer
     * @copyright  2023 Proyecto UNIMOODLE
     * @covers \quiztimer_insert_section_time_test::insert_section_time
     * @dataProvider dataprovider
     * @param string $param Parameters (timevalue and timeunit)
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_insert_section_time($param) {
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
        $this->assertNotNull($quizobj);
        // Create a section.
        $DB->insert_record('quiz_sections', ['quizid' => $cm->id, 'firstslot' => 1, 'shufflequestion' => 0, 'heading' => '']);
        $quizaccess = new \quizaccess_quiztimer_external();

        // Get section id
        $sectionssetings = new sections_settings();
        $sectionssetings->get_by_quiz_id(self::$quiz->id);
        $reflectionmethod = new ReflectionMethod(sections_settings::class, 'get_sectionid');
        $reflectionmethod->setAccessible(true);
        $sectionid = $reflectionmethod->invoke($sectionssetings, $cm->id);

        $sectiontime = $quizaccess->get_section_time(self::$quiz->id, $sectionid);

        // In sectionid doesn't work try 1
        // Set section time.
        $quizaccess->set_section_time(self::$quiz->id, $sectionid, $param);
        $quizaccess->set_section_time_returns();
        $sectiontime = $quizaccess->get_section_time(self::$quiz->id, $sectionid);
        $quizaccess->get_section_time_returns();
        // $this->assertArrayHasKey('timeunit', $sectiontime, 'There is not timeunit');
        // $this->assertArrayHasKey('timevalue', $sectiontime, 'There is not timevalue');
        $this->assertNotNull($sectiontime);
    }
    public static function dataprovider(): array {
        return [
            ['{"value": 10, "unit": 1}'],
            ['{"value": 20, "unit": 2}'],
            ['{"value": 20, "unit": 3}'],
            ['{"value": 100, "unit": 0}'],
        ];
    }
}
