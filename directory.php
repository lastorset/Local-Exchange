<?php
include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = LISTINGS;
$p->page_title = $lng_download_directory;

include("classes/class.directory.php");
include("includes/inc.forms.php");

$form->addElement("static", null, $lng_click_to_open_pdf, null);
$form->addElement("static", null, null, null);
$form->addElement("static", null, $lng_download_acrobat, null);
$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", $lng_download);

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $p;

	$dir = new cDirectory();
	$dir->DownloadDirectory();

	$list = $lng_download_complete;
	$p->DisplayPage($list);
}
?>
