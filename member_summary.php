<?php

include_once("includes/inc.global.php");
$p->site_section = PROFILE;

// bugfix RF 090905 added logged in check
$cUser->MustBeLoggedOn();

$member = new cMember;
$member->LoadMember($_REQUEST["member_id"]);

$p->page_title = $lng_summary_for." ".$member->PrimaryName();

include_once("classes/class.listing.php");

$output = "<STRONG><I>".$lng_contact_information_cap."</I></STRONG><P>";
$output .= $member->DisplayMember();

$output .= "<BR><P><STRONG><I>".$lng_offerd_listings_cap."</I></STRONG><P>";
$listings = new cListingGroup(OFFER_LISTING);
$listings->LoadListingGroup(null, null, $_REQUEST["member_id"]);
$output .= $listings->DisplayListingGroup();

$output .= "<BR><P><STRONG><I>".$lng_wanted_listings_cap."</I></STRONG><P>";
$listings = new cListingGroup(WANT_LISTING);
$listings->LoadListingGroup(null, null, $_REQUEST["member_id"]);
$output .= $listings->DisplayListingGroup();

$p->DisplayPage($output); 

?>
