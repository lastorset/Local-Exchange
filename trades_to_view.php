<?php
include_once("includes/inc.global.php");
$cUser->MustBeLevel(2); // $cUser->MustBeLoggedOn(); - changed to MustBeLevel(2) by ejkv
$p->site_section = EXCHANGES;
$p->page_title = $lng_choose_member_view;

include("includes/inc.forms.php");

$ids = new cMemberGroup;
$ids->LoadMemberGroup();
$form->addElement("select", "member_id", $lng_whose_exchange_history, $ids->MakeIDArray());
$form->addElement("static", null, null, null);
$form->addElement('submit', 'btnSubmit', $lng_view);

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $cUser;
	header("location:http://".HTTP_BASE."/timeframe_choose.php?mode=other&member_id=".$values["member_id"]); // changed next module from trade_history.php into timeframe_choose.php to select timeframe (from / to) - by ejkv
	exit;	
}

?>
