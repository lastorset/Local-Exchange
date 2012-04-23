<?php

include_once("includes/inc.global.php");
include("classes/class.info.php");
include("includes/inc.forms.php");

// CKEditor - added by ejkv
// Include the CKEditor class.
include_once "../ckeditor/ckeditor.php";
// Create a class instance.
$CKEditor = new CKEditor();
// Path to the CKEditor directory.
$CKEditor->basePath = '/ckeditor/';
// CKEditor - added by ejkv

$cUser->MustBeLevel(1);

$p->site_section = EVENTS;

$p->page_title = _("Create a New Information Page");

//
// First, we define the form
//

$form->addElement("text", "title", _("Title"), array("size" => 35, "maxlength" => 100));
$form->addElement("textarea", "description", _("Content"), array("cols"=>55, "rows"=>20, "wrap"=>"soft")); // changed cols from 65 into 55 by ejkv

// not essential, since the save-button in CKeditor does the same - ejkv
$form->addElement("submit", "btnSubmit", _("Submit"));

//
// Set up validation rules for the form
//
$form->addRule("title",_("Enter a title"),"required");
$form->addRule("description",_("Enter some body text"),"required");

//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {
	
   $p->DisplayPage($form->toHtml());  // just display the form

// CKEditor - added by ejkv
// Replace a textarea element with id "description".
$CKEditor->replace("description");
// CKEditor - added by ejkv
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $cErr, $cDB;
	$q = 'INSERT INTO cdm_pages set date='.time().', title='.$cDB->EscTxt($values["title"]).', body='.$cDB->EscTxt($values["description"]).'';
	$success = $cDB->Query($q);
	
	if ($success)
		$output = _("New information page added.");
	else
		$output = _("There was a problem adding the new page.");
		
	$p->DisplayPage($output);
	
}
