<?php
include_once("includes/inc.global.php");
$p->site_section = LISTINGS;

if ($_REQUEST["type"]==OFFER_LISTING)
	$p->page_title = _("Choose the Offered Listing to Edit");
else
	$p->page_title = _("Choose the Wanted Listing to Edit");

include_once("classes/class.listing.php");

$listings = new cTitleList($_GET['type']);

$member = new cMember;

if($_REQUEST["mode"] == "admin") {
	$cUser->MustBeLevel(1);
	$member->LoadMember($_REQUEST["member_id"]);
} else {
	$cUser->MustBeLoggedOn();
	$member = $cUser;
}

$list = $listings->DisplayMemberListings($member);

if($list == "")
	if ($_REQUEST["type"]==OFFER_LISTING)
		$list = _("You don't currently have any Offered listings").".";
	else
		$list = _("You don't currently have any Wanted listings").".";

$p->DisplayPage($list);

?>
