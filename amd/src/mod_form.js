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
