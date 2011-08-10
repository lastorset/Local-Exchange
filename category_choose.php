<?php

include_once("includes/inc.global.php");

$p->site_section = LISTINGS;
$p->page_title = _("Choose Category");

include("includes/inc.forms.php");
include_once("classes/class.category.php");

//
// Define form elements
//
$cUser->MustBeLevel(2);

$categories = new cCategoryList;
$category_list = $categories->MakeCategoryArray();
unset($category_list[0]);

$form->addElement("select", "category", _("Which Category?"), $category_list);
$form->addElement("static", null, null, null);

$buttons[] = &HTML_QuickForm::createElement('submit', 'btnEdit', _("Edit"));
$buttons[] = &HTML_QuickForm::createElement('submit', 'btnDelete', _("Delete"));
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
	global $p, $cErr;
	
	if(isset($values["btnDelete"])) {
		$category = new cCategory;
		$category->LoadCategory($values["category"]);
		if($category->HasListings()) {
			$output = _("This category still has listings in it.  You will need to move these listings to new categories or delete them before you can delete this category.  Note that the listings could be temporarily inactive or expired, in which case they will not show in the offered/wanted lists.")."<P>";

			$output .= _("Listings in this category:")."<BR>";
			$listings = new cListingGroup(OFFER_LISTING);
			$listings->LoadListingGroup(null, $values["category"]);
			foreach($listings->listing as $listing)
				$output .= _("OFFERED").": ". $listing->description ." (". $listing->member_id .")<BR>"; 
				
			$listings = new cListingGroup(WANT_LISTING);
			$listings->LoadListingGroup(null, $values["category"]);
			foreach($listings->listing as $listing)
				$output .= _("WANTED").": ". $listing->description ." (". $listing->member_id .")<BR>";
		} else {
			if($category->DeleteCategory())
				$output = _("The category has been deleted.");
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
