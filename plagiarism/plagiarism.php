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
 * Prints a particular instance of mycat
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage mycat
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');


if (isguestuser()) {
			print_error(get_string('norightpermissions', 'local_plagiarism'));
	die;
}


$mid = required_param('mid', PARAM_INT); // module ID
$type = required_param('type', PARAM_INT); // type: 0 for assignment, 1 for quiz
$qid = optional_param('qid', -1, PARAM_INT);

if ($type == ASSIGNMENT) {
	$module = $DB->get_record('assignment', array('id' => $mid), '*', MUST_EXIST);
}
else if ($type == QUIZ) {
	$module = $DB->get_record('quiz', array('id' => $mid), '*', MUST_EXIST);
}
else {
	print_error(get_string('wrongtype', 'local_plagiarism'));
	die;
}

if ($mid <= 0) {
	print_error(get_string('wrongid', 'local_plagiarism'));
	die;
}


$the_type = $type == ASSIGNMENT ? 'assignment' : 'quiz';
$cm = get_coursemodule_from_instance($the_type, $module->id, $module->course);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);
require_login($module->course, true, $cm);

require_capability('local/plagiarism:editingteacher', $context);

/// Print the page header
//$PAGE->set_context($context); // not needed if we have set it before in require_login
$PAGE->set_url('/local/plagiarism/plagiarism.php', array('mid' => $mid, 'type' => $type, 'qid' => $qid));
$PAGE->set_title(format_string(get_string('plagiarismdetection', 'local_plagiarism')));
$PAGE->set_heading(format_string(get_string('plagiarismdetection', 'local_plagiarism')));
$PAGE->set_pagelayout('mydashboard');


	
// Output starts here
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('plagiarismdetection', 'local_plagiarism'));


if ($type == ASSIGNMENT || ($type == QUIZ && $qid > 0)) {
	
	if ($type == QUIZ) {
		$question = $DB->get_record('question', array('id' => $qid), '*', MUST_EXIST);
		if (strcmp($question->qtype, 'essay') != 0) {
			print_error(get_string('wrongquestiontype', 'local_plagiarism'));
			die;
		}
	}	

	echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
	echo ($type == QUIZ) ? $OUTPUT->heading($question->name) : $OUTPUT->heading($module->name);
	echo '<center><div>';
	echo ($type == QUIZ) ? $question->questiontext : $module->intro; 
	echo '</div></center>';
	$similarities = plagiarism_detection($type, $mid, $qid);
	echo get_similarities_table_css_javascript();
	echo get_similarities_table($similarities, 1);
	echo $OUTPUT->box_end();
}
else {
	
	$qtypes = $DB->get_records_sql('
  	SELECT q.id, q.qtype, q.name, q.questiontext
    FROM {question} q
    JOIN {quiz_question_instances} qqi ON qqi.question = q.id
    WHERE qqi.quiz = ? AND q.qtype = "essay"
    ORDER BY id', array('quizid' => $mid));
    
    $i = 0;
    foreach ($qtypes as $qid=>$q) {
    	$i++;
			echo $OUTPUT->box_start('generalbox boxaligncenter', 'intro');
			echo $OUTPUT->heading($i . ') ' . $q->name);
			echo '<center><div>';
			echo $q->questiontext;
			echo '</div></center>';
			$similarities = plagiarism_detection(QUIZ, $mid, $qid);
			echo get_similarities_table_css_javascript();
			echo get_similarities_table($similarities, $i);
			echo $OUTPUT->box_end();    	
    }
	
}

// Finish the page
echo $OUTPUT->footer();

