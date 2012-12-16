<?php
include_once("includes/inc.global.php");

$cUser->MustBeLoggedOn();
$p->site_section = LISTINGS;
$p->page_title = _("Download Directory");

include_once("classes/class.directory.php");
include("includes/inc.forms.php");

$form->addElement("static", null, _("Click on the button below and you will be prompted to open or save a printer-friendly (PDF) version of the directory. If you don't already have it, you can download Adobe Acrobat from <A HREF=\"http://www.tucows.com/preview/194959.html\">here</A>."), null);
$form->addElement("static", null, null, null);
$form->addElement("static", null, _("Note that <u>older versions of Acrobat may not be able to read this file</u>.  If you get an error message when trying to download, try upgrading <A HREF=\"http://www.tucows.com/preview/194959.html\">Acrobat</A>. If you have Windows XP, Vista or 7 or Mac Intel, you can upgrade to the newest version, but if you have Windows 98 or 2000 or Mac OS X 10.4, you will need an older version."), null);
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

	$dir = new cDirectory();
	$dir->DownloadDirectory();

	$list = _("Download complete.");
	$p->DisplayPage($list);
}
?>
