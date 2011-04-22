<?php

include_once("includes/inc.global.php");
include("classes/class.info.php");
include("includes/inc.forms.php");

$cUser->MustBeLevel(1);

$p->site_section = EVENTS;

$p->page_title = $lng_create_new_info_page;

//
// First, we define the form
//

$form->addElement("text", "title", $lng_title, array("size" => 35, "maxlength" => 100));
$form->addElement("textarea", "description", $lng_content, array("cols"=>55, "rows"=>20, "wrap"=>"soft")); // changed cols from 65 into 55 by ejkv

$form->addElement("submit", "btnSubmit", $lng_submit);

//
// Set up validation rules for the form
//
$form->addRule("title",$lng_enter_title,"required");
$form->addRule("description",$lng_enter_body_text,"required");

//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {
	
   $p->DisplayPage($form->toHtml());  // just display the form
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $cErr, $cDB, $lng_new_info_page_added, $lng_problem_adding_new_page;
	$q = 'INSERT INTO cdm_pages set date='.time().', title='.$cDB->EscTxt($values["title"]).', body='.$cDB->EscTxt($values["description"]).'';
	$success = $cDB->Query($q);
	
	if ($success)
		$output = $lng_new_info_page_added;
	else
		$output = $lng_problem_adding_new_page;
		
	$p->DisplayPage($output);
	
}
