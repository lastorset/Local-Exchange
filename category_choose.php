<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = $lng_choose_category;

include("includes/inc.forms.php");
include_once("classes/class.category.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$categories = new cCategoryList;
$category_list = $categories->MakeCategoryArray();
unset($category_list[0]);

$form->addElement("select", "category", $lng_which_category, $category_list);
$form->addElement("static", null, null, null);

$buttons[] = &HTML_QuickForm::createElement('submit', 'btnEdit', $lng_edit);
$buttons[] = &HTML_QuickForm::createElement('submit', 'btnDelete', $lng_delete);
$form->addGroup($buttons, null, null, '&nbsp;');

//
// Define form rules
//


//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {
   $p->DisplayPage($form->toHtml());  // just display the form
}

function process_data ($values) {
	global $p, $cErr, $lng_category_deleted;
	
	if(isset($values["btnDelete"])) {
		$category = new cCategory;
		$category->LoadCategory($values["category"]);
		if($category->HasListings()) {
			$output = $lng_remove_listings_before_delete_category."<P>";

			$output .= $lng_listings_in_this_category."<BR>";
			$listings = new cListingGroup(OFFER_LISTING);
			$listings->LoadListingGroup(null, $values["category"]);
			foreach($listings->listing as $listing)
				$output .= $lng_offered_cap.": ". $listing->description ." (". $listing->member_id .")<BR>"; 
				
			$listings = new cListingGroup(WANT_LISTING);
			$listings->LoadListingGroup(null, $values["category"]);
			foreach($listings->listing as $listing)
				$output .= $lng_wanted_cap.": ". $listing->description ." (". $listing->member_id .")<BR>";			
		} else {
			if($category->DeleteCategory())
				$output = $lng_category_deleted;
		}
	} else {
		header("location:http://".HTTP_BASE."/category_edit.php?category_id=". $values["category"]);
		exit;	
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//


?>
