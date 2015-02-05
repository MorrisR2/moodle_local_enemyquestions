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
 * A search class to control whether hidden / deleted questions are hidden in the list.
 *
 * @package   local_enemyquestions
 * @copyright 2015 Ray Morris
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * This class hides questions that are enemies of in-use questions.
 */
class local_enemyquestions_question_bank_search_condition  extends core_question\bank\search\condition  {
    protected $tags;
    protected $where;
    protected $params;

    public function __construct($caller) {
        global $DB;
        global $PAGE;
        if ((get_class($caller) != 'quiz_question_bank_view') || ($PAGE->cm->modname != 'quiz')) {
            return;
        }

        $sql = "SELECT quid FROM (
                    (SELECT questionb AS quid FROM {enemyquestions}, {quiz_slots} WHERE questiona=questionid AND quizid=:quizida)
                    UNION
                    (SELECT questiona AS quid FROM {enemyquestions}, {quiz_slots} WHERE questionb=questionid AND quizid=:quizidb)
               ) AS enemies";

        $enemies = $DB->get_fieldset_sql($sql, array('quizida' => $PAGE->cm->instance, 'quizidb' => $PAGE->cm->instance));

        if (count($enemies)) {
            list ($wherefrag, $this->params) = $DB->get_in_or_equal($enemies, SQL_PARAMS_NAMED, 'param', false);
            $this->where = "(q.id $wherefrag)";
        }
    }

    public function where() {
        return $this->where;
    }

    public function params() {
        return $this->params;
    }
}
