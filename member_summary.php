<?php

include_once("includes/inc.global.php");
$p->site_section = PROFILE;

// bugfix RF 090905 added logged in check
$cUser->MustBeLoggedOn();

$member = new cMember;
$member->LoadMember($_REQUEST["member_id"]);

$p->page_title = _("Summary for")." ".$member->PrimaryName();

include_once("classes/class.listing.php");

$output = "<STRONG><I>"._("CONTACT INFORMATION")."</I></STRONG><P>";
$output .= $member->DisplayMember();

$output .= "<BR><P><STRONG><I>"._("OFFERED LISTINGS")."</I></STRONG><P>";
$listings = new cListingGroup(OFFER_LISTING);
$listings->LoadListingGroup(null, null, $_REQUEST["member_id"]);
$output .= $listings->DisplayListingGroup();

$output .= "<BR><P><STRONG><I>"._("WANTED LISTINGS")."</I></STRONG><P>";
$listings = new cListingGroup(WANT_LISTING);
$listings->LoadListingGroup(null, null, $_REQUEST["member_id"]);
$output .= $listings->DisplayListingGroup();

$p->DisplayPage($output); 

?>
