<?php
include_once("includes/inc.global.php");
$p->site_section = LISTINGS;

if ($_REQUEST["type"]==Offer)
    $listing_name=_("Offered");
else
    $listing_name=_("Wanted");

$p->page_title = _("Choose the")." ". $listing_name ." "._("Listing to Edit");

include("classes/class.listing.php");

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
	$list = _("You don't currently have any")." ". strtolower($listing_name) ." "._(" listings").".";

$p->DisplayPage($list);

?>
