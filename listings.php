<?php
include_once("includes/inc.global.php");
$p->site_section = LISTINGS;

if ($_REQUEST["type"]==Offer)
	$p->page_title = _("Offered listings");
else
	$p->page_title = _("Wanted listings");

include_once("classes/class.listing.php");
include_once("classes/class.geocode.php");
include_once("includes/inc.forms.php");

$form->addElement("hidden","type", $_REQUEST["type"]);
$form->addElement("static", null, _("Select the category and time frame to view and press Continue. Or to view all listings at once, just press Continue. If you would like to print or download the complete directory, click")." <A HREF=directory.php>"._("here")."</A>.", null);
$form->addElement("static", null, null, null);
$category_list = new cCategoryList();
$categories = $category_list->MakeCategoryArray(ACTIVE, substr($_REQUEST["type"],0,1));
$categories[0] = "("._("View All Categories").")";
$form->addElement("select", "category", _("Category")." ", $categories);
$text = _("New/updated in last")." ";
$form->addElement("select", "timeframe", _("Time Frame")." ", array("0"=>"("._("View All Listings").")", "3"=>$text ._("3 days"), "7"=>$text ._("week"), "14"=>$text ._("2 weeks"), "30"=>$text ._("month"), "90"=>$text ._("3 months")));

if (KEYWORD_SEARCH_DIR==true)
	$form->addElement("text","keyword",_("Keyword")." ");

if (GEOCODE===true && $cUser->IsLoggedOn()) {
	$disabled = !is_array($cUser->person[0]->coordinates);

	// TODO Add HTML5 form support to QuickForm to avoid this hack
	$form->addElement("html",
		"<tr><td>".
		_("Distance from my address (km)").
		" <input name=distance type=number size=6 min=0 max=2000 ". ($disabled ? "disabled" : "") ."/> ".
		($disabled ?replace_tags(
			_("(You cannot search by distance because <a>your profile</a>'s address has not been properly geocoded.)"),
			array('a' => 'a href=member_edit.php?mode=self')
		) : "").
		"</td></tr>"
	);
}

$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", _("Continue"));

//$form->registerRule('verify_selection','function','verify_selection');
//$form->addRule('category', 'Choose a category', 'verify_selection');

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$output = $form->toHtml();
	$output .= cGeoCode::GenerateMap();
	$p->DisplayPage($output);
}

function process_data ($values) {
	global $p;

	header("location:http://".HTTP_BASE."/listings_found.php?type=".$_REQUEST["type"]."&keyword=".$_REQUEST["keyword"]."&category=".$values["category"]."&timeframe=".$_REQUEST["timeframe"]."&distance=".$_REQUEST["distance"]);
	exit;
}

function verify_selection ($z, $selection) {
	if($selection == "0")
		return false;
	else
		return true;
}


?>
