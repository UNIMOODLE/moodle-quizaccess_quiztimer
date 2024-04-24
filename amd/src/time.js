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
/**
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, ajax, notification, str) {

    const urlparams = new URLSearchParams(window.location.search);
    const cmid = urlparams.get('cmid');

    const selectstrings = str.get_strings([{key: 'sectiontime', component: 'quizaccess_quiztimer'},
                                             {key: 'questiontime', component: 'quizaccess_quiztimer'},
                                             {key: 'distributesectiontime', component: 'quizaccess_quiztimer'},
                                             {key: 'timelimitedit', component: 'quizaccess_quiztimer'},]);
    const unitsstrings = str.get_strings([{key: 'seconds', component: 'quizaccess_quiztimer'},
                                             {key: 'minutes', component: 'quizaccess_quiztimer'},
                                             {key: 'hours', component: 'quizaccess_quiztimer'},]);
    const repaginatewarning = str.get_string('repaginatewarning', 'quizaccess_quiztimer');
    /**
     * When a key is pressed, editing a question time,
     * checks what it should do, and displays the question
     * edit, removing the input of time edit.
     * If necesary, inserts the time of the question in the db.
     *
     * @param {event} e
     */
    const questiontime = async function(e) {
        const invalidsettedtime = await str.get_string('invalidsettedtime', 'quizaccess_quiztimer');
        let target = e.currentTarget;
        if (e.key === 'Enter') {
            let timevalue = target.value;
            target.value = timevalue.replace(/[^0-9].*$/,'');
            timevalue = target.value;
            let timedisplay = target.closest('.time').querySelector('.question-time');
            if(timevalue == '') {
                notification.addNotification({
                    message: invalidsettedtime,
                    type: "error"
                 });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else if (timevalue == 0) {
                notification.addNotification({
                    message: invalidsettedtime,
                    type: "error"
                 });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                timedisplay.innerHTML = timevalue;
            }
            timedisplay.removeAttribute('style');

            let timeid = timedisplay.getAttribute('id') + '-edit';
            $('#' + timeid).remove();
            set_question_time_call(timedisplay);
        } else if (e.key === 'Escape') {
            let timedisplay = target.closest('.time').querySelector('.question-time');
            timedisplay.removeAttribute('style');
            this.remove();
        }
    };

    /**
     * When a key is pressed, editing a section time,
     * checks what it should do, and displays the section
     * edit, removing the input of time edit.
     * If necesary, inserts the time of the section in the db.
     *
     * @param {event} e
     */
    const sectiontime = async function(e) {
        const invalidsettedtime = await str.get_string('invalidsettedtime', 'quizaccess_quiztimer');
        let target = e.currentTarget;
        if (e.key === 'Enter') {
            target.value = target.value.replace(/[^0-9].*$/,'');
            let timevalue = target.value;
            let timedisplay = target.closest('.section-heading').querySelector('.section-time');
            timedisplay.innerHTML = timevalue;
            if(timevalue == '') {
                timedisplay.innerHTML = 0;
                notification.addNotification({
                    message: invalidsettedtime,
                    type: "error"
                 });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else if (timevalue == 0) {
                notification.addNotification({
                    message: invalidsettedtime,
                    type: "error"
                 });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            timedisplay.removeAttribute('style');
            let timeid = timedisplay.getAttribute('id') + '-edit';
            $('#' + timeid).remove();
            let timedata = get_section_time_from_slots(timedisplay.closest('.section-heading'));
            set_section_time_call(timedisplay.closest('.section.main.clearfix'),
                parseFloat(timedata.unit), parseFloat(timedata.value));
        } else if (e.key === 'Escape') {
            let timedisplay = target.closest('.section-heading').querySelector('.section-time');
            timedisplay.removeAttribute('style');
            this.remove();
        }
    };

    /**
     * Adjust the question time to display, when a new
     * unit time is selected, checking beforehand if
     * the unit is valid.
     *
     * @param {event} e
     */
    const adjust_question_time_display = function (e) {
        let selectedoption = e.currentTarget;
        value = selectedoption.value;
        let timedata = get_question_time(selectedoption.closest('.slot'));
        let value = parseFloat(timedata.value);
        if (value !== 0 && !isNaN(value)) {
            selectedoption.options[0].setAttribute('disabled', 'true');
            set_question_time_call(selectedoption);
        }
    };

    /**
     * Adjust the section time to display, when a new
     * unit time is selected, checking beforehand if
     * the unit is valid.
     *
     * @param {event} e
     */
    const adjust_section_time_display = function (e) {
        let selectedoption = e.currentTarget;
        let section = selectedoption.closest('.section.main.clearfix');
        let unit = selectedoption.value;
        let value = parseFloat(section.querySelector('.section-time').innerHTML);
        if ((value !== 0 && !isNaN(value)) && (unit !== '0' && !isNaN(unit))) {
            selectedoption.options[0].setAttribute('disabled', 'true');
            unit = parseFloat(section.querySelector('.time-select').options[unit].value);
            set_section_time_call(section, unit, value);
        }
    };

    /**
     * Gets a question time from the page inputs.
     *
     * @param {event} question
     * @returns JSON string containing the question time.
     */
        const get_question_time = function(question) {
            let unit = question.querySelector('.time-select').value;
            let timevalue = question.querySelector('.question-time').innerHTML;
            timevalue = get_time_in_seconds(parseFloat(unit), parseFloat(timevalue));
            return {'unit' : unit, 'value' : timevalue};
        };

    /**
     * Gets a section time from the slots time.
     *
     * @param {event} section
     * @returns JSON string containing the secton time.
     */
    const get_section_time_from_slots = function(section) {
        let unit = section.querySelector('.time-select').value;
        let timevalue = section.querySelector('.section-time').innerHTML;
        return { 'unit' : unit ,'value' : timevalue};
    };

    /**
     * Gets the id of a section.
     *
     * @param {htmlElement} section
     * @returns JSON string with the section id
     */
    const get_section_id = function(section) {
        let id = section.id;
        let sectionid = id.substring(id.indexOf('-') + 1);
        return { 'sectionid':sectionid};
    };
    /**
     * Gets the id of a question
     *
     * @param {htmlElement} question
     * @returns JSON string containing the numeric part of the question id.
     */
    const get_question_id = function(question) {
        let id = question.id;
        let slotid = id.substring(id.indexOf('-') + 1);
        return { 'questionid':slotid};
    };

    /**
     * When the edit question event is triggered,
     * creates a input for editing the time, and hides
     * the display time.
     * Also assigns events when a key is pressed on the newly
     * created input, aswell as a blur event.
     *
     * @param {event} e
     */
    const edit_question_time = function(e) {
        let time = e.currentTarget.closest('.time');
        let timevalue = time.querySelector('.question-time');
        let timeid = timevalue.getAttribute('id') + '-edit';
        $('<input></input>')
            .attr({
                'type': 'text',
                'name': 'time',
                'id': timeid,
                'size': '5',
                'value': timevalue.innerHTML
            })
            .prependTo(time);
        timevalue.setAttribute('style', 'display:none');
        $('#' + timeid).focus();
        $('#' + timeid).on('keydown', this, questiontime);
        $('#' + timeid).on('blur', this, function() {
            this.closest('.time').querySelector('.question-time').removeAttribute('style');
            this.remove();
        });
    };

    /**
     * When the edit section event is triggered,
     * creates a input for editing the time, and hides
     * the display time.
     * Also assigns events when a key is pressed on the newly
     * created input, aswell as a blur event.
     *
     * @param {event} e
     */
    const edit_section_time = function(e) {
        let time = e.currentTarget.closest('.time');
        let timevalue = time.querySelector('.section-time');
        let timeid = timevalue.getAttribute('id') + '-edit';
        $('<input></input>')
            .attr({
                'type': 'text',
                'name': 'time',
                'id': timeid,
                'size': '5',
                'value': timevalue.innerHTML,
            })
            .prependTo(time);
        timevalue.setAttribute('style', 'display:none');
        $('#' + timeid).focus();
        $('#' + timeid).on('keydown', this, sectiontime);
        $('#' + timeid).on('blur', this, function() {
            this.closest('.section-heading').querySelector('.section-time').removeAttribute('style');
            this.remove();
        });
    };

    /**
     * Triggered at changing the edit format used,
     * sets a new url based on the option picked,
     * then it redirects the user to the new url.
     *
     * @param {event} e
     */
    const change_time_edit_method = function(e) {
        $.when(repaginatewarning).done( repaginatestring => {
            if (confirm(repaginatestring)) {
                let editmethod = e.currentTarget.value;
                let url = location.href;
                let k = url.search('&edittype=');
                if (k != -1) {
                    let param = url.substring(k);
                    let editmethod2 = param.split('=')[1];
                    url = url.replace(editmethod2, editmethod);
                } else {
                    url += '&edittype=' + editmethod;
                }
                url = url.replace('#', '');
                get_quiz_id(cmid).then(response => {
                    let quizid = JSON.parse(response).quizid;
                    repaginate_slots(quizid,editmethod).done( () => {
                        window.location.href = url;
                    });
                });
            }
        });
    };

    /**
     * Loads a section time based on the questions times
     * of the section, and their unit selected.
     *
     * @param {htmlElement} section
     *
     */
    const load_section_time = (section) => {
        let questions = section.querySelectorAll('.slot');
        let totaltime = get_total_time_of_questions(questions);
        $.when(unitsstrings).done( unitsstrings => {
            totaltime = format_pagetime(totaltime, unitsstrings[0], unitsstrings[1], unitsstrings[2]);
        });
        section.querySelector('.total-section-time').innerHTML = totaltime;
        if (parseFloat(section.querySelector('.time-select').value) === 0) {
            section.querySelector('.total-section-unit').value = 1;
        }
    };

    const repaginate_slots = (quizid, editmethod) => ajax.call([{
        methodname: 'quizaccess_quiztimer_repaginate_slots',
        args: {
            quizid,
            editmethod,
        },
    }])[0].done( response => {
        return response;
    }).fail( err => {
        // eslint-disable-next-line no-console
        console.log(err);
    });

    /**
     * Sets a section time with an ajax call, saving the time
     * information on the sections db.
     *
     * @param {htmlElement} section
     * @param {int} unit
     * @param {int} value
     */
    const set_section_time_call = (section, unit, value) => {
        value = get_time_in_seconds(unit, value);
        let timedata = {'unit' : unit, 'value' : value};
            get_quiz_id(cmid).then( response => {
                let quizid = JSON.parse(response).quizid;
                let sectionid = get_section_id(section).sectionid;
                set_section_time(quizid, sectionid, timedata).then( () => {
                    get_section_time_call(section);
                    let timetype = document.querySelector('input[name="timetype"]');
                    if (timetype.value == 'equitative') {
                        get_page_time_from_section(section);
                    }
                });
        });
    };

    /**
     * Gets a section time from the db
     * if the section dont have a time, sets it
     * visually to 0 and no unit, without inserting in db.
     *
     * @param {htmlElement} section
     */
    const get_section_time_call = async (section) => {
        const warningtimestr = await str.get_string('warningtime', 'quizaccess_quiztimer');
        get_quiz_id(cmid).then( response => {
            let quizid = JSON.parse(response).quizid;
            let sectionid = get_section_id(section).sectionid;
            get_section_time(quizid, sectionid).then(response => {
                let timedata = JSON.parse(response);
                if(!timedata) {
                    section.querySelector('.time-select').value = 2;
                    section.querySelector('.total-section-time').innerHTML = '';
                    section.querySelector('.total-section-unit').innerHTML = '';
                    section.querySelector('.section-time').innerHTML = 0;
                }
                let unit = timedata.timeunit;
                let value = get_time_in_unit(parseFloat(unit), parseFloat(timedata.timevalue));
                if (value === 0) {
                    section.querySelector('.time-select').value = 2;
                    if (!section.querySelector('.warningtime')) {
                        let warningtime = document.createElement('span');
                        warningtime.setAttribute('class', 'text-danger warningtime');
                        warningtime.append(warningtimestr);
                        section.querySelector('.section-heading').appendChild(warningtime);
                    }
                } else {
                    section.querySelector('.time-select').value = unit;
                    if (section.querySelector('.warningtime')) {
                        section.querySelector('.section-heading').removeChild(section.querySelector('.warningtime'));
                    }
                }
                section.querySelector('.total-section-time').innerHTML = value;
                section.querySelector('.total-section-unit').innerHTML = section.querySelector('.time-select').options[unit].text;
                section.querySelector('.section-time').innerHTML = value;
                display_quiz_time('section');
                quiz_has_attemps();
            });
        });
    };

    /**
     * Gets the total amount of time of the selected questions
     * and returns it in seconds.
     *
     * @param {array} questions
     * @returns int total questions time in seconds.
     */
    const get_total_time_of_questions = (questions) => {
        let totaltime = 0;
        for(let t = 0; t < questions.length; t ++) {
            let timeunit = parseInt(questions[t].querySelector('.time-select').value);
            let time = parseFloat(questions[t].querySelector('.question-time').innerHTML);
            let timeinseconds = get_time_in_seconds(timeunit, time);
            totaltime += timeinseconds;
        }
        return totaltime;
    };


    /**
     * Given a page, if it exist, updates the displayed time
     * else creates the page elements for time control.
     *
     * @param {htmlElement} page
     */
    const load_page_time = (page) => {

        if (page.querySelector('.pagetime')) {
            let pagetime = page.querySelector('.pagetime');
            let time = pagetime.querySelector('.total-page-time');
            let unit = pagetime.querySelector('.total-page-unit');
            let question = $(page).nextUntil('.pagenumber.activity.timed');
            let totaltime = get_total_time_of_questions(question);
            let pagetimeunit = parseFloat(question[0].querySelector('.time-select').value);
            totaltime = (get_time_in_unit(pagetimeunit, totaltime));
            time.innerHTML = totaltime;
            unit.innerHTML = question[0].querySelector('.time-select').options[pagetimeunit].innerHTML;
        } else {
            create_timed_page_elements(page);
        }
    };

    /**
     * Generates all the html elements to edit a page time[TODO],
     * the time of the page, based on the containing slot,
     * aswell as assign them the properties to modify the time.
     *
     * @param {htmlElement} page
     */
    const create_timed_page_elements = (page) => {
        let id = page.getAttribute('id');
        let question = $('#' + id).nextUntil('.pagenumber.activity.timed');
        let totaltime = get_total_time_of_questions(question);
        let pagetimeunit = parseFloat(question[0].querySelector('.time-select').value);
        totaltime = (get_time_in_unit(pagetimeunit, totaltime));
        let pagetime = document.createElement('span');
        pagetime.setAttribute('class', 'pagetime');
        page.append(pagetime);

        let pagevalue = document.createElement('span');
        pagevalue.setAttribute('class', 'total-page-time');
        pagevalue.innerHTML = totaltime;
        pagetime.append(pagevalue);

        let pageunit = document.createElement('span');
        pageunit.setAttribute('class', 'total-page-unit');
        pagetime.append(' ');
        pagetime.append(pageunit);
    };

    /**
     * Loads tbe page time
     *
     * @param {htmlElement} page
     */
    const get_page_time_from_section = (page) => {
        var pageloadedsections = [];
        let section = page.closest('.section.main.clearfix');
        if (!pageloadedsections.includes(section.id)) {
            pageloadedsections.push(section.id);
            let sectionpages = section.querySelectorAll('.pagenumber');
            let sectionid = get_section_id(section).sectionid;
            get_quiz_id(cmid).then( response => {
                let quizid = JSON.parse(response).quizid;
                get_section_time(quizid, sectionid).then( response => {
                    let totalsectime = JSON.parse(response).timevalue;
                    let pagetime = totalsectime / sectionpages.length;
                    $.when(unitsstrings).done( unitsstrings => {
                        pagetime = format_pagetime(pagetime, unitsstrings[0], unitsstrings[1], unitsstrings[2]);
                        for (let t = 0; t < sectionpages.length; t ++) {
                            sectionpages[t].querySelector('.total-page-time').innerHTML = pagetime;
                        }
                    });
                });
            });
        }
    };

    /**
     * Formats the time in seconds, and gets a
     * string to display the units using moodle lang strings.
     *
     * @param {int} time
     * @param {string} seconds
     * @param {string} minutes
     * @param {string} hours
     * @returns {string} formatted time
     */
    const format_pagetime = (time, seconds, minutes, hours) => {
        let pagetime = '';
        if (time / 3600 >= 1) {
            let h = Math.floor(time / 3600);
            if (time % 3600 !== 0) {
                if (time % 3600 >= 60) {
                    if ((time % 3600) % 60 !== 0 ) {
                        let m = Math.floor((time % 3600) / 60);
                        let s = (time % 3600) % 60;
                        pagetime = h + ' ' + hours + ' ' + m + ' ' + minutes + ' ' + s + ' ' + seconds;
                        return pagetime;
                    }
                    let m = (time % 3600) / 60;
                    pagetime = h + ' ' + hours + ' ' + m + ' ' + minutes;
                    return pagetime;
                }
                let s = (time % 3600);
                pagetime = h + ' ' + hours + ' ' + s + ' ' + seconds;
                return pagetime;
            }
            pagetime = h + ' ' + hours;
            return pagetime;
        } else if (time / 60 >= 1) {
            let m = Math.floor(time / 60);
            if (time % 60 !== 0) {
                let s = +(time % 60).toFixed(2);
                pagetime = m + ' ' + minutes + ' ' + s + ' ' + seconds;
                return pagetime;
            }
            pagetime = m + ' ' + minutes;
            return pagetime;
        } else {
            pagetime = +time.toFixed(2) + ' ' + seconds;
            return pagetime;
        }
    };

    /**
     * Gets the time to set in seconds for the insert
     * in the database.
     *
     * @param {int} timeunit
     * @param {int} time
     * @returns the time to insert in the database.
     */
    const get_time_in_seconds = (timeunit, time) => {
        let timeinseconds = 0;
        switch (timeunit) {
            case 1:
                timeinseconds = time;
                break;
            case 2:
                timeinseconds = time * 60;
                break;
            case 3:
                timeinseconds = time * 3600;
                break;
            default:
                timeinseconds = 0;
                break;

        }
        return timeinseconds;
    };

    /**
     * Gets the time in the unit requested
     * to show.
     *
     * @param {int} timeunit
     * @param {int} time
     * @returns the time in the selected unit.
     */
    const get_time_in_unit = (timeunit, time) => {
        let sectiontimeinunit = 0;
        switch (timeunit) {
            case 1:
                sectiontimeinunit = time;
                break;
            case 2:
                sectiontimeinunit = time / 60;
                break;
            case 3:
                sectiontimeinunit = time / 3600;
                break;
            default:
                sectiontimeinunit = 0;
                break;

        }
        return parseFloat(sectiontimeinunit.toFixed(2));
    };

    /**
     * Displays and updates the total quiz time.
     *
     * @param {string} timetype
     */
    const display_quiz_time = (timetype) => {
        let quiztimer = document.querySelector('.quiztimer-time');
        $.when(unitsstrings).done( unitsstrings => {
            get_quiz_id(cmid).then( response => {
                let quizid = JSON.parse(response).quizid;
                get_quiz_time(quizid, timetype).then( r => {
                    let time = format_pagetime(JSON.parse(r).time,unitsstrings[0], unitsstrings[1], unitsstrings[2]);
                    if (quiztimer.innerHTML == '') {
                        quiztimer.append(' | ' + time);
                    } else {
                        quiztimer.innerHTML = ' | ' + time;
                    }
                });
            });
        });
    };

    /**
     * Returns the quiz time.
     *
     * @param {int} quizid
     * @param {string} editmethod
     * @returns
     */
    const get_quiz_time = (quizid, editmethod) => ajax.call([{
        methodname: 'quizaccess_quiztimer_get_quiz_time',
        args: {
            quizid,
            editmethod,
        },
    }])[0].done( response => {
        return response;
    }).fail( err => {
        // eslint-disable-next-line no-console
        console.log(err);
    });

    /**
     * Sets a section time with an ajax call, updating or inserting
     * the section with the new timedata.
     *
     * @param {int} quizid
     * @param {int} sectionid
     * @param {JSON} timedata
     * @returns JSON string with the modified section data in db.
     */
    const set_section_time = (quizid, sectionid, timedata) => ajax.call([{
        methodname: 'quizaccess_quiztimer_set_section_time',
        args: {
            quizid,
            sectionid,
            timedata: JSON.stringify(timedata),
        },
    }])[0].done( response => {
        return response;
    }).fail( err => {
        // eslint-disable-next-line no-console
        console.log(err);
    });

    /**
     * Gets a section time info
     * with an ajax call to the sections time db.
     *
     * @param {int} quizid
     * @param {int} sectionid
     * @returns JSON string with the section time.
     */
    const get_section_time = (quizid, sectionid) => ajax.call([{
        methodname: 'quizaccess_quiztimer_get_section_time',
        args: {
            quizid,
            sectionid,
        },
    }])[0].done( response => {
        return response;
    }).fail( err => {
        // eslint-disable-next-line no-console
        console.log(err);
    });

    /**
     * Calls the ajax function to set a slot time and
     * updates the page and section time display to reflect
     * their time adjusted with the new question time set.
     *
     * @param {htmlElement} slot
     * @returns JSON string with the modified slot info.
     */
    const set_question_time_call = async(slot) => {
        let timedata = get_question_time(slot.closest('.slot'));
        if (timedata.unit != 0) {
            let question = slot.closest('[id^="slot"]');
            let questionid = get_question_id(question);
            if (timedata.value != 0) {
                if (question.querySelector('.warningtime')) {
                    question.querySelector('.activityinstance').removeChild(question.querySelector('.warningtime'));
                }
            }
            get_quiz_id(cmid).then( response => {
                let quizid = JSON.parse(response);
                set_question_time(quizid.quizid, questionid.questionid, timedata).then(response => {
                    let page = get_page_from_slotid(JSON.parse(response).slot);
                    load_page_time(page);
                    load_section_time(question.closest('.section.main.clearfix'));
                    display_quiz_time('slots');
                });
            });
        }
    };
    /**
     *
     * @param {int} quizid
     * @param {int} questionid
     * @param {array} timedata
     * @returns response with the modified slot information.
     */
    const set_question_time = (quizid, questionid, timedata) => ajax.call([{
        methodname: 'quizaccess_quiztimer_set_question_time',
        args: {
            quizid,
            questionid,
            timedata: JSON.stringify(timedata),
        },
    }])[0].done( response => {
        return response;
    }).fail( err => {
        // eslint-disable-next-line no-console
        console.log(err);
    });

    /**
     *
     * @param {int} cmid
     * @returns ajax response with the quizid
     */
    const get_quiz_id = (cmid) => ajax.call([{
        methodname: 'quizaccess_quiztimer_get_quiz_id',
        args: {
            cmid,
        },
    }])[0].done(function(response) {
        return response;
    }).fail(function(err) {
        // eslint-disable-next-line no-console
        console.log(err);
    });

    /**
     *
     * @param {int} questionid
     * @returns
     */
    const load_question_time = (questionid) => ajax.call([{
        methodname: 'quizaccess_quiztimer_get_question_time',
        args: {
            questionid,
        },
    }])[0].done( response => {
        return response;
    }).fail( err => {
        // eslint-disable-next-line no-console
        console.log(err);
    });

    /**
     *
     * @param {htmlElement} slot
     * @returns the page the slot belongs to.
     */
    const get_page_from_slotid = (slot) => {
        let slotid = 'slot-' + slot;
        let page = ($('#' + slotid).prev('.pagenumber.timed')[0]);
        return(page);
    };

    const quiz_has_attemps = () => {
        let quizhasattemps = document.querySelector('.statusdisplay');
        if (quizhasattemps !== null) {
            document.querySelector('#id_quiztimer_quizmodeselector').setAttribute('disabled', true);
            let mainquizslots = document.querySelector('.slots');
            let selectors = mainquizslots.querySelectorAll('select');
            let pencils = mainquizslots.querySelectorAll('.fa-pencil');
            selectors.forEach(selector => {
                selector.setAttribute('disabled', true);
            });
            pencils.forEach(pencil => {
                pencil.setAttribute('style', 'display:none;');
            });
        }
    };

    return {
        init: async function(timetype = 'timelimit') {
            const warningtimestr = await str.get_string('warningtime', 'quizaccess_quiztimer');
            const noquestions = await str.get_string('noquestions', 'mod_quiz');
            const selectatimetype = await str.get_string('selecttypetimes', 'quizaccess_quiztimer');
            $(document).ready(function() {
                let navitem = $('.activity-header')[0];
                $.when(selectstrings).done( selectstrings => {
                    let select = document.createElement('select');
                    select.setAttribute('class', 'custom-select urlselect timeselect');
                    select.setAttribute('id', 'id_quiztimer_quizmodeselector');
                    select.add(new Option(selectstrings[3], 'timelimit'));
                    select.add(new Option(selectstrings[0], 'section'));
                    select.add(new Option(selectstrings[1], 'slots'));
                    select.add(new Option(selectstrings[2], 'equitative'));
                    navitem.append(select);
                    let selectedoption = 0;
                    if (select.options[0].value == timetype) {
                        selectedoption = 0;
                    } else if (select.options[1].value == timetype) {
                        selectedoption = 1;
                    } else if (select.options[2].value == timetype) {
                        selectedoption = 2;
                    } else {
                        selectedoption = 3;
                    }
                    select.options[selectedoption].setAttribute('selected', 'true');
                    select.addEventListener('change', change_time_edit_method, true);
                });
                let quiztime = document.createElement('span');
                quiztime.setAttribute('class', 'quiztimer-time');
                let slotheader = document.querySelector('.mod-quiz-edit-content').querySelector('h2');
                slotheader.append(quiztime);
                display_quiz_time(timetype);
                let secrettimetype = document.createElement('input');
                secrettimetype.setAttribute("type", "hidden");
                secrettimetype.setAttribute("name", "timetype");
                secrettimetype.setAttribute("value", timetype);
                slotheader.append(secrettimetype);

                if (timetype === 'timelimit') {
                    let sections = $('.section.main.clearfix');
                    for (let x = 0; x < sections.length; x ++) {
                        let section = sections[x];
                        section.setAttribute('style', 'display:none');
                    }
                    let selecttypewarning = document.createElement('h2');
                    selecttypewarning.append(selectatimetype);
                    selecttypewarning.setAttribute('class', 'text text-info');
                    document.querySelector('.statusbar').append(selecttypewarning);
                    return;
                }

                let questions = $('.slot');
                if (questions.length === 0) {
                    let section = $('.section.main.clearfix')[0];
                    section.setAttribute('style', 'display:none');
                    notification.addNotification({
                       message: noquestions,
                       type: "error"
                    });
                    return;
                }
                let pagesar = [];
                for (let x = 0; x < questions.length; x ++) {
                    let question = questions[x];
                    let select = question.querySelector('.time-select');

                    select.addEventListener('change', adjust_question_time_display, true);

                    let editpen = question.querySelector('.editing-question-time');
                    editpen.addEventListener('click', edit_question_time, true);

                    let questionid = get_question_id(question);
                    let timeinput = question.querySelector('.question-time');
                    load_question_time(questionid.questionid).then( (response) => {
                        if (timetype === 'section') {
                            question.querySelector('.time').setAttribute('style', 'display:none');
                            select.setAttribute('style', 'display:none');
                        } else if (timetype === 'slots') {
                            if (JSON.parse(response) || JSON.parse(response).timevalue == 0) {
                                let timedata = JSON.parse(response);
                                select.value = timedata.timeunit;
                                timeinput.innerHTML = get_time_in_unit(parseFloat(timedata.timeunit),
                                    parseFloat(timedata.timevalue));
                                if ((!question.querySelector('.warningtime') && timedata.timevalue == 0)) {
                                    let warningtime = document.createElement('span');
                                    warningtime.setAttribute('class', 'text-danger warningtime');
                                    warningtime.append(warningtimestr);
                                    question.querySelector('.activityinstance').appendChild(warningtime);
                                }
                            }
                            let pages = $('.pagenumber.activity.timed');
                            for (let t = 0; t < pages.length; t ++) {
                                let page = pages[t];
                                if (!pagesar.includes(page.id)) {
                                    pagesar.push(page.id);
                                }
                                load_page_time(page);
                            }
                            let section = question.closest('.section.main.clearfix');
                            section.querySelector('.time-select').options[2].setAttribute('selected', true);
                            section.querySelector('.time-select').setAttribute('style', 'display:none');
                            section.querySelector('.time').setAttribute('style', 'display:none');
                            load_section_time(section);
                            section.querySelector('.section-time').innerHTML =
                                section.querySelector('.total-section-time').innerHTML;
                            display_quiz_time('slots');
                            quiz_has_attemps();
                        } else if (timetype === 'equitative') {
                            question.querySelector('.time').setAttribute('style', 'display:none');
                            select.setAttribute('style', 'display:none');
                            let pages = $('.pagenumber.activity.timed');
                            for (let t = 0; t < pages.length; t ++) {
                                let page = pages[t];
                                if (!pagesar.includes(page.id)) {
                                    pagesar.push(page.id);
                                    load_page_time(page, false);
                                    get_page_time_from_section(page);
                                }
                            }
                        }

                    });

                }
                let sections = $('.section.main.clearfix');
                for (let x = 0; x < sections.length; x ++) {
                    let section = sections[x];
                    let select = section.querySelector('.time-select');
                    select.addEventListener('change', adjust_section_time_display, true);

                    let editpen = section.querySelector('.editing-section-time');
                    editpen.addEventListener('click', edit_section_time, true);
                    if (timetype !== 'slots') {
                        get_section_time_call(section.closest('.section.main.clearfix')).then();
                    }
                }
            });
        },
    };
});
