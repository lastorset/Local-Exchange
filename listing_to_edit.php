<?php
include_once("includes/inc.global.php");
$p->site_section = LISTINGS;

if ($_REQUEST["type"]==Offer)
    $listing_name=$lng_offered;
else
    $listing_name=$lng_wanted;

$p->page_title = $lng_choose_the." ". $listing_name ." ".$lng_listing_to_edit;

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
	$list = $lng_you_dont_have_any." ". strtolower($listing_name) ." ".$lng_ed_listings.".";

$p->DisplayPage($list);

?>
