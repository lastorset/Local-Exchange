<?php
include_once("includes/inc.global.php");
include_once("classes/class.listing.php");

$cUser->MustBeLevel(2);
$p->site_section = ADMINISTRATION;

$member = new cMember;
$member->LoadMember($_REQUEST["member_id"]);
if($member->status == 'A')
	$p->page_title = $lng_inactivate." ";
else
	$p->page_title = $lng_reactivate." ";
	
$p->page_title .= $member->PrimaryName() ." (". $member->member_id .")";

include("includes/inc.forms.php");
include_once("classes/class.news.php");

$form->addElement("hidden", "member_id", $_REQUEST["member_id"]);

if($member->status == 'A') {
	$form->addElement("static", null, $lng_sure_to_inactivate_member, null);
	$form->addElement("static", null, null, null);
	$form->addElement('submit', 'btnSubmit', $lng_inactivate);
} else {
	$form->addElement("static", null, $lng_sure_to_reactivate_member, null);
	$form->addElement("static", null, null, null);
	$form->addElement('submit', 'btnSubmit', $lng_reactivate);
}

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p, $member, $lng_changes_member_status_saved, $lng_error_saving_member_status, $lng_try_again_later;
	
	if($member->status == 'A') {
		$success = $member->DeactivateMember();
		$listings = new cListingGroup(OFFER_LISTING);
		$listings->LoadListingGroup(null,null,$member->member_id);
		$date = new cDateTime("yesterday");
		if($success)
			$success = $listings->ExpireAll($date);
		if($success) {
			$listings = new cListingGroup(WANT_LISTING);
			$listings->LoadListingGroup(null,null,$member->member_id);
			$success = $listings->ExpireAll($date);
		}
	} else {
		$success = $member->ReactivateMember();
	}

	if($success)
		$output = $lng_changes_member_status_saved;
	else
		$output = $lng_error_saving_member_status." ".$lng_try_again_later;	
			
	$p->DisplayPage($output);
}

?>
