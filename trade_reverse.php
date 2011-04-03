<?php

include_once("includes/inc.global.php");

$cUser->MustBeLevel(2);
$p->site_section = EXCHANGES;
$p->page_title = $lng_reverse_exchange;

include("classes/class.trade.php");
include("includes/inc.forms.php");

//
// Define form elements
//
$trades = new cTradeGroup;
$trades->LoadTradeGroup();
$form->addElement("select", "trade_id", $lng_choose_exchange_to_reverse, $trades->MakeTradeArray());
$form->addElement("html", "<TR></TR>");
$form->addElement('static', null, $lng_enter_brief_explanation, null);
$form->addElement('textarea', 'description', null, array('cols'=>50, 'rows'=>2, 'wrap'=>'soft', 'maxlength' => 75));
$form->addElement('submit', 'btnSubmit', $lng_reverse);

//
// Define form rules
//
//$form->addRule('description', 'Enter a description', 'required');


//
// Then check if we are processing a submission or just displaying the form
//
if ($form->validate()) { // Form is validated so processes the data
   $form->freeze();
 	$form->process('process_data', false);
} else {
   $p->DisplayPage($form->toHtml());  // just display the form
}

function process_data ($values) {
	global $p, $cErr, $lng_trade_reversed, $lng_error_reversing_trade;

	$old_trade = new cTrade;
	$old_trade->LoadTrade($values["trade_id"]);
	$success = $old_trade->ReverseTrade($values["description"]);	
	
	if($success)
		$list = $lng_trade_reversed;
	else
		$list = "<i>".$lng_error_reversing_trade."!<i>";
	
   $p->DisplayPage($list);
}




?>
