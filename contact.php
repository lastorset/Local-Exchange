<?php
include_once("includes/inc.global.php");
$p->site_section = SECTION_EMAIL;
$p->page_title = $lng_contact_us;

include("includes/inc.forms.php");

//
// First, we define the form
//
$form->addElement("static", null, $lng_for_more_information." ". SITE_LONG_TITLE ." ".$lng_fill_out_info_request." <A HREF=news.php>".$lng_events."</A> ".$lng_next_member_meeting, null);
$form->addElement("static", null, null, null);
$form->addElement("text", "name", $lng_name);
$form->addElement("text", "email", $lng_email);
$form->addElement("text", "phone", $lng_phone);
$form->addElement("static", null, null, null);
$form->addElement("textarea", "message", $lng_your_message, array("cols"=>55, "rows"=>10, "wrap"=>"soft")); // colls changed from 65 to 55 by ejkv
$form->addElement("static", null, null, null);
$heard_from = array ("0"=>$lng_select_one, "1"=>$lng_newspaper, "2"=>$lng_radio, "3"=>$lng_search_engine, "4"=>$lng_friend, "5"=>$lng_local_business, "6"=>$lng_artical, "7"=>$lng_other);
$form->addElement("select", "how_heard", $lng_how_did_you_hear_about_us, $heard_from);

$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", $lng_send);

//
// Define form rules
//
$form->addRule("name", $lng_enter_name, "required");
$form->addRule("email", $lng_enter_email_address, "required");
$form->addRule("phone", $lng_enter_phone_number, "required");


if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

//
// The form has been submitted with valid data, so process it   
//
function process_data ($values) {
	global $p, $heard_from, $lng_contact_form, $lng_from, $lng_phone, $lng_heard_from, $lng_thank_you, $lng_problem_sending_email; // replaced $lng_from_colon, $lng_phone_colon, $lng_heard_from_colon by $lng_from, $lng_phone, $lng_heard_from - by ejkv
	
	$mailed = mail(EMAIL_ADMIN, SITE_SHORT_TITLE ." ".$lng_contact_form, $lng_from.": ". $values["name"]. "\n". $lng_phone.": ". $values["phone"] ."\n". $lng_heard_from.": ". $heard_from[$values["how_heard"]] ."\n\n". wordwrap($values["message"], 64) , "From:". $values["email"]); // replaced $lng_from.":" by "From:" - by ejkv
	
	if($mailed)
		$output = $lng_thank_you;
	else
		$output = $lng_problem_sending_email;	
	$p->DisplayPage($output);
}

?>
