<?php

include_once("includes/inc.global.php");

$p->site_section = SITE_SECTION_OFFER_LIST;

include("includes/inc.forms.php");

//
// First, we define the form
//
if($_REQUEST["mode"] == "admin") {  // Administrator is editing a member's account
	$cUser->MustBeLevel(1);
	$form->addElement("hidden","mode","admin");
	$form->addElement("hidden","member_id",$_REQUEST["member_id"]);		
} else {  // Member is editing own account
	$cUser->MustBeLoggedOn();
	$cUser->VerifyPersonInAccount($_REQUEST["person_id"]); // Make sure hacker didn't change URL
	$form->addElement("hidden","member_id", $cUser->member_id);
	$form->addElement("hidden","mode","self");
}

$person = new cPerson;
$person->LoadPerson($_REQUEST["person_id"]);
$form->addElement("header", null, $lng_edit_joint_member." " . $person->first_name . " " . $person->mid_name . " " . $person->last_name); // added mid_name by ejkv
$form->addElement("html", "<TR></TR>");

$form->addElement("hidden","person_id",$_REQUEST["person_id"]);
$form->addElement("text", "first_name", $lng_first_name, array("size" => 15, "maxlength" => 20));
$form->addElement("text", "mid_name", $lng_middle_name, array("size" => 10, "maxlength" => 20));
$form->addElement("text", "last_name", $lng_last_name, array("size" => 20, "maxlength" => 30));
$form->addElement("static", null, null, null);

if ($_REQUEST["mode"] == "admin") {
    $cUser->MustBeLevel(1);

	$today = getdate();
	$options = array("language"=> $lng_language, "format" => "dFY", "maxYear"=>$today["year"], "minYear"=>"1880"); // - changed "en" by $lng_language by ejkv
	$form->addElement("date", "dob", $lng_date_of_birth, $options);
	$form->addElement("text", "mother_mn", $lng_mothers_maiden_name, array("size" => 20, "maxlength" => 30)); 
	$form->addElement("static", null, null, null);
}

$form->addElement("select","directory_list", $lng_list_persons_contact_info_to_dir, array("Y"=>$lng_yes, "N"=>$lng_no));
$form->addElement("text", "email", $lng_email_address, array("size" => 25, "maxlength" => 40));
$form->addElement("text", "phone1", $lng_primary_phone, array("size" => 20));
$form->addElement("text", "phone2", $lng_secondary_phone, array("size" => 20));
$form->addElement("text", "fax", $lng_fax_number, array("size" => 20));
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

// TODO: Add the ability to make this person the primary member on the account

$form->addElement("static", null, null, null);
$form->addElement('submit', 'btnSubmit', $lng_update);

//
// Define form rules
//
$form->addRule('first_name', $lng_enter_first_name, 'required');
$form->addRule('last_name', $lng_enter_last_name, 'required');
$form->registerRule('verify_not_future_date','function','verify_not_future_date');
$form->addRule('dob', $lng_birthday_not_in_future, 'verify_not_future_date');
$form->registerRule('verify_reasonable_dob','function','verify_reasonable_dob');
$form->addRule('dob', $lng_little_young_dont_you_think, 'verify_reasonable_dob');
$form->addRule('address_city',$lng_enter_a." ". ADDRESS_LINE_3, 'required');
$form->addRule('address_state_code',$lng_enter_a." ". STATE_TEXT, 'required');
$form->addRule('address_post_code',$lng_enter_a." ".ZIP_TEXT, 'required');
$form->addRule('address_country', $lng_enter_country, 'required');
$form->registerRule('verify_valid_email','function', 'verify_valid_email');
$form->addRule('email', $lng_not_valid_email, 'verify_valid_email');
$form->registerRule('verify_phone_format','function','verify_phone_format');
$form->addRule('phone1', $lng_phone_not_valid, 'verify_phone_format');
$form->addRule('phone2', $lng_phone_not_valid, 'verify_phone_format');
$form->addRule('fax', $lng_phone_not_valid, 'verify_phone_format');


//
// Check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Otherwise we need to load the existing values			
	$current_values = array ("first_name"=>$person->first_name, "mid_name"=>$person->mid_name, "last_name"=>$person->last_name, "directory_list"=>$person->directory_list, "email"=>$person->email, "phone1"=>$person->DisplayPhone(1), "phone2"=>$person->DisplayPhone(2), "fax"=>$person->DisplayPhone("fax"), "address_street1"=>$person->address_street1, "address_street2"=>$person->address_street2, "address_city"=>$person->address_city, "address_state_code"=>$person->address_state_code, "address_post_code"=>$person->address_post_code, "address_country"=>$person->address_country);
	
	if($_REQUEST["mode"] == "admin") {  // Load defaults for extra fields visible by administrators
        $cUser->MustBeLevel(1);

		$current_values["mother_mn"] = $person->mother_mn;
		
		if ($person->dob) {		
			$current_values["dob"] = array ('d'=>substr($person->dob,8,2),'F'=>date('n',strtotime($person->dob)),'Y'=>substr($person->dob,0,4));  // Using 'n' due to a bug in Quickform
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
	global $p, $cUser, $cErr, $person, $today, $lng_changes_saved, $lng_error_saving_person, $lng_try_again_later ;
	$list = "";

	$person->first_name = htmlspecialchars($values["first_name"]);
	$person->mid_name = htmlspecialchars($values["mid_name"]);
	$person->last_name = htmlspecialchars($values["last_name"]);
	$person->directory_list = htmlspecialchars($values["directory_list"]);
	$person->email = htmlspecialchars($values["email"]);
	$person->address_street1 = htmlspecialchars($values["address_street1"]);
	$person->address_street2 = htmlspecialchars($values["address_street2"]);
	$person->address_city = htmlspecialchars($values["address_city"]);
	$person->address_state_code =
                            htmlspecialchars($values["address_state_code"]);
	$person->address_post_code = htmlspecialchars($values["address_post_code"]);
	$person->address_country = htmlspecialchars($values["address_country"]);	

	$phone = new cPhone_uk($values['phone1']);
	$person->phone1_area = $phone->area;
	$person->phone1_number = $phone->SevenDigits();
	$person->phone1_ext = $phone->ext;
	$phone = new cPhone_uk($values['phone2']);
	$person->phone2_area = $phone->area;
	$person->phone2_number = $phone->SevenDigits();
	$person->phone2_ext = $phone->ext;	
	$phone = new cPhone_uk($values['fax']);
	$person->fax_area = $phone->area;
	$person->fax_number = $phone->SevenDigits();
	$person->fax_ext = $phone->ext;	
	
	if($_REQUEST["mode"] == "admin")	{
        $cUser->MustBeLevel(1);

		$person->mother_mn = htmlspecialchars($values["mother_mn"]);
		
		// [chris] Fixed issue with passing Array to htmlspecialchars()
		$date = $values['dob'];
		$dob = htmlspecialchars($date['Y'] . '/' . $date['F'] . '/' . $date['d']);
//		echo $dob ."=". $today['year']."/".$today['mon']."/".$today['mday'];
		if($dob != $today['year']."/".$today['mon']."/".$today['mday']) { 
			$person->dob = $dob; 
		} // if date left as default (today's date), we don't want to set it
	} 	
	
	if($person->SavePerson()) {
		$list .= $lng_changes_saved;	 
	} else {
		$cErr->Error($lng_error_saving_person." ".$lng_try_again_later);
	}
   $p->DisplayPage($list);
}
//
// The following functions verify form data
//

// TODO: All my validation functions should go into a new cFormValidation class

function verify_no_apostraphes_or_backslashes($element_name,$element_value) {
	if(strstr($element_value,"'") or strstr($element_value,"\\"))
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

function verify_not_future_date ($element_name,$element_value) {
	$date = $element_value;
	$date_str = $date['Y'] . '/' . $date['F'] . '/' . $date['d'];

	if (strtotime($date_str) > strtotime("now"))
		return false;
	else
		return true;
}

?>
