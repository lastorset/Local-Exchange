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
else {
	// TODO: Ideally, people should be returned to this listing after creating an account.
	if (SELF_REGISTRATION)
		$prompt = _('You may see more details if you <a1>sign up</a1> or <a2>log in</a2>.');
	else
		$prompt = _('You may see more details if you sign up or <a2>log in</a2>.');

	$output .= "<p>". replace_tags($prompt, array(
		"a1" => "a href=member_create.php",
		"a2" => "a href={$listing->GetUrl()}&amp;log_me_in"
	));
}

$output .= "</article>";

$p->DisplayPage($output);

include("includes/inc.events.php");

?>
