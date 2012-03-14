<?php

require_once($CFG->dirroot . '/local/plagiarism/lib.php');

function plagiarism_get_info_msg($msg) {
	return '<p><font color="#006600">' . $msg . '</font></p>';
}




class plagiarism_test extends UnitTestCase {
	
	/*
		@param $single_answers: array of student answers with key userid and value the student's answer
		@return $similarities - An array of similar objects where each similar object will have one new extra property (spots).
													The "spots" property will be an array of strings whose key is the userid
													and value a string similar to initial answer of that student but with plagiarised
													words highlighted.			
	*/
	function plagiarism_detection_flexible($single_answers) {
		global $DB;
		
		$type = ASSIGNMENT;
		$mid = 1;
		$qid = -1;
	

		$settings = get_default_settings();
		
		//$settings->indepth = 0.1;
		//$settings->jaccard = 0.1;	
	
		$plagiarism = new Plagiarism($type, $mid, $single_answers, $qid);
		
		// phase 1: preprocessing
		$answers = preprocessing($plagiarism, $settings);
		
		/*
		foreach ($answers as $answer) {
			foreach ($answer->words as $w) {
				echo $w->modified . '   ';
			}
			
			echo '<br/><br/><br/>';
		}*/
	
		// phase 2: candidates retrieval
		$answers = candidates_retrieval($answers, $settings);
	
		// phase 3: in-depth analysis
		$similarities = indepth_analysis($answers, $settings);
	
		// phase 4: post processing
		$similarities = postprocessing($plagiarism, $similarities);
	
		return $similarities;
	}	
	
	function test_two_students_exact_copy() {
		
		$ans = 'History can also mean the period of time after writing was invented. Scholars who write about history are called historians. It is a field of research which uses a narrative to examine and analyse the sequence of events, and it sometimes attempts to investigate objectively the patterns of cause and effect that determine events. Historians debate the nature of history and its usefulness. This includes discussing the study of the discipline as an end in itself and as a way of providing "perspective" on the problems of the present. The stories common to a particular culture, but not supported by external sources (such as the legends surrounding King Arthur) are usually classified as cultural heritage rather than the "disinterested investigation" needed by the discipline of history.[8][9] Events of the past prior to written record are considered prehistory.';
		
		$answers = array(1 => $ans, 2 => $ans);
		
		$similarities = $this->plagiarism_detection_flexible($answers);

		$key = null;
		if (isset($similarities['1.2'])) {
			$key = '1.2';
		}
		else if (isset($similarities['2.1'])) {
			$key = '2.1';
		}
		else {
			$this->assertTrue(false);
		}
		
		if ($key != null) {
			$this->assertTrue($similarities[$key]->similarity == 1);
		}
		
	}
	
	function test_many_students_exact_copy() {
		
		$ans = 'History can also mean the period of time after writing was invented. Scholars who write about history are called historians. It is a field of research which uses a narrative to examine and analyse the sequence of events, and it sometimes attempts to investigate objectively the patterns of cause and effect that determine events. Historians debate the nature of history and its usefulness. This includes discussing the study of the discipline as an end in itself and as a way of providing "perspective" on the problems of the present. The stories common to a particular culture, but not supported by external sources (such as the legends surrounding King Arthur) are usually classified as cultural heritage rather than the "disinterested investigation" needed by the discipline of history.[8][9] Events of the past prior to written record are considered prehistory.';
		
		$answers = array(1 => $ans, 2 => $ans, 3 => $ans);
		
		$similarities = $this->plagiarism_detection_flexible($answers);

		$key1_2 = isset($similarities['1.2']) ? '1.2' : (isset($similarities['2.1']) ? '2.1' : null);
		$key1_3 = isset($similarities['1.3']) ? '1.3' : (isset($similarities['3.1']) ? '3.1' : null);
		$key2_3 = isset($similarities['2.3']) ? '2.3' : (isset($similarities['3.2']) ? '3.2' : null);
		
		$this->assertTrue($key1_2 != null && $key1_3 != null && $key2_3 != null
											&& $similarities[$key1_2]->similarity == 1 && $similarities[$key1_3]->similarity == 1
											&& $similarities[$key2_3]->similarity == 1);
		
		
	}
	
	function test_two_students_non_intelligence_paraphrasing() {
	
		$ans1 = 'Increasingly, researchers have been turning to identical and fraternal twins for answers, with dramatic results. They are finding that genetics, in addition to familial interests, educational, social and other environmental pressures, have a considerable impact on how we choose what we do--and how happy we are with that choice.';
		
		$ans2 = 'To answer the question of how genetics influence career choices, researchers have turned to identical and fraternal twins, with impressive results. They have found that genetics, in additional to familial interests, educational, social and other environmental pressures, have a major impact on how people choose what they do—and how satisfied they are with that choice.';
		
		
		$answers = array(1 => $ans1, 2 => $ans2);
		
		$similarities = $this->plagiarism_detection_flexible($answers);

		$key1_2 = isset($similarities['1.2']) ? '1.2' : (isset($similarities['2.1']) ? '2.1' : null);
		
		$this->assertTrue($key1_2 != null
											&& $similarities[$key1_2]->similarity > 0.75);		
		
	}
	
	
	function test_many_students_non_intelligence_paraphrasing() {
	
		$ans1 = 'Increasingly, researchers have been turning to identical and fraternal twins for answers, with dramatic results. They are finding that genetics, in addition to familial interests, educational, social and other environmental pressures, have a considerable impact on how we choose what we do--and how happy we are with that choice.';
		
		$ans2 = 'To answer the question of how genetics influence career choices, researchers have turned to identical and fraternal twins, with impressive results. They have found that genetics, in additional to familial interests, educational, social and other environmental pressures, have a major impact on how people choose what they do—and how satisfied they are with that choice.';
		
		$ans3 = 'By turning to identical and fraternal twins, researchers have answered how career choices are influenced by genetics.';
		
		$answers = array(1 => $ans1, 2 => $ans2, 3 => $ans3);
		
		$similarities = $this->plagiarism_detection_flexible($answers);

		$key1_2 = isset($similarities['1.2']) ? '1.2' : (isset($similarities['2.1']) ? '2.1' : null);
		$key1_3 = isset($similarities['1.3']) ? '1.3' : (isset($similarities['3.1']) ? '3.1' : null);
		$key2_3 = isset($similarities['2.3']) ? '2.3' : (isset($similarities['3.2']) ? '3.2' : null);
				
		$this->assertTrue($key1_2 != null && $key1_3 != null && $key2_3 != null
											&& $similarities[$key1_2]->similarity > 0.75 && $similarities[$key1_3]->similarity > 0.55
											&& $similarities[$key2_3]->similarity > 0.70);		
	}
	
	function test_two_students_intelligence_paraphrasing_catch() {
	
		// the definitions taken from http://en.wikipedia.org/wiki/Philosophy
	
		$ans1 = 'Philosophy is the study of general and fundamental problems, such as those connected with existence, knowledge, reason, mind, and language.  Epistemology is concerned with the nature and scope of knowledge, such as the relationships between truth, belief, and the concept of consideration.';
		
		$ans2 = 'Phelosophi is the analysis of ganaral and central troubles which are related with being, cognition, cause, mentality and speech.  Epestimloge refers to the way cognition reacts with the true, impression and the idea of thoughtfulness.'; 
		
		
		$answers = array(1 => $ans1, 2 => $ans2);
		
		$similarities = $this->plagiarism_detection_flexible($answers);


		$key1_2 = isset($similarities['1.2']) ? '1.2' : (isset($similarities['2.1']) ? '2.1' : null);
				
		$this->assertTrue($key1_2 != null && $similarities[$key1_2]->similarity > 0.50);
	}
	

	function test_many_students_intelligence_paraphrasing_catch() {
	
		// the definitions taken from http://en.wikipedia.org/wiki/Philosophy
	
		$ans1 = 'Philosophy is the study of general and fundamental problems, such as those connected with existence, knowledge, reason, mind, and language.  Epistemology is concerned with the nature and scope of knowledge, such as the relationships between truth, belief, and the concept of consideration.';
		
		$ans2 = 'Phelosophi is the analysis of ganaral and central troubles which are related with being, cognition, cause, mentality and speech.  Epestimloge refers to the way cognition reacts with the true, impression and the idea of thoughtfulness.'; 
		
		$ans3 = '<p>Epistemology:  Cognitive relation between actuality, feeling and the idea of consideration</p>
						 <p>Philoswphi: Examination of generic issues that related with causal, existential notions, mentality, knowledge and speaking.</p>';
		
		$answers = array(1 => $ans1, 2 => $ans2, 3 => $ans3);
		
		$similarities = $this->plagiarism_detection_flexible($answers);

		$key1_2 = isset($similarities['1.2']) ? '1.2' : (isset($similarities['2.1']) ? '2.1' : null);
		$key1_3 = isset($similarities['1.3']) ? '1.3' : (isset($similarities['3.1']) ? '3.1' : null);
		$key2_3 = isset($similarities['2.3']) ? '2.3' : (isset($similarities['3.2']) ? '3.2' : null);
				
		$this->assertTrue($key1_2 != null && $key1_3 != null && $key2_3 != null
											&& $similarities[$key1_2]->similarity > 0.50 && $similarities[$key1_3]->similarity > 0.60
											&& $similarities[$key2_3]->similarity > 0.60);	
	}
	
	function test_two_students_intelligence_paraphrasing_not_catch() {
	
		// the definitions taken from http://en.wikipedia.org/wiki/Philosophy
 
	
		$ans1 = 'History is the discovery, collection, organization, and presentation of information about past events. The study of history has sometimes been classified as part of the humanities and at other times as part of the social sciences.';
		
		$ans2 = 'History is the denudation, aggregation, orgaizateon, and demonstration of info about previous circumstances. Examining history has been categorized at some periods as part of the world and at other periods as part of the societal scientific disciplines.'; 		
		
		$answers = array(1 => $ans1, 2 => $ans2);
		
		$similarities = $this->plagiarism_detection_flexible($answers);

		$key1_2 = isset($similarities['1.2']) ? '1.2' : (isset($similarities['2.1']) ? '2.1' : null);
				
		$this->assertTrue($key1_2 == null); // similarity is 29-33%
	}
	
	function test_two_students_intelligence_paraphrasing_not_catch_2() {
	
		// the definitions taken from http://www.upenn.edu/academicintegrity/ai_paraphrasing.html and modified
 
	
		$ans1 = 'We do not yet understand all the central ways in which brain chemicals are related to emotions and thoughts, but the general knowledge is that our state of mind has an immediate and direct effect on our state of body.';
		
		$ans2 = 'Siegel (1986) writes that the relationship between the chemicals in the brain and our thoughts and feelings remains only partially understood. He goes on to say, however, that one thing is clear: our mental state affects our bodily state.'; 		
		
		$answers = array(1 => $ans1, 2 => $ans2);
		
		$similarities = $this->plagiarism_detection_flexible($answers);

		$key1_2 = isset($similarities['1.2']) ? '1.2' : (isset($similarities['2.1']) ? '2.1' : null);
				
		$this->assertTrue($key1_2 == null); // similarity is 33%
	}	
	
	function test_false_positive_no_real_plagiarism_exist() {
	
		// ans1 taken from http://www.upenn.edu/academicintegrity/ai_paraphrasing.html and modified
		// ans2 taken form http://en.wikipedia.org/wiki/Philosophy
 
	
		$ans1 = 'We do not yet understand all the central ways in which brain chemicals are related to emotions and thoughts, but the general knowledge is that our state of mind has an immediate and direct effect on our state of body.';
		
		$ans2 = 'Phelosophi is the analysis of ganaral and central troubles which are related with being, cognition, cause, mentality and speech. Epestimloge refers to the way cognition reacts with the true, impression and the idea of thoughtfulness.'; 		
		
		$answers = array(1 => $ans1, 2 => $ans2);
		
		$similarities = $this->plagiarism_detection_flexible($answers);


		$key1_2 = isset($similarities['1.2']) ? '1.2' : (isset($similarities['2.1']) ? '2.1' : null);
				
		$this->assertTrue($key1_2 != null && $similarities[$key1_2]->similarity > 0.40); // similarity is 43%
	}						
	
}
