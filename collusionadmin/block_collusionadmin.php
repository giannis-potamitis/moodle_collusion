<?php

class block_collusionadmin extends block_base {
    public function init() {
        $this->title = get_string('collusionadmin', 'block_collusionadmin');
    }
    
		
    public function get_nav_item($id = null, $parent = null, $visible = true) {
			global $CFG;
			if ($parent == 1) {
				$url = $CFG->wwwroot . "/pix/t/collapsed.png";
			}
			else if ($parent == 2) {
				$url = $CFG->wwwroot . "/pix/t/expanded.png";
			}
			else {
				$url = $CFG->wwwroot . "/pix/i/navigationitem.png";
			}
			
			$visibility = $visible ? 'visible' : 'hidden';
			if ($id != null) {
				$html = '<img id="' . $id . '" src="' . $url .'" alt="" style="visibility:' . $visibility . ';"/>';
			}
			else {
				$html = '<img src="' . $url .'" alt="" style="visibility:' . $visibility . ';"/>';
			}
			return $html; 
		}
    
    public function get_content() {
    	global $DB, $USER, $CFG, $PAGE;
    
 			$context = $this->page->context;
 			
 			if ($this->content !== null && strcmp($this->content->text, '') != 0) {
 				return $this->content;
 			}
 			
 			if (!is_readable($CFG->dirroot . '/local/plagiarism/lib.php')) {
 				if ($this->content == null) {
 					$this->content         =  new stdClass;
    			$this->content->text   = '';
    			$this->content->footer = '';
 				}
 				return $this->content;
 			}
 			
 			$html = '';
 			if ($context->contextlevel >= 50) {
 				if (has_capability('local/plagiarism:editingteacher', $context)) {
 					// add the settings link
 					$url = $CFG->wwwroot . '/local/plagiarism/set_plagiarism.php?cid=' . $this->page->course->id;
 					$html .= $this->get_nav_item() . ' ' . '<a href="' . $url . '">' . get_string('settings', 'block_collusionadmin') . '</a><br/>';
 					
 					if ($context->contextlevel == 70) { // a module
 						$cm = $this->page->cm;
 						$module = $DB->get_record('modules', array('id' => $cm->module), '*', MUST_EXIST);
 						if (strcmp($module->name, 'assignment') == 0) { // assignment module
 							$assignment = $DB->get_record('assignment', array('id' => $cm->instance), '*', MUST_EXIST);
 							if (strcmp($assignment->assignmenttype, 'online') == 0) { // an online text assignment
 								require_once($CFG->dirroot . '/local/plagiarism/lib.php');
 								$url = $CFG->wwwroot . '/local/plagiarism/plagiarism.php?type=' . ASSIGNMENT . '&mid=' . $assignment->id;
 								$html .= $this->get_nav_item() . ' ' . '<a href="' . $url . '">' . get_string('similarity', 'block_collusionadmin') . '</a><br/>';
 							}
 						}
 						else if (strcmp($module->name, 'quiz') == 0) { // quiz module
 							
 							$qtypes = $DB->get_records_sql('
        				SELECT q.id, q.qtype, q.name
        				FROM {question} q
        				JOIN {quiz_question_instances} qqi ON qqi.question = q.id
        				WHERE qqi.quiz = ? AND q.qtype = "essay"
        				ORDER BY id', array('quizid' => $cm->instance));

							if ($qtypes != null) {	
								$html .= '<style type="text/css">
														table.collusiontbl {
															position: relative;
															border-collapse: collapse;
														}
													
														table.collusiontbl td {
															padding: 0;
														}
														table.simitemstbl {
															position: relative;
															left: 8%;
															display: none;
															
														}
													</style>';
												
								$html .= '<script type="text/javascript">
														function show_hide_items(wwwroot) {
															var items = document.getElementById("simitems");
															var image = document.getElementById("simimage");
															if (items.style.display == "none" || items.style.display == "") {
																items.style.display = "block";
																image.src = wwwroot + "/pix/t/expanded.png";
															}
															else {
																items.style.display = "none";
																image.src = wwwroot + "/pix/t/collapsed.png";
															}
														}
													</script>';

								$html .= '<table class="collusiontbl">';
								$html .= '<tr>';
								$html .= '<td colspan="2" style="cursor: pointer" onclick = "show_hide_items(\'' . $CFG->wwwroot . '\')">' 
																. $this->get_nav_item("simimage", 1, true) 
																/*. $this->get_nav_item("simexpanded", 2, false)	*/
															  . ' ' . get_string('similarity', 'block_collusionadmin') . '</td>';
								$html .= '</tr>';
							
								// items
								$html .= '<tr>';
								$html .= '<td></td>';
								$html .= '<td><table id="simitems" class="simitemstbl">';
								
								require_once($CFG->dirroot . '/local/plagiarism/lib.php');
								$html .= '<tr>';
								$url = $CFG->wwwroot . '/local/plagiarism/plagiarism.php?type=' . QUIZ . '&mid=' . $cm->instance . '&qid=' . -1;
								$html .= '<td>' . $this->get_nav_item() . ' ' . '<a href="' . $url . '">' . get_string('allquestions', 'block_collusionadmin') . '</td>';
								$html .= '</tr>';
							
								
								foreach ($qtypes as $qid => $q) {
									$html .= '<tr>';
									$url = $CFG->wwwroot . '/local/plagiarism/plagiarism.php?type=' . QUIZ . '&mid=' . $cm->instance . '&qid=' . $qid;
									$html .= '<td>' . $this->get_nav_item() . ' ' . '<a href="' . $url . '">' . $q->name . '</td>';
									$html .= '</tr>';
								}					
							
								$html .= '</table></td>';
							
							
								$html .= '</tr></table>';
							
							}
 						}
 					}
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
