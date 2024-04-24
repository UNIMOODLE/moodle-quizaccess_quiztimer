define([], function() {

    var init = function(url, strattemps, attemps, strdisabledbytimer) {
        if (url) {
            let timequestion = document.querySelector('#id_timequestion')
            timequestion.disabled = true;
            // Once we disabled the times mode selector, we create the warning.
    
            let hasattemps = document.createElement('a');
            let hasattempsdiv = document.createElement('div');
            hasattempsdiv.setAttribute('class', 'box py-3');
            hasattempsdiv.setAttribute('style', 'background-color: #ffc; margin: 0.3em 0; padding: 1px 10px;');
            hasattemps.setAttribute('href', url);
            // We create the container and warning content.
    
            hasattemps.append('(' + attemps + ')');
            hasattempsdiv.append(strattemps);
            hasattempsdiv.append(hasattemps);
            timequestion.closest('div').append(hasattempsdiv);
            // We append the warning content to the view.
        }
        if (document.querySelector('#id_navmethod').getAttribute('disabled') !== null) {
            let warningdisabledlayout = document.createElement('p');
            warningdisabledlayout.append(strdisabledbytimer);
            warningdisabledlayout.setAttribute('class', 'text text-info');
            document.querySelector('#id_layouthdrcontainer').append(warningdisabledlayout);
        }
    }
    return {
        init: init
    };
});
