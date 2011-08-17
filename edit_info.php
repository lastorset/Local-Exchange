<?php
include_once("includes/inc.global.php");
$cUser->MustBeLevel(1);
$p->site_section = EVENTS;
$p->page_title = _("Choose Info Page to Edit");

include("includes/inc.forms.php");
include_once("classes/class.info.php");

$pgs = cInfo::LoadPages();

if ($pgs) {
	
	foreach($pgs as $pg) {
		
		$p_array[$pg["id"]] = stripslashes($pg["title"]);
	}
	
	$form->addElement("select", "news_id", _("Which Info Page?"), $p_array);
	$form->addElement("static", null, null, null);
	$form->addElement('submit', 'btnSubmit', _("Edit"));
}
 else {
	$form->addElement("static", null, _("There are no current Info Pages."), null);
}


if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $cUser;
	header("location:http://".HTTP_BASE."/do_info_edit.php?id=".$values["news_id"]);
	exit;	
}

?>
