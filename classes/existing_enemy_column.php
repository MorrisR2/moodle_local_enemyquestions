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

class local_enemyquestions_existing_enemy_column extends \core_question\bank\action_column_base {
    public function get_name() {
        return 'local_enemyquestions|enemies';
    }

    protected function display_content($question, $rowclasses) {
        global $OUTPUT;
        global $qa;
        if ($question->isenemy) {
            echo '<img src="' . $OUTPUT->pix_url('t/enemy', 'local_enemyquestions') . '" />';
        }
    }


    public function get_extra_joins() {
        global $qa;
        return array('enemya' => 'LEFT JOIN {enemyquestions} eq ON eq.questionb = q.id AND eq.questiona=' . $qa);
    }

    public function get_required_fields() {
        return array('eq.id AS isenemy');
    }

    public function is_sortable() {
        return 'eq.id';
    }
}

