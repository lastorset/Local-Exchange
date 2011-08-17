<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = _("Create a New Listing Category");

include("includes/inc.forms.php");
include_once("classes/class.category.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$form->addElement("text", "category", _("Category Description"), array("size" => 30, "maxlength" => 30));
$form->addElement("static", null, null, null);

$form->addElement('submit', 'btnSubmit', _("Submit"));

//
// Define form rules
//
$form->addRule('category', _("Enter the category description"), 'required');

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

	$category = new cCategory($values["category"]);
	
	if ($category->SaveNewCategory()) {
		$output = _("The category has been created.");
	} else {
		$output = _("Could not save the category.")." "._("Please try again later.");
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//


?>
