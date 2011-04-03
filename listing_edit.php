<?php

include_once("includes/inc.global.php");  

$cUser->MustBeLoggedOn();
$p->site_section = LISTINGS;
$title = $cDB->UnEscTxt($_REQUEST['title']);

if ($_REQUEST["type"]==Offer) // added to translate Offerd and Wanted in page title - by ejkv
    $listing_name=$lng_offered;
else
    $listing_name=$lng_wanted;
$p->page_title = $lng_edit." ". $listing_name .': '.$title; // $_REQUEST['type'] replaced by $listing_name - by ejkv

include("classes/class.listing.php");
include("includes/inc.forms.php");

//
// First we define the form
//
if($_REQUEST["mode"] == "admin") {  // Administrator is creating listing for another member
	$cUser->MustBeLevel(1);
	$form->addElement("hidden","mode","admin");
	$form->addElement("hidden", "member_id", $_REQUEST["member_id"]);
} else {  // Member is creating offer for his/her self
	$cUser->MustBeLoggedOn();
	$form->addElement("hidden","member_id", $cUser->member_id);
	$form->addElement("hidden","mode","self");
}

$form->addRule('title',$lng_enter_title,'required');
$form->registerRule('verify_not_duplicate','function','verify_not_duplicate');
//$form->addRule('title','You already have a listing with this title','verify_not_duplicate');
$category_list = new cCategoryList();
$form->addElement('select', 'category', $lng_category, $category_list->MakeCategoryArray());

if(USE_RATES)
	$form->addElement('text', 'rate', $lng_rate, array('size' => 15, 'maxlength' => 30));
else
	$form->addElement('hidden', 'rate');

$form->addElement('hidden', 'title', $title);
$form->addElement('hidden','type',$_REQUEST['type']);
$form->addElement('static', null, $lng_description, null);
$form->addElement('textarea', 'description', null, array('cols'=>45, 'rows'=>5, 'wrap'=>'soft'));
$form->addElement('html', '<TR><TD></TD><TD><BR></TD></TR>');
$form->addElement('advcheckbox', 'set_expire_date', $lng_should_automatically_expire);
$today = getdate();
$options = array('language'=> 'en', 'format' => 'dFY', 'minYear' => $today['year'],'maxYear' =>$today['year']+5, 'addEmptyOption'=>'Y', 'emptyOptionValue'=>'0');
$form->addElement('date','expire_date', $lng_expires, $options);
$form->registerRule('verify_future_date','function','verify_future_date');
$form->addRule('expire_date',$lng_expiration_future_date,'verify_future_date');
$form->registerRule('verify_valid_date','function','verify_valid_date');
$form->addRule('expire_date',$lng_date_invalid,'verify_valid_date');
$form->addElement('advcheckbox', 'set_reactivate_date', $lng_should_listing_temp_inactive);
$form->addElement('date','reactivate_date', $lng_reactivates, $options);
$form->addRule('reactivate_date',$lng_must_be_future_date,'verify_future_date');
$form->addRule('reactivate_date',$lng_date_invalid,'verify_valid_date');
$form->addElement('submit', 'btnSubmit', $lng_update);

//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 $form->process('process_data', false);
} else {  // Download existing values and display them
	$listing = new cListing;
	$listing->LoadListing($title,$_REQUEST['member_id'],substr($_REQUEST['type'],0,1));
	if ($listing->expire_date) {
		$temporary_listing = true;
		$expire_date = array ('d'=>substr($listing->expire_date,8,2),'F'=>date('n',strtotime($listing->expire_date)),'Y'=>substr($listing->expire_date,0,4));  // Using 'n' due to a bug in Quickform
	} else {
		$temporary_listing = false;
		$expire_date = array("d"=>0, "F"=>0, "Y"=>0);
	}
	if ($listing->reactivate_date) {
		$inactive_listing = true;
		$reactivate_date = array ('d'=>substr($listing->reactivate_date,8,2),'F'=>date('n',strtotime($listing->reactivate_date)),'Y'=>substr($listing->reactivate_date,0,4));  // Using 'n' due to a bug in Quickform
	} else {
		$inactive_listing = false;
		$reactivate_date = array("d"=>0, "F"=>0, "Y"=>0);
	}
		
	$current_values = array ("title"=>$listing->title, "description"=>$listing->description, "rate"=>$listing->rate, "category"=>$listing->category->id, "set_expire_date"=>$temporary_listing, "expire_date"=>$expire_date, "set_reactivate_date"=>$inactive_listing, "reactivate_date"=>$reactivate_date);

	$form->setDefaults($current_values);
    $p->DisplayPage($form->toHtml());  // just display the form
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $cUser,$cErr, $cDB, $title, $lng_listings_chages_saved_want_to, $lng_lc_edit, $lng_another_listing, $lng_error_saving_listing, $lng_try_again_later;
	$list = "";
	
	$listing = new cListing();
	$listing->LoadListing($title,$_REQUEST['member_id'],substr($_REQUEST['type'],0,1));  
	$date = $values['expire_date'];
	$expire_date = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];
	$date = $values['reactivate_date'];
	$reactivate_date = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];
	$today = getdate();

	if($values['set_expire_date'] and $expire_date != "0/0/0") { 
		// they checked the box and entered a date, so store the value
		$listing->expire_date = htmlspecialchars($expire_date);
	} elseif ($listing->expire_date==null and $expire_date != "0/0/0") {	
		// they didn't check it but they changed the date, so store
		$listing->expire_date = htmlspecialchars($expire_date);
	} else { 
		$listing->expire_date = null;
		if($listing->status == 'E') // they must have unchecked the box or blanked the date
			$listing->status = 'A';
	}	

	if($values['set_reactivate_date'] and $reactivate_date != "0/0/0") { 
		// they checked the box and entered a date, so store the value
		$listing->reactivate_date = htmlspecialchars($reactivate_date);
		$listing->status = 'I';
	} elseif ($listing->reactivate_date==null and $reactivate_date != "0/0/0") {	
		// they didn't check it but they changed the date, so store
		$listing->reactivate_date = htmlspecialchars($reactivate_date);
		$listing->status = 'I';
	} else { 
		$listing->reactivate_date = null;
		if($listing->status == 'I') // they must have unchecked the box or blanked the date
			$listing->status = 'A';
	}	
	
	$listing->title = htmlspecialchars($title);
	$listing->description = $values['description']; // changed htmlspecialchars($values['description']) - by ejkv
	$listing->category->id = htmlspecialchars($values['category']);
	$listing->rate = $values['rate'];

	$created = $listing->SaveListing();

	if($created) {
		$list .= $lng_listings_chages_saved_want_to.' <A HREF="listing_to_edit.php?mode='. $_REQUEST['mode'] .'&member_id='. $_REQUEST["member_id"] .'&type='. $_REQUEST["type"] .'">'.$lng_lc_edit.'</A> '.$lng_another_listing;	
	} else {
		$cErr->Error($lng_error_saving_listing." ".$lng_try_again_later);
	}
    $p->DisplayPage($list);
}

//
// And finally, the following functions verify form data
//
function verify_future_date ($element_name,$element_value) {
	global $form, $title;

	$listing = new cListing;
	$listing->LoadListing($title,$_REQUEST['member_id'],substr($_REQUEST['type'],0,1));
	if ($listing->status == 'E' and !$form->getElementValue("set_expire_date")) {
		return true; // They must have unchecked the box to reactivate the listing
	}
	
	$today = getdate();
	$date = $element_value;

	if($date['F'] == '0' and $date['d'] == '0' and $date['Y'] == '0')
		return true;
	
	$date_str = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];

	if (strtotime($date_str) <= strtotime("now")) // date is a past date
		return false;
	else
		return true;
}

function verify_valid_date ($element_name,$element_value) {
	$date = $element_value;
	
	if($date['F'] == '0' and $date['d'] == '0' and $date['Y'] == '0')
		return true;
	return checkdate($date['F'],$date['d'],$date['Y']);
}

function verify_not_duplicate ($element_name,$element_value) {
	global $cUser;
	$title_list = new cTitleList();
	
	$titles = $title_list->MakeTitleArray($cUser->member_id);
	
	foreach ($titles as $title) {
		if($element_value == $title)
			return false;
	}
	return true;
}

?>
