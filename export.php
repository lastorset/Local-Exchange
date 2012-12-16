<?php
include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);
$p->site_section = ADMINISTRATION;
$p->page_title = _("Export to Spreadsheet");

include_once("classes/class.backup.php");
include("includes/inc.forms.php");

$form->addElement("static", 'contact', _("This will export all the tables in the database to an Excel spreadsheet for backup and  reporting purposes.  Click on the button below and you will be prompted to open or save the file.  Please note that the information in this file is confidential and should only be stored in a private, secure location."), null);
$form->addElement("static", null, null, null);
$form->addElement("submit", "btnSubmit", _("Download"));

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

	$list = _("Export complete.");
	$p->DisplayPage($list);
}
?>
