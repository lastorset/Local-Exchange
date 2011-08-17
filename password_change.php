<?php

include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = 0;

include("includes/inc.forms.php");

//
// Define form elements
//
$form->addElement('header', null, _("Change Password for")." ". $cUser->person[0]->first_name ." " . $cUser->person[0]->mid_name ." " . $cUser->person[0]->last_name); // added mid_name by ejkv
$form->addElement('html', '<TR></TR>');  // TODO: Move this to the header
$form->addElement('static',null,_("For your security, passwords must be at least 7 characters long and include at least one number."));
$form->addElement('html', '<TR></TR>');
$options = array('size' => 10, 'maxlength' => 15);
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
$form->addRule('new_passwd', _("Password not long enough"), 'minlength', 7);
$form->registerRule('verify_passwords_equal','function','verify_passwords_equal');
$form->addRule('new_passwd', _("Passwords are not the same"), 'verify_passwords_equal');
$form->registerRule('verify_old_password','function','verify_old_password');
$form->addRule('old_passwd', _("Password is incorrect"), 'verify_old_password');
$form->registerRule('verify_good_password','function','verify_good_password');
$form->addRule('new_passwd', _("For your security, passwords must be at least 7 characters long and include at least one number."), 'verify_good_password');

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


function verify_passwords_equal() {
	global $form;

	if ($form->getElementValue('new_passwd') != $form->getElementValue('rpt_passwd'))
		return false;
	else
		return true;
}

?>
