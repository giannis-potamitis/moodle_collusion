<?php

require_once($CFG->dirroot . '/mod/quiz/attemptlib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');

//defined('MOODLE_INTERNAL') || die();

// Similar to get_string function. Since get_string cannot be used
// in my_plagiarism because is not a module/plugin I need to re-implement it
function str($w) {
	//require_once ("lang/en/my_plagiarism.php");
	require ("lang/en/local_plagiarism.php");
	if (isset($string[$w])) {
		return $string[$w];
	}
	else {
		return '[[' . $w . ']]';
	}
}

define('ASSIGNMENT', 0);
define('QUIZ', 1);

// plagiarism detection thresholds. Default settings
define('WORDNGRAMS', 1); // word-n-grams: number of n
define('JACCARD', 0.1); // the minimum jaccard similarity (threshold)
define('INDEPTH', /*0.65*/0.30); // the minimum indepth similarity (threshold)
define('ALLOWSPELLING', 1);
define('CHECKSYNONYMS', 1);

define('MAX_INT', 2147483647); // the maximum integer

// the general object containing all the answers of quiz or online assignment
class Plagiarism {
	// the type of assignment
	protected $type;
	
	// id of module (either the id of online assignment or Quiz essay)
	protected $mid;
	
	// the question id of the essay like question if type == QUIZ
	protected $qid;
	
	// Answers: Array of Strings: key is the userid and value the actual answer
	protected $answers;
	
	// constructor
	public function __construct($t, $i, $a, $q=-1) {
		$this->set_type($t);
		$this->set_mid($i);
		$this->set_answers($a);
		$this->set_qid($q);
	}
	
	// return the type
	public function type() {
		return $this->type;
	}
	
	public function mid() {
		return $this->mid;
	}
	
	public function answers() {
		return $this->answers;
	}
	
	public function set_type($t) {
		if ($t != ASSIGNMENT && $t != QUIZ) {
			print_error(get_string('wrongtype','local_plagiarism'));
			die;
		}
		$this->type = $t;
	}
	
	public function set_mid($i) {
		if ($i < 0) {
			print_error(get_string('wrongid', 'local_plagiarism'));
			die;
		}
		$this->id = $i;
	}
	
	public function set_answers($a) {
		$this->answers = $a;
	}
	
	public function set_qid($q=-1) {
		if ($q <= 0 && $this->type == QUIZ) {
			print_error(get_string('wrongqid', 'local_plagiarism'));
			die;
		}
		$this->qid = $q;
	}
}

// the Answer Object
class Answer {
	public $userid; // the id of user who provided that answer
	public $words; // array of words
}

// the Word Object
class Word {
	public $original; // the original world (string)
	public $modified; // the modified world (string)
}

/* phase 1: preprocessing. It contains a number of stages
	 @param $settings - Settings object for plagiarism detection
   @param $plagiarism The General Plagiarism Object
   @return $answers An array of Answers 
*/
function preprocessing($plagiarism, $settings) {

	require_once("stopwords.php"); // for stage 6
	$answers = array(); // the array of answers
	foreach ($plagiarism->answers() as $key=>$a) { // since not &$a then $a remains unchaned
		
		// stage 1: remove tags
		$a = strip_tags($a); // $a remains unchanged in the original array
		
		// stage 2: split by spaces
		$words = preg_split("/\s+/", $a);
		
		// stage 3: create the Answer and Words objects and perform to lower 
		$answer = pre_stage3($key, $words);
		
		// stage 4: Remove unwanted characters
		$answer = pre_stage4($answer);
		
		// stage 5: Spelling Correction
		if ($settings->allowspelling == 1) {
			$answer = pre_stage5($answer);
		}
		
		// stage 6: Porter Stemming Algorithm
		$answer = pre_stage6($answer);
		
		// stage 7: Stop words removal
		$answer = pre_stage7($answer, $stop);
		
		$answers[$key] = $answer;
	}
	
	return $answers;
}

// Preprocessing stage 3: create the Answer and Words objects and perform to lower 
function pre_stage3($key, $words) {
	$answer = new Answer();
	$answer->userid = $key;
	$answer->words = array();
	foreach ($words as $w) {
		$wobject = new Word(); // a new word object
		$wobject->original = $w;
		$wobject->modified = strtolower($w);
		$answer->words[$w] = $wobject; // the key is the original word
	}
	return $answer;	
}

// Preprocessing stage 4: Remove unwanted characters
function pre_stage4($answer) {
	foreach ($answer->words as $w) {
		$w->modified = preg_replace('/[^A-Za-z\ ]+/', '', $w->modified);
	}
	return $answer;	
}

// Preprocessing stage 5: Spelling Correction
function pre_stage5($answer) {
	foreach ($answer->words as $w) {
		
		// code taken from http://www.php.net/manual/en/function.pspell-suggest.php	
		$lang = pspell_new("en");

		if (!pspell_check($lang, $w->modified)) {
  	  $suggest = pspell_suggest($lang, $w->modified);
			$min_suggest = $w->modified;
			$min = MAX_INT;
  	  foreach ($suggest as $s) {
  	  	$dis = levenshtein($w->modified, $s); // the levenshtein distance
  	  	if ($dis < $min) {
  	  		$min = $dis;
  	  		$min_suggest = $s;
  	  	}
  	  }
    
  	  $w->modified = $min_suggest;
		}
	}
	return $answer;
}

// Preprocessing stage 6: Porter Stemming Algorithm
function pre_stage6($answer) {
	require_once("class.stemmer.inc");
	$stem = new Stemmer();
	foreach ($answer->words as $w) {
		$w->modified = $stem->stem($w->modified);
	}
	return $answer;
}

// Preprocessing stage 7: Stop words removal
function pre_stage7($answer, $stop) {
	//require("stopwords.php");
	foreach ($answer->words as $key=>$value) {
		if (isset($stop[$value->modified]) || isset($stop[$key])) {
			unset($answer->words[$key]);
		}
	}
	return $answer;
}

/* @param $n - Number of successive words
   @param $answers - An array of answers
   @return - An array of array of strings. Each array of string contains word-n-grams
*/ 
function word_n_grams($n, $answers) {
	$return = array();
	foreach ($answers as $key => $ans) {
		$ngrams = array();
		$i = 0;
		$words = array_values($ans->words); // convert associative array into indexed one
		$extra = ""; // extra string
		$first_extra = true;
		while ($i < count($words)) {
			$string = "";
			$initial = true;
			$loop = false;
			for ($j = $i; $j < ($i+$n) && ($i+$n) <= count($words); $j++) {
				$loop = true;
				if ($initial) {
					$initial = false;
				}
				else {
					$string .= " ";
				}
				$string .= $words[$j]->modified;
			}
			
			if ($loop) { 
				$ngrams[] = $string;
			}
			else {
				if ($first_extra) {
					$first_extra = false;
				}
				else {
					$extra .= " ";
				}
				$extra .= $words[$i]->modified;		
			}
			$i++;
		}
		if (!empty($extra)) {
			$ngrams[] = $extra;
		}
		
		$return[$key] = $ngrams;
	}
	return $return;
}

// computes jaccard similarity between two arrays
function jaccard($answerA, $answerB) {
	$intersection = array_intersect($answerA, $answerB);
	return count($intersection) / (count($answerA) + count($answerB));
}

/* Performs jaccard similarity on each pair of documents
	@param $settings - Settings object for plagiarism detection
	@param $ngrams - Array of n grams
  @Returns $answers array but each answer object has an extra array of users' id
  such that this answer is similar to the other one */
function produce_candidates($answers, $ngrams, $settings) {
	foreach ($ngrams as $keyA => $gramsA) {
		$candidates = array();
		foreach ($ngrams as $keyB => $gramsB) {
			if ($keyA != $keyB) {
				$jaccard = jaccard($gramsA, $gramsB);
				if ($jaccard >= $settings->jaccard) {
					// then there may exist plagiarism
					$candidates[$keyB] = $keyB;
				}
			}
		}
		$answers[$keyA]->candidates = $candidates;
	}
	return $answers;
}

/* phase 2: candidates retrieval.
   @param An array of Answers
	 @param $settings - Settings object for plagiarism detection   
   @return An array of Answers but each with an extra field
   That extra field will be an array of users' id that are candidates to this answer */
function candidates_retrieval($answers, $settings) {
	$n = $settings->wordngrams; // define n. to be used in splitting answer in word-n-grams
	
	// stage 1: get word-n-grams
	$ngrams = word_n_grams($n , $answers);
	
	// stage 2: retrieve candidate answers
	$answers = produce_candidates($answers, $ngrams, $settings);
	
	return $answers;
}

class pair {
	public $x;
	public $y;
	public function __construct($theX, $theY) {
		$this->x = $theX;
		$this->y = $theY;
	}
}

class similar {
	public $pair; // a pair object
	public $words; // array of array of words. The key is the userid and value the
								 // array of words.  Each array of words contain word objects that are similar with the array of words 
								 // of the other user
	public $similarity; // their actual similarity number
	public $valid; // a boolean value: true if that similarity is above threshold, false otherwise 
}

/*
	Calculates the fuzzy similarity between two words
	@param $wordA - the first word object
	@param $wordB - the second word object
	@param similarA - array of words of A that are similar to B
	@param similarB - array of words of B that are similar to A	
	@param $settings - Settings object for plagiarism detection
	@return i) 0 if they are not equal ii) 1 if they are identical
					iii) 0.5 if they are synonym
*/
function fuzzy_similarity($wordA, $wordB, &$similarA, &$similarB, $settings) {
	
	if (strcmp($wordA->modified, $wordB->modified) == 0) {
		$similarA[$wordA->original] = $wordA;
		$similarB[$wordB->original] = $wordB;
		return 1;
	}

	if ($settings->checksynonyms == 0) {
		$synA = array();
		$synB = array();
	}
	else {
		$synA = get_synonyms($wordA);
		$synB = get_synonyms($wordB);			
	}
	if (in_array($wordA->modified, $synB, true) || in_array($wordB->modified, $synA, true)) { // true means strict checking
		echo '<br/>' . $wordA->modified . ' AND ' . $wordB->modified . ' are synonym<br/>'; 
		$similarA[$wordA->original] = $wordA;
		$similarB[$wordB->original] = $wordB;	
		return 0.5; // words are synonym
	}
	else {
		return 0;
	}
}

/*
	To be used in phase 3
	@param $wordsA an array of words
	@param $wordsB an array of words
	@param similarA - array of words of A that are similar to B
	@param similarB - array of words of B that are similar to A
	@param $settings - Settings object for plagiarism detection			
	@return the correlation between all the words
*/
function correlation($wordsA, $wordsB, &$similarA, &$similarB, $settings) {
	$correlation = 0;
	foreach ($wordsA as $a) {
		$product = 1;
		foreach ($wordsB as $b) {
			$fuzzy = fuzzy_similarity($a, $b, $similarA, $similarB, $settings);
			$product = $product * (1 - $fuzzy);
		}
		$correlation = $correlation + (1 - $product);
	}
	
	return $correlation;
}

/*
	phase 3: In-depth analysis of candidate answers
	@param $answers - An array of answers with an extra "candidates" field
	@param $settings - Settings object for plagiarism detection
	@return An array of similar objects
*/

$synonyms = array(); // array of synonyms. key is the word

function indepth_analysis($answers, $settings) {

	global $CFG, $synonyms;
	
	$similarities = array(); // array of similarities
	
	foreach ($answers as $key => $ans) { // wordsA
		foreach ($ans->candidates as $cands) { // wordsB
			echo 'INDEPTH OF ' . $key . ' WITH ' . $cands . '<br/>';
			$similarA = array(); // the similar words of first answer
			$similarB = array(); // the similar words of second answer
			$corel = correlation($ans->words, $answers[$cands]->words, $similarA, $similarB, $settings);
			$similarity = $corel / count($ans->words);
			echo 'Similarity: ' . $similarity . '<br/>'; 
			
			$stringA = $key . '.' . $cands;
			$stringB = $cands . '.' . $key;
			if (!isset($similarities[$stringA]) && !isset($similarities[$stringB])) {
				echo 'create new similar object with key ' . $stringA . '<br/>';
				$similar = new similar();
				$similar->pair = new pair($key, $cands);
				$similar->words = array ($key => $similarA, $cands => $similarB);
				$similar->similarity = $similarity;
				
				$similar->valid = $similarity >= $settings->indepth; // true if greater or equal, false otherwise
				
				$similarities[$stringA] = $similar;
				
			}
			else if (isset($similarities[$stringB])) {
				echo 'similar object with key ' . $stringB . ' alreay exists<br/>'; 
				$temp_sim = $similarities[$stringB]->similarity;
				$avg = ($similarity + $temp_sim) / 2; // use their average similarity
				$similarities[$stringB]->valid = $avg >= $settings->indepth; // if less than false otherwise true
				$similarities[$stringB]->similarity = $avg;
			}
			else { // else isset($similarities[$stringA] : it should NEVER reach here
				print_error(get_string('indepthunexpected', 'local_plagiarism'));
				die;
			}
		}
	}
	
	return $similarities;
}
/*
// taken from http://stackoverflow.com/questions/4081372/highlight-keywords-in-a-paragraph
function highlight($string, $keyword) {
	//$string = '<p>foo<b>bar</b></p>';
	//$keyword = 'foo';
	$dom = new DomDocument();
	$dom->loadHtml($string);
	$xpath = new DomXpath($dom);
	$elements = $xpath->query('//*[contains(.,"'.$keyword.'")]');
	foreach ($elements as $element) {
  	  foreach ($element->childNodes as $child) {
  	      if (!$child instanceof DomText) continue;
  	      $fragment = $dom->createDocumentFragment();
   	     $text = $child->textContent;
    	    $stubs = array();
    	    while (($pos = stripos($text, $keyword)) !== false) {
    	        $fragment->appendChild(new DomText(substr($text, 0, $pos)));
    	        $word = substr($text, $pos, strlen($keyword));
    	        $highlight = $dom->createElement('span');
    	        $highlight->appendChild(new DomText($word));
    	        $highlight->setAttribute('class', 'highlight');
    	        $fragment->appendChild($highlight);
    	        $text = substr($text, $pos + strlen($keyword));
    	    }
    	    if (!empty($text)) $fragment->appendChild(new DomText($text));
    	    $element->replaceChild($fragment, $child);
    	}
	}
	$string = $dom->saveXml($dom->getElementsByTagName('body')->item(0)->firstChild)
}*/

/*
	@param $words - An array of word objects
	@param $original - boolean If true deal with original words else with modified
	@param/return $initial - An array of strings.  This will initially be empty.
													 After function execution will contain strings, the actual words
													 that we want to add some extra bits before and after them
	@param/return $replace - An array of strings.  This will be initially be empty.
													 After function execution will contain strings.  Each string will be
													 a word of $initial surrounded by $before and $after strings
	@param $before string - Usually a starting HTML tag
	@param $after string	- Usually an ending HTML tag 
*/
function words_to_arrays($words, $original=true, &$initial, &$replace, $before, $after) {
	foreach ($words as $word) {
		if ($original) {
			$string = $word->original;
		}
		else {
			$string = $word->modified;
		}
		$initial[] = $string;
		$replace[] = $before . $string . $after;
	}
}


/*
	@param $words - An array of word objects
	@param $original - boolean If true deal with original words else with modified
	@param $before string - Usually a starting HTML tag
	@param $after string	- Usually an ending HTML tag
	@param $answer string - A student's answer
	@return string - the $answer string but with some words surrounded with $before and $after strings  	
*/
function get_spot($words, $original, $before, $after, $answer) {
	$initial = array();
	$replace = array();
	words_to_arrays($words, $original, $initial, $replace, $before, $after);
	return str_replace($initial, $replace, $answer);
}

/*
	phase 4: Postprocessing
	@param $plagiarism - The main plagiarism object
	@param $similarities - The array of similar objects of phase 3
	@return $similarities - The array of similar objects but each object will have a new property (spots).
													The "spots" property will be an array of strings whose key is the userid
													and value a string similar to initial answer of that student but with plagiarised
													words highlighted.
*/
function postprocessing($plagiarism, $similarities) {
	global $DB, $CFG;
	
	
	echo '<style type="text/css">
					.ht{
        		background-color: yellow;
					}
				</style>';	
	
	//$font_start = '<font style="background-color: yellow;">';
	//$font_end = '</font>';
	$font_start = '<span class="ht">';
	$font_end = '</span>';
	
	$answers = $plagiarism->answers();
	$nsimilarities = array();
	
	foreach ($similarities as $key => $similar) {
		if (empty($similar->valid) || $similar->valid == 0) {
			continue;
		}
		$spots = array();
		
		// for x: first user
		$spots[$similar->pair->x] = get_spot($similar->words[$similar->pair->x], true, $font_start, $font_end, $answers[$similar->pair->x]);
		
		// for y: second user
		$spots[$similar->pair->y] = get_spot($similar->words[$similar->pair->y], true, $font_start, $font_end, $answers[$similar->pair->y]);
		  
		$similar->spots = $spots;
		$nsimilarities[$key] = $similar;
	}
	
	// sort the nsimilarities array
	$success = uasort($nsimilarities, 'cmp_similar');
	if (!$success) {
		print_error(get_string('sortfailed', 'local_plagiarism'));
		die;
	}
	
	return $nsimilarities; 
}

/*
	A function which compares two similar object
	Returns 0 if they are equal, < 0 if $a is less than $b and > 0 if $a is greater than b
*/
function cmp_similar($a, $b) {
	if ($a->similarity > $b->similarity) {
		return -1; // $a should appear first since has higher similarity
	}
	else if ($a->similarity < $b->similarity) {
		return 1;
	}
	else { // equal
		return 0;
	}
}

/*
	A more efficient way of getting synonyms
	@param word - A word object whose synonyms are needed
	@return an array of strings - words that are synonyms
*/
function get_synonyms($word) {
	
	global $DB, $CFG, $synonyms;
	
	$original = strtolower($word->original);
	$modified = $word->modified;
	if (isset($synonyms[$original])) {
		return $synonyms[$original];
	}
	
	if (isset($synonyms[$modified])) {
		return $synonyms[$modified];
	}
	
	$same = false;
	if (strcmp($original, $modified) == 0) {
		$same = true;
	}
	
	echo 'getting synonyms for word: ' . $original . ' | ' . $modified . '<br/>';
		
	$tblsynonyms = $CFG->prefix . 'wn_synonyms';	
	if ($same) {
		$sql = 'SELECT wordno, synonyms FROM ' . $tblsynonyms . ' WHERE lemma = "' . $modified . '"';
	}
	else {
		$sql = 'SELECT wordno, synonyms FROM ' . $tblsynonyms . ' WHERE lemma = "' . $original . '" OR lemma = "' . $modified . '"';	
	}
	
	$ret = $DB->get_records_sql($sql);
	$edit = array();
	foreach ($ret as $r) {
		$edit[] = $r->synonyms;
	}
	
	$syns = array();
	foreach ($edit as $e) {
		$tmp = explode('|', $e);
		$syns = array_merge($tmp, $syns);
	}
						
	$synonyms[$modified] = $syns;
	if (!$same) {
		$synonyms[$original] = $syns;
	}
	return $syns;
				  
}


/*
	Getting synonyms in an INEFFICIENT way. Do not use
	@param word - A word object whose synonyms are needed
	@return an array of strings - words that are synonyms
*/
function get_synonyms_inefficient($word) {

	//return array();
	
	global $DB, $CFG, $synonyms;
	
	$original = strtolower($word->original);
	$modified = $word->modified;
	if (isset($synonyms[$original])) {
		return $synonyms[$original];
	}
	
	if (isset($synonyms[$modified])) {
		return $synonyms[$modified];
	}
	
	$same = false;
	if (strcmp($original, $modified) == 0) {
		$same = true;
	}
	
	//echo 'getting synonyms for word: ' . $original . ' | ' . $modified . '<br/>';
	
	$tblword = $CFG->prefix . 'wn_word';
	$tbllexrel = $CFG->prefix . 'wn_lexrel';
	$tblsense = $CFG->prefix . 'wn_sense';
	
	if ($same) {			  
		$step1 = 'SELECT wordno FROM ' . $tblword . ' WHERE lemma = "' . $modified . '"';
	}
	else {
		$step1 = 'SELECT wordno FROM ' . $tblword . ' WHERE lemma = "' . $original . '" OR lemma = "' . $modified . '"';
	}
	$step2 = 'SELECT synsetno FROM ' . $tblsense . ' WHERE wordno IN (' . $step1 . ')';
	$step3 = 'SELECT wordno1, wordno2 FROM ' . $tbllexrel . ' WHERE synsetno1 IN (' . $step2 . ') OR synsetno2 IN (' . $step2 . ')';
	if ($same) {
		$step4 = 'SELECT DISTINCT w.lemma FROM ' . $tblword . ' w, (' . $step3 . ') l
						WHERE (w.wordno = l.wordno1 OR w.wordno = l.wordno2) AND lemma <> "' . $modified . '"';	
	}
	else {
		$step4 = 'SELECT DISTINCT w.lemma FROM ' . $tblword . ' w, (' . $step3 . ') l
						WHERE (w.wordno = l.wordno1 OR w.wordno = l.wordno2) AND w.lemma <> "' . $original . '" AND lemma <> "' . $modified . '"';
	}					
	$array = array_keys($DB->get_records_sql($step4));
	$synonyms[$modified] = $array;
	if (!$same) {
		$synonyms[$original] = $array;
	}
	return $array;
				  
}

// A utility function that popups database table wn_synonyms
function db_synonyms_popup() {
	global $DB, $CFG;
	$start_time = microtime(true);
	echo 'STARTING PROCESS: <br/><br/>';
	//$words = $DB->get_records('wn_word');
	
	//$sql = 'SELECT * FROM ' . $CFG->prefix . 'wn_word2 WHERE lemma NOT IN (SELECT lemma FROM ' . $CFG->prefix . 'wn_synonyms' . ')';
	//$sql = 'SELECT * FROM ' . $CFG->prefix . 'wn_word2';
	$sql = 'SELECT * FROM ' . $CFG->prefix . 'wn_word WHERE lemma NOT IN (SELECT lemma FROM ' . $CFG->prefix . 'wn_synonyms' . ')';	
	$words = $DB->get_records_sql($sql);
	//$i = 0;
	foreach ($words as $word) {
		try {
		
		/*
		if ($DB->record_exists('wn_synonyms', array ('lemma' => $word->lemma))) {
			continue;
		} */
		
		$wobj = new Word();
		$wobj->original = $word->lemma;
		$wobj->modified = $wobj->original;
		$syn = get_synonyms($wobj);
		$synonyms = ""; // string of synonyms
		$first = true;
		foreach ($syn as $s) {
			$str = strtolower($s);
			if ($first) {
				$first = false;
				$synonyms .= $str;
			}
			else {
				$synonyms .= '|' . $str;
			}
			//$i++;
			//echo 'Processed ' . $i . ' words! Last was: ' . $word->lemma . '<br/>'; 
		}
		
		$store = new stdClass();
		$store->wordno = $word->wordno;
		$store->lemma = $word->lemma;
		$store->synonyms = $synonyms;
		$DB->insert_record('wn_synonyms', $store);
		}
		catch (Exception $e) {
			echo 'Exception ' . $e->getMessage() . '<br/>';
			continue;	
		}
	}
	
	echo '<br/>PROCESS DONE IN: ' . (microtime(true) - $start_time) . ' seconds<br/>';
}

/*
	@return An object with the default embedded settings of plagiarism detection system 
*/
function get_default_settings() {
	$plag = new stdClass();
	$plag->indepth = INDEPTH;
	$plag->jaccard = JACCARD;
	$plag->allowspelling = ALLOWSPELLING;
	$plag->checksynonyms = CHECKSYNONYMS;
	$plag->wordngrams = WORDNGRAMS;
	return $plag;
}

/*
	The main method for plagiarism detection
	@param $type eg ASSIGNMENT or QUIZ
	@param $mid - id of module.
	@param $qid - the  question id if type == QUIZ
	@return $similarities - An array of similar objects where each similar object will have one new extra property (spots).
													The "spots" property will be an array of strings whose key is the userid
													and value a string similar to initial answer of that student but with plagiarised
													words highlighted.	
*/
function plagiarism_detection($type, $mid, $qid = -1) {
	global $DB;
	
	$settings = get_default_settings();
	
	// gather answers from the DB
	if ($type == QUIZ) {
		$single_answers = retrieve_quiz_answers($mid, $qid, 1); // attemptno = 1
	}
	else if ($type == ASSIGNMENT) {
		$single_answers = retrieve_assignment_answers($mid);
	}
	else { // it should not reach here
		print_error(get_string('wrongtype', 'local_plagiarism'));
		die;
	}
	
	$plagiarism = new Plagiarism($type, $mid, $single_answers, $qid);
	// phase 1: preprocessing
	$answers = preprocessing($plagiarism, $settings);
	
	// phase 2: candidates retrieval
	$answers = candidates_retrieval($answers, $settings);
	
	// phase 3: in-depth analysis
	$similarities = indepth_analysis($answers, $settings);
	
	// phase 4: post processing
	$similarities = postprocessing($plagiarism, $similarities);
	
	return $similarities;
}

/*
	A function to retrieve answers of a quiz question
	@param $quiz object
	@param $course object that this quiz belongs to
	@param $cm object - the course module object
	@param $questionid int the id of questions
	@param $users array of ints, the userids of users who have submit answers for that quiz
	@param $attemptno int The answers of which attempt we should get. Eg 1 for the first attempt, 2 for the second
	@return $answers an array of strings. Key is the userid and value the user's answer
*/
function retrieve_question_answers($quiz, $course, $cm, $questionid, $users, $attemptno) {
	global $DB, $CFG;
	$answers = array();
	foreach ($users as $userid) {
		$attempt = $DB->get_record('quiz_attempts', array('userid' => $userid, 'quiz' => $quizid, 'attempt' => $attemptno), '*', MUST_EXIST);
		$quiz_attempt = new quiz_attempt($attempt, $quiz, $cm, $course);	
		$slot = quiz_get_slot_for_question($quiz, $questionid);
		$question_attempt = $quiz_attempt->get_question_attempt($slot);
		
		$sql = 'SELECT * FROM ' . $CFG->prefix . 'question_attempt_steps WHERE questionattemptid = ' . $question_attempt->get_database_id() . ' AND state="complete" AND userid = ' . $userid;
		$steps = $DB->get_records_sql($sql);
		if ($steps != null) {
			if (count($steps) > 1) {
				print_error(get_string('morethanonestep', 'local_plagiarism'));
				die;
			}
			else {
				$step = reset($steps); // it should always return one object
			}
			$sql = 'SELECT * FROM ' . $CFG->prefix . 'question_attempt_step_data WHERE attemptstepid = ' . $step->id . ' AND name="answer"';
			$data_ = $DB->get_records_sql($sql);
			if ($data != null) {
				if (count($data_) > 1) {
					print_error(get_string('morethanonestepdata', 'local_plagiarism'));
					die;
				}
				else {
					$data = reset($data_); // it should always return one object
				}			
				$answers[$userid] = $data->value;
		 }
	 }		
	}
	return $answers;
}

/*
	A function to retrieve the answers of all students who have submit answers on a quiz
	This function will either return answers on a specific quiz question or on all the questions
	@param $quizid int - The id of the quiz
	@param $questionid - The id of an essay type question we are interested at or -1 for all essay type questions
	@param $attemptno int The answers of which attempt we should get. Eg 1 for the first attempt, 2 for the second
	@return $answers - i) When $questionid > 0 it will return an array of strings whose key is the userid and value the user's answer.
										 ii) When $questionid == -1 it will return an array of array of strings. The key will be the question id and
										 the value an array of strings as in i).
*/
function retrieve_quiz_answers($quizid, $questionid, $attemptno) {
	global $DB, $CFG;
	$quiz = $DB->get_record('quiz', array('id' => $quizid), '*', MUST_EXIST);
	$course = $DB->get_record('course', array('id' => $quiz->course), '*', MUST_EXIST);
	$cm = get_coursemodule_from_instance('quiz', $quiz->id, $course->id);
	$sql = 'SELECT DISTINCT userid FROM ' . $CFG->prefix . 'quiz_attempts WHERE attempt = ' . $attemptno . ' AND quiz = ' . $quizid . ' AND timefinish > 0';
	$users = array_keys($DB->get_records_sql($sql)); // this is fine since the userid will be used as key to describe each object returned
	if ($questionid > 0) {
		return retrieve_question_answers($quiz, $course, $cm, $questionid, $users, $attemptno);
	}
	else if ($questionid == -1) { // retrieve the answers of all essay type questions
		$insql = 'SELECT id FROM ' . $CFG->prefix . 'quiz_question_instances WHERE quiz = ' . $quiz->id; 
		$sql = 'SELECT * FROM ' . $CFG->prefix . 'question WHERE qtype="essay" AND id IN (' . $insql . ')';
		$questions = $DB->get_records_sql($sql);
		$answers = array();
		foreach ($questions as $question) {
			$answers[$question->id] = retrieve_question_answers($quiz, $course, $cm, $question->id, $users, $attemptno);
		}
		return $answers;
	}
	else {
		print_error(get_string('wrongquestionid', 'local_plagiarism'));
		die;
	}
}

/*
	A function to retrieve the answers given by students in an online assignment
	@param $assignmentid - The id of an online assignment
	@return $answers - An array of strings. Key is the userid and value the user's answer
*/
function retrieve_assignment_answers($assignmentid) {
	global $DB, $CFG;
	$assignment = $DB->get_record('assignment', array('id' => $assignmentid), '*', MUST_EXIST);
	if (strcmp($assignment->assignmenttype, 'online') != 0) {
		print_error(get_string('requireonlineassignment', 'local_plagiarism'));
		die;
	}
	$submissions = $DB->get_records('assignment_submissions', array('assignment' => $assignmentid));
	$answers = array();
	foreach ($submissions as $sub) {
		$answers[$sub->userid] = $sub->data1;
	}
	
	return $answers;
}
