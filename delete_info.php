<?php
include_once("includes/inc.global.php");
$cUser->MustBeLevel(1);
$p->site_section = EVENTS;
$p->page_title = _("Choose Info Page to Delete");

include("includes/inc.forms.php");
include_once("classes/class.info.php");

if ($_REQUEST["id"] && $_REQUEST["confirm"]==1) {
	
		$q = 'delete from cdm_pages where id='.$cDB->EscTxt($_REQUEST["id"]).'';
		$success = $cDB->Query($q);
	
		if ($success)
				$output = _("Page deleted.");
		else
				$output = _("There was a problem deleting the page.");
		
		$p->DisplayPage($output);
		
		die;
}
			
$pgs = cInfo::LoadPages();

if ($pgs) {
	
	foreach($pgs as $pg) {
		
		$p_array[$pg["id"]] = stripslashes($pg["title"]);
	}
	
	$form->addElement("select", "id", _("Which Info Page?"), $p_array);
	$form->addElement("static", null, null, null);
	$form->addElement('submit', 'btnSubmit', _("Delete"));
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
	global $cUser,$p,$cDB;
	
	if ($_REQUEST["confirm"]!=1) {
		
		$output .= _("Really Delete this page (ID#").$_REQUEST["id"].")? <a href=delete_info.php?id=".$_REQUEST["id"]."&confirm=1>"._("Yes")."</a> | <a href=javascript:history.back(1)>"._("No")." "._("(go back)")."</a>";
	}
	
	$p->DisplayPage($output);
	//header("location:http://".HTTP_BASE."/do_info_edit.php?id=".$values["news_id"]);
	//exit;	
}