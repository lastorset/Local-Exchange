<?php
include_once("includes/inc.global.php");
$cUser->MustBeLevel(1);
$p->site_section = EVENTS;
$p->page_title = _("Choose Item to Edit");

include("includes/inc.forms.php");
include_once("classes/class.news.php");

$news = new cNewsGroup;
$news->LoadNewsGroup();
if($news_array = $news->MakeNewsArray()) {
	$form->addElement("select", "news_id", _("Which News Item?"), $news_array);
	$form->addElement("static", null, null, null);
	$form->addElement('submit', 'btnSubmit', _("Edit"));
} else {
	$form->addElement("static", null, _("There are no current news items").".", null);
}

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $cUser;
	header("location:http://".HTTP_BASE."/news_edit.php?news_id=".$values["news_id"]);
	exit;	
}

?>
