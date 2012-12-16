<?php
include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = LISTINGS;

if ($_REQUEST["type"]==OFFER_LISTING)
	$p->page_title = _("Create Offered Listing");
else
	$p->page_title = _("Create Wanted Listing");
    

include_once("classes/class.listing.php");
include("includes/inc.forms.php");

// removed by ejkv
/* if($cUser->member_id == "ADMIN") {
 $p->DisplayPage("I'm sorry, you cannot create listings while logged in as the ADMIN account.  This is a special account for administration purposes only.<p>To create member accounts go to the <a href=admin_menu.php>Administration menu</a>."); 
 exit;
}
*/

//
// First, we define the form
//
if($_REQUEST["mode"] == "admin") {  // Administrator is creating listing for another member
	$cUser->MustBeLevel(1);
	$form->addElement("hidden","mode","admin");
	if (isset($_REQUEST["member_id"])) {
		$form->addElement("hidden","member_id", $_REQUEST["member_id"]);
	} else {
		$ids = new cMemberGroup;
		$ids->LoadMemberGroup();
		$form->addElement("select", "member_id", _("For which member?"), $ids->MakeIDArray());
	}
} else {  // Member is creating offer for his/her self
	$cUser->MustBeLoggedOn();
	$form->addElement("hidden","member_id", $cUser->member_id);
	$form->addElement("hidden","mode","self");
}

$form->addElement('hidden','type',$_REQUEST['type']);
$title_list = new cTitleList($_REQUEST['type']);
$form->addElement('text', 'title', _("Title"), array('size' => 30, 'maxlength' => 60));
$form->addRule('title',_("Enter a title"),'required');
$form->registerRule('verify_not_duplicate','function','verify_not_duplicate');
$form->addRule('title',_("You already have a listing with this title"),'verify_not_duplicate');
$category_list = new cCategoryList();
$form->addElement('select', 'category', _("Category"), $category_list->MakeCategoryArray());

if(USE_RATES)
	$form->addElement('text', 'rate', _("Rate"), array('size' => 15, 'maxlength' => 30));
else
	$form->addElement('hidden', 'rate');

$form->addElement('static', null, _("Description"), null);
$form->addElement('textarea', 'description', null, array('cols'=>45, 'rows'=>5, 'wrap'=>'soft'));
$form->addElement('html', '<TR><TD></TD><TD><BR></TD></TR>');
$form->addElement('advcheckbox', 'set_expire_date', _("Should this listing be set to automatically expire?"));
$today = getdate();
$options = array('language'=> _("en"), 'format' => 'dFY', 'minYear' => $today['year'],'maxYear' => $today['year']+5, 'addEmptyOption'=>'Y', 'emptyOptionValue'=>'0');
$form->addElement('date','expire_date', _("Expires"), $options);
$form->registerRule('verify_temporary','function','verify_temporary');
//$form->addRule('expire_date','Temporary listing box must be checked for expiration','verify_temporary');
$form->registerRule('verify_future_date','function','verify_future_date');
$form->addRule('expire_date',_("Expiration must be for a future date"),'verify_future_date');
$form->registerRule('verify_valid_date','function','verify_valid_date');
$form->addRule('expire_date',_("Date is invalid"),'verify_valid_date');
$form->registerRule('verify_category','function','verify_category');
$form->addRule('category', _("Choose Category"), 'verify_category');

$form->addElement('submit', 'btnSubmit', _("Submit"));

//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process('process_data', false);
} else {
   $p->DisplayPage($form->toHtml());  // just display the form
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $cUser, $cErr;
	
	$member = new cMember;
	
	if($_REQUEST["mode"] == "admin"){
        $cUser->MustBeLevel(1);
		$member->LoadMember($_REQUEST["member_id"]);
    }
	else {
		$member = $cUser;
    }
		
	$list = "";
	$date = $values['expire_date'];

	if($date['F'] == '0' and $date['d'] == '0' and $date['Y'] == '0') {
		$parms['expire_date'] = null;
	} else {
		$expire_date = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];
		$parms['expire_date'] = $expire_date;
	}

	$parms['title'] = htmlspecialchars($values['title']);
	$parms['description'] = $values['description']; // changed htmlspecialchars($values['description']) - by ejkv
	$parms['category'] = $values['category'];	
	$parms['rate'] = htmlspecialchars($values['rate']);
	$parms['type'] = $_REQUEST['type'];

	$listing = new cListing($member, $parms);
	$created = $listing->SaveNewListing();

	if($created) {
		$list .= _("Listing created. Create")." <A HREF=listing_create.php?type=".$_REQUEST["type"]."&mode=".$_REQUEST["mode"]."&member_id=".$member->member_id.">"._("another")."</A>?";	
	} else {
		$cErr->Error(_("There was an error saving the listing.")." "._("Please try again later."));
	}
   $p->DisplayPage($list);
}
//
// And the following functions verify form data
//

function verify_future_date ($element_name,$element_value) {
	global $form;

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
	global $title_list;
	
	$titles = $title_list->MakeTitleArray($_REQUEST["member_id"]);
	
	foreach ($titles as $title) {
		if($element_value == $title)
			return false;
	}
	return true;
}

function verify_category ($z, $category) {
	if($category == "0")
		return false;
	else
		return true;
}

?>
