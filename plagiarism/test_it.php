
<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once("class.stemmer.inc");
require_once("lib.php");


echo "Plagiarism testing" . "<br/>";
/*
$answerA = "my name is Giannis Potamitis and i am studying in Manchester. I will go";
$answerB = "studies are not always good. i hate them";
$answerC = "networks can have different nimes like giannis, potamitis, nick etc. I will depart"; */

$answerA = "This book has been written against a background of both reckless optimism and reckless despair. It holds that Progress and Doom are two sides of the same medal; that both are articles of superstition, not of faith. It was written out of the conviction that it should be possible to discover the hidden mechanics by which all traditional elements of our political and spiritual world were dissolved into a conglomeration where everything seems to have lost specific value, and has become unrecognizable for human comprehension, unusable for human purpose. Hannah Arendt, The Origins of Totalitarianism (New York: Harcourt Brace Jovanovich, Inc., 1973 ed.), p.vii, Preface to the First Edition.";
$answerB = "This book class has been written against a background of both reckless optimism and reckless despair. It holds that Progress and Doom are two sides of the same medal; that both are articles of superstition, not of faith. Interestingly enough, Arendt avoids much of the debates found in some of the less philosophical literature about totalitarianism.";
$answerC = "This book class has been written against a background of both reckless optimism and reckless despair. It holds that Progress and Doom are two sides of the same medal; that both are articles of superstition, not of faith.1 Interestingly enough, Arendt avoids much of the debates found in some of the less philosophical literature about totalitarianism. 1 Hannah Arendt, The Origins of Totalitarianism (New York: Harcourt Brace Jovanovich, Inc., 1973 ed.), p.vii, Preface to the First Edition.";

$settings = get_default_settings();
$answers = array (1 => $answerA, 2 => $answerB, 3 => $answerC);
$plagiarism = new Plagiarism(ASSIGNMENT, 10, $answers);

	// phase 1: preprocessing
	$answers = preprocessing($plagiarism, $settings);
	
	// phase 2: candidates retrieval
	$answers = candidates_retrieval($answers, $settings);
	
	// phase 3: in-depth analysis
	$similarities = indepth_analysis($answers, $settings);
	
	// phase 4: post processing
	$similarities = postprocessing($plagiarism, $similarities);
	
	print_r($similarities);
/*
foreach ($similarities as $similar) {
	foreach ($similar->spots as $user => $spot) {
		echo 'ANSWER OF USER ' . $user . '<br/>';
		echo $spot . '<br/>';
	}
	echo '<br/><br/>';
}*/

/*
echo '<br/><br/>';
$s = '<font style="background-color: yellow;">';
$e = '</font>';
$string = 'This book';

$string = str_replace(array('cat', 'hat', 'This'), array($s . 'cat' . $e, $s . 'hat' . $e, $s . 'This' . $e), $answerA);
echo $string; */
