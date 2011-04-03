<?php
include_once("includes/inc.global.php");
$p->site_section = PROFILE;
$p->page_title = $lng_delete_joint_member;

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
$form->addElement("static", null, $lng_sure_to_perm_delete." ". $person->Name() ."?", null);
$form->addElement("static",null,null);
$form->addElement('submit', 'btnSubmit', $lng_delete);

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p, $person, $lng_joint_member_deleted, $lng_error_deleting_joint_member;
	
	if($person->DeletePerson())
		$output = $lng_joint_member_deleted;
	else
		$output = $lng_error_deleting_joint_member;
		
	$p->DisplayPage($output);
}

?>
