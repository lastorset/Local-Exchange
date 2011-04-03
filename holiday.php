<?php
include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = PROFILE;
$p->page_title = $lng_inactivate_listing_holiday;

include("classes/class.directory.php");
include("includes/inc.forms.php");

if($_REQUEST["mode"] == "admin") {
	$cUser->MustBeLevel(1);
	$form->addElement("hidden","mode","admin");
	$member = new cMember();
	$member->LoadMember($_REQUEST["member_id"]);
	$text = $lng_this_feature_will_inactivate_members_listings." ";
	$pronoun = $lng_they;
} else {
	$cUser->MustBeLoggedOn();
	$member = $cUser;
	$form->addElement("hidden","mode","self");
	$text = $lng_this_feature_will_inactivate_your_listings." ";
	$pronoun = $lng_you;
}

$text .= $lng_they_will_not_appear_during_set_time;
$form->addElement("static", null, $text, null);
$form->addElement("hidden","member_id", $member->member_id);
$form->addElement("static", null, null, null);
$today = getdate();
$options = array('language'=> $lng_language, 'format' => 'dFY', 'minYear' => $today['year'],'maxYear' => $today['year']+5);
$form->addElement("date", "return_date", $lng_when_will." ".$pronoun." ".$lng_return, $options);
$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", $lng_inactivate);

$form->registerRule('verify_future_date','function','verify_future_date');
$form->addRule('return_date',$lng_must_be_future_date,'verify_future_date');
$form->registerRule('verify_valid_date','function','verify_valid_date');
$form->addRule('return_date',$lng_date_invalid,'verify_valid_date');

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p, $member, $lng_listings_inactivated;
	
	$date = $values['return_date'];
	$return_date = new cDateTime($date['Y'] . '/' . $date['F'] . '/' . $date['d']);
	
	$listings = new cListingGroup(OFFER_LISTING);
	$listings->LoadListingGroup(null,"%",$member->member_id);
	$listings->InactivateAll($return_date);
	
	$listings = new cListingGroup(WANT_LISTING);
	$listings->LoadListingGroup(null,"%",$member->member_id);
	$listings->InactivateAll($return_date);
	
	$output = $lng_listings_inactivated;
	
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
