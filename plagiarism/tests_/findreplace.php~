
<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

$filename = '../db/plagiarism_wn_synonyms.sql';
$file = file_get_contents($filename);
file_put_contents($filename, str_replace('mdl_', $CFG->prefix,$file));
//file_put_contents($filename, str_replace('mdl_', 'man_',$file));

echo 'done';
