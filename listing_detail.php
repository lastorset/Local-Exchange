<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;

include_once("classes/class.listing.php");

$listing = new cListing();
if ($_GET['member_id'] && $_GET['title'] && $_GET['type'])
	// Old primary key
	$listing->LoadListingOldPK($cDB->UnEscTxt($_GET['title']), $_GET['member_id'], substr($_GET['type'],0,1));
else
	$listing->LoadListing($_GET['id']);

$p->page_title = $listing->title;

$output = "<article class=listing>"
	. $listing->DisplayListing();
if ($cUser->IsLoggedOn())
	$output .= $listing->member->DisplayMember();
else
	$output .= "<p>". _("You may see more details if you sign up and log in.");

$output .= "</article>";

$p->DisplayPage($output);

include("includes/inc.events.php");

?>
