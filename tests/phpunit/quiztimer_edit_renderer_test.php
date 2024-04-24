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
require_once($CFG->dirroot . '/mod/quiz/accessrule/quiztimer/classes/quiz_options.php');

use quizaccess_quiztimer\quiz_options;
use quizaccess_quiztimer\output\edit_renderer;
class quiztimer_edit_renderer_test extends \advanced_testcase {

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
     * @var \stdClass
     */
    private static $page;

    /**
     * Course start.
     */
    private const COURSE_START = 1706009000;

    /**
     * Course end.
     */
    private const COURSE_END = 1906009000;
    public function setUp(): void {
        global $USER, $PAGE;
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
        self::$page = $PAGE;

    }

    /**
     * Get quiz time
     *
     * Get quiz time by editmethod
     *
     * @package    quizaccess_quiztimer
     * @copyright  2023 Proyecto UNIMOODLE
     * @covers \quiztimer_edit_renderer_test::edit_renderer
     * @dataProvider dataprovider
     * @param string $param Parameters (timevalue and timeunit)
     * @param string $editmethod Method quiz timer
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_edit_renderer($param, $editmethod) {
        global $DB, $SITE;

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        // Generate quiz.
        self::$quiz = $quizgenerator->create_instance(['course' => self::$course->id,
            'seb_program_autocomplete_program_quiz' => [1],
            ]);
        $quiz = \quiz::create(self::$quiz->id, self::$user->id);
        $cm = get_coursemodule_from_instance('quiz', self::$quiz->id, self::$course->id);
        $this->assertNotNull($cm);

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        // Generate question.
        $truefalse = $questiongenerator->create_question('truefalse', null, ['category' => $cat->id]);
        quiz_add_quiz_question($truefalse->id, self::$quiz);

        $quizobj = new quiz($quiz, $cm, self::$course);

        $editrender = new edit_renderer(self::$page, '');
        $structure = $quiz->get_structure();

        $statusbar = $editrender->quiz_information($structure);

        $reflectionmethod = new ReflectionMethod(edit_renderer::class, 'start_section_list');
        $reflectionmethod->setAccessible(true);
        $startsectionlist = $reflectionmethod->invoke($editrender, $structure);

        // $startsectionlist = $editrender->start_section_list($structure);
        $reflectionmethod = new ReflectionMethod(edit_renderer::class, 'end_section_list');
        $reflectionmethod->setAccessible(true);
        $startsectionlist = $reflectionmethod->invoke($editrender);

        $reflectionmethod = new ReflectionMethod(edit_renderer::class, 'end_section');
        $reflectionmethod->setAccessible(true);
        $startsectionlist = $reflectionmethod->invoke($editrender);

        $url = new moodle_url('mod/quiz/accesrule/quiztimer/edit.php', ["editmethod" => 'time']);

        $editrender->question_number(1);

        // Generate question.
        $question = \test_question_maker::make_question('truefalse', 'true');
        $quizaccess = new \quizaccess_quiztimer_external();
        $timedata = new \stdClass();
        $datadecoded = json_decode($param);
        $timedata->value = $datadecoded->timevalue;
        $timedata->unit = $datadecoded->timeunit;

        $quizoptions = new quiz_options();
        $quizoptions->set_quiz_option($cm->id, $editmethod);

        // Insert slots into db
        $DB->insert_record('quiz_slots', ['slot' => 1, 'quizid' => $cm->id, 'page' => 1, 'requireprevious' => 1, 'maxmark' => 1]);
        // $slot = $DB->get_record_sql('SELECT TOP(1) * FROM {quiz_slots}');
        $quizaccess->set_question_time(self::$quiz->id, $question->id, json_encode($timedata));
        $quizaccess->set_question_time_returns();
        $this->assertNotNull($quizaccess->get_question_time($question->id));
        // Get quiz time.
        $quiztime = $quizaccess->get_quiz_time(self::$quiz->id, $editmethod);
        $quizaccess->get_quiz_time_returns();

        $warnings = $editrender->quiz_state_warnings($structure);
        // Print question.
        $question = $editrender->question($structure, 1, new \moodle_url('/mod/quiz/accessrule/quiztimer'));
        $this->assertNotNull($question);
        // Print question row.
        $editrender->question_row($structure, 1, '', [], new \moodle_url('/mod/quiz/accessrule/quiztimer'));

        // Question name.
        $editrender->question_name($structure, 1, new \moodle_url('/mod/quiz/accessrule/quiztimer'));
        
        // Question.
        $editrender->question($structure, 1, new \moodle_url('/mod/quiz/accessrule/quiztimer'));

        $newpagetemplate = $reflectionmethod->invoke($editrender, $structure, '', [], new \moodle_url('/mod/quiz/accessrule/quiztimer'));
        $this->assertNotNull($newpagetemplate);
        //$editrender->get_action_icon($structure, 1, new \moodle_url('/mod/quiz/accessrule/quiztimer'));

        $this->assertNotNull($quiztime);
    }
    public static function dataprovider(): array {
        return [
            ['{"timevalue": 10, "timeunit": 1}', 'slots'],
            ['{"timevalue": 20, "timeunit": 2}', 'sections'],
            ['{"timevalue": 20, "timeunit": 3}', 'slots'],
        ];
    }

}

