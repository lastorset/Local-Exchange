<?php
include_once("includes/inc.global.php");
$cUser->MustBeLevel(1);
$p->site_section = EVENTS;
$p->page_title = $lng_choose_info_page_delete;

include("includes/inc.forms.php");
include_once("classes/class.info.php");

if ($_REQUEST["id"] && $_REQUEST["confirm"]==1) {
	
		$q = 'delete from cdm_pages where id='.$cDB->EscTxt($_REQUEST["id"]).'';
		$success = $cDB->Query($q);
	
		if ($success)
				$output = $lng_page_deleted;
		else
				$output = $lng_problem_deleting_page;
		
		$p->DisplayPage($output);
		
		die;
}
			
$pgs = cInfo::LoadPages();

if ($pgs) {
	
	foreach($pgs as $pg) {
		
		$p_array[$pg["id"]] = stripslashes($pg["title"]);
	}
	
	$form->addElement("select", "id", $lng_which_info_page, $p_array);
	$form->addElement("static", null, null, null);
	$form->addElement('submit', 'btnSubmit', $lng_delete);
}
 else {
	$form->addElement("static", null, $lng_no_current_info_pages, null);
}


if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $cUser,$p,$cDB, $lng_really_delete_page_id, $lng_yes, $lng_no, $lng_go_back;
	
	if ($_REQUEST["confirm"]!=1) {
		
		$output .= $lng_really_delete_page_id.$_REQUEST["id"].")? <a href=delete_info.php?id=".$_REQUEST["id"]."&confirm=1>".$lng_yes."</a> | <a href=javascript:history.back(1)>".$lng_no." ".$lng_go_back."</a>";
	}
	
	$p->DisplayPage($output);
	//header("location:http://".HTTP_BASE."/do_info_edit.php?id=".$values["news_id"]);
	//exit;	
}