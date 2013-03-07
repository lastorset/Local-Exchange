<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;

if (isset($_GET['log_me_in']))
	$cUser->MustBeLoggedOn();

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
	// TODO: Ideally, people should be returned to this listing after creating an account.
	// Translation hint: %1$s and the like are link tags and must be left as they are.
	$output .= "<p>". sprintf(_('You may see more details if you %1$s sign up%2$s or %3$s log in%4$s.'),
		SELF_REGISTRATION ? "<a href=member_create.php>" : "", SELF_REGISTRATION ? "</a>" : "",
		"<a href={$listing->GetUrl()}&amp;log_me_in>", "</a>");

$output .= "</article>";

$p->DisplayPage($output);

include("includes/inc.events.php");

?>
