
<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

echo '<style type="text/css">
					.ht{
        		background-color: yellow;
					}
				</style>';	

// taken from user ircmaxell at http://stackoverflow.com/questions/4081372/highlight-keywords-in-a-paragraph
// I just modified line $highlight->setAttribute('class', 'highlight') to $highlight->setAttribute('class', 'ht')   
function highlight_paragraph($string, $keyword) {
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
					echo 'text content: ' . $text . '<br/>';
    	    while (($pos = stripos($text, $keyword)) !== false) {
    	    		echo 'TEXT: ' . $text . '<br/>';
    	    		echo 'keyword found in: ' . $pos . '<br/>';
    	        $fragment->appendChild(new DomText(substr($text, 0, $pos)));
    	        echo 'Making a child from 0 to ' . $pos . '<br/>'; 
    	        $word = substr($text, $pos, strlen($keyword));
    	        echo 'Highlighting word: ' . $word . ' <br/>';
    	        $highlight = $dom->createElement('span');
    	        $highlight->appendChild(new DomText($word));
    	        //$highlight->setAttribute('class', 'highlight');
    	        $highlight->setAttribute('class', 'ht');
    	        $fragment->appendChild($highlight);
    	        $text = substr($text, $pos + strlen($keyword));
    	        echo 'TEXT IS NOT JUST: ' . $text . '<br/>';    
    	    }
    	    if (!empty($text)) $fragment->appendChild(new DomText($text));
    	    echo 'replacing child with fragment...<br/>';
    	    $element->replaceChild($fragment, $child);
    	}
	}
	//$string = $dom->saveXml($dom->getElementsByTagName('body')->item(0)->firstChild);
	$string = $dom->saveHTML();
	return $string;
}

$string = '<p>This book class has been written against a background of both reckless optimism and reckless despair.</p>
<p>It holds that Progress and Doom are two sides of the same medal; that both are articles of superstition, not of faith. It was written out of the conviction that it should be possible to discover the hidden mechanics by which all traditional elements of our political and spiritual world were dissolved into a conglomeration where everything seems to have lost specific value, and has become unrecognizable for human comprehension, unusable for human purpose.</p>
<p> Hannah Arendt, The Origins of Totalitarianism (New York: Harcourt Brace Jovanovich, Inc., 1973 ed.), p.vii, Preface to the First Edition.</p>';

$keywords = array('This', 'book', 'has', 'been', 'written', 'background', 'reckless', 'optimism', 'despair.', 'holds', 'Progress', 'Doom ', 'two', 'sides', 'medal;', 'articles', 'superstition,', 'faith.', 'lost', 'Arendt,', 'Totalitarianism', 'class');

foreach ($keywords as $kw) {
	echo '--------------For keyword ' . $kw . '<br/>';
	$string = highlight_paragraph($string, $kw);
	echo 'STRING UNTIL NOW: ' . $string . '<br/><br/>';
}

echo $string;
