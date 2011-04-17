<?php

include_once("includes/inc.global.php");
include("includes/inc.forms.php");

// The rest of the form logic serves different results depending on whether the user is an admin or not.
if (!SELF_REGISTRATION)
	$cUser->MustBeLevel(1);
else
{
	require_once('Services/ReCaptcha.php');

	$recaptcha = new Services_ReCaptcha(RECAPTCHA_PUBKEY, RECAPTCHA_PRIVKEY);
	$recaptcha->setOption('theme', 'white');
}
$p->site_section = 0;

//
// First, we define the form
//
$form->addElement("header", null, $lng_create_new_member);
$form->addElement("html", "<TR></TR>");

$form->addElement("text", "member_id", $lng_member_id, array("size" => 10, "maxlength" => 15));
$form->addElement("text", "password", $lng_pwd, array("size" => 10, "maxlength" => 15));

if ($cUser->HasLevel(1))
	$form->addElement("select", "member_role", $lng_member_role, array("0"=>$lng_member, "1"=>$lng_committee, "2"=>$lng_admin));

$acct_types = array("S"=>$lng_single, "J"=>$lng_joint, "H"=>$lng_household, "O"=>$lng_organisation, "B"=>$lng_business, "F"=>$lng_fund);
$form->addElement("select", "account_type", $lng_account_type, $acct_types);
if ($cUser->IsLoggedOn()) // Administrative note not for self-registration
{
	$form->addElement("static", null, $lng_admin_note, null);
	$form->addElement("textarea", "admin_note", null, array("cols"=>45, "rows"=>2, "wrap"=>"soft", "maxlength" => 100));
}

$today = getdate();
if ($cUser->HasLevel(1))
{
	$options = array("language"=> $lng_language, "format" => "dFY", "minYear"=>JOIN_YEAR_MINIMUM, "maxYear"=>$today["year"]);
	$form->addElement("date", "join_date",	$lng_join_date, $options);
}
$form->addElement("static", null, null, null);	

$form->addElement("text", "first_name", $lng_first_name, array("size" => 15, "maxlength" => 20));
$form->addElement("text", "mid_name", $lng_middle_name, array("size" => 10, "maxlength" => 20));
$form->addElement("text", "last_name", $lng_last_name, array("size" => 20, "maxlength" => 30));
$form->addElement("static", null, null, null); 

$options = array("language"=> $lng_language, "format" => "dFY", "maxYear"=>$today["year"], "minYear"=>$today["year"]-120);
$form->addElement("date", "dob", $lng_date_of_birth, $options);
$form->addElement("text", "mother_mn", $lng_mothers_maiden_name, array("size" => 20, "maxlength" => 30)); 
$form->addElement("static", null, null, null);
$form->addElement("text", "email", $lng_email_address, array("size" => 25, "maxlength" => 40));
$form->addElement("text", "phone1", $lng_primary_phone, array("size" => 20));
$form->addElement("text", "phone2", $lng_secondary_phone, array("size" => 20));
$form->addElement("text", "fax", $lng_fax_number, array("size" => 20));
$form->addElement("static", null, null, null);
$frequency = array("0"=>$lng_never, "1"=>$lng_daily, "7"=>$lng_weekly, "30"=>$lng_monthly);

if ($cUser->IsLoggedOn()) // Registering other people gives a 3rd-person question
	$form->addElement("select", "email_updates", $lng_how_frequently_updates, $frequency);
else // Users registering themselves get a 2nd-person question
	$form->addElement("select", "email_updates", $lng_how_frequently_updates_you, $frequency);

$form->addElement("static", null, null, null);
$form->addElement("text", "address_street1", ADDRESS_LINE_1, array("size" => 25, "maxlength" => 50));
$form->addElement("text", "address_street2", ADDRESS_LINE_2, array("size" => 25, "maxlength" => 50));
$form->addElement("text", "address_city", ADDRESS_LINE_3, array("size" => 25, "maxlength" => 50));

// TODO: The State and Country codes should be Select Menus, and choices should be built
// dynamically using an internet database (if such exists).
$state = new cStateList; // added by ejkv
$state_list = $state->MakeStateArray(); // added by ejkv
$state_list[0]="---"; // added by ejkv

// address_state_code textbox replaced by Select menu, and contents filled from Database table states
// $form->addElement("text", "address_state_code", STATE_TEXT, array("size" => 25, "maxlength" => 50));
$form->addElement("select", "address_state_code", STATE_TEXT, $state_list); // changed by ejkv
$form->addElement("text", "address_post_code", ZIP_TEXT, array("size" => 10, "maxlength" => 20));
$form->addElement("text", "address_country", $lng_country, array("size" => 25, "maxlength" => 50));

$form->addElement("static", null, null, null);
if (!$cUser->HasLevel(1))
	$form->addElement("static", null, $recaptcha, null);
$form->addElement('submit', 'btnSubmit', $lng_create_member);

//
// Define form rules
//
$form->addRule('member_id', $lng_enter_member_id, 'required');
$form->addRule('password', $lng_pwd_not_long_enough, 'minlength', 7);
$form->addRule('first_name', $lng_enter_first_name, 'required');
$form->addRule('last_name', $lng_enter_last_name, 'required');
$form->addRule('address_city', $lng_enter_a." ". ADDRESS_LINE_3, 'required');
$form->addRule('address_state_code',$lng_enter_a." " . STATE_TEXT, 'required');
$form->addRule('address_post_code',$lng_enter_a." ".ZIP_TEXT, 'required');
$form->addRule('address_country', $lng_enter_country, 'required');

$form->registerRule('verify_unique_member_id','function','verify_unique_member_id');
$form->addRule('member_id',$lng_id_already_used,'verify_unique_member_id');
$form->registerRule('verify_good_member_id','function','verify_good_member_id');
$form->addRule('member_id',$lng_spec_char_not_allowed,'verify_good_member_id');
$form->registerRule('verify_good_password','function','verify_good_password');
$form->addRule('password', $lng_pwd_must_contain_nmbr, 'verify_good_password');
$form->registerRule('verify_no_apostraphes_or_backslashes','function','verify_no_apostraphes_or_backslashes');
$form->addRule("password", $lng_no_apps_or_backslhs_in_pwd, "verify_no_apostraphes_or_backslashes");
$form->registerRule('verify_role_allowed','function','verify_role_allowed');
if ($cUser->HasLevel(1))
	$form->addRule('member_role',$lng_cannot_assign_higher_level,'verify_role_allowed');
$form->registerRule('verify_not_future_date','function','verify_not_future_date');
if ($cUser->HasLevel(1))
	$form->addRule('join_date', $lng_join_date_not_future, 'verify_not_future_date');
$form->addRule('dob', $lng_birthday_not_in_future, 'verify_not_future_date');
$form->registerRule('verify_reasonable_dob','function','verify_reasonable_dob');
$form->addRule('dob', $lng_little_young_dont_you_think, 'verify_reasonable_dob');
$form->registerRule('verify_valid_email','function', 'verify_valid_email');
$form->addRule('email', $lng_not_valid_email, 'verify_valid_email');
$form->registerRule('verify_phone_format','function','verify_phone_format');
$form->addRule('phone1', $lng_phone_not_valid, 'verify_phone_format');
$form->addRule('phone2', $lng_phone_not_valid, 'verify_phone_format');
$form->addRule('fax', $lng_phone_not_valid, 'verify_phone_format');


//
// Check if we are processing a submission or just displaying the form
//
if ($form->validate() && ($cUser->HasLevel(1) || $recaptcha->validate())) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {
	$today = getdate();
	$current_date = array("Y"=>$today["year"], "F"=>$today["mon"], "d"=>$today["mday"]);
	$defaults = array("password"=>$cUser->GeneratePassword(), "dob"=>$current_date, "join_date"=>$current_date, "account_type"=>"S", "member_role"=>"0", "email_updates"=>DEFAULT_UPDATE_INTERVAL, "address_state_code"=>DEFAULT_STATE, "address_country"=>DEFAULT_COUNTRY);
	$form->setDefaults($defaults);
   $p->DisplayPage($form->toHtml());  // just display the form
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $cUser,$cErr, $today, $lng_member_created, $lng_click, $lng_here, $lng_create_another_member_acc, $lng_if_want_add_joint_member, $lng_member_no_email_address, $lng_and_pwd, $lng_member_id, $lng_pwd, $lng_email_has_been_send_to, $lng_containing_userid_and_pwd, $lng_email_new_member_failed, $lng_member_email_failed, $lng_error_saving_member, $lng_try_again_later;
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

	if($created = $new_person->SaveNewPerson()) 
		$created = $new_member->SaveNewMember();

	if($created) {
		$list .= $lng_member_created.". ".$lng_click." <A HREF=member_create.php>".$lng_here."</A> ". $lng_create_another_member_acc.".<P>".$lng_if_want_add_joint_member." <A HREF=member_contact_create.php?mode=admin&member_id=". $values["member_id"] .">".$lng_here."</A>.<P>";
		if($values['email'] == "") {
			$msg_no_email = $lng_member_no_email_address." ('". $values["member_id"]. "') ".$lng_and_pwd." ('". $values["password"] ."').";
			$list .= $msg_no_email;
			mail(EMAIL_ADMIN, $lng_member_created .": ". $values['member_id'], $msg_no_email, "From:".EMAIL_FROM);
		} else {
			$mailed = mail($values['email'], NEW_MEMBER_SUBJECT, NEW_MEMBER_MESSAGE . "\n\n".$lng_member_id.": ". $values['member_id'] ."\n". $lng_pwd.": ". $values['password'], "From:". EMAIL_FROM . (NEW_MEMBER_EMAIL_ADMIN ? "\r\nCc: ". EMAIL_ADMIN : "")); // added "From:" - by ejkv
			if($mailed)
				$list .= $lng_email_has_been_send_to." '". $values["email"] ."' ".$lng_containing_userid_and_pwd.".";
			else
				$list .= $lng_email_new_member_failed." ". PHONE_ADMIN .". <I>".$lng_member_email_failed." ('". $values["member_id"]. "') ".$lng_and_pwd." ('". $values["password"] ."').</I>";	 
		}
	} else {
		$cErr->Error($lng_error_saving_member." ".$lng_try_again_later);
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
