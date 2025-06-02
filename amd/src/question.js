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

define(function() {
    'use strict';

    var init = function(data) {
        var slot = data.slot;
        var secondsleft = data.secondsleft;
        var timefinish = Math.floor(Date.now() / 1000) + secondsleft;

        // Disable original timer.
        var divElement = document.getElementById("quiz-timer");
        if (divElement) {
            divElement.disabled = true;
            divElement.innerHTML = "Timer disabled";
        }

        var quizTimerWrapper = document.getElementById("quiz-timer-wrapper");
        if (quizTimerWrapper && quizTimerWrapper.style.display === "flex") {
            quizTimerWrapper.style.display = "";
        }

        // Create the div for the timer.
        var countdownID = "countdown" + slot;
        var quiztimercountdown = document.createElement('div');
        quiztimercountdown.id = countdownID;
        quiztimercountdown.className = 'quiztimer-countdown-question';

        quiztimercountdown.style.maxWidth = "max-content";
        quiztimercountdown.style.marginLeft = "auto";

        var existingDiv = document.querySelector('.container-fluid.tertiary-navigation');
        if (existingDiv) {
            existingDiv.parentNode.insertBefore(quiztimercountdown, existingDiv.nextSibling);
        }

        var headingElement = document.querySelectorAll(".container-fluid.tertiary-navigation");

        function updateCountdownTimer(endTime) {
            var countdownElement = document.getElementById("countdown" + slot);

            var countdownInterval = setInterval(function() {
                var currentTime = Math.floor(Date.now() / 1000);
                var timeRemaining = endTime - currentTime;

                if (timeRemaining <= 0) {
                    clearInterval(countdownInterval);
                    countdownElement.innerHTML = "00:00:00";
                    countdownElement.disabled = true;

                    var button = document.getElementById("mod_quiz-next-nav");
                    if (button) {
                        button.click();
                    }
                    return;
                }

                var hours = Math.floor(timeRemaining / 3600);
                var minutes = Math.floor((timeRemaining % 3600) / 60);
                var seconds = timeRemaining % 60;

                var formattedTime = hours.toString().padStart(2, "0") + ":" +
                                    minutes.toString().padStart(2, "0") + ":" +
                                    seconds.toString().padStart(2, "0");
                countdownElement.innerHTML = formattedTime;
            }, 1);
        }

        updateCountdownTimer(timefinish);

        headingElement.forEach(function(heading) {
            var textoElement = document.getElementById("countdown" + slot);
            if (heading && textoElement) {
                heading.appendChild(textoElement);
            }
        });
    };

    return {
        init: init
    };
});
