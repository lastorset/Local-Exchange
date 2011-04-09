<?php
include_once("includes/inc.global.php");
$p->site_section = 0;

include("includes/inc.forms.php");

$form->addElement("header", null, $lng_reset_pwd);
$form->addElement("html", "<TR></TR>");

$form->addElement("text", "member_id", $lng_enter_member_id);
$form->addElement("text", "email", $lng_enter_eml_addr_for_your_acc);

$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", $lng_reset_pwd);

$form->registerRule('verify_email','function','verify_email');
$form->addRule('email',$lng_address_or_member_id_incorrect,'verify_email');
$form->addElement("static", null, null, null);
$form->addElement("static", 'contact', $lng_if_not_remember_id_or_eml_please." <A HREF=contact.php>".$lng_contact."</A> ".$lng_us.".", null);

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p, $lng_pwd_reset_change_after_login, $lng_new_pwd, $lng_new_pwd_has_been_sent, $lng_eml_new_pwd_failed;
	
	$member = new cMember;
	$member->LoadMember($values["member_id"]);

	$password = $member->GeneratePassword();
	$member->ChangePassword($password); // This will bomb out if the password change fails
	$member->UnlockAccount();
	
	$list = $lng_pwd_reset_change_after_login."<P>";
	$mailed = mail($values['email'], PASSWORD_RESET_SUBJECT, PASSWORD_RESET_MESSAGE . "\n\n".$lng_new_pwd.": ". $password, "From:".EMAIL_FROM); // added "From:" - by ejkv
	if($mailed)
		$list .= $lng_new_pwd_has_been_sent;
	else
		$list .= "<I>".$lng_eml_new_pwd_failed." ". PHONE_ADMIN ."</I>.";	
	$p->DisplayPage($list);
}

function verify_email($element_name,$element_value) {
	global $form;
	$member = new cMember;

	if(!$member->VerifyMemberExists($form->getElementValue("member_id")))
		return false;  // Don't want to try to load member if member_id invalid, 
							// because of inappropriate error message.
		
	$member->LoadMember($form->getElementValue("member_id"));

	if($element_value == $member->person[0]->email)
		return true;
	else
		return false;
}

?>
