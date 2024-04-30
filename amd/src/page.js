define(function(require, exports, module) {
    'use strict';

    var init = function(data) {
        var attemptid = data.attemptid;
        var tiempos = data.tiempos;

        const backgroundColors = ["#ca3120", "#d73422", "#dd3d2b", "#e04938",
        "#e25546", "#e46153", "#e66d60", "#e8796d", "#ea867a", "#ec9288",
        "#ee9e95", "#f0aaa2", "#f2b6af", "#f4c2bc", "#f7ceca", "#f9dad7", "#fbe6e4"];

       const textColors = ["#fff", "#fff", "#fff", "#fff", "#fff", "#fff",
        "#fff", "#1d2125", "#1d2125", "#1d2125", "#1d2125",
        "#1d2125", "#1d2125", "#1d2125", "#1d2125", "#1d2125", "#1d2125"];

       //Disable original timer
       var divElement = document.getElementById("quiz-timer");
       if (divElement) {
       divElement.disabled = true;
       divElement.innerHTML = "Timer disabled";
        }

       var quizTimerWrapper = document.getElementById("quiz-timer-wrapper");
       if (quizTimerWrapper.style.display === "flex") {
       quizTimerWrapper.style.display = "";
       }

       var currentPageURL = window.location.href;
       var urlParams = new URLSearchParams(currentPageURL);
       var pageID = urlParams.get("page");

       if(pageID === null){
           pageID = 0;
       }

       var countdownID = "countdown" + pageID;
       var quiztimercountdown = document.createElement('div');
       quiztimercountdown.id = countdownID;
       quiztimercountdown.className = 'countdown-section';

       quiztimercountdown.style.maxWidth = "max-content";
       quiztimercountdown.style.marginLeft = "auto";

       var existingDiv = document.querySelector('.container-fluid.tertiary-navigation');
       existingDiv.parentNode.insertBefore(quiztimercountdown, existingDiv.nextSibling);

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
               var timeRemaining =  (endTime - 1) - currentTime;

               function getColorBackgroundByPercentage(percentage) {

                   var index = percentage / 10;

                   // Devolver el color correspondiente
                   return backgroundColors[index];
               }
               function getColorTextByPercentage(percentage) {
                   var index = percentage / 10;

                   // Devolver el color correspondiente
                   return textColors[index];
               }


               // Calcular el porcentaje actual en relación con la duración total
               var totalDuration = endTime - (endTime - 1);
               var currentPercentage = (timeRemaining / totalDuration) * 10;
               countdownElement.style.backgroundColor = getColorBackgroundByPercentage(currentPercentage);
               countdownElement.style.color = getColorTextByPercentage(currentPercentage);




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

               // Function to save the countdown time before leaving the page
               function saveCountdownTime() {
                   var countdownElement = document.getElementById("countdown" + pageID);
                   localStorage.setItem("countdown" + pageID, timeRemaining);
                   localStorage.setItem("attempt",  JSON.stringify(attemptid));
               }

               // Attach the saveCountdownTime function to the beforeunload event
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




       // Get the end time from your server-side variable.
       var endTime = tiempos[pageID] + Math.floor(Date.now() / 1000);


       var storedTime = localStorage.getItem("countdown" + pageID);
       if (storedTime !== null) {
           // Use the stored time as the end time
           endTime = parseInt(storedTime) + Math.floor(Date.now() / 1000);
           // Remove the stored time from localStorage
           localStorage.removeItem("countdown" + pageID);
       }



       // Start the countdown timer.
       updateCountdownTimer(endTime + 1,5);

       // Insert countdown elements after the respective heading elements.
       headingElement.forEach(function(headingElement, index) {

       var textoElement = document.getElementById("countdown" + (index));
       if (headingElement && textoElement) {
           //headingElement.parentNode.insertBefore(textoElement, headingElement.nextSibling);
           headingElement.appendChild(textoElement);
       }
        });
    };

    return {
        init: init
    };
});
