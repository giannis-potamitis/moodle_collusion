<?php

class block_catadmin extends block_base {
    public function init() {
        $this->title = get_string('catadmin', 'block_catadmin');
    }
    
		
    public function get_nav_item() {
			global $CFG;
			$url = $CFG->wwwroot . "/pix/i/navigationitem.png";
			return '<img src="' . $url .'" alt=""/>'; 
		}
    
    public function get_content() {
    	global $DB, $USER, $CFG;
    
 			$context = $this->page->context;
 			
 			if ($this->content !== null && strcmp($this->content->text, '') != 0) {
 				return $this->content;
 			}
 			
 			if (!is_readable($CFG->dirroot . '/local/cat/locallib.php')) {
 				if ($this->content == null) {
 					$this->content         =  new stdClass;
    			$this->content->text   = '';
    			$this->content->footer = '';
 				}
 				return $this->content;
 			}
 			
 			$html = '';
 			if (has_capability('local/cat:admin', $context)) { // add site level ranks link
 					$url = $CFG->wwwroot . '/local/cat/subranks.php?privilege=1';
 					$html .= $this->get_nav_item() . ' ' . '<a href="' . $url . '">' . get_string('siteranks', 'block_catadmin') . '</a><br/>'; 			
 			}
 			
 			if ($context->contextlevel >= 50) {
 				if (has_capability('local/cat:editingteacher', $context)) {
 					$url = $CFG->wwwroot . '/local/cat/subranks.php?cid=' . $this->page->course->id;
 					$html .= $this->get_nav_item() . ' ' . '<a href="' . $url . '">' . get_string('courseranks', 'block_catadmin') . '</a><br/>';
 				}
 			} 
 			$this->content         =  new stdClass;
    	$this->content->text   = $html;
    	$this->content->footer = '';
 			
    	return $this->content;
  }
  
  public function instance_allow_config() {
  	return true;
	}
	

} 
