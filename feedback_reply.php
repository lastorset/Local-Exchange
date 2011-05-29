<?php

include_once("includes/inc.global.php");

$member_about = new cMember;
$member_about->LoadMember($_REQUEST["about"]);

$p->site_section = SECTION_FEEDBACK;
$p->page_title = _("Follow Up on Feedback");

include("classes/class.feedback.php");
include("includes/inc.forms.php");
include("includes/inc.forms.validation.php");

//
// Define form elements
//
$feedback = new cFeedback;
$feedback->LoadFeedback($_REQUEST["feedback_id"]);

$feedback_history = $feedback->comment;
if($feedback->rebuttals)
	 $feedback_history .= "<BR>" . $feedback->rebuttals->DisplayRebuttalGroup($_REQUEST["about"]);

$member = new cMember;

if($_REQUEST["mode"] == "admin") {
	$cUser->MustBeLevel(2);
	$member->LoadMember($_REQUEST["author"]);
	$p->page_title .= " for ". $member->PrimaryName();
} else {
	$cUser->MustBeLoggedOn();
	$member = $cUser;
}

$form->addElement('static', null, "<B><I>"._("Prior Comments")."</I></B>", null);
$form->addElement('static', null, $feedback_history, null);
$form->addElement("hidden", "about", $member_about->member_id);
$form->addElement("hidden", "author", $_REQUEST["author"]);
$form->addElement("hidden", "mode", $_REQUEST["mode"]);
$form->addElement("hidden", "feedback_id", $_REQUEST["feedback_id"]);
$form->addElement('static', null, _("Comments"), null);
$form->addElement('textarea', 'comments', null, array('cols'=>60, 'rows'=>5, 'wrap'=>'soft', 'maxlength' => 255));
$form->addElement('submit', 'btnSubmit', _("Submit"));

//
// Define form rules
//
$form->addRule('rating', _("Choose a rating"), 'verify_selection');
$form->addRule('comments', _("Comments cannot be longer than 255 characters"), 'verify_max255');

//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {
   $p->DisplayPage($form->toHtml());  // just display the form
}

function process_data ($values) {
	global $p, $member_about, $member, $cErr, $cUser, $feedback;
	
	$trade = new cTrade();
	$trade->LoadTrade($feedback->trade_id);
	
	// Make sure trade members correct
	if ($trade->member_from->member_id == $member->member_id and $trade->member_to->member_id == $member_about->member_id) {
	} elseif ($trade->member_to->member_id == $member->member_id and $trade->member_from->member_id == $member_about->member_id) {
	} else {
		$cErr->Error(_("Members do not match the trade selected.")); // Theoretically, must be a hacker
		include("redirect.php");
	}
	
	$rebuttal = new cFeedbackRebuttal($_REQUEST["feedback_id"], $member->member_id, $values["comments"]);
	$success = $rebuttal->SaveRebuttal();
	
	if($success) {
        // Log if enabled & entered by an admin
		if(LOG_LEVEL > 0 and $_REQUEST["mode"] == "admin") {
            $cUser->MustBeLevel(2);
			$log_entry = new cLogEntry (FEEDBACK, FEEDBACK_BY_ADMIN, $feedback->feedback_id);
			$log_entry->SaveLogEntry();	
		}
		$output = _("Your feedback has been recorded.");
	} else {
		$output = _("There was an error recording your feedback.");
	}
	
	$p->DisplayPage($output);
}

//
// Form rule validation functions
//

?>
