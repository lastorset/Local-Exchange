<?php

include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = LISTINGS;
$p->page_title = $cDB->UnEscTxt($_GET['title']);

include("classes/class.listing.php");

$listing = new cListing();
$listing->LoadListing($cDB->UnEscTxt($_GET['title']), $_GET['member_id'], substr($_GET['type'],0,1));
$output = $listing->DisplayListing();

$p->DisplayPage($output);

include("includes/inc.events.php");

?>
