<?php
include_once("includes/inc.global.php");
$p->site_section = PROFILE;
$p->page_title = _("Choose Joint Member");

$cUser->MustBeLoggedOn();
include("includes/inc.forms.php");

$form->addElement("select", "person_id", _("Which Joint Member?"), $cUser->MakeJointMemberArray($cUser->member_id));

$form->addElement("static", null, null, null);
$buttons[] = &HTML_QuickForm::createElement('submit', 'btnEdit', _("Edit"));
$buttons[] = &HTML_QuickForm::createElement('submit', 'btnDelete', _("Delete"));
$form->addGroup($buttons, null, null, '&nbsp;', false);

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	if(isset($values["btnDelete"])) {
		header("location:http://".HTTP_BASE."/member_contact_delete.php?mode=self&person_id=". $values["person_id"]);
		exit;	
	} else {
		header("location:http://".HTTP_BASE."/member_contact_edit.php?mode=self&person_id=". $values["person_id"]);
		exit;	
	}
}

?>
