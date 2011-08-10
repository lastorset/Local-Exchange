<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = _("Choose state");

include("includes/inc.forms.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$state = new cStateList;
$state_list = $state->MakeStateArray();
unset($state_list[0]);

$form->addElement("select", "state", _("Which state?"), $state_list);
$form->addElement("static", null, null, null);

$buttons[] = &HTML_QuickForm::createElement('submit', 'btnEdit', _("Edit"));
// DeleteState not used, state only to be changed, else there should be checked if the state is still used - ejkv
// $buttons[] = &HTML_QuickForm::createElement('submit', 'btnDelete', _("Delete"));
$form->addGroup($buttons, null, null, '&nbsp;');

//
// Define form rules
//


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

// DeleteState not used, state only to be changed, else there should be checked if the state is still used - ejkv
	header("location:http://".HTTP_BASE."/state_edit.php?state_id=". $values["state"]);
	exit;	
// DeleteState not used, state only to be changed, else there should be checked if the state is still used - ejkv

	if(isset($values["btnDelete"])) {
		$state = new cState;
		$state->LoadState($values["state"]);
		if($state->DeleteState())
			$output = _("State deleted");
	} else {
		header("location:http://".HTTP_BASE."/state_edit.php?state_id=". $values["state"]);
		exit;	
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//

?>
