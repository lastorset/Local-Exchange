<?php
include_once("includes/inc.global.php");
include("classes/class.uploads.php");

$cUser->MustBeLevel(1);

$p->site_section = EVENTS;
$p->page_title = _("Newsletter Uploaded");

$upload = new cUpload("N", $_REQUEST[_("Description")]);
if($upload->SaveUpload())
	$output = _("File uploaded.");
else
	$output = _("There was a problem uploading the file.");

$p->DisplayPage($output);
?>
