<?php

include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = SITE_SECTION_OFFER_LIST;

include("includes/inc.forms.php");

//
// Define form elements
//
$form->addElement('header', null, $lng_changes_pwd_for." ". $cUser->person[0]->first_name ." " . $cUser->person[0]->mid_name ." " . $cUser->person[0]->last_name); // added mid_name by ejkv
$form->addElement('html', '<TR></TR>');  // TODO: Move this to the header
$form->addElement('static',null,$lng_pwd_must_be_seven_char_and_one_number);
$form->addElement('html', '<TR></TR>');
$options = array('size' => 10, 'maxlength' => 15);
$form->addElement('password', 'old_passwd', $lng_old_pwd,$options);
$form->addElement('password', 'new_passwd', $lng_choose_new_pwd,$options);
$form->addElement('password', 'rpt_passwd', $lng_repeat_new_pwd,$options);
$form->addElement('submit', 'btnSubmit', $lng_change_pwd);

//
// Define form rules
//
$form->addRule('old_passwd', $lng_enter_current_pwd, 'required');
$form->addRule('new_passwd', $lng_enter_new_pwd, 'required');
$form->addRule('rpt_passwd', $lng_reenter_new_pwd, 'required');
$form->addRule('new_passwd', $lng_pwd_not_long_enough, 'minlength', 7);
$form->registerRule('verify_passwords_equal','function','verify_passwords_equal');
$form->addRule('new_passwd', $lng_pwds_not_same, 'verify_passwords_equal');
$form->registerRule('verify_old_password','function','verify_old_password');
$form->addRule('old_passwd', $lng_pwd_incorrect, 'verify_old_password');
$form->registerRule('verify_good_password','function','verify_good_password');
$form->addRule('new_passwd', $lng_pwd_must_be_seven_char_and_one_number, 'verify_good_password');

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
	global $p, $cUser, $lng_pwd_successfully_changed, $lng_error_changing_pwd, $lng_try_again_later;
	
	if($cUser->ChangePassword($values['new_passwd']))
		$list = $lng_pwd_successfully_changed;
	else
		$list = $lng_error_changing_pwd." ".$lng_try_again_later;
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
