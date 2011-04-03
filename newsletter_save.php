<?php
include_once("includes/inc.global.php");
include("classes/class.uploads.php");

$cUser->MustBeLevel(1);

$p->site_section = EVENTS;
$p->page_title = $lng_newsletter_uploaded;

$upload = new cUpload("N", $_REQUEST[$lng_description]);
if($upload->SaveUpload())
	$output = $lng_file_uploaded;
else
	$output = $lng_problem_uploading_file;

$p->DisplayPage($output);
?>
