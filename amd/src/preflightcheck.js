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
define(['jquery', 'core/str', 'core/notification'], function($, str, notification) {

    const change_quiz_form = (e) => {
        target = e.currentTarget;
        let va = target.value;
        va = target.webroot + va;
        window.location.href = va;
    }
    selectstrings = str.get_strings([{key: 'questions', component: 'quiz'},
                                {key: 'quiztime', component: 'quizaccess_quiztimer'},
                                {key: 'pagingchangesnotapply', component: 'quizaccess_quiztimer'}, ]);
        return {
            init: function(cmid, editmethod, webroot) {
                $(document).ready(function() {
                    if ($('.custom-select.urlselect.timeselect')[0] !=undefined ) {
                        $.when(selectstrings).done(function(selectstrings) {
                            select = $('.custom-select.urlselect.timeselect')[0];
                            select.add(new Option(selectstrings[1], '/mod/quiz/accessrule/quiztimer/edit.php?cmid=' + cmid +
                            '&editmethod=time'));
                        });

                    } else {
                        $.when(selectstrings).done(function(selectstrings) {
                            select = document.createElement('select');
                            select.setAttribute('class', 'custom-select urlselect timeselect');
                            select.setAttribute('id', 'id_quiztimer_editviewselector');

                            if (editmethod == 'time') {
                                select.add(new Option(selectstrings[1], '/mod/quiz/accessrule/quiztimer/edit.php?cmid=' + cmid +
                                 ''));
                                select.add(new Option(selectstrings[0], '/mod/quiz/edit.php?cmid=' + cmid + '') );

                            } else {
                                select.add(new Option(selectstrings[0], '/mod/quiz/edit.php?cmid=' + cmid + '') );
                                select.add(new Option(selectstrings[1], '/mod/quiz/accessrule/quiztimer/edit.php?cmid=' + cmid +
                                 '&editmethod=time'));
                                 if (editmethod == 'section' || editmethod == 'slots') {
                                    notification.addNotification({
                                        message: selectstrings[2],
                                        type: "warning"
                                     });
                                     let pagebreaks = document.querySelectorAll('.page_split_join');
                                     pagebreaks.forEach(pagebreak => {
                                        pagebreak.addEventListener('click', function() {
                                            alert(selectstrings[2]);
                                        })
                                     });
                                }
                            }
                            let qactions = $('.mod_quiz-edit-action-buttons')[0];
                            qactions == null ? $('.activity-header')[0].append(select) : qactions.append(select);
                            select.webroot = webroot;
                            select.addEventListener('change', change_quiz_form, true);
                        })
                    }
                });
            }
        }
});