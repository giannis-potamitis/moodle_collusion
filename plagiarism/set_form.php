<?php
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once $CFG->libdir.'/formslib.php';

class local_plagiarism_set_form extends moodleform {

    function definition() {
        global $CFG, $DB;
        $mform =& $this->_form;
        $data = &$this->_customdata;
        $mform->addElement('header', 'setheader');
        
        $select = array(0 => 'No', 1 => 'Yes');        

       	
        $def = $data->default_settings;
        
        // default ratio
        $mform->addElement('radio', 'usedefault', '', ' ' . get_string('usedefault', 'local_plagiarism') . ':', 1);
        
        $mform->addElement('html', '<br/>');  
           

        // the defaults
				$mform->addElement('static', 'default_jaccard', '','<b>1) </b>' . get_string('candidates_retrieval', 'local_plagiarism') . ': <b>' . $def->jaccard . ' %</b>');
				$mform->addHelpButton('default_jaccard', 'candidates_retrieval', 'local_plagiarism');
				
				$mform->addElement('static', 'default_indepth', '','<b>2) </b>' . get_string('indepth', 'local_plagiarism') . ': <b>' . $def->indepth . ' %</b>');
				$mform->addHelpButton('default_indepth', 'indepth', 'local_plagiarism');
				
				$mform->addElement('static', 'default_wordngrams', '','<b>3) </b>' . get_string('wordngrams', 'local_plagiarism') . ': <b>' . $def->wordngrams . '</b>');
				$mform->addHelpButton('default_wordngrams', 'wordngrams', 'local_plagiarism');
				
				$mform->addElement('static', 'default_allowspelling', '','<b>4) </b>' . get_string('allowspelling', 'local_plagiarism') . ': <b>' . $select[$def->allowspelling] . '</b>');
				$mform->addHelpButton('default_allowspelling', 'allowspelling', 'local_plagiarism');
				
				$mform->addElement('static', 'default_checksynonyms', '','<b>5) </b>' . get_string('checksynonyms', 'local_plagiarism') . ': <b>' . $select[$def->checksynonyms] . '</b>');
				$mform->addHelpButton('default_checksynonyms', 'checksynonyms', 'local_plagiarism');																
        
        $mform->addElement('html', '<br/><br/>');
        
        
        // specific ratio

        $mform->addElement('radio', 'usedefault', '', ' ' . get_string('usespecific', 'local_plagiarism') . ':', 0);
        $mform->addElement('html', '<br/>');
        
        
        // specifics
       	
       	$specarray = array();
        $specarray[] = &$mform->createElement('text', 'jaccard', '<b>1) </b>', array('size' => '3'));
        $specarray[] = &$mform->createElement('static', 'jaccard_per', '', '%');
				$mform->addGroup($specarray, 'jaccard_array', '<b>1) </b>', array(' '), false);
        $mform->disabledif('jaccard', 'usedefault', 'checked');
      
       	$specarray = array();
        $specarray[] = &$mform->createElement('text', 'indepth', '<b>2) </b>', array('size' => '3'));
        $specarray[] = &$mform->createElement('static', 'indepth_per', '', '%');
				$mform->addGroup($specarray, 'indepth_array', '<b>2) </b>', array(' '), false);        
        $mform->disabledif('indepth', 'usedefault', 'checked');
     
        $mform->addElement('text', 'wordngrams', '<b>3) </b>', array('size' => '3'));      
        $mform->disabledif('wordngrams', 'usedefault', 'checked');  
        
        $mform->addElement('select', 'allowspelling', '<b>4) </b>', $select);
        $mform->disabledif('allowspelling', 'usedefault', 'checked');
       
        $mform->addElement('select', 'checksynonyms', '<b>5) </b>', $select);
        $mform->disabledif('checksynonyms', 'usedefault', 'checked');
        
        $mform->addElement('html', '<br/><br/>');
               	        
        $mform->addElement('submit', 'setplagiarism', get_string('save', 'local_plagiarism'));


                                               
    }
    
    function validation($data, $files) {
    	global $DB;
    
    	$errors = parent::validation($data, $files);
    	if (isset($data['usedefault']) && $data['usedefault'] == 0) {
    	
    		if (empty($data['jaccard']) && $data['jaccard'] != 0) {
    			$errors['jaccard_array'] = get_string('cannotempty', 'local_plagiarism');
    		}
    		else if ($data['jaccard'] <= 0 || $data['jaccard'] > 100) {
    			$errors['jaccard_array'] = get_string('zeroonerange', 'local_plagiarism');
    		}
    		else if (!is_numeric($data['jaccard'])) {
    			$errors['jaccard_array'] = get_string('mustbenumber', 'local_plagiarism');
    		}
    		
    		if (empty($data['indepth']) && $data['indepth'] != 0) {
    			$errors['indepth_array'] = get_string('cannotempty', 'local_plagiarism');
    		}
    		else if ($data['indepth'] <= 0 || $data['indepth'] > 100) {
    			$errors['indepth_array'] = get_string('zeroonerange', 'local_plagiarism');
    		}
    		else if (!is_numeric($data['indepth'])) {
    			$errors['indepth_array'] = get_string('mustbenumber', 'local_plagiarism');
    		}    		
    		
    		if (empty($data['wordngrams']) && $data['wordngrams'] != 0) {
    			$errors['wordngrams'] = get_string('cannotempty', 'local_plagiarism');
    		}
    		else if (!is_numeric($data['wordngrams']) || $data['wordngrams'] != round($data['wordngrams']) || $data['wordngrams'] <= 0) {
    			$errors['wordngrams'] = get_string('positiveinteger', 'local_plagiarism');
    		} 		       		    		
    	}
    	return $errors;
    }    
}
