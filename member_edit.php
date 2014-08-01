<?php

include_once("includes/inc.global.php");

$p->site_section = 0;

include("includes/inc.forms.php");
include_once("classes/class.state_address.php"); // added by ejkv
//
// First, we define the form
//
if($_REQUEST["mode"] == "admin") {  // Administrator is editing a member's account
	$cUser->MustBeLevel(1);
	$member = new cMember;
	$member->LoadMember($_REQUEST["member_id"]);
	$form->addElement("header", null, _("Edit Member")." " . $member->person[0]->first_name . " " . $member->person[0]->mid_name . " " . $member->person[0]->last_name); // added mid_name by ejkv

	$form->addElement("html", "<TR></TR>");
	$form->addElement("hidden","mode","admin");
	$form->addElement("hidden","member_id",$_REQUEST["member_id"]);
	if($_REQUEST["member_id"] == "ADMIN") {
		$form->addElement("hidden","member_role","9");
	} else {
		$form->addElement("select", "member_role", _("Member Role"), array("0"=>_("Member"), "1"=>_("Committee"), "2"=>_("Admin")));
	}
	$acct_types = array("S"=>_("Single"), "J"=>_("Joint"), "H"=>_("Household"), "O"=>_("Organization"), "B"=>_("Business"), "F"=>_("Fund"));
	$form->addElement("select", "account_type", _("Account Type"), $acct_types);
	$form->addElement("static", null, _("Administrator Note"), null);
	$form->addElement("textarea", "admin_note", null, array("cols"=>45, "rows"=>2, "wrap"=>"soft", "maxlength" => 100));
	$today = getdate();
	$options = array("language"=> _("en"), "format" => "dFY", "minYear"=>JOIN_YEAR_MINIMUM, "maxYear"=> $today["year"]); // changed "en" by _("en") by ejkv
	$form->addElement("date", "join_date",	_("Join Date"), $options);	
	$options = array("language"=> _("en"), "format" => "dFY", "maxYear"=>$today["year"], "minYear"=>"1880"); // changed "en" by _("en") by ejkv	
	$form->addElement("date", "dob", _("Date of Birth"), $options);
	$form->addElement("text", "mother_mn", _("Mother's Maiden Name"), array("size" => 20, "maxlength" => 30)); 	
	$form->addElement("static", null, null, null);		
	$update_text = _("How frequently should the member receive email updates?");
	$update2_text = _("Should the member confirm any payments made to him/her?");
} else {  // Member is editing own profile
	$cUser->MustBeLoggedOn();
	$form->addElement("header", null, _("Edit Personal Profile"));
	$form->addElement("html", "<TR></TR>");
	$form->addElement("hidden","member_id", $cUser->member_id);
	$form->addElement("hidden","mode","self");
	$update_text = _("How often would you like to receive email updates?");
	$update2_text = _("Do you wish to confirm payments that are made to you?");
}

$form->addElement("text", "first_name", _("First Name"), array("size" => 15, "maxlength" => 20));
$form->addElement("text", "mid_name", _("Middle Name"), array("size" => 10, "maxlength" => 20));
$form->addElement("text", "last_name", _("Last Name"), array("size" => 20, "maxlength" => 30));
$form->addElement("static", null, null, null); 

$form->addElement("text", "email", _("Email Address"), array("size" => 25, "maxlength" => 40));
$form->addElement("text", "phone1", _("Primary Phone"), array("size" => 20));
$form->addElement("text", "phone2", _("Secondary Phone"), array("size" => 20));
$form->addElement("text", "fax", _("Fax Number"), array("size" => 20));
$form->addElement("static", null, null, null);
$frequency = array("0"=>_("Never"), "1"=>_("Daily"), "7"=>_("Weekly"), "30"=>_("Monthly"));
$form->addElement("select", "email_updates", $update_text, $frequency);

$confirmP = array("0"=>_("Automatically Accept Payments"), "1"=>_("Confirm Payments"));
$form->addElement("select", "confirm_payments", $update2_text, $confirmP);
$form->addElement("static", null, null, null);

$form->addElement("text", "address_street1", _("Address Line 1"), array("size" => 25, "maxlength" => 30));
$form->addElement("text", "address_street2", _("Address Line 2"), array("size" => 25, "maxlength" => 30));
$form->addElement("text", "address_city", _("City"), array("size" => 25, "maxlength" => 50));

// TODO: The State and Country codes should be Select Menus, and choices should be built
// dynamically using an internet database (if such exists).
$state = new cStateList; // added by ejkv
$state_list = $state->MakeStateArray(); // added by ejkv
$state_list[0]="---"; // added by ejkv

// address_state_code textbox replaced by Select menu, and contents filled from Database table states
// $form->addElement("text", "address_state_code", _("State"), array("size" => 25, "maxlength" => 50));
$form->addElement("select", "address_state_code", _("State"), $state_list); // changed by ejkv
$form->addElement("text", "address_post_code", _("Zip Code"), array("size" => 10, "maxlength" => 20));
$form->addElement("text", "address_country", _("Country"), array("size" => 25, "maxlength" => 50));

/*[chris] Personal Profile bits */

if (SOC_NETWORK_FIELDS==true) {
	
	$form->addElement("static", null, null, null);
	$form->addElement("select", "age", _("Age"), $agesArr);
	$form->addElement("select", "sex", _("Sex"), $sexArr);
	$form->addElement("textarea", "about_me", _("About Me"), array("cols"=>45, "rows"=>5, "wrap"=>"soft", "maxlength" => 300));
}

$form->addElement("static", null, null, null);
$form->addElement('submit', 'btnSubmit', _("Update"));

//
// Define form rules
//
$form->addRule('member_id', _("Enter your username"), 'required');
$form->addRule('password', _("Password not long enough"), 'minlength', 7);
$form->addRule('first_name', _("Enter a first name"), 'required');
$form->addRule('last_name', _("Enter a last name"), 'required');
$form->addRule('address_city', _("Enter a").' ' . _("City"), 'required');
$form->addRule('address_state_code', _("Enter a").' ' . _("State"), 'required');
$form->addRule('address_post_code', _("Enter a").' ' ._("Zip Code"), 'required');
$form->addRule('address_country', _("Enter a country"), 'required');

$form->registerRule('verify_role_allowed','function','verify_role_allowed');
$form->addRule('member_role',_("You cannot assign a higher level of access than you have"),'verify_role_allowed');
$form->registerRule('verify_role_allowed1', 'function','verify_role_allowed1');
$form->addRule('member_role', _("You cannot modify the member role of a higher level account"), 'verify_role_allowed1');

$form->registerRule('verify_not_future_date','function','verify_not_future_date');
$form->addRule('join_date', _("Join date cannot be in the future"), 'verify_not_future_date');
$form->addRule('dob', _("Birth date cannot be in the future"), 'verify_not_future_date');
$form->registerRule('verify_reasonable_dob','function','verify_reasonable_dob');
$form->addRule('dob', _("A little young, don't you think?"), 'verify_reasonable_dob');
$form->registerRule('verify_valid_email','function', 'verify_valid_email');
$form->addRule('email', _("Not a valid email address"), 'verify_valid_email');
$form->registerRule('verify_phone_format','function','verify_phone_format');
$form->addRule('phone1', _("Phone format invalid"), 'verify_phone_format');
$form->addRule('phone2', _("Phone format invalid"), 'verify_phone_format');
$form->addRule('fax', _("Phone format invalid"), 'verify_phone_format');


//
// Check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Otherwise we need to load the existing values
	$member = new cMember;
	if($_REQUEST["mode"] == "admin") {
        $cUser->MustBeLevel(1);
		$member->LoadMember($_REQUEST["member_id"]);
    }
	else {
		$member = $cUser;
    }
			
	$current_values = array ("member_id"=>$member->member_id, "first_name"=>$member->person[0]->first_name, "mid_name"=>$member->person[0]->mid_name, "last_name"=>$member->person[0]->last_name, "email"=>$member->person[0]->email, "phone1"=>$member->person[0]->DisplayPhone(1), "phone2"=>$member->person[0]->DisplayPhone(2), "fax"=>$member->person[0]->DisplayPhone("fax"), "email_updates"=>$member->email_updates, "address_street1"=>$member->person[0]->address_street1, "address_street2"=>$member->person[0]->address_street2, "address_city"=>$member->person[0]->address_city, "address_state_code"=>$member->person[0]->address_state_code, "address_post_code"=>$member->person[0]->address_post_code, "address_country"=>$member->person[0]->address_country, "age"=>$member->person[0]->age, "sex"=>$member->person[0]->sex, "about_me"=>$member->person[0]->about_me,"confirm_payments"=>$member->confirm_payments);

	// Load defaults for extra fields visible by administrators
	if($_REQUEST["mode"] == "admin") {
        $cUser->MustBeLevel(1);

		$current_values["member_role"] = $member->member_role;
		$current_values["account_type"] = $member->account_type;
		$current_values["admin_note"] = $member->admin_note;
		$current_values["join_date"] = array ('d'=>substr($member->join_date,8,2),'F'=>date('n',strtotime($member->join_date)),'Y'=>substr($member->join_date,0,4));
		$current_values["mother_mn"] = $member->person[0]->mother_mn;
		
		if ($member->person[0]->dob) {		
			$current_values["dob"] = array ('d'=>substr($member->person[0]->dob,8,2),'F'=>date('n',strtotime($member->person[0]->dob)),'Y'=>substr($member->person[0]->dob,0,4));  // Using 'n' due to a bug in Quickform
		} else { // If date of birth was left empty originally, display default date
			$today = getdate();
			$current_values["dob"] = array ('d'=>$today['mday'],'F'=>$today['mon'],'Y'=>$today['year']);
		}		
	}
		
	$form->setDefaults($current_values);
   $p->DisplayPage($form->toHtml());  // display the form
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	
	global $p, $cUser,$cErr, $today;
	$list = "";

	$member = new cMember;
	if($_REQUEST["mode"] == "admin") {
        $cUser->MustBeLevel(1);
		$member->LoadMember($_REQUEST["member_id"]);
    }
	else {
		$member = $cUser;
   
    }

	if($_REQUEST["mode"] == "admin") {
        $cUser->MustBeLevel(1);
		
		$member->confirm_payments = htmlspecialchars($values["confirm_payments"]);
	
		$member->member_role = htmlspecialchars($values["member_role"]);
		$member->account_type = htmlspecialchars($values["account_type"]);
		$member->admin_note = htmlspecialchars($values["admin_note"]);
		$member->person[0]->mother_mn = htmlspecialchars($values["mother_mn"]);
		
		// [chris] fixed problem with passing this ARRAY to htmlspecialchars()...
		$date = $values['join_date'];
		
		// ... pass to htmlspecialchars() here instead [chris]
		$member->join_date = htmlspecialchars($date['Y'] . '/' . $date['F'] . '/' . $date['d']);
		
		// [chris] ditto re htmlspecialchars() [see comment above]
		$date = $values['dob'];

		$dob = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];
		
		// ... pass to htmlspecialchars() here instead [chris]
		$dob = htmlspecialchars($dob);
		
		if($dob != $today['year']."/".$today['mon']."/".$today['mday']) { 
			$member->person[0]->dob = $dob; 
		} // if date left as default (today's date), we don't want to set it
	} 

	$member->confirm_payments = htmlspecialchars($values["confirm_payments"]);
	
    // TODO: Add ability to temporarily disable an account (vacation) or to
    // disable altogether (left 4th Corner).  Also add ability for user to add
    // a personal note.
	$member->person[0]->first_name = htmlspecialchars($values["first_name"]);
	$member->person[0]->mid_name = htmlspecialchars($values["mid_name"]);
	$member->person[0]->last_name = htmlspecialchars($values["last_name"]);
	$member->person[0]->email = htmlspecialchars($values["email"]);
	$member->email_updates = htmlspecialchars($values["email_updates"]);
	$member->person[0]->address_street1 =
                                htmlspecialchars($values["address_street1"]);
	$member->person[0]->address_street2 = 
                                htmlspecialchars($values["address_street2"]);
	$member->person[0]->address_city =
                                htmlspecialchars($values["address_city"]);
	$member->person[0]->address_state_code =
                                htmlspecialchars($values["address_state_code"]);
	$member->person[0]->address_post_code =
                                htmlspecialchars($values["address_post_code"]);
	$member->person[0]->address_country =
                                htmlspecialchars($values["address_country"]);	

	$phone = new cPhone_uk($values['phone1']);
	$member->person[0]->phone1_area = $phone->area;
	$member->person[0]->phone1_number = $phone->SevenDigits();
	$member->person[0]->phone1_ext = $phone->ext;
	$phone = new cPhone_uk($values['phone2']);
	$member->person[0]->phone2_area = $phone->area;
	$member->person[0]->phone2_number = $phone->SevenDigits();
	$member->person[0]->phone2_ext = $phone->ext;	
	$phone = new cPhone_uk($values['fax']);
	$member->person[0]->fax_area = $phone->area;
	$member->person[0]->fax_number = $phone->SevenDigits();
	$member->person[0]->fax_ext = $phone->ext;	
	
	/*[chris]*/
	if (SOC_NETWORK_FIELDS==true) {
	
		$member->person[0]->age = htmlspecialchars($values["age"]);	
		$member->person[0]->sex = htmlspecialchars($values["sex"]);	
		$member->person[0]->about_me = ($values["about_me"]);	
	}

	$member->person[0]->GeocodeCatch();
	
	if($member->SaveMember()) {
		$list .= _("Changes saved."); 
	} else {
		$cErr->Error(_("There was an error saving the member.")." "._("Please try again later."));
	}
   $p->DisplayPage($list);
}
//
// The following functions verify form data
//

// TODO: All my validation functions should go into a new cFormValidation class

function verify_good_member_id ($element_name,$element_value) {
	if(ctype_alnum($element_value)) { // it's good, so return immediately & save a little time
		return true;
	} else {
		$member_id = ereg_replace("\_","",$element_value);
		$member_id = ereg_replace("\-","",$member_id);
		$member_id = ereg_replace("\.","",$member_id);
		if(ctype_alnum($member_id))  // test again now that we've stripped the allowable special chars
			return true;		
	}
}


function verify_role_allowed($element_name,$element_value) {
	global $cUser;
	if($element_value > $cUser->member_role)
		return false;
	else
		return true;
}


/**
 * You cannot downgrade an account that has higher privileges than you.
 */
function verify_role_allowed1($element_name,$element_value) {
	global $cUser, $member;

	if (($member->member_role > $cUser->member_role) &&
          ($element_value != $member->member_role)) {
		return false;
    }
	else {
		return true;
    }
}


function verify_reasonable_dob($element_name,$element_value) {
	global $today;
	$date = $element_value;
	$date_str = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];

	if ($date_str == $today['year']."/".$today['mon']."/".$today['mday']) 
		// date wasn't changed by user, so no need to verify it
		return true;
	elseif ($today['year'] - $date['Y'] < 3)  // A little young to be trading, presumably a mistake
		return false;
	else
		return true;
}

function verify_good_password($element_name,$element_value) {
	$i=0; $upper=false; $lower=false; $number=false; $punct=false;
	$length=strlen($element_value);
	
	while($i<$length) {
		if(ctype_upper($element_value{$i}))
			$upper=true;
		if(ctype_lower($element_value{$i}))
			$lower=true;
		if(ctype_punct($element_value{$i}))
			$punct=true;
		if(ctype_digit($element_value{$i}))
			$number=true;	
		$i+=1;
	}
	
	if($upper and $lower and ($number or $punct))
		return true;
	else
		return false;
}

function verify_no_apostraphes_or_backslashes($element_name,$element_value) {
	if(strstr($element_value,"'") or strstr($element_value,"\\"))
		return false;
	else
		return true;
}

function verify_not_future_date ($element_name,$element_value) {
	$date = $element_value;
	$date_str = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];

	if (strtotime($date_str) > strtotime("now"))
		return false;
	else
		return true;
}

// TODO: This simplistic function should ultimately be replaced by this class method on Pear:
// 		http://pear.php.net/manual/en/package.mail.mail-rfc822.intro.php
function verify_valid_email ($element_name,$element_value) {
	if ($element_value=="")
		return true;		// Currently not planning to require this field
	if (strstr($element_value,"@") and strstr($element_value,"."))
		return true;	
	else
		return false;
	
}

function verify_phone_format ($element_name,$element_value) {
	$phone = new cPhone_uk($element_value);
	
	if($phone->prefix)
		return true;
	else
		return false;
}

?>
