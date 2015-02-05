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

/*
 *
 * @package    local_enemyquestions
 * @copyright  2015 onwards Ray Morris and others {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../config.php');
require_once($CFG->dirroot . '/mod/quiz/editlib.php');
require_once($CFG->dirroot . '/question/category_class.php');


/**
 * Callback function called from question_list() function
 * (which is called from showbank())
 * Displays button in form with checkboxes for each question.
 */
function module_specific_buttons($cmid, $cmoptions) {
    global $OUTPUT;
    $paramsadd = array(
        'type' => 'submit',
        'name' => 'add',
        'value' => get_string('declareenemy', 'local_enemyquestions'),
    );

    $paramsremove = array(
        'type' => 'submit',
        'name' => 'remove',
        'value' => get_string('removeenemy', 'local_enemyquestions'),
    );
    return html_writer::empty_tag('input', $paramsadd) . html_writer::empty_tag('input', $paramsremove);
}

// These params are only passed from page request to request while we stay on
// this page otherwise they would go in question_edit_setup.
$scrollpos = optional_param('scrollpos', '', PARAM_INT);
$qa = optional_param('qa', '', PARAM_INT);
$courseid = optional_param('courseid', false, PARAM_INT);

list($thispageurl, $contexts, $cmid, $cm, $quiz, $pagevars) =
        question_edit_setup('editq', '/local/enemyquestions/edit.php');

$defaultcategoryobj = question_make_default_categories($contexts->all());
$defaultcategory = $defaultcategoryobj->id . ',' . $defaultcategoryobj->contextid;

$thispageurl->param('qa', $qa);
$PAGE->set_url($thispageurl);
$PAGE->set_pagelayout('popup');

if (!$courseid) {
    $courseid = $quiz->course;
}
// Get the course object and related bits.
$course = $DB->get_record('course', array('id' => $courseid));
if (!$course) {
    print_error('invalidcourseid', 'error');
}

$questionbank = new local_enemyquestions_question_bank_view($contexts, $thispageurl, $course, $cm, $quiz);

// You need mod/quiz:manage in addition to question capabilities to access this page.
require_capability('mod/quiz:manage', $contexts->lowest());

// Get the list of question ids had their check-boxes ticked.
$selectedslots = array();
$params = (array) data_submitted();
foreach ($params as $key => $value) {
    if (preg_match('!^s([0-9]+)$!', $key, $matches)) {
        $selectedslots[] = $matches[1];
    }
}

$afteractionurl = new moodle_url($thispageurl);
if ($scrollpos) {
    $afteractionurl->param('scrollpos', $scrollpos);
}


if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    global $DB;

    $rawdata = (array) data_submitted();
    foreach ($rawdata as $key => $value) { // Parse input for question ids.
        if (preg_match('!^q([0-9]+)$!', $key, $matches)) {
            $row = array('questiona' => $qa, 'questionb' => $matches[1]);
            if ($DB->count_records('enemyquestions', $row) == 0) {
                $DB->insert_record('enemyquestions', (object) $row);
            }
        }
    }
    redirect($afteractionurl);
}

if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    global $DB;

    $rawdata = (array) data_submitted();
    foreach ($rawdata as $key => $value) { // Parse input for question ids.
        if (preg_match('!^q([0-9]+)$!', $key, $matches)) {
            $DB->delete_records('enemyquestions', array('questiona' => $qa, 'questionb' => $matches[1]));
            $DB->delete_records('enemyquestions', array('questionb' => $qa, 'questiona' => $matches[1]));
        }
    }
    redirect($afteractionurl);
}

$questionbank->process_actions($thispageurl, $cm);


$PAGE->requires->skip_link_to('questionbank',
        get_string('skipto', 'access', get_string('questionbank', 'question')));
$PAGE->set_title(get_string('pluginname', 'local_enemyquestions'));
$PAGE->set_heading(get_string('pluginname', 'local_enemyquestions'));

echo $OUTPUT->header();

echo '<div class="content">';
echo '<h1>' . get_string('pluginname', 'local_enemyquestions') . "</h1>\n";
$question = $DB->get_record('question', array('id' => $qa));
echo '<h3>Enemies of <q>' . $question->name . "</q></h3>\n";
echo '<div class="container">';
echo '<div id="module" class="module">';
echo '<div class="bd">';

$questionbank->display('editq',
        $pagevars['qpage'],
        $pagevars['qperpage'],
        $pagevars['cat'], $pagevars['recurse'], $pagevars['showhidden'],
        $pagevars['qbshowtext']);


echo '</div>';
echo '</div>';
echo '</div>';

echo '</div></div>';


echo $OUTPUT->footer();
