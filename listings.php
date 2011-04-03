<?php
include_once("includes/inc.global.php");
$p->site_section = LISTINGS;

if ($_REQUEST["type"]==Offer)
    $listing_name=$lng_offered;
else
    $listing_name=$lng_wanted;

$p->page_title = $listing_name.$lng_ed_listings;

include("classes/class.listing.php");
include("includes/inc.forms.php");

$form->addElement("hidden","type", $_REQUEST["type"]);
$form->addElement("static", null, $lng_select_category_and_timeframe." <A HREF=directory.php>".$lng_here."</A>.", null);
$form->addElement("static", null, null, null);
$category_list = new cCategoryList();
$categories = $category_list->MakeCategoryArray(ACTIVE, substr($_REQUEST["type"],0,1));
$categories[0] = "(".$lng_view_all_categories.")";
$form->addElement("select", "category", $lng_category." ", $categories);
$text = $lng_new_updated_in_last." ";
$form->addElement("select", "timeframe", $lng_time_frame." ", array("0"=>"(".$lng_view_all_listings.")", "3"=>$text .$lng_three_days, "7"=>$text .$lng_week, "14"=>$text .$lng_two_weeks, "30"=>$text .$lng_month, "90"=>$text .$lng_three_months));

if (KEYWORD_SEARCH_DIR==true)
	$form->addElement("text","keyword",$lng_keyword." ");

$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", $lng_continue);

//$form->registerRule('verify_selection','function','verify_selection');
//$form->addRule('category', 'Choose a category', 'verify_selection');

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p;

	header("location:http://".HTTP_BASE."/listings_found.php?type=".$_REQUEST["type"]."&keyword=".$_REQUEST["keyword"]."&category=".$values["category"]."&timeframe=".$_REQUEST["timeframe"]);
	exit;
}

function verify_selection ($z, $selection) {
	if($selection == "0")
		return false;
	else
		return true;
}


?>
