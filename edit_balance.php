<?php

include_once("includes/inc.global.php");

$p->site_section = SITE_SECTION_OFFER_LIST;

include("includes/inc.forms.php");
	

//
// First, we define the form
//

$cUser->MustBeLevel(2);

if (OVRIDE_BALANCES!=true) // Provision for overriding member balances has been turned off, return to the admin menu
	header("location:http://".HTTP_BASE."/admin_menu.php");
	
$member = new cMember;
$member->LoadMember($_REQUEST["member_id"]);
	
$form->addElement("header", null, $lng_edit_member." " . $member->person[0]->first_name . " " . $member->person[0]->mid_name . " " . $member->person[0]->last_name.$lng_s_balance); // added mid_name by ejkv
$form->addElement("hidden","member_id",$_REQUEST["member_id"]);
$form->addElement("text", "balance1", $lng_value_before_dec_pnt_8_dgt_max, array("size" => 6, "maxlength" => 8));
$form->addElement("text", "balance2", $lng_value_after_dec_pnt_2_dgt_max, array("size" => 1, "maxlength" => 2));
$form->addElement('submit', 'btnSubmit', $lng_update_balance);

$balance = explode(".",$member->balance);

$current_values["balance1"] = $balance[0];
$current_values["balance2"] = $balance[1];
$form->setDefaults($current_values);
//
// Check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Otherwise we need to load the existing values
	$member = new cMember;
	if($_REQUEST["mode"] == "admin") {
        $cUser->MustBeLevel(1);
		$member->LoadMember($_REQUEST["member_id"]);
    }
	else {
		$member = $cUser;
    }
			
   $p->DisplayPage($form->toHtml());  // display the form
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $cUser,$cErr, $cDB, $lng_edit_another_members_balance, $lng_edit_another_members_balance, $lng_problem_updating_balance;
	
	$balance = trim($values["balance1"]).".".trim($values["balance2"]);
	
	$q = 'UPDATE member set balance='.$cDB->EscTxt($balance).' where member_id='.$cDB->EscTxt($values["member_id"]).'';
//	echo $q;
	$success = $cDB->Query($q);
	
	if ($success)
		$output = $lng_balance_set_to. $balance." <a href=balance_to_edit.php>".$lng_edit_another_members_balance."</a>";
	else
		$output = $lng_problem_updating_balance;
		
	$p->DisplayPage($output);
}
