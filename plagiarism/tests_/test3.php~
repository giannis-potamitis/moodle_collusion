
<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot . '/local/plagiarism/lib.php');

/*
$string = '<p>This book has been written against a background of both reckless optimism and reckless despair.</p>
<p>It holds that Progress and Doom are two sides of the same medal; that both are articles of superstition, not of faith. It was written out of the conviction that it should be possible to discover the hidden mechanics by which all traditional elements of our political and spiritual world were dissolved into a conglomeration where everything seems to have lost specific value, and has become unrecognizable for human comprehension, unusable for human purpose.</p>
<p> Hannah Arendt, <a href="http://www.google.com">The Origins</a> of <a href="http://www.yahoo.com">Totalitarianism</a> (New York: Harcourt Brace Jovanovich, Inc., 1973 ed.), p.vii, Preface to the First Edition.</p><p>href, http</p>';

echo $string . '<br/><br/><br/><br/>';

$before = '<span class="highlight">';
$after = '</span>';
$initial = array('Origins', 'Totalitarianism', 'traditional', 'href', 'http');
$replace = array($before.'Origins'.$after, $before.'Totalitarianism'.$after, $before.'traditional'.$after, $before.'href'.$after, $before.'http'.$after);

$dom = new DomDocument();
$dom->loadHtml($string);
$domlist = $dom->getElementsByTagName('*'); // use all the tags to retrieve elements
for ($i = 0; $i < $domlist->length; $i++) {
	$node = &$domlist->item($i);
	echo 'name: ' . $node->nodeName . ' value: ' . $node->nodeValue . '<br/><br/><br/>';
	
	//$node->nodeValue = str_replace($initial, $replace, $node->nodeValue); */
	
	/*
	$newnode = clone $node;
	$newnode->nodeValue = $newvalue;
	$dom->replaceChild($newnode, $node); */
	
	/*
}

echo $dom->saveHTML(); */

/*
$settings = get_default_settings();
print_r($settings);

echo '<br/><br/>';

$fromDB = $DB->get_record('plagiarism_settings', array('courseid' => 4));
print_r($settings); */

$word = $_GET['word'];

$lang = pspell_new("en");

if (!pspell_check($lang, $word)) {
	$suggest = pspell_suggest($lang, $word);
	$min_suggest = $word;
	$min = MAX_INT;
  foreach ($suggest as $s) {
  	$dis = levenshtein($word, $s); // the levenshtein distance
  	if ($dis < $min) {
  		$min = $dis;
  	  $min_suggest = $s;
  	}
  }
  
  echo 'MOST CLOSE SUGGESTION: ' . $min_suggest . '<br/><br/>'; 
  echo 'All SUGGESTIONS: ' . '<br/>'; print_r($suggest);
}
else {
	echo 'CORRECT<br/>';
}
