<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = _("edit_listing_State" /* orphaned string */);

include("includes/inc.forms.php");
include_once("classes/class.state_address.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$state = new cState();
$state->LoadState($_REQUEST["state_id"]);

$form->addElement("hidden", "state_id", $_REQUEST["state_id"]);
$form->addElement("text", "state", _("state_description" /* orphaned string */), array("size" => 30, "maxlength" => 30));
$form->addElement("static", null, null, null);

$form->addElement('submit', 'btnSubmit', _("Submit"));

//
// Define form rules
//
$form->addRule('state', _("state_description_cannot_be_blank" /* orphaned string */), 'required');

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
		$output = _("state_updated" /* orphaned string */);
	} else {
		$output = _("could_not_save_changes_state" /* orphaned string */)." "._("Please try again later.");
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//

?>
