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

global $CFG;
require_once($CFG->dirroot . '/question/editlib.php');

class local_enemyquestions_question_bank_view extends core_question\bank\view {

    protected function wanted_columns() {
        global $CFG;

        $questionbankcolumns = array('checkbox_column', 'question_type_column',
                                     'local_enemyquestions_existing_enemy_column',
                                     'question_name_column',
                                     'preview_action_column',
                                     'modifier_name_column');
        if (question_get_display_preference('qbshowtext', 0, PARAM_BOOL, new \moodle_url(''))) {
            $questionbankcolumns[] = 'question_text_row';
        }

        foreach ($questionbankcolumns as $fullname) {
            if (! class_exists($fullname)) {
                if (class_exists('core_question\\bank\\' . $fullname)) {
                    $fullname = 'core_question\\bank\\' . $fullname;
                } else {
                    throw new \coding_exception("No such class exists: $fullname");
                }
            }
            $this->requiredcolumns[$fullname] = new $fullname($this);
        }
        return $this->requiredcolumns;
    }

    /**
     * Prints the table of questions in a category with interactions
     *
     * @param array      $contexts    Not used!
     * @param moodle_url $pageurl     The URL to reload this page.
     * @param string     $categoryandcontext 'categoryID,contextID'.
     * @param stdClass   $cm          Not used!
     * @param bool       $recurse     Whether to include subcategories.
     * @param int        $page        The number of the page to be displayed
     * @param int        $perpage     Number of questions to show per page
     * @param bool       $showhidden  whether deleted questions should be displayed.
     * @param bool       $showquestiontext whether the text of each question should be shown in the list. Deprecated.
     * @param array      $addcontexts contexts where the user is allowed to add new questions.
     */
    protected function display_question_list($contexts, $pageurl, $categoryandcontext,
            $cm = null, $recurse=1, $page=0, $perpage=100, $showhidden=false,
            $showquestiontext = false, $addcontexts = array()) {
        global $CFG, $DB, $OUTPUT;

        $category = $this->get_current_category($categoryandcontext);

        $cmoptions = new \stdClass();
        $cmoptions->hasattempts = !empty($this->quizhasattempts);

        $strselectall = get_string('selectall');
        $strselectnone = get_string('deselectall');
        $strdelete = get_string('delete');

        list($categoryid, $contextid) = explode(',', $categoryandcontext);
        $catcontext = \context::instance_by_id($contextid);

        $canadd = has_capability('moodle/question:add', $catcontext);
        $caneditall = has_capability('moodle/question:editall', $catcontext);
        $canuseall = has_capability('moodle/question:useall', $catcontext);
        $canmoveall = has_capability('moodle/question:moveall', $catcontext);

        $this->create_new_question_form($category, $canadd);

        $this->build_query();
        $totalnumber = $this->get_question_count();
        if ($totalnumber == 0) {
            return;
        }
        $questions = $this->load_page_questions($page, $perpage);

        echo '<div class="categorypagingbarcontainer">';
        $pageingurl = new \moodle_url('edit.php');
        $r = $pageingurl->params($pageurl->params());
        $pagingbar = new \paging_bar($totalnumber, $page, $perpage, $pageingurl);
        $pagingbar->pagevar = 'qpage';
        echo $OUTPUT->render($pagingbar);
        echo '</div>';

        echo '<form method="post" action="edit.php">';
        echo '<fieldset class="invisiblefieldset" style="display: block;">';
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
        echo \html_writer::input_hidden_params($this->baseurl);

        echo '<div class="categoryquestionscontainer">';
        $this->start_table();
        $rowcount = 0;
        foreach ($questions as $question) {
            $this->print_table_row($question, $rowcount);
            $rowcount += 1;
        }
        $this->end_table();
        echo "</div>\n";

        echo '<div class="categorypagingbarcontainer pagingbottom">';
        echo $OUTPUT->render($pagingbar);
        if ($totalnumber > DEFAULT_QUESTIONS_PER_PAGE) {
            if ($perpage == DEFAULT_QUESTIONS_PER_PAGE) {
                $url = new \moodle_url('edit.php', array_merge($pageurl->params(), array('qperpage' => 1000)));
                $showall = '<a href="'.$url.'">'.get_string('showall', 'moodle', $totalnumber).'</a>';
            } else {
                $url = new \moodle_url('edit.php', array_merge($pageurl->params(),
                                              array('qperpage' => DEFAULT_QUESTIONS_PER_PAGE)));
                $showall = '<a href="'.$url.'">'.get_string('showperpage', 'moodle', DEFAULT_QUESTIONS_PER_PAGE).'</a>';
            }
            echo "<div class='paging'>{$showall}</div>";
        }
        echo '</div>';

        echo '<div class="modulespecificbuttonscontainer">';
        if ($caneditall || $canmoveall || $canuseall) {
            echo '<strong>&nbsp;'.get_string('withselected', 'question').':</strong><br />';

            if (function_exists('module_specific_buttons')) {
                echo module_specific_buttons($this->cm->id, $cmoptions);
            }

            if (function_exists('module_specific_controls') && $canuseall) {
                $modulespecific = module_specific_controls($totalnumber, $recurse, $category, $this->cm->id, $cmoptions);
                if (!empty($modulespecific)) {
                    echo "<hr />{$modulespecific}";
                }
            }
        }

        echo "</div>\n";

        echo '</fieldset>';
        echo "</form>\n";
    }

    protected function display_options_form($showquestiontext, $scriptpath = '/local/enemyquestions/edit.php',
            $showtextoption = true) {
        // Overridden just to change the default values of the argument.
        parent::display_options_form($showquestiontext, $scriptpath, $showtextoption);
    }
}

