<?php

include_once("includes/inc.global.php");
include("includes/inc.forms.php");

// The rest of the form logic serves different results depending on whether the user is an admin or not.
if (SELF_REGISTRATION !== true)
	$cUser->MustBeLevel(1);
$p->site_section = 0;

//
// First, we define the form
//
$form->addElement("header", null, _("Register New Member"));
$form->addElement("html", "<TR></TR>");

$form->addElement("text", "member_id", _("Choose a Member ID"), array("size" => 10, "maxlength" => 15));
$form->addElement("text", "password", _("Password"), array("size" => 10, "maxlength" => 15));

if ($cUser->HasLevel(1))
	$form->addElement("select", "member_role", _("Member Role"), array("0"=>_("Member"), "1"=>_("Committee"), "2"=>_("Admin")));

$acct_types = array("S"=>_("Single"), "J"=>_("Joint"), "H"=>_("Household"), "O"=>_("Organization"), "B"=>_("Business"), "F"=>_("Fund"));
$form->addElement("select", "account_type", _("Account Type"), $acct_types);
if ($cUser->IsLoggedOn()) // Administrative note not for self-registration
{
	$form->addElement("static", null, _("Administrator Note"), null);
	$form->addElement("textarea", "admin_note", null, array("cols"=>45, "rows"=>2, "wrap"=>"soft", "maxlength" => 100));
}

$today = getdate();
if ($cUser->HasLevel(1))
{
	$options = array("language"=> _("en"), "format" => "dFY", "minYear"=>JOIN_YEAR_MINIMUM, "maxYear"=>$today["year"]);
	$form->addElement("date", "join_date",	_("Join Date"), $options);
}
$form->addElement("static", null, null, null);	

$form->addElement("text", "first_name", _("First Name"), array("size" => 15, "maxlength" => 20));
$form->addElement("text", "mid_name", _("Middle Name"), array("size" => 10, "maxlength" => 20));
$form->addElement("text", "last_name", _("Last Name"), array("size" => 20, "maxlength" => 30));
$form->addElement("static", null, null, null); 

$form->addElement("static", null, _("The next two fields may help us recover your password if you forget it."), null);
$options = array("language"=> _("en"), "format" => "dFY", "maxYear"=>$today["year"], "minYear"=>$today["year"]-120);
$form->addElement("date", "dob", _("Date of Birth"), $options);
$form->addElement("text", "mother_mn", _("Mother's Maiden Name"), array("size" => 20, "maxlength" => 30)); 

$form->addElement("static", null, null, null);
$form->addElement("text", "email", _("Email Address"), array("size" => 25, "maxlength" => 40));
$form->addElement("text", "phone1", _("Primary Phone"), array("size" => 20));
$form->addElement("text", "phone2", _("Secondary Phone"), array("size" => 20));
$form->addElement("text", "fax", _("Fax Number"), array("size" => 20));
$form->addElement("static", null, null, null);
$frequency = array("0"=>_("Never"), "1"=>_("Daily"), "7"=>_("Weekly"), "30"=>_("Monthly"));

if ($cUser->IsLoggedOn()) // Registering other people gives a 3rd-person question
	$form->addElement("select", "email_updates", _("How frequently should the member receive email updates?"), $frequency);
else // Users registering themselves get a 2nd-person question
	$form->addElement("select", "email_updates", _("How frequently do you wish to receive email updates?"), $frequency);

$form->addElement("static", null, null, null);
$form->addElement("text", "address_street1", _("Address Line 1"), array("size" => 25, "maxlength" => 50));
$form->addElement("text", "address_street2", _("Address Line 2"), array("size" => 25, "maxlength" => 50));
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
$form->addElement("static", null, null, null);
$form->addElement('submit', 'btnSubmit', _("Create Member"));

//
// Define form rules
//
$form->addRule('member_id', _("Enter your Member ID"), 'required');
$form->addRule('password', _("Password not long enough"), 'minlength', PASSWORD_MIN_LENGTH);
$form->addRule('first_name', _("Enter a first name"), 'required');
$form->addRule('last_name', _("Enter a last name"), 'required');
if (SELF_REGISTRATION && REQUIRE_EMAIL)
	$form->addRule('email', _("Enter an e-mail address"), 'required');
$form->addRule('address_city', _("Enter a")." ". _("City"), 'required');
$form->addRule('address_state_code',_("Enter a")." " . _("State"), 'required');
$form->addRule('address_post_code',_("Enter a")." "._("Zip Code"), 'required');
$form->addRule('address_country', _("Enter a country"), 'required');

$form->registerRule('verify_unique_member_id','function','verify_unique_member_id');
$form->addRule('member_id',_("This ID is already being used"),'verify_unique_member_id');
$form->registerRule('verify_good_member_id','function','verify_good_member_id');
$form->addRule('member_id',_("Special characters are not allowed"),'verify_good_member_id');
$form->registerRule('verify_good_password','function','verify_good_password');
$form->addRule('password', _("Password must contain at least one number"), 'verify_good_password');
$form->registerRule('verify_no_apostraphes_or_backslashes','function','verify_no_apostraphes_or_backslashes');
$form->addRule("password", _("You have the right idea, but it's best not to use apostraphes or backslashes in passwords"), "verify_no_apostraphes_or_backslashes");
$form->registerRule('verify_role_allowed','function','verify_role_allowed');
if ($cUser->HasLevel(1))
	$form->addRule('member_role',_("You cannot assign a higher level of access than you have"),'verify_role_allowed');
$form->registerRule('verify_not_future_date','function','verify_not_future_date');
if ($cUser->HasLevel(1))
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
if ($form->validate() && ($cUser->HasLevel(1) || SELF_REGISTRATION === true)) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {
	$today = getdate();
	$current_date = array("Y"=>$today["year"], "F"=>$today["mon"], "d"=>$today["mday"]);
	switch (DEFAULT_UPDATE_INTERVAL) {
	case "NEVER":
		$defaultUpdateInterval = 0; break;
	case "DAILY":
		$defaultUpdateInterval = 1; break;
	case "WEEKLY":
		$defaultUpdateInterval = 7; break;
	case "MONTHLY":
		$defaultUpdateInterval = 30; break;
	}
	$defaults = array("password"=>$cUser->GeneratePassword(), "dob"=>$current_date, "join_date"=>$current_date, "account_type"=>"S", "member_role"=>"0", "email_updates"=>$defaultUpdateInterval, "address_state_code"=>DEFAULT_STATE, "address_country"=>DEFAULT_COUNTRY);
	$form->setDefaults($defaults);
   $p->DisplayPage($form->toHtml());  // just display the form
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $cUser,$cErr, $today;
	$list = "";

	// Following are default values for which this form doesn't allow input
	$values['security_q'] = "";
	$values['security_a'] = "";
	$values['status'] = "A";
	$values['member_note'] = "";
	$values['expire_date'] = "";
	$values['away_date'] = "";
	$values['balance'] = 0;
	$values['primary_member'] = "Y";
	$values['directory_list'] = "Y";

	if (!$values['member_role'])
		$values['member_role'] = 0;

	if ($values['join_date'])
	{
		$date = $values['join_date'];
	}
	else
	{
		$date = array('Y' => $today['year'], 'F' => $today['mon'], 'd' => $today['mday']);
	}
	$values['join_date'] = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];
	$date = $values['dob'];
	$values['dob'] = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];
	if($values['dob'] == $today['year']."/".$today['mon']."/".$today['mday'])
		$values['dob'] = ""; // if birthdate was left as default, set to null
	
	$phone = new cPhone_uk($values['phone1']);
	$values['phone1_area'] = $phone->area;
	$values['phone1_number'] = $phone->SevenDigits();
	$values['phone1_ext'] = $phone->ext;
	$phone = new cPhone_uk($values['phone2']);
	$values['phone2_area'] = $phone->area;
	$values['phone2_number'] = $phone->SevenDigits();
	$values['phone2_ext'] = $phone->ext;	
	$phone = new cPhone_uk($values['fax']);
	$values['fax_area'] = $phone->area;
	$values['fax_number'] = $phone->SevenDigits();
	$values['fax_ext'] = $phone->ext;	


	$new_member = new cMember($values);
	$new_person = new cPerson($values);

	$new_person->GeocodeCatch();

	if($created = $new_person->SaveNewPerson()) 
		$created = $new_member->SaveNewMember();

	if($created) {
		$list .= _("Member created").". "._("Click")." <A HREF=member_create.php>"._("here")."</A> ". _("to create another member account").".<P>"._("Or if you would like to add a joint member to this account (such as a spouse), click")." <A HREF=member_contact_create.php?mode=admin&member_id=". $values["member_id"] .">"._("here")."</A>.<P>";
		if($values['email'] == "") {
			$msg_no_email = _("Since the new member does not have an email address, he/she needs to be notified of the member id")." ('". $values["member_id"]. "') "._("and password")." ('". $values["password"] ."').";
			$list .= $msg_no_email;
			mail(EMAIL_ADMIN, _("Member created") .": ". $values['member_id'], $msg_no_email, "From:".EMAIL_FROM ."\nContent-type: text/plain; charset=UTF-8");
		} else {
			$mailed = mail($values['email'], NEW_MEMBER_SUBJECT, NEW_MEMBER_MESSAGE . "\n\n"._("Member ID").": ". $values['member_id'] ."\n". _("Password").": ". $values['password'], "From:". EMAIL_FROM ."\nContent-type: text/plain; charset=UTF-8"); // added "From:" - by ejkv
			if($mailed)
				$list .= _("An email has been sent to")." '". $values["email"] ."' "._("containing the new user id and password").".";
			else
				$list .= _("An attempt to email the new member information failed.  This is most likely due to a technical problem.  You may want to contact your administrator at")." ". PHONE_ADMIN .". <I>"._("Since the email failed, the new member needs to be notified of the member id")." ('". $values["member_id"]. "') "._("and password")." ('". $values["password"] ."').</I>";	 

			if (NEW_MEMBER_EMAIL_ADMIN)
				mail(EMAIL_ADMIN,
					_("New member of "). SITE_SHORT_TITLE .": ". $values['first_name'] ." ". $values['mid_name'] ." ". $values['last_name'],
					_("Member ID").": ". $values['member_id'] ."\n"
					._("City").": ". $values['address_city']  ."\n\n"
					._("Read more").": http://". SERVER_DOMAIN.SERVER_PATH_URL ."/member_summary.php?member_id=". $values['member_id']
					);
		}
	} else {
		$cErr->Error(_("There was an error saving the member.")." "._("Please try again later."));
	}
   $p->DisplayPage($list);
}
//
// The following functions verify form data
//

// TODO: All my validation functions should go into a new cFormValidation class

function verify_unique_member_id ($element_name,$element_value) {
	$member = new cMember();
	
	return !($member->LoadMember($element_value, false));
}

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
		
function verify_reasonable_dob($element_name,$element_value) {
	global $today;
	$date = $element_value;
	$date_str = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];
//	echo $date_str ."=".$today['year']."/".$today['mon']."/".$today['mday'];

	if ($date_str == $today['year']."/".$today['mon']."/".$today['mday']) 
		// date wasn't changed by user, so no need to verify it
		return true;
	elseif ($today['year'] - $date['Y'] < 3)  // A little young to be trading, presumably a mistake
		return false;
	else
		return true;
}

function verify_good_password($element_name,$element_value) {
	$i=0;
	$length=strlen($element_value);
	
	while($i<$length) {
		if(ctype_digit($element_value{$i}))
			return true;	
		$i+=1;
	}
	
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
	if ($element_value=="" && !(REQUIRE_EMAIL && SELF_REGISTRATION))
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
