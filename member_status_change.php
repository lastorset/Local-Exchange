<?php
include_once("includes/inc.global.php");
include_once("classes/class.listing.php");

$cUser->MustBeLevel(2);
$p->site_section = ADMINISTRATION;

$member = new cMember;
$member->LoadMember($_REQUEST["member_id"]);
if($member->status == 'A')
	$p->page_title = _("Inactivate")." ";
else
	$p->page_title = _("Re-activate")." ";
	
$p->page_title .= $member->PrimaryName() ." (". $member->member_id .")";

include("includes/inc.forms.php");
include_once("classes/class.news.php");

$form->addElement("hidden", "member_id", $_REQUEST["member_id"]);

if($member->status == 'A') {
	$form->addElement("static", null, _("Are you sure you want to inactivate this member?  They will no longer be able to use this website, and all their listings will be inactivated as well."), null);
	$form->addElement("static", null, null, null);
	$form->addElement('submit', 'btnSubmit', _("Inactivate"));
} else {
	$form->addElement("static", null, _("Are you sure you want to re-activate this member?  Their listings will need to be reactivated individually or new ones created."), null);
	$form->addElement("static", null, null, null);
	$form->addElement('submit', 'btnSubmit', _("Re-activate"));
}

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p, $member;
	
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
		$output = _("Changes to member status saved.");
	else
		$output = _("There was an error changing the member's status.")." "._("Please try again later.");	
			
	$p->DisplayPage($output);
}

?>
