<?php

include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = 0;

include("includes/inc.forms.php");

// TODO: Factor out and require i18n, which in turn can require the necessary .json files (which could be
// served using a special PHP script that knows the language from the cookie) for the rudimentary i18n implementation.
$form->addElement("html", "<script data-main='ajax/password-quality' src='lib/require.min.js'></script>");

$form->addElement("html", "<script type='text/javascript'>addPasswordMeter('new_passwd');</script>");

//
// Define form elements
//
$form->addElement('header', null, _("Change Password for")." ". $cUser->person[0]->first_name ." " . $cUser->person[0]->mid_name ." " . $cUser->person[0]->last_name); // added mid_name by ejkv
$form->addElement('html', '<TR></TR>');  // TODO: Move this to the header
$form->addElement("static", null, _("Good passwords are hard to guess. Use uncommon words or inside jokes, non-standard uPPercasing, creative spelllling, and non-obvious numbers and symbols."), null);
$form->addElement('html', '<TR></TR>');
$options = array('size' => 30, 'maxlength' => 255);
$form->addElement('password', 'old_passwd', _("Old Password"),$options);
$form->addElement('password', 'new_passwd', _("Choose a New Password"),$options);
$form->addElement('password', 'rpt_passwd', _("Repeat the New Password"),$options);
$form->addElement('submit', 'btnSubmit', _("Change Password"));

//
// Define form rules
//
$form->addRule('old_passwd', _("Enter your current password"), 'required');
$form->addRule('new_passwd', _("Enter a new password"), 'required');
$form->addRule('rpt_passwd', _("You must re-enter the new password"), 'required');
$form->registerRule('verify_passwords_equal','function','verify_passwords_equal');
$form->addRule('new_passwd', _("Passwords are not the same"), 'verify_passwords_equal');
$form->registerRule('verify_old_password','function','verify_old_password');
$form->addRule('old_passwd', _("Password is incorrect"), 'verify_old_password');

//
//	Display or process the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process('process_data', false);
} else {
   $p->DisplayPage($form->toHtml());  // just display the form
}

function process_data ($values) {
	global $p, $cUser;
	
	if($cUser->ChangePassword($values['new_passwd']))
		$list = _("Password successfully changed.");
	else
		$list = _("There was an error changing the password.")." "._("Please try again later.");
	$p->DisplayPage($list);
}

function verify_old_password($element_name,$element_value) {
	global $cUser;
	if($cUser->ValidatePassword($element_value))
		return true;
	else
		return false;
}

function verify_passwords_equal() {
	global $form;

	if ($form->getElementValue('new_passwd') != $form->getElementValue('rpt_passwd'))
		return false;
	else
		return true;
}

?>
