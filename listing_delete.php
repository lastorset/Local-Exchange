<?php

include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = LISTINGS;

if ($_REQUEST["type"]==OFFER_LISTING)
	$p->page_title = _("Delete Offered Listings");
else
	$p->page_title = _("Delete Wanted Listings");

include_once("classes/class.listing.php");
include("includes/inc.forms.php");
$message = "";

// First, need to change the default form template so checkbox comes before the label
$renderer->setElementTemplate('<TR><TD><LABEL>{element}<!-- BEGIN required --><font> *</font><!-- END required --></FONT><!-- BEGIN error --><font color=RED size=2>   *{error}*</font><br /><!-- END error -->&nbsp;<FONT SIZE=2>{label}</FONT></LABEL></TD></TR>');

$form->addElement('hidden','mode',$_REQUEST['mode']);


$member = new cMember;

if($_REQUEST["mode"] == "admin") {
    $cUser->MustBeLevel(1);
	$member->LoadMember($_REQUEST["member_id"]);
}
else {
	$member = $cUser;
}

$title_list = new cTitleList($_REQUEST['type']);
$titles = $title_list->MakeTitleArray($member->member_id);

$listings_exist = false;

while (list($key, $title) = each ($titles)) {
	$form->addElement('checkbox', $key, $title);
	$listings_exist=true;
}

if ($listings_exist) {
	$form->addElement('static', null, null);
	$form->addElement('submit', 'btnSubmit', _("Delete"));
} else {
	if($_REQUEST["mode"] == "self")
		if ($_REQUEST["type"]==OFFER_LISTING)
			$message = _("You don't currently have any Offered listings.");
		else
			$message = _("You don't currently have any Wanted listings.");
	else
		if ($_REQUEST["type"]==OFFER_LISTING)
			$message = $member->PrimaryName()." "._("doesn't currently have any Offered listings.");
		else
			$message = $member->PrimaryName()." "._("doesn't currently have any Wanted listings.");
}

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process('process_data', false);
} else {
   $p->DisplayPage($form->toHtml() ."<BR>". $message);  // just display the form
}

function process_data ($values) {
	global $p, $cErr, $member, $cUser;
	$list = "";
	$deleted = 0;
	$listing = new cListing;
	while (list ($key, $value) = each ($values)) {
		$affected = 0;
		if(is_numeric($key))  // Two of the values are hidden fields.  Need to skip those.
			// Check that we only delete the member's listings
			$listing->LoadListing($key);
			if ($listing->member->member_id == $member->member_id)
				$affected = $listing->DeleteListing($key);
			else
				$cErr->InternalError($cUser->member_id
					." tried to delete listing ". $listing->listing_id ." owned by ". $listing->member->member_id);

		$deleted += $affected;
	}
	
	if($deleted == 1) 
		$list .= _("1 listing deleted").".";
	elseif($deleted > 1)
		$list .= $deleted . " "._("listings deleted");	
	else
		$cErr->Error(_("There was an error deleting the listings. Did you check any boxes?"));
		
   $p->DisplayPage($list);
}

?>
