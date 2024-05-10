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

namespace quizaccess_quiztimer\output;

use mod_quiz\question\bank\qbank_helper;
use mod_quiz\structure;
use html_writer;
use renderable;

/**
 * Renderer outputting the quiz editing UI.
 */
class edit_renderer extends \plugin_renderer_base {

    /** @var string The toggle group name of the checkboxes for the toggle-all functionality. */
    protected $togglegroup = 'quiz-questions';

    /**
     * Render the edit page
     *
     * @param \quiz $quizobj object containing all the quiz settings information.
     * @param structure $structure object containing the structure of the quiz.
     * @param \core_question\local\bank\question_edit_contexts $contexts the relevant question bank contexts.
     * @param \moodle_url $pageurl the canonical URL of this page.
     * @param array $pagevars the variables from {@see question_edit_setup()}.
     * @return string HTML to output.
     */
    public function edit_page(\quiz $quizobj, structure $structure,
        \core_question\local\bank\question_edit_contexts $contexts, \moodle_url $pageurl, array $pagevars) {
        $output = '';

        // Page title.
        $output .= $this->heading(get_string('questions', 'quiz'));
        // Top information.
        $output .= $this->quiz_state_warnings($quizobj);
        $output .= $this->quiz_information($structure);
        // Show the questions organised into sections and pages.
        $output .= $this->start_section_list($structure);

        foreach ($structure->get_sections() as $section) {
            $output .= $this->start_section($structure, $section);
            $output .= $this->questions_in_section($structure, $section, $contexts, $pagevars, $pageurl);
            $output .= $this->end_section();
        }

        $output .= $this->end_section_list();

        return $output;
    }


    /**
     * Generate the function comment for the given function body in a markdown code block with the correct language syntax.
     *
     * @param datatype $quizobj description
     * @return Some_Return_Value
     */
    public function quiz_state_warnings($quizobj) {
        $warnings = $this->get_edittimes_page_warnings($quizobj);

        if (empty($warnings)) {
            return '';
        }

        $output = [];
        foreach ($warnings as $warning) {
            $output[] = \html_writer::tag('p', $warning);
        }
        return $this->box(implode("\n", $output), 'statusdisplay');
    }

    /**
     * Render the status bar.
     *
     * @param structure $structure the quiz structure.
     * @return string HTML to output.
     */
    public function quiz_information(structure $structure) {
        list($currentstatus, $explanation) = $structure->get_dates_summary();

        $output = html_writer::span(
                    get_string('numquestionsx', 'quiz', $structure->get_question_count()),
                    'numberofquestions') . ' | ' .
                html_writer::span($currentstatus, 'quizopeningstatus',
                    ['title' => $explanation]);

        return html_writer::div($output, 'statusbar');
    }
    /**
     * Generate the starting container html for the start of a list of sections
     * @param structure $structure the structure of the quiz being edited.
     * @return string HTML to output.
     */
    protected function start_section_list(structure $structure) {
        $class = 'slots';
        if ($structure->get_section_count() == 1) {
            $class .= ' only-one-section';
        }
        return html_writer::start_tag('ul', ['class' => $class, 'role' => 'presentation']);
    }

    /**
     * Generate the closing container html for the end of a list of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    /**
     * Display the start of a section, before the questions.
     *
     * @param structure $structure the structure of the quiz being edited.
     * @param \stdClass $section The quiz_section entry from DB
     * @return string HTML to output.
     */
    protected function start_section($structure, $section) {

        $output = '';

        $sectionstyle = '';
        if ($structure->is_only_one_slot_in_section($section)) {
            $sectionstyle = ' only-has-one-slot';
        }

        if ($section->heading) {
            $sectionheadingtext = format_string($section->heading);
            $sectionheading = html_writer::span($sectionheadingtext, 'instancesection');
        } else {
            // Use a sr-only default section heading, so we don't end up with an empty section heading.
            $sectionheadingtext = get_string('sectionnoname', 'quiz');
            $sectionheading = html_writer::span($sectionheadingtext, 'instancesection sr-only');
        }

        $output .= html_writer::start_tag('li', ['id' => 'section-'.$section->id,
            'class' => 'section main clearfix'.$sectionstyle, 'role' => 'presentation',
            'data-sectionname' => $sectionheadingtext, ]);

        $output .= html_writer::start_div('content');

        $output .= html_writer::start_div('section-heading');

        $headingtext = $this->heading(html_writer::span($sectionheading, 'sectioninstance'), 3);

        $output .= html_writer::div($headingtext, 'instancesectioncontainer');

        $output .= html_writer::start_tag('span', ['id' => 'section-time-' . $section->id,
            'class' => 'section-time-'.$sectionstyle, ]);
        $data = new \stdClass();
        $data->id = $section->id;
        $output .= $this->render_from_template('quizaccess_quiztimer/section_time', $data);
        $output .= html_writer::end_tag('span');
        $output .= html_writer::end_div($output, 'section-heading');

        return $output;
    }

    /**
     * Display the end of a section, after the questions.
     *
     * @return string HTML to output.
     */
    protected function end_section() {
        $output = html_writer::end_tag('div');
        $output .= html_writer::end_tag('li');

        return $output;
    }


    /**
     * Renders HTML to display the questions in a section of the quiz.
     *
     * This function calls {@see core_course_renderer::quiz_section_question()}
     *
     * @param structure $structure object containing the structure of the quiz.
     * @param \stdClass $section information about the section.
     * @param \core_question\local\bank\question_edit_contexts $contexts the relevant question bank contexts.
     * @param array $pagevars the variables from {@see \question_edit_setup()}.
     * @param \moodle_url $pageurl the canonical URL of this page.
     * @return string HTML to output.
     */
    public function questions_in_section(structure $structure, $section,
            $contexts, $pagevars, $pageurl) {

        $output = '';
        foreach ($structure->get_slots_in_section($section->id) as $slot) {
            $output .= $this->question_row($structure, $slot, $contexts, $pagevars, $pageurl);
        }
        return html_writer::tag('ul', $output, ['class' => 'section img-text']);
    }

    /**
     * Displays one question with the surrounding controls.
     *
     * @param structure $structure object containing the structure of the quiz.
     * @param int $slot which slot we are outputting.
     * @param \core_question\local\bank\question_edit_contexts $contexts the relevant question bank contexts.
     * @param array $pagevars the variables from {@see \question_edit_setup()}.
     * @param \moodle_url $pageurl the canonical URL of this page.
     * @return string HTML to output.
     */
    public function question_row(structure $structure, $slot, $contexts, $pagevars, $pageurl) {
        $output = '';
        $output .= $this->page_row($structure, $slot, $contexts, $pagevars, $pageurl);
        // Question HTML.
        $questionhtml = $this->question($structure, $slot, $pageurl);
        $qtype = $structure->get_question_type_for_slot($slot);
        $questionclasses = 'activity ' . $qtype . ' qtype_' . $qtype . ' slot';

        $output .= html_writer::tag('li', $questionhtml,
                ['class' => $questionclasses, 'id' => 'slot-' . $structure->get_slot_id_for_slot($slot),
                        'data-canfinish' => $structure->can_finish_during_the_attempt($slot), ]);
        return $output;
    }

    /**
     * Displays one question with the surrounding controls.
     *
     * @param structure $structure object containing the structure of the quiz.
     * @param int $slot the first slot on the page we are outputting.
     * @param \core_question\local\bank\question_edit_contexts $contexts the relevant question bank contexts.
     * @param array $pagevars the variables from {@see \question_edit_setup()}.
     * @param \moodle_url $pageurl the canonical URL of this page.
     * @return string HTML to output.
     */
    public function page_row(structure $structure, $slot, $contexts, $pagevars, $pageurl) {
        $output = '';
        $pagenumber = $structure->get_page_number_for_slot($slot);

        // Put page in a heading for accessibility and styling.
        $page = $this->heading(get_string('page') . ' ' . $pagenumber, 4);

        if ($structure->is_first_slot_on_page($slot)) {
            $output .= html_writer::tag('li', $page . ' | ',
            ['class' => 'pagenumber activity yui3-dd-drop page timed', 'id' => 'page-' . $pagenumber]);
        }

        return $output;
    }

    /**
     * Display a question.
     *
     * @param structure $structure object containing the structure of the quiz.
     * @param int $slot the first slot on the page we are outputting.
     * @param \moodle_url $pageurl the canonical URL of this page.
     * @return string HTML to output.
     */
    public function question(structure $structure, int $slot, \moodle_url $pageurl) {
        // Get the data required by the question_slot template.
        $slotid = $structure->get_slot_id_for_slot($slot);

        $output = '';
        $output .= html_writer::start_tag('div');
        $data = [
            'slotid' => $slotid,
            'questionnumber' => $this->question_number($structure->get_displayed_number_for_slot($slot)),
            'questionname' => $this->get_question_name_for_slot($structure, $slot, $pageurl),
        ];
        // Render the question slot template.
        $output .= $this->render_from_template('quizaccess_quiztimer/question_slot', $data);

        $output .= html_writer::end_tag('div');

        return $output;
    }

    /**
     * Get the question name for the slot.
     *
     * @param structure $structure object containing the structure of the quiz.
     * @param int $slot the slot on the page we are outputting.
     * @param \moodle_url $pageurl the canonical URL of this page.
     * @return string HTML to output.
     */
    public function get_question_name_for_slot(structure $structure, int $slot, \moodle_url $pageurl): string {
        // Display the link to the question (or do nothing if question has no url).
        if ($structure->get_question_type_for_slot($slot) === 'random') {
            $questionname = $this->random_question($structure, $slot, $pageurl);
        } else {
            $questionname = $this->question_name($structure, $slot, $pageurl);
        }

        return $questionname;
    }

    /**
     * Renders html to display a random question the link to edit the configuration
     * and also to see that category in the question bank.
     *
     * @param structure $structure object containing the structure of the quiz.
     * @param int $slotnumber which slot we are outputting.
     * @param \moodle_url $pageurl the canonical URL of this page.
     * @return string HTML to output.
     */
    public function random_question(structure $structure, $slotnumber, $pageurl) {
        $question = $structure->get_question_in_slot($slotnumber);
        $slot = $structure->get_slot_by_number($slotnumber);
        $editurl = new \moodle_url('/mod/quiz/editrandom.php',
                ['returnurl' => $pageurl->out_as_local_url(), 'slotid' => $slot->id]);

        $temp = clone($question);
        $temp->questiontext = '';
        $temp->name = qbank_helper::describe_random_question($slot);
        $instancename = quiz_question_tostring($temp);

        $configuretitle = get_string('configurerandomquestion', 'quiz');
        $qtype = \question_bank::get_qtype($question->qtype, false);
        $namestr = $qtype->local_name();
        $icon = $this->pix_icon('icon', $namestr, $qtype->plugin_name(), ['title' => $namestr,
                'class' => 'icon activityicon', 'alt' => ' ', 'role' => 'presentation', ]);

        $editicon = $this->pix_icon('t/edit', $configuretitle, 'moodle', ['title' => '']);
        $qbankurlparams = [
            'cmid' => $structure->get_cmid(),
            'cat' => $slot->category . ',' . $slot->contextid,
            'recurse' => $slot->randomrecurse,
        ];

        $slottags = [];
        if (isset($slot->randomtags)) {
            $slottags = $slot->randomtags;
        }
        foreach ($slottags as $index => $slottag) {
            $slottag = explode(',', $slottag);
            $qbankurlparams["qtagids[{$index}]"] = $slottag[0];
        }

        // If this is a random question, display a link to show the questions
        // selected from in the question bank.
        $qbankurl = new \moodle_url('/question/edit.php', $qbankurlparams);
        $qbanklink = ' ' . \html_writer::link($qbankurl,
                        get_string('seequestions', 'quiz'), ['class' => 'mod_quiz_random_qbank_link']);

        return html_writer::link($editurl, $icon . $editicon, ['title' => $configuretitle]) .
                ' ' . $instancename . ' ' . $qbanklink;
    }

    /**
     * Get the action icons render.
     *
     * @param structure $structure object containing the structure of the quiz.
     * @param int $slot the slot on the page we are outputting.
     * @param \moodle_url $pageurl the canonical URL of this page.
     * @return string HTML to output.
     */
    public function get_action_icon(structure $structure, int $slot, \moodle_url $pageurl): string {
        // Action icons.
        $qtype = $structure->get_question_type_for_slot($slot);
        $questionicons = '';
        if ($qtype !== 'random') {
            $questionicons .= $this->question_preview_icon($structure->get_quiz(),
                    $structure->get_question_in_slot($slot),
                    null, null, $qtype);
        }
        if ($structure->can_be_edited() && $structure->has_use_capability($slot)) {
            $questionicons .= $this->question_remove_icon($structure, $slot, $pageurl);
        }
        $questionicons .= $this->marked_out_of_field($structure, $slot);

        return $questionicons;
    }

    /**
     * Output the question number.
     * @param string $number The number, or 'i'.
     * @return string HTML to output.
     */
    public function question_number($number) {
        if (is_numeric($number)) {
            $number = html_writer::span(get_string('question'), 'accesshide') . ' ' . $number;
        }
        return html_writer::tag('span', $number, ['class' => 'slotnumber']);
    }

    /**
     * Renders html to display a name with the link to the question on a quiz edit page
     *
     * If the user does not have permission to edi the question, it is rendered
     * without a link
     *
     * @param structure $structure object containing the structure of the quiz.
     * @param int $slot which slot we are outputting.
     * @param \moodle_url $pageurl the canonical URL of this page.
     * @return string HTML to output.
     */
    public function question_name(structure $structure, $slot, $pageurl) {
        $output = '';

        $question = $structure->get_question_in_slot($slot);
        $editurl = new \moodle_url('/question/bank/editquestion/question.php', [
                'returnurl' => $pageurl->out_as_local_url(),
                'cmid' => $structure->get_cmid(), 'id' => $question->questionid, ]);

        $instancename = quiz_question_tostring($question);

        $qtype = \question_bank::get_qtype($question->qtype, false);
        $namestr = $qtype->local_name();

        $icon = $this->pix_icon('icon', $namestr, $qtype->plugin_name(), ['title' => $namestr,
                'class' => 'activityicon', 'alt' => ' ', 'role' => 'presentation', ]);

        $activitylink = $icon . html_writer::tag('span', $instancename, ['class' => 'instancename']);
        $output .= $activitylink;

        return $output;
    }


    /**
     * Returns a message if the quiz has attemps, warning the user about not being able to modify quiz times or mode.
     *
     * @param datatype $quizobj quiz object
     * @return array
     */
    public function get_edittimes_page_warnings($quizobj) {
        $warnings = [];

        if (quiz_has_attempts($quizobj->get_quizid())) {
            $reviewlink = $this->page->get_renderer('mod_quiz')->quiz_attempt_summary_link_to_reports($quizobj->get_quiz(),
                    $quizobj->get_cm(), $quizobj->get_context());
            $warnings[] = get_string('canteditquiztimes', 'quizaccess_quiztimer', $reviewlink);
        }
        return $warnings;
    }

}
