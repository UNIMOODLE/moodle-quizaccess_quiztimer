define(function(require, exports, module) {
    'use strict';

    var init = function(data) {
        var attemptid = data.attemptid;
        var tiempos = data.tiempos;

        var backgroundColors = ["#ca3120", "#d73422", "#dd3d2b", "#e04938",
            "#e25546", "#e46153", "#e66d60", "#e8796d", "#ea867a", "#ec9288",
            "#ee9e95", "#f0aaa2", "#f2b6af", "#f4c2bc", "#f7ceca", "#f9dad7", "#fbe6e4"];

        var textColors = ["#fff", "#fff", "#fff", "#fff", "#fff", "#fff",
            "#fff", "#1d2125", "#1d2125", "#1d2125", "#1d2125",
            "#1d2125", "#1d2125", "#1d2125", "#1d2125", "#1d2125", "#1d2125"];

        //Disable original timer
        var divElement = document.getElementById("quiz-timer");
        if (divElement) {
            divElement.disabled = true;
            divElement.innerHTML = "Timer disabled";
        }

        var currentPageURL = window.location.href;
        var urlParams = new URLSearchParams(currentPageURL);
        var pageID = urlParams.get("page");


        if (pageID === null) {
            pageID = 0;
        }

        var countdownID = "countdown" + pageID;
        var quiztimercountdown = document.createElement('div');
        quiztimercountdown.id = countdownID;
        quiztimercountdown.className = 'countdown-question';

        quiztimercountdown.style.maxWidth = "max-content";
        quiztimercountdown.style.marginLeft = "auto";

        var existingDiv = document.querySelector('.container-fluid.tertiary-navigation');
        if (existingDiv) {
            existingDiv.parentNode.insertBefore(quiztimercountdown, existingDiv.nextSibling);
        }

        var headingElement = document.querySelectorAll(".container-fluid.tertiary-navigation");

        /**
         * Updates the countdown timer based on the end time.
         *
         * @param {number} endTime - The end time of the countdown timer in seconds.
         */
        function updateCountdownTimer(endTime) {
            var countdownElement = document.getElementById("countdown" + (pageID));
            var countdownInterval = setInterval(function() {
                var currentTime = Math.floor(Date.now() / 1000);
                var timeRemaining = (endTime - 1) - currentTime;

                var totalDuration = endTime - (endTime - 1);
                var currentPercentage = (timeRemaining / totalDuration) * 10;
                countdownElement.style.backgroundColor = backgroundColors[currentPercentage];
                countdownElement.style.color = textColors[currentPercentage];

                if (timeRemaining <= -1 ) {
                    clearInterval(countdownInterval);
                    var button = document.getElementById("mod_quiz-next-nav");
                    localStorage.setItem("countdown" + pageID, 0);
                    countdownElement.innerHTML = "00:00:00";
                    countdownElement.disabled = true;
                    button.click();
                    return;
                } else {
                    var button = document.getElementById("mod_quiz-next-nav");
                    if (button) {
                        button.addEventListener("click", function() {
                            localStorage.setItem("countdown" + pageID, timeRemaining);
                        });
                    }
                }

                function saveCountdownTime() {
                    localStorage.setItem("countdown" + pageID, timeRemaining);
                    localStorage.setItem("attempt", attemptid);
                }

                window.addEventListener("beforeunload", saveCountdownTime);

                var hours = Math.floor(timeRemaining / 3600);
                var minutes = Math.floor((timeRemaining % 3600) / 60);
                var seconds = timeRemaining % 60;

                var formattedTime = hours.toString().padStart(2, "0") + ":" +
                                    minutes.toString().padStart(2, "0") + ":" +
                                    seconds.toString().padStart(2, "0");
                countdownElement.innerHTML = formattedTime;
                }, 1);
        }

        var endTime = tiempos[pageID] + Math.floor(Date.now() / 1000);

        var storedTime = localStorage.getItem("countdown" + pageID);
        if (storedTime !== null) {
            endTime = parseInt(storedTime) + Math.floor(Date.now() / 1000);
            localStorage.removeItem("countdown" + pageID);
        }

        updateCountdownTimer(endTime + 1, 5);

        headingElement.forEach(function(headingElement, index) {
            var textoElement = document.getElementById("countdown" + (index));
            if (headingElement && textoElement) {
                headingElement.appendChild(textoElement);
            }
        });
    };

    return {
        init: init
    };
});
