<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = $lng_edit_listing_State;

include("includes/inc.forms.php");
include_once("classes/class.state_address.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$state = new cState();
$state->LoadState($_REQUEST["state_id"]);

$form->addElement("hidden", "state_id", $_REQUEST["state_id"]);
$form->addElement("text", "state", $lng_state_description, array("size" => 30, "maxlength" => 30));
$form->addElement("static", null, null, null);

$form->addElement('submit', 'btnSubmit', $lng_submit);

//
// Define form rules
//
$form->addRule('state', $lng_state_description_cannot_be_blank, 'required');

//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {
	$form->setDefaults(array("state"=>$state->description));
   $p->DisplayPage($form->toHtml());  // just display the form
}

function process_data ($values) {
	global $p, $cErr, $state, $lng_state_updated, $lng_could_not_save_changes_state, $lng_try_again_later;
	
	$state->description = $values["state"];
	if ($state->SaveState()) {
		$output = $lng_state_updated;
	} else {
		$output = $lng_could_not_save_changes_state." ".$lng_try_again_later;
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//

?>
