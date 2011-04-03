<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = $lng_create_new_listing_state;

include("includes/inc.forms.php");
include_once("classes/class.state_address.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$form->addElement("text", "state", $lng_state_description, array("size" => 30, "maxlength" => 30));
$form->addElement("static", null, null, null);

$form->addElement('submit', 'btnSubmit', $lng_submit);

//
// Define form rules
//
$form->addRule('state', $lng_enter_state_description, 'required');

//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {
   $p->DisplayPage($form->toHtml());  // just display the form
}

function process_data ($values) {
	global $p, $cErr, $lng_state_created, $lng_could_not_save_state, $lng_try_again_later;

	$state = new cState($values["state"]);
	
	if ($state->SaveNewstate()) {
		$output = $lng_state_created;
	} else {
		$output = $lng_could_not_save_state." ".$lng_try_again_later;
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//

?>
