<?php
include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);
$p->site_section = ADMINISTRATION;
$p->page_title = $lng_export_to_spreadsheet;

include("classes/class.backup.php");
include("includes/inc.forms.php");

$form->addElement("static", 'contact', $lng_this_will_export_db_to_spreadsheet, null);
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

	$backup = new cBackup();
	$backup->BackupAll();

	$list = $lng_export_complete;
	$p->DisplayPage($list);
}
?>
