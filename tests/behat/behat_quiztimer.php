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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Behat quizaccess_quiztimer-related steps definitions.
 *
 * @package    quizaccess_quiztimer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

/**
 * hybridteaching-related steps definitions.
 *
 * @package    quizaccess_quiztimer
 * @copyright  2024 ISYC
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class behat_quiztimer extends behat_base {
    /**
     * @When I click on the edit icon
     */
    public function i_click_on_the_edit_icon() {
        $editicon = $this->getSession()->getPage()->find('css', '.fa-pencil');

        if (!$editicon) {
            throw new \Exception("Edit icon not found");
        }

        $editicon->click();
    }

    /**
     * @When I click on the pencil icon to edit the time of the question
     */
    public function i_click_on_pencil_icon_to_edit_time_of_question() {
        $page = $this->getSession()->getPage();
        $pencilicon = $page->find('css', '.editing-question-time .editicon');

        if (!$pencilicon) {
            throw new \Exception("Pencil icon for editing time not found");
        }

        $pencilicon->click();
    }

    /**
     * @When The :element should change his content
     */
    public function the_element_should_change_his_content($element) {
        $countdownelement = $this->getSession()->getPage()->find('css', '.' . $element);

        if (!$countdownelement) {
            throw new \Exception("Element '$element' not found");
        }

        $pattern = '/^\d{2}:\d{2}:\d{2}$/';
        $changecount = 0;

        while ($changecount < 3) {
            $initialcontent = $countdownelement->getText();

            $timeout = 10;
            $start = time();
            $changed = false;

            while (time() - $start < $timeout) {
                $currentcontent = $countdownelement->getText();
                if ($currentcontent !== $initialcontent && preg_match($pattern, $currentcontent)) {
                    $changed = true;
                    $changecount++;
                    break;
                }
                sleep(1);
            }

            if (!$changed) {
                throw new \Exception("Element '$element' content did not change within $timeout seconds");
            }
        }
    }
}
