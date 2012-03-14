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
require_once('set_form.php');



if (isguestuser()) {
			print_error(get_string('norightpermissions', 'local_plagiarism'));
	die;
}

$cid = required_param('cid', PARAM_INT); // course id

require_login($cid); // NOTE: if cid is a valid course, it will show the Course Administration Settings and also set the navbar

if ($cid < 0) {
	print_error(get_string('nonegative', 'local_plagiarism'));
	die;
}


$course = $DB->get_record('course', array('id' => $cid), '*', MUST_EXIST);
$context = get_context_instance(CONTEXT_COURSE, $cid);
if (!has_capability('local/plagiarism:editingteacher', $context)) {
	print_error(get_string('norightpermissions', 'local_plagiarism'));
	die;	
}

/// Print the page header
$PAGE->set_context($context); // not needed if we have set it before in require_login
$PAGE->set_url('/local/plagiarism/set_plagiarism.php', array('cid' => $cid));
$PAGE->set_title(format_string(get_string('plagiarism_settings', 'local_plagiarism')));
$PAGE->set_heading(format_string(get_string('plagiarism_settings', 'local_plagiarism')));
$PAGE->set_pagelayout('mydashboard');

$url = new moodle_url('/local/plagiarism/set_plagiarism.php', array('cid' => $cid));


// Set the navbar
$PAGE->navbar->add(get_string('plagiarism_settings', 'local_plagiarism'));

$settings = $DB->get_record('plagiarism_settings', array('courseid' => $cid));

$data = new stdClass();
$default = get_default_settings();
$default->jaccard *= 100;
$default->indepth *= 100;

$data->default_settings = $default;

$form = new local_plagiarism_set_form($url, $data);

// set data
if ($settings == null) {
	$setdata = $default;
	$setdata->usedefault = 1;
} else {
	$setdata = $settings;
	$settings->jaccard *= 100;
	$settings->indepth *= 100;
	if ($settings->valid == 0) {
		$setdata->usedefault = 1;
	}
	else {	
		$setdata->usedefault = 0;
	}
}

$form->set_data($setdata);
	
$submit = $form->get_data();

if ($submit) { // form submit
	if ($submit->usedefault == 1) {
		if ($settings != null) {
			$settings->valid = 0;
			$settings->indepth /= 100;
			$settings->jaccard /= 100;
			$DB->update_record('plagiarism_settings', $settings);
		}
	}
	else { // specific settings
		if ($settings == null) {
			$id = $DB->insert_record('plagiarism_settings', array ('courseid' => $cid));
			$settings = $DB->get_record('plagiarism_settings', array('id' => $id), '*', MUST_EXIST);
		}
		
		$settings->indepth = $submit->indepth / 100;
		$settings->jaccard = $submit->jaccard / 100;
		$settings->allowspelling = $submit->allowspelling;
		$settings->checksynonyms = $submit->checksynonyms;
		$settings->wordngrams = $submit->wordngrams;
		$settings->valid = 1;
		
		$DB->update_record('plagiarism_settings', $settings);
	}
	redirect(new moodle_url('/course/view.php', array('id' => $cid)));
}
else {	
	// Output starts here
	echo $OUTPUT->header();

	echo $OUTPUT->heading(get_string('plagiarism_settings', 'local_plagiarism'));
	$form->display();
	// Finish the page
	echo $OUTPUT->footer();
}
