<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = $lng_edit_listing_category;

include("includes/inc.forms.php");
include_once("classes/class.category.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$category = new cCategory();
$category->LoadCategory($_REQUEST["category_id"]);

$form->addElement("hidden", "category_id", $_REQUEST["category_id"]);
$form->addElement("text", "category", $lng_category_description, array("size" => 30, "maxlength" => 30));
$form->addElement("static", null, null, null);

$form->addElement('submit', 'btnSubmit', $lng_submit);

//
// Define form rules
//
$form->addRule('category', $lng_category_description_cannot_be_blank, 'required');

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
	global $p, $cErr, $category, $lng_category_updated, $lng_could_not_save_changes_category, $lng_try_again_later;
	
	$category->description = $values["category"];
	if ($category->SaveCategory()) {
		$output = $lng_category_updated;
	} else {
		$output = $lng_could_not_save_changes_category." ".$lng_try_again_later;
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//


?>
