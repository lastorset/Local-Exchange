<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = _("Edit Listing Category");

include("includes/inc.forms.php");
include_once("classes/class.category.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$category = new cCategory();
$category->LoadCategory($_REQUEST["category_id"]);

$form->addElement("hidden", "category_id", $_REQUEST["category_id"]);
$form->addElement("text", "category", _("Category Description"), array("size" => 30, "maxlength" => 30));
$form->addElement("static", null, null, null);

$form->addElement('submit', 'btnSubmit', _("Submit"));

//
// Define form rules
//
$form->addRule('category', _("Category description cannot be blank"), 'required');

//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {
	$form->setDefaults(array("category"=>$category->description));
   $p->DisplayPage($form->toHtml());  // just display the form
}

function process_data ($values) {
	global $p, $cErr, $category;
	
	$category->description = $values["category"];
	if ($category->SaveCategory()) {
		$output = _("The category has been updated.");
	} else {
		$output = _("Could not save changes to the category.")." "._("Please try again later.");
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//


?>
