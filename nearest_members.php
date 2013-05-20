
<?php
include_once("includes/inc.global.php");
$p->site_section = LISTINGS;

$p->page_title = _("Nearby listings");

include_once("classes/class.listing.php");
include_once("classes/class.geocode.php");
include_once("includes/inc.forms.php");

$form->addElement("text","member", $cUser->member_id);
$form->addElement("submit", "btnSubmit", _("Continue"));

$cUser->MustBeLoggedOn();
$location = $cUser->person[0]->coordinates;

/*
if ($form->validate()) { // Form is validated so processes the data
	$form->freeze();
	$form->process("process_data", false);
} else {  // Display the form
	$output = $form->toHtml();
	$output .= cGeoCode::GenerateMap();
	$p->DisplayPage($output);
}
*/

// TODO Geolocation
// TODO With geolocation, logged-off users can be supported

// function process_data ($values) {
	// global $p;
	$out = "";

	// Wash input
	// Look up member location

	$listings = new cListingGroup($_GET["type"]); // TODO: cListingGroup expects listing type
	$listings->LoadNearbyListings($location[0], $location[1], 5);

	$out .= "Results:<table style='color: black'>";
	foreach($listings->listing as $listing)
		$out .= "<tr><td>{$listing->member->member_id}<td>$listing->title<td>$listing->distance km</tr>";
	$out .= "</table>";

	$p->DisplayPage($out);
// }

function verify_selection ($z, $selection) {
	if($selection == "0")
		return false;
	else
		return true;
}


?>
