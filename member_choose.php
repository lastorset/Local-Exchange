<?php
include_once("includes/inc.global.php");
$cUser->MustBeLevel(1);

$p->site_section = ADMINISTRATION;
$p->page_title = _("For which member?");

include("includes/inc.forms.php");

//$form->addElement("header", null, "For which member?");
//$form->addElement("html", "<TR></TR>");
$form->addElement("hidden", "action", $_REQUEST["action"]);

if(isset($_REQUEST["get1"])) {
	$form->addElement("hidden", "get1", $_REQUEST["get1"]);
	$form->addElement("hidden", "get1val", $_REQUEST["get1val"]);
}

$ids = new cMemberGroup;
		
if(isset($_REQUEST["inactive"]))
	$ids->LoadMemberGroup(false, true);
else
	$ids->LoadMemberGroup();
	
$form->addElement("select", "member_id", _("Member"), $ids->MakeIDArray());
$form->addElement("static", null, null, null);
$form->addElement('submit', 'btnSubmit', _("Submit"));

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $cUser;
	
	if(isset($_REQUEST["get1"]))
		$get_string = "&". $_REQUEST["get1"] ."=". $_REQUEST["get1val"];
	else
		$get_string = "";
		
	header("location:http://".HTTP_BASE."/". $_REQUEST["action"] .".php?mode=admin&member_id=".$values["member_id"] . $get_string);
	exit;	
}

?>
