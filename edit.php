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


 require_once(__DIR__ . '/../../../../config.php');
 require_once($CFG->dirroot . '/mod/quiz/accessrule/quiztimer/lib.php');
 require_once($CFG->libdir . '/tablelib.php');
 use quizaccess_quiztimer\quiz_options;
 require_once($CFG->dirroot . '/mod/quiz/locallib.php');
 require_once($CFG->dirroot . '/question/editlib.php');

// These params are only passed from page request to request while we stay on
// this page otherwise they would go in question_edit_setup.
$scrollpos = optional_param('scrollpos', '', PARAM_INT);

list($thispageurl, $contexts, $cmid, $cm, $quiz, $pagevars) =
        question_edit_setup('editq', '/mod/quiz/edit.php', true);

require_capability('quizaccess/quiztimer:manage', context_module::instance($PAGE->cm->id), $USER->id, true,
    $errormessage = 'nopermissions', $stringfile = '');
$defaultcategoryobj = question_make_default_categories($contexts->all());
$defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

$quizhasattempts = quiz_has_attempts($quiz->id);
$thispageurl = new moodle_url('/mod/quiz/accessrule/quiztimer/edit.php?cmid=' . optional_param('cmid', 1, PARAM_INT) . '');
$PAGE->set_url($thispageurl);
$PAGE->set_secondary_active_tab("mod_quiz_edit");
$PAGE->navbar->add(get_string('pluginname', 'quizaccess_quiztimer' ),
new moodle_url('/mod/quiz/accessrule/quiztimer/edit.php?cmid=' . $cmid));

// Get the course object and related bits.
$course = $DB->get_record('course', ['id' => $quiz->course], '*', MUST_EXIST);
require_login($course);
$quizobj = new \mod_quiz\quiz_settings($quiz, $cm, $course);

$structure = $quizobj->get_structure();

// You need mod/quiz:manage in addition to question capabilities to access this page.
require_capability('mod/quiz:manage', $contexts->lowest());

// Process commands ============================================================.

// Get the list of question ids had their check-boxes ticked.
$selectedslots = [];
$params = (array) data_submitted();
foreach ($params as $key => $value) {
    if (preg_match('!^s([0-9]+)$!', $key, $matches)) {
        $selectedslots[] = $matches[1];
    }
}


// Log this visit.
$event = \mod_quiz\event\edit_page_viewed::create([
    'courseid' => $course->id,
    'context' => $contexts->lowest(),
    'other' => [
        'quizid' => $quiz->id,
    ],
]);
$event->trigger();

// End of process commands =====================================================.

$PAGE->set_pagelayout('incourse');
$PAGE->add_body_class('limitedwidth');
$PAGE->set_pagetype('mod-quiz-edit');


$output = $PAGE->get_renderer('quizaccess_quiztimer', 'edit' );

$PAGE->set_title(get_string('editingquizx', 'quiz', format_string($quiz->name)));
$PAGE->set_heading($course->fullname);
$PAGE->activityheader->disable();
$PAGE->navbar->add(get_string('quiztime', 'quizaccess_quiztimer' ));
echo $OUTPUT->header();

// Initialise the JavaScript.
$quizeditconfig = new stdClass();
$quizeditconfig->url = $thispageurl->out(true, ['qbanktool' => '0']);
$quizeditconfig->dialoglisteners = [];
$numberoflisteners = $DB->get_field_sql("
    SELECT COALESCE(MAX(page), 1)
      FROM {quiz_slots}
     WHERE quizid = ?", [$quiz->id]);

for ($pageiter = 1; $pageiter <= $numberoflisteners; $pageiter++) {
    $quizeditconfig->dialoglisteners[] = 'addrandomdialoglaunch_' . $pageiter;
}

$PAGE->requires->data_for_js('quiz_edit_config', $quizeditconfig);
$PAGE->requires->js_call_amd('core_question/question_engine');

$edittype = optional_param('edittype', null, PARAM_TEXT);
$quizopt = new quiz_options();
if ($edittype === null) {
    $edittype = $quizopt->get_quiz_option($quiz->id);
} else {
    $quizopt->set_quiz_option($quiz->id, $edittype);
}
$PAGE->requires->js_call_amd('quizaccess_quiztimer/time', 'init', ['edittype' => $edittype]);

// Questions wrapper start.
echo html_writer::start_tag('div', ['class' => 'mod-quiz-edit-content']);

echo $output->edit_page($quizobj, $structure, $contexts, $thispageurl, $pagevars);

// Questions wrapper end.
echo html_writer::end_tag('div');

echo $OUTPUT->footer();
