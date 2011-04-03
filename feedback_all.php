<?php

include_once("includes/inc.global.php");
	
include("classes/class.feedback.php");
	
$cUser->MustBeLoggedOn();
	
if($_REQUEST["mode"] == "other") 
	$member_id = $_REQUEST["member_id"];
else
	$member_id = $cUser->member_id;
	
$p->site_section = SECTION_FEEDBACK;
$member = new cMember;
$member->LoadMember($member_id);
$p->page_title = $lng_feedback_for." ". $member->PrimaryName();

$feedbackgrp = new cFeedbackGroup;
$feedbackgrp->LoadFeedbackGroup($member_id);

if (isset($feedbackgrp->feedback)) {
	$output = $feedbackgrp->DisplayFeedbackTable($cUser->member_id);
} else  {
	if($_REQUEST["mode"] == "self")
		$output = $lng_dont_have_feedback;
	else
		$output = $lng_member_does_not_have_feedback;
}

$p->DisplayPage($output);
	
?>	
