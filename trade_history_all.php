<?php
	include_once("includes/inc.global.php");
	
	$cUser->MustBeLoggedOn();
	$p->site_section = EXCHANGES;
	$p->page_title = _("Exchange History");

	include_once("classes/class.trade.php");
	
	$from = new cDateTime($_REQUEST["from"]);
	$to = new cDateTime($_REQUEST["to"]);
	
	$output = "<B>"._("For period from")." ". $from->ShortDate() ." to ". $to->ShortDate() ."</B><P>";	

	$member_id = $_REQUEST["member_id"]; // added by ejkv
	if ($member_id == NULL) $member_id = "%"; // added by ejkv
	
	$trade_group = new cTradeGroup($member_id, $_REQUEST["from"], $_REQUEST["to"]); // replaced "%" by $member_id by ejkv
	$trade_group->LoadTradeGroup();
	$output .= $trade_group->DisplayTradeGroup();
	
	$p->DisplayPage($output);
	
?>
