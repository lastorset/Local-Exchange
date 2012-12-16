<?php
include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = PROFILE;
$p->page_title = _("Inactivate Listings While on Holiday");

include_once("classes/class.directory.php");
include("includes/inc.forms.php");

if($_REQUEST["mode"] == "admin") {
	$cUser->MustBeLevel(1);
	$form->addElement("hidden","mode","admin");
	$member = new cMember();
	$member->LoadMember($_REQUEST["member_id"]);
	$text = _("This feature will temporarily inactivate all of the member's offered and wanted listings while they are gone.")." ";
	$pronoun = _("they");
} else {
	$cUser->MustBeLoggedOn();
	$member = $cUser;
	$form->addElement("hidden","mode","self");
	$text = _("This feature will temporarily inactivate all of your offered and wanted listings while you are gone.")." ";
	$pronoun = _("you");
}

$text .= _("They will not appear in the directory during that time.  When the date you specify below arrives the listings will automatically reactivate.");
$form->addElement("static", null, $text, null);
$form->addElement("hidden","member_id", $member->member_id);
$form->addElement("static", null, null, null);
$today = getdate();
$options = array('language'=> _("en"), 'format' => 'dFY', 'minYear' => $today['year'],'maxYear' => $today['year']+5);
$form->addElement("date", "return_date", _("When will")." ".$pronoun." "._("return?"), $options);
$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", _("Inactivate"));

$form->registerRule('verify_future_date','function','verify_future_date');
$form->addRule('return_date',_("Must be a future date"),'verify_future_date');
$form->registerRule('verify_valid_date','function','verify_valid_date');
$form->addRule('return_date',_("Date is invalid"),'verify_valid_date');

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p, $member;
	
	$date = $values['return_date'];
	$return_date = new cDateTime($date['Y'] . '/' . $date['F'] . '/' . $date['d']);
	
	$listings = new cListingGroup(OFFER_LISTING);
	$listings->LoadListingGroup(null,"%",$member->member_id);
	$listings->InactivateAll($return_date);
	
	$listings = new cListingGroup(WANT_LISTING);
	$listings->LoadListingGroup(null,"%",$member->member_id);
	$listings->InactivateAll($return_date);
	
	$output = _("Listings successfully inactivated.");
	
	$p->DisplayPage($output);
}

function verify_future_date ($element_name,$element_value) {
	$today = getdate();
	$date = $element_value;
	$date_str = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];

	if (strtotime($date_str) <= strtotime("now")) // date is a past date
		return false;
	else
		return true;
}

function verify_valid_date ($element_name,$element_value) {
	$date = $element_value;
	return checkdate($date['F'],$date['d'],$date['Y']);
}

?>
