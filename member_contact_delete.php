<?php
include_once("includes/inc.global.php");
$p->site_section = PROFILE;
$p->page_title = _("Delete Joint Member");

include("includes/inc.forms.php");

if($_REQUEST["mode"] == "admin") {
	$cUser->MustBeLevel(1);
	$form->addElement("hidden","mode","admin");
} else {
	$cUser->MustBeLoggedOn();
	$form->addElement("hidden","mode","self");
}

$person = new cPerson;
$person->LoadPerson($_REQUEST["person_id"]);

$form->addElement("hidden", "person_id", $_REQUEST["person_id"]);
$form->addElement("static", null, _("Are you sure you want to permanently delete")." ". $person->Name() ."?", null);
$form->addElement("static",null,null);
$form->addElement('submit', 'btnSubmit', _("Delete"));

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p, $person;
	
	if($person->DeletePerson())
		$output = _("Joint member deleted.");
	else
		$output = _("There was an error deleting the joint member.");
		
	$p->DisplayPage($output);
}

?>
