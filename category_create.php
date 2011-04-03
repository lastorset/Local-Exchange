<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = $lng_create_new_listing_category;

include("includes/inc.forms.php");
include_once("classes/class.category.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$form->addElement("text", "category", $lng_category_description, array("size" => 30, "maxlength" => 30));
$form->addElement("static", null, null, null);

$form->addElement('submit', 'btnSubmit', $lng_submit);

//
// Define form rules
//
$form->addRule('category', $lng_enter_category_description, 'required');

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
	global $p, $cErr, $lng_category_created, $lng_could_not_save_category, $lng_try_again_later;

	$category = new cCategory($values["category"]);
	
	if ($category->SaveNewCategory()) {
		$output = $lng_category_created;
	} else {
		$output = $lng_could_not_save_category." ".$lng_try_again_later;
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//


?>
