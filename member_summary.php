<?php

include_once("includes/inc.global.php");
$p->site_section = PROFILE;

// bugfix RF 090905 added logged in check
$cUser->MustBeLoggedOn();

$member = new cMember;
$member->LoadMember($_REQUEST["member_id"]);

$p->page_title = _("Summary for")." ".$member->PrimaryName();

include_once("classes/class.listing.php");

$output = "<DIV ID=member_summary>";

$output .= "<H4>"._("Contact information")."</H4>";
$output .= $member->DisplayMember();

if (GEOCODE) {
	$output .= cGeocode::UserMap($member->person[0]->coordinates);
}

$output .= "<H4>"._("Offered listings")."</H4>";
$listings = new cListingGroup(OFFER_LISTING);
$listings->LoadListingGroup(null, null, $_REQUEST["member_id"]);
$output .= $listings->DisplayListingGroup();

$output .= "<H4>"._("Wanted listings")."</H4>";
$listings = new cListingGroup(WANT_LISTING);
$listings->LoadListingGroup(null, null, $_REQUEST["member_id"]);
$output .= $listings->DisplayListingGroup();
$output .= "</DIV>";

$p->DisplayPage($output);

?>
