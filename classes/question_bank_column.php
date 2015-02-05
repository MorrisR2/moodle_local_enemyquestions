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
defined('MOODLE_INTERNAL') || die;

global $CFG;
require_once($CFG->dirroot . '/question/editlib.php');


class local_enemyquestions_question_bank_column extends \core_question\bank\action_column_base {
    public function get_name() {
        return 'local_enemyquestions|enemies';
    }

    protected function display_content($question, $rowclasses) {
        global $OUTPUT;

        $url = new moodle_url('/local/enemyquestions/edit.php', array_merge($this->qbank->base_url()->params(),
                array('qa' => $question->id)));
        echo $OUTPUT->action_icon($url,
              new pix_icon('t/enemy', get_string('declareenemy', 'local_enemyquestions'), 'local_enemyquestions'), null,
                      array('target' => '_blank'));
    }

    public function get_extra_joins() {
        return array();
    }

    public function get_required_fields() {
        return array();
    }

    /**
     * Can this column be sorted on? You can return either:
     *  + false for no (the default),
     *  + a field name, if sorting this column corresponds to sorting on that datbase field.
     *  + an array of subnames to sort on as follows
     *  return array(
     *      'firstname' => array('field' => 'uc.firstname', 'title' => get_string('firstname')),
     *      'lastname' => array('field' => 'uc.lastname', 'field' => get_string('lastname')),
     *  );
     * As well as field, and field, you can also add 'revers' => 1 if you want the default sort
     * order to be DESC.
     * @return mixed as above.
     */
    public function is_sortable() {
        return false;
    }
}

