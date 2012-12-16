<?php

include_once("includes/inc.global.php");

$member_about = new cMember;
$member_about->LoadMember($_REQUEST["about"]);

$p->site_section = SECTION_FEEDBACK;
$p->page_title = _("Leave Feedback about")." ". $member_about->PrimaryName();

include_once("classes/class.feedback.php");
include("includes/inc.forms.php");
include("includes/inc.forms.validation.php");

//
// Define form elements
//
$member = new cMember;

if($_REQUEST["mode"] == "admin") {
	$cUser->MustBeLevel(2);
	$member->LoadMember($_REQUEST["author"]);
	$p->page_title .= " for ". $member->PrimaryName();
} else {
	$cUser->MustBeLoggedOn();
	$member = $cUser;
}

$form->addElement('static', null, _("All feedback is public. Before leaving <i>negative</i> feedback, we recommend trying to address your concerns with the other community member.  Often misunderstandings can be resolved to the benefit of both parties."), null);
$form->addElement('static', null, null, null);
$ratings = array(0=>"", POSITIVE=>_("Positive"), NEUTRAL=>_("Neutral"), NEGATIVE=>_("Negative")); 
$form->addElement("select", "rating", "Feedback Rating", $ratings);
$form->addElement("hidden", "about", $member_about->member_id);
$form->addElement("hidden", "author", $_REQUEST["author"]);
$form->addElement("hidden", "mode", $_REQUEST["mode"]);
$form->addElement("hidden", "trade_id", $_REQUEST["trade_id"]);
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
	global $p, $member_about, $member, $cErr, $cUser;
	
	$feedback = new cFeedback($member->member_id, $member_about->member_id, $_REQUEST["trade_id"], $values["rating"], $values["comments"]);
	$success = $feedback->SaveFeedback();
	
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
