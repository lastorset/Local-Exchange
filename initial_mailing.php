<?php

include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);
$p->site_section = ADMINISTRATION;
$p->page_title = _("Initial Mailing for Rollout");

$subject = _("Important information from")." " . SITE_LONG_TITLE;
/*
$message = "Hello,\n\nThe new " . SITE_LONG_TITLE . " interactive website is now online!  You can now browse the directory, create and modify your listings, and exchange hours on the web.\n\nThe website address is the same (http://www.fourthcornerexchange.com) and your new userid and password are listed at the end of this message.  The password was automatically generated and we recommend you go to the Member Profile section and change it to something you can more easily remember.\n\nIf you have questions about the site you can reply to this email or call Calvin at 201-7361.";
*/

$message = "";

$all_members = new cMemberGroup();
$all_members->LoadMemberGroup();

$output = "";

foreach ($all_members->members as $member) {
	if ($member->member_id == 'francis' or $member->member_id == 'lia')
		continue;
	
	$password = $member->GeneratePassword();
	$changed = $member->ChangePassword($password);
	
	if(!$changed) {
		$output .= _("Could not reset password for")." '". $member->member_id ."'. "._("Skipped email").".<BR>";
		continue;
	}

// $member->person[0]->email
	$mailed = mail($member->person[0]->email, $subject, $message . "\n\n"._("Member ID").": ". $member->member_id ."\n". _("Password").": ". $password, "From:".EMAIL_FROM ."\nContent-type: text/plain; charset=UTF-8"); // added "From:" - by ejkv

	if(!$mailed)
		$output .= _("Could not email")." ". $member->member_id .".  "._("His/her password is")." '". $password ."'.<BR>";
}

if($output == "")
	$output = _("Email successfully sent to all members.");

$p->DisplayPage($output);


?>
