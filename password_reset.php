<?php
include_once("includes/inc.global.php");
$p->site_section = 0;

include("includes/inc.forms.php");

$form->addElement("header", null, _("Reset Password"));
$form->addElement("html", "<TR></TR>");

$form->addElement("text", "member_id", _("Enter your username"));
$form->addElement("text", "email", _("Enter the Email Address for your Account"));

$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", _("Reset Password"));

$form->registerRule('verify_email','function','verify_email');
$form->addRule('email',_("Address or username is incorrect"),'verify_email');
$form->addElement("static", null, null, null);
$form->addElement("static", 'contact', _("If you cannot remember your username or email address, please")." <A HREF=contact.php>"._("contact")."</A> "._("us").".", null);

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p;
	
	$member = new cMember;
	$member->LoadMember($values["member_id"]);

	$password = $member->GeneratePassword();
	$member->ChangePassword($password); // This will bomb out if the password change fails
	$member->UnlockAccount();
	
	$list = _("Your password has been reset.  You can change the new password after you login by going into the Member Profile section of the web site.")."<P>";
	$mailed = mail($values['email'], PASSWORD_RESET_SUBJECT, PASSWORD_RESET_MESSAGE . "\n\n"._("New Password").": ". $password ."\n"._("Username").": ". $member->member_id, "From:".EMAIL_FROM ."\nContent-type: text/plain; charset=UTF-8"); // added "From:" - by ejkv
	if($mailed)
		$list .= _("The new password has been sent to your email address.");
	else
		$list .= "<I>"._("However, the attempt to email the new password failed.  This is most likely due to a technical problem.  Contact your administrator at")." ". PHONE_ADMIN ."</I>.";	
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
