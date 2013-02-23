<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = $cDB->UnEscTxt($_GET['title']);

include_once("classes/class.listing.php");

$listing = new cListing();
$listing->LoadListing($cDB->UnEscTxt($_GET['title']), $_GET['member_id'], substr($_GET['type'],0,1));
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
