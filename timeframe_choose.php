<?php
include_once("includes/inc.global.php");

$p->site_section = EXCHANGES;
$p->page_title = _("Choose time period");

$cUser->MustBeLoggedOn();

include("includes/inc.forms.php");

$form->addElement("hidden", "action", $_REQUEST["action"]);
$form->addElement("hidden", "mode", $_REQUEST["mode"]); // added by ejkv
$form->addElement("hidden", "member_id", $_REQUEST["member_id"]); // added by ejkv
$today = getdate();
$options = array('language'=> _("en"), 'format' => 'dFY', 'minYear' => $today['year']-3,'maxYear' => $today['year']);
$form->addElement("date", "from", _("From when?"), $options);
$form->addElement("date", "to", _("To when?"), $options);
$form->addElement("static", null, null, null);
$form->addElement('submit', 'btnSubmit', _("Submit"));

if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process("process_data", false);
} else {  // Display the form
	$form->setDefaults(array("from"=>"1 month ago", "to"=>"today"));
	$p->DisplayPage($form->toHtml());
}

function process_data ($values) {
	global $cUser;
	
	$date = $values['from'];
	$from = $date['Y'] . '-' . $date['F'] . '-' . $date['d'];
	$date = $values['to'];
	$to = $date['Y'] . '-' . $date['F'] . '-' . $date['d'];

// added trade history timeframe for specific member (from / to)  - by ejkv
	if ($_REQUEST["mode"]==NULL) // added by ejkv
		header("location:http://".HTTP_BASE."/". $_REQUEST["action"] .".php?from=".$from . "&to=". $to);
	else // added by ejkv
		header("location:http://".HTTP_BASE."/trade_history.php?mode=".$_REQUEST["mode"]."&member_id=".$_REQUEST["member_id"]."&from=".$from . "&to=". $to); // added by ejkv
	exit;	
} 

?>
