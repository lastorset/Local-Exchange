<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = _("Modify state information");

include("includes/inc.forms.php");
include_once("classes/class.state_address.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$state = new cState();
$state->LoadState($_REQUEST["state_id"]);

$form->addElement("hidden", "state_id", $_REQUEST["state_id"]);
$form->addElement("text", "state", _("State name"), array("size" => 30, "maxlength" => 30));
$form->addElement("static", null, null, null);

$form->addElement('submit', 'btnSubmit', _("Submit"));

//
// Define form rules
//
$form->addRule('state', _("State name cannot be blank"), 'required');

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
	global $p, $cErr, $state;
	
	$state->description = $values["state"];
	if ($state->SaveState()) {
		$output = _("State name updated");
	} else {
		$output = _("Could not save state name changes")." "._("Please try again later.");
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//

?>
