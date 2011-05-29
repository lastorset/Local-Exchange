<?php
include_once("includes/inc.global.php");
$cUser->MustBeLoggedOn();
$p->site_section = EXCHANGES;
$p->page_title = _("Choose Member");

include("includes/inc.forms.php");

$ids = new cMemberGroup;
$ids->LoadMemberGroup();
$form->addElement("select", "member_id", _("Whose Feedback?"), $ids->MakeIDArray());
$form->addElement("static", null, null, null);
$form->addElement('submit', 'btnSubmit', _("View"));

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $cUser;
	header("location:http://".HTTP_BASE."/feedback_all.php?mode=other&member_id=".$values["member_id"]);
	exit;	
}

?>
