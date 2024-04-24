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

class quiztimer_complete_attempt_set_time_question_test extends \advanced_testcase {

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
     * Course start
     */

    private const COURSE_START = 1706009000;

    /**
     * Course end
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
     * Insert question timer and try attempt
     *
     * Insert question time into quiz and try attempt
     *
     * @package    quizaccess_quiztimer
     * @copyright  2023 Proyecto UNIMOODLE
     * @covers \quiztimer_complete_attempt_set_time_question_test::complete_attempt_set_time_question
     * @dataProvider dataprovider
     * @param string $param Parameters (timevalue and timeunit)
     * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
     */
    public function test_complete_attempt_set_time_question($param, $editmethod) {
        global $DB, $SITE;

        $timedata = new \stdClass();
        $datadecoded = json_decode($param);
        $timedata->value = $datadecoded->timevalue;
        $timedata->unit = $datadecoded->timeunit;

        $quizgenerator = self::getDataGenerator()->get_plugin_generator('mod_quiz');
        // Generate quiz.
        self::$quiz = $quizgenerator->create_instance(['course' => self::$course->id,
            'seb_program_autocomplete_program_quiz' => [1],
            'grade' => 100.0,
            'sumgrades' => 2,
            'layout' => '1,0',
            ]);

        $cm = get_coursemodule_from_instance('quiz', self::$quiz->id, self::$course->id);
        $this->assertNotNull($cm);
        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $quizaccess = new \quizaccess_quiztimer_external();
        // Generate question.
        $truefalse = $questiongenerator->create_question('truefalse', null, ['category' => $cat->id]);
        // Set time question.
        $quizaccess->set_question_time(self::$quiz->id, $truefalse->id, json_encode($timedata));

        quiz_add_quiz_question($truefalse->id, self::$quiz);

        // Create a true/false question
        $quizaccess->set_question_time(self::$quiz->id, $truefalse->id, json_encode($timedata));
        $quiztime = $quizaccess->get_quiz_time(self::$quiz->id, $editmethod);
        // print print_r($quiztime);
        // Create quiz object.
        $quizobj = \quiz::create(self::$quiz->id, self::$user->id);

        $quba = question_engine::make_questions_usage_by_activity('mod_quiz', $quizobj->get_context());
        $quba->set_preferred_behaviour($quizobj->get_quiz()->preferredbehaviour);

        $this->assertNotNull($quizaccess->get_question_time($truefalse->id));
        $this->assertNotNull($truefalse);
        $timenow = time();
        // Create attempt.
        $attempt = quiz_create_attempt($quizobj, 1, false, $timenow, false, self::$user->id);
        // Start attempt.
        quiz_start_new_attempt($quizobj, $quba, $attempt, 1, $timenow);

        // Save question started.
        quiz_attempt_save_started($quizobj, $quba, $attempt);

        $attemptobj = quiz_attempt::create($attempt->id);
        $attemptobj->process_submitted_actions($timenow, false, [1 => ['answer' => '0']]);

        // Proccess answers of participant
        $attemptobj = quiz_attempt::create($attempt->id);
        $this->assertTrue($attemptobj->has_response_to_at_least_one_graded_question());
        // Simulate the quiz take 15 sec.
        sleep(15);
        // We won't finish manually the quiz.
        $attemptobj->process_finish(time(), false);
        // print print_r($attempt);
        $quizoptions = new quiz_options();
        $quizoptions->set_quiz_option(self::$quiz->id, $editmethod);
        // Check attempt is finished
        $this->assertEquals(true, $attemptobj->is_finished());
    }
    public static function dataprovider(): array {
        return [

            ['{"timevalue": 4, "timeunit": 1}', 'question'],
        ];
    }

}
