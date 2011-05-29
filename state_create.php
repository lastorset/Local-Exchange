<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = _("create_new_listing_state" /* orphaned string */);

include("includes/inc.forms.php");
include_once("classes/class.state_address.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$form->addElement("text", "state", _("state_description" /* orphaned string */), array("size" => 30, "maxlength" => 30));
$form->addElement("static", null, null, null);

$form->addElement('submit', 'btnSubmit', _("Submit"));

//
// Define form rules
//
$form->addRule('state', _("enter_state_description" /* orphaned string */), 'required');

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
	global $p, $cErr;

	$state = new cState($values["state"]);
	
	if ($state->SaveNewstate()) {
		$output = _("state_created" /* orphaned string */);
	} else {
		$output = _("could_not_save_state" /* orphaned string */)." "._("Please try again later.");
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//

?>
