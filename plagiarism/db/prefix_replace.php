
<?php

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

require_login();

$context = get_context_instance(CONTEXT_USER, $USER->id);
require_capability('local/plagiarism:admin', $context);

$filename = 'plagiarism_wn_synonyms.sql';
$file = file_get_contents($filename);
file_put_contents($filename, str_replace('mdl_', $CFG->prefix,$file));

echo 'done';
