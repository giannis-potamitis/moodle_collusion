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
 * English strings for plagiarism
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage plagiarism
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Plagiarism Detection';
$string['wrongtype'] = 'Wrong Module Type';
$string['wrongid'] = 'ID must be a positive integer';
$string['indepthunexpected'] = 'Unexpected error in plagiarism in-depth analysis phase';
$string['block:plagiarism'] = 'Plagiarism';
$string['block:plagiarism_settings'] = 'Settings';
$string['nonegative'] = 'Page parameters cannot be negative';
$string['norightpermissions'] = 'You do not have the right permissions to access this page';
$string['plagiarism:editingteacher'] = 'Editing Teacher';
$string['plagiarism:admin'] = 'Admin User';
$string['plagiarism_settings'] = 'Collusion Settings';
$string['usedefault'] = 'Use default settings';
$string['usespecific'] = 'Use specific settings';
$string['candidates_retrieval'] = 'Candidates Retrieval Threshold';
$string['candidates_retrieval_help'] = 'It is used in the second phase of the plagiarism detection algorithm. It specifies the threshold of Jaccard Similarity which is used to retrieve the candidate answers. This should be a percentage number in the >0..100 range';
$string['indepth'] = 'Indepth Analysis Threshold';
$string['indepth_help'] = 'It is used in the third phase of the plagiarism detection algorithm. It specifies the threshold of the Indepth Analysis Similarity. This should be a percentage number in the >0..100 range';
$string['wordngrams'] = 'Number of Words per Group';
$string['wordngrams_help'] = 'In the second phase of plagiarism detection algorithm each answer is splitted into groups of consecutive words. The size of each group is determined by this number.  This should be between 1 and 4';
$string['student'] = 'Student';
$string['allowspelling'] = 'Allow Spelling Correction';
$string['allowspelling_help'] = 'In the first phase spelling correction of words is used.';
$string['checksynonyms'] = 'Check Synonyms of Words';
$string['checksynonyms_help'] = 'Checking the use of synonyms of words is always a good thing to do but this makes the plagiarism detection system slower';
$string['save'] = 'Save';
$string['cannotempty'] = 'Cannot be empty';
$string['zeroonerange'] = 'It must be a percentage number in the >0..100 range';
$string['mustbenumber'] = 'it must be a number';
$string['positiveinteger'] = 'It must be a non-zero positive integer';
$string['wrongqid'] = 'Question ID must be a positive integer';
$string['morethanonestep'] = 'Unexpected error in retrieve_question_answers function: More than one steps returned from DB';
$string['morethanonestepdata'] = 'Unexpected error in retrieve_question_answers function: More than one step_data returned from DB';
$string['wrongquestionid'] = 'Wrong value supplied for question id in retrieve_quiz_answers';
$string['requireonlineassignment'] = 'Wrong assignment type in retrieve_assignment_answers.  An online assignment is required';
$string['sortfailed'] = 'Sorting failed in postprocessing phase';
$string['similarity'] = 'Similarity';
$string['action'] = 'Action';
$string['view'] = 'View';
$string['hide'] = 'Hide';
$string['answerof'] = 'Answer of';
$string['plagiarismdetection'] = 'Collusion Detection';
$string['a/a'] = 'A/A';
$string['wrongquestiontype'] = 'Wrong Question type.  Only essay type question are allowed';
$string['question'] = 'Question';
$string['noessaytypequestions'] = 'No essay type questions found for plagiarism detection';
$string['plagiarism:editingteacher'] = 'Editing Teacher';
$string['plagiarism:admin'] = 'Admin User';
