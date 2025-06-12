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

$string['pluginname'] = 'Quiztimer access rules';


$string['subtimes'] = 'Time limit to use';
$string['subtimes_help'] = 'The dropdown offers three options to configure the quiz timer (Section, Question, Total)';


// Other strings.
$string['totaltime'] = 'Total Time';
$string['minutes'] = 'minutes';
$string['submit'] = 'Submit';

$string['quiztime'] = 'Adjust questions times';
$string['timeunit'] = '...';
$string['totalsectiontime'] = 'Total section time:';
$string['hours'] = 'hours';
$string['minutes'] = 'minutes';
$string['seconds'] = 'seconds';
$string['distributesectiontime'] = 'Distribute the section time in pages';
$string['timelimit'] = 'Time limit';
$string['sectiontime'] = 'Time for section';
$string['pagetime'] = 'Time for page';
$string['questiontime'] = 'Time for question';

$string['setting:timedsections'] = 'Section default time';
$string['setting:timedsections_desc'] = 'The defualt time assigned to the created sections';
$string['setting:timedslots'] = 'Slot default time';
$string['setting:timedslots_desc'] = 'The defualt time assigned to the created slots';
$string['unitsections'] = 'Sections unit used';
$string['unitslots'] = 'Slots unit used';

$string['quiztimer'] = 'Quiz times message zone:';
$string['requirequiztimermessage'] = 'This quiz uses a custom time limit for questions and sections.';
$string['quizquiztimer'] = 'Quiz timer';
$string['quiztimererrors'] = 'Quiz setted timers that need attention to allow the quiz execution:';
$string['warningtime'] = 'Invalid time setted';
$string['invalidsettedtime'] = 'Tiempo introducido invalido, se ha restablecido al valor anterior';

$string['canteditquiztimes'] = 'You cannot edit questions times because this quiz has been attempted. ({$a})';
$string['eventslottimerupdated'] = 'Slot timer updated';
$string['eventsectiontimerupdated'] = 'Section timer updated';
$string['quiztimer:manage'] = 'Manage quiztimer time settings';

$string['eventslottimerupdateddescription'] = 'User with id {$a->userid} updated slot timer with id {$a->slot} with a new time of {$a->timevalue} {$a->timeunit}';
$string['eventsectiontimerupdateddescription'] = 'User with id {$a->userid} updated section timer with id {$a->section} with a new time of {$a->timevalue} {$a->timeunit}';

$string['timelimitedit'] = 'Time limit (no use of custom times)';
$string['selecttypetimes'] = 'Select a time type to use customized quiz times';

$string['repaginatewarning'] = 'Selecting question or sections edit type may cause the quiz slots to repaginate as follows:
- Time for section: All questions in one page for each section.
- Time for question: 1 question per page.
Do you still wish to continue with the operation?';

$string['pagingchangesnotapply'] = 'This quiz has a custom timer selected using slots or sections times, your changes made to the questions pagination will not be applied, getting overwrited';
$string['canteditquiztype'] = 'You cannot edit quiz times type because this quiz has been attempted.';
$string['disabledbycustomtimer'] = 'Fixed because of the custom timer selected in timing section.';

$string['configsavedsection'] = 'Configuration saved successfully. <br> <b>Remember to change the section times.</b>';
$string['configsavedquestion'] = 'Configuration saved successfully. <br> <b>Remember to change the question times.</b>';
$string['configsavedpage'] = 'Configuration saved successfully. <br> <b>Remember to change the page times.</b>';



$string['privacy:metadata:quiz'] = 'The quiz to which this timer configuration belongs.';
$string['privacy:metadata:quiz_mode'] = 'The timer mode used in the quiz.';
$string['privacy:metadata:usermodified'] = 'The ID of the user who modified the timer settings.';
$string['privacy:metadata:timecreated'] = 'The time when the timer settings were created.';
$string['privacy:metadata:timemodified'] = 'The time when the timer settings were last modified.';

$string['privacy:metadata:quizid'] = 'The quiz associated with the user timer data.';
$string['privacy:metadata:slot'] = 'The slot (question position) to which the timing data refers.';
$string['privacy:metadata:section'] = 'The section of the quiz to which the timing data refers.';
$string['privacy:metadata:userid'] = 'The ID of the user whose timing data is recorded.';
$string['privacy:metadata:attempt'] = 'The attempt number related to the timing data.';
$string['privacy:metadata:timestart'] = 'The start time recorded for this slot or section.';
$string['privacy:metadata:timefinish'] = 'The end time recorded for this slot or section.';

$string['privacy:metadata:quizaccess_quiztimer'] = 'Configuration data for the quiz timer.';
$string['privacy:metadata:quizaccess_usertimedslots'] = 'User timing data per question.';
$string['privacy:metadata:quizaccess_usertimedsections'] = 'User timing data per section.';
