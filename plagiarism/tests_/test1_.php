
<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

$quizid = 1;
$userid = 3;
$attempt = 1; // get the first attempt

$attempt = $DB->get_record('quiz_attempts', array('userid' => $userid, 'quiz' => $quizid, 'attempt' => $attempt), '*', MUST_EXIST); // get the first attempt of the user
$quiz = $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
$cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id);

$quiz_attempt = new quiz_attempt($attempt, $quiz, $cm, $course);

// Getting the essay like questions
// First way
/*
$essay_like = array();
$quiz_question = $DB->get_records('quiz_question_instances', array ('quiz' => $quiz->id));
foreach ($quiz_question as $q) {
	$sql = 'SELECT * FROM ' . $CFG->prefix . 'question WHERE id = ' . $q->question . ' AND qtype="essay"';
	$question = $DB->get_records_sql($sql);
	if ($question != null) {
		$essay_like[$q->id] = $question;
	}
}
*/
// Second way
$insql = 'SELECT id FROM ' . $CFG->prefix . 'quiz_question_instances WHERE quiz = ' . $quiz->id; 
$sql = 'SELECT * FROM ' . $CFG->prefix . 'question WHERE qtype="essay" AND id IN (' . $insql . ')';
$questions = $DB->get_records_sql($sql);
$question_attempts = array();
foreach ($questions as $q) {
	$slot = quiz_get_slot_for_question($quiz, $q->id);
	$question_attempts[$q->id] = $quiz_attempt->get_question_attempt($slot);
}

$answers = array();
foreach ($question_attempts as $att) {
	$sql = 'SELECT * FROM ' . $CFG->prefix . 'question_attempt_steps WHERE questionattemptid = ' . $att->get_database_id() . ' AND state="complete" AND userid = ' . $userid;
	$steps = $DB->get_records_sql($sql);
	if ($steps != null) {
		$step = reset($steps);
		// $step should be a unique object
		$sql = 'SELECT * FROM ' . $CFG->prefix . 'question_attempt_step_data WHERE attemptstepid = ' . $step->id . ' AND name="answer"';
		$data_ = $DB->get_records_sql($sql);
		$data = reset($data_);
		if ($data != null) {
			$answers[$userid] = $data->value;
		}
	}
	
}

echo '<br/><br/>';
print_r($answers);


