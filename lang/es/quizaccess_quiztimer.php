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

$string['pluginname'] = 'Reglas de acceso de Quiztimer';


$string['subtimes'] = 'Límite de tiempo para usar';
$string['subtimes_help'] = 'El desplegable ofrece tres opciones para configurar el temporizador del cuestionario (Sección, Pregunta, Total)';

// Other strings.
$string['totaltime'] = 'Total Time';
$string['minutes'] = 'minutes';
$string['submit'] = 'Submit';

$string['quiztime'] = 'Ajustar tiempos por pregunta';
$string['timeunit'] = '...';
$string['totalsectiontime'] = 'Tiempo total de sección:';
$string['hours'] = 'horas';
$string['minutes'] = 'minutos';
$string['seconds'] = 'segundos';
$string['distributesectiontime'] = 'Dividir tiempo sección en paginas';
$string['timelimit'] = 'Tiempo límite';
$string['sectiontime'] = 'Tiempo por secciones';
$string['pagetime'] = 'Tiempo por páginas';
$string['questiontime'] = 'Tiempo por preguntas';

$string['setting:timedsections'] = 'Tiempo por defecto de secciones';
$string['setting:timedsections_desc'] = 'Tiempo por defecto usado en las secciones creadas';
$string['setting:timedslots'] = 'Tiempo por defecto de preguntas';
$string['setting:timedslots_desc'] = 'Tiempo por defecto usado en las preguntas creadas';
$string['unitsections'] = 'Unidad usada para las secciones por defecto';
$string['unitslots'] = 'Unidad usada para las preguntas por defecto';

$string['quiztimer'] = 'Zona de mensajes de tiempos del quiz:';
$string['requirequiztimermessage'] = 'Este quiz utiliza tiempos personalizados para las preguntas y secciones.';
$string['quizquiztimer'] = 'Tiempo del quiz';
$string['quiztimererrors'] = 'Tiempos del quiz que necesitan ser reajustados para poder continuar:';
$string['warningtime'] = 'Tiempo no válido, introduzca otro';
$string['invalidsettedtime'] = 'Tiempo introducido invalido, se ha restablecido al valor anterior';

$string['canteditquiztimes'] = 'No puede editar los tiempos de las preguntas porque este cuestionario ya ha sido respondido. ({$a})';
$string['eventslottimerupdated'] = 'Tiempo de pregunta actualizado';
$string['eventsectiontimerupdated'] = 'Tiempo de sección actualizado';
$string['quiztimer:manage'] = 'Gestiona los ajustes de los tiempos de las reglas de acceso quiztimer';

$string['eventslottimerupdateddescription'] = 'El usuario con id {$a->userid} ha actualizado el temporizador de la pregunta con id {$a->slot} con un nuevo tiempo de {$a->timevalue} {$a->timeunit}';
$string['eventsectiontimerupdateddescription'] = 'El usuario con id {$a->userid} ha actualizado el temporizador de la sección con id {$a->section} con un nuevo tiempo de {$a->timevalue} {$a->timeunit}';

$string['timelimitedit'] = 'Tiempo limite (sin uso de tiempos personalizados)';
$string['selecttypetimes'] = 'Selecciona un tipo de tiempos para usar tiempos personalizados';

$string['repaginatewarning'] = 'Al seleccionar métodos de edición por preguntas o secciones puede repaginar las preguntas del quiz:
- Tiempo por secciones: Todas las preguntas en una página dentro de cada sección.
- Tiempo por preguntas: 1 pregunta por cada página.
¿Deséa continuar con la operación?';

$string['pagingchangesnotapply'] = 'El quiz usa tiempos personalizados por secciones o preguntas, los cambies que realices a las paginas del quiz no se aplicarán y serán sobrescritos';
$string['canteditquiztype'] = 'No puede editar el modo de tiempo del cuestionario porque ya ha sido respondido.';
$string['disabledbycustomtimer'] = 'Fijo por el modo de tiempos del cuestionario en la seccion de temporalización. ';

$string['configsavedsection'] = 'Configuración guardada correctamente. <br> <b>Recuerda cambiar los tiempos de las secciones.</b>';
$string['configsavedquestion'] = 'Configuración guardada correctamente. <br> <b>Recuerda cambiar los tiempos de las preguntas.</b>';
$string['configsavedpage'] = 'Configuración guardada correctamente. <br> <b>Recuerda cambiar los tiempos de las páginas.</b>';


$string['privacy:metadata:quiz'] = 'El cuestionario al que pertenece esta configuración de temporizador.';
$string['privacy:metadata:quiz_mode'] = 'El modo de temporizador utilizado en el cuestionario.';
$string['privacy:metadata:usermodified'] = 'El ID del usuario que modificó la configuración del temporizador.';
$string['privacy:metadata:timecreated'] = 'La fecha y hora en que se creó la configuración del temporizador.';
$string['privacy:metadata:timemodified'] = 'La fecha y hora en que se modificó por última vez la configuración del temporizador.';

$string['privacy:metadata:quizid'] = 'El cuestionario asociado con los datos temporales del usuario.';
$string['privacy:metadata:slot'] = 'La posición de la pregunta a la que se refiere el dato temporal.';
$string['privacy:metadata:section'] = 'La sección del cuestionario a la que se refiere el dato temporal.';
$string['privacy:metadata:userid'] = 'El ID del usuario al que pertenecen estos datos temporales.';
$string['privacy:metadata:attempt'] = 'El número de intento relacionado con los datos temporales.';
$string['privacy:metadata:timestart'] = 'La fecha y hora de inicio registrados para esta pregunta o sección.';
$string['privacy:metadata:timefinish'] = 'La fecha y hora de finalización registrados para esta pregunta o sección.';

$string['privacy:metadata:quizaccess_quiztimer'] = 'Datos de configuración del temporizador del cuestionario.';
$string['privacy:metadata:quizaccess_usertimedslots'] = 'Datos de tiempo por pregunta del usuario.';
$string['privacy:metadata:quizaccess_usertimedsections'] = 'Datos de tiempo por sección del usuario.';
