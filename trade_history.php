<?php
	include_once("includes/inc.global.php");

	global $site_settings;
	
	$cUser->MustBeLoggedOn();
	$p->site_section = EXCHANGES;
	$p->page_title = _("Exchange History");

	include_once("classes/class.trade.php");
	
	$member = new cMember;
	
	if($_REQUEST["mode"] == "self") {
		$member = $cUser;
	} else {
		$member->LoadMember($_REQUEST["member_id"]);
		$p->page_title = sprintf(_("Exchange history for %s"), $member->PrimaryName());
	}
	
	if ($member->balance > 0)
		$color = "#4a5fa4";
	else
		$color = "#554f4f";
	
// added trade history timeframe (from / to)  - by ejkv
	$from_date = $_REQUEST["from"];
	if ($_REQUEST["from"] == NULL ) $from_date = LONG_LONG_AGO;
	$from = new cDateTime($from_date);

	$to_date = $_REQUEST["to"];
	if ($_REQUEST["to"] == "" ) $to_date = FAR_FAR_AWAY;
	$to = new cDateTime($to_date);
// added trade history timeframe (from / to) - by ejkv
	
	$list = "<B>"._("Current Balance").": </B><FONT COLOR=". $color .">". $member->balance . " ". strtolower($site_settings->getUnitString()) ." - "._("For period from")." ". $from->ShortDate() ." to ". $to->ShortDate() ."</FONT><P>"; // added (from / to) - by ejkv

	$trade_group = new cTradeGroup($member->member_id, $from_date, $to_date); // added (from / to) - by ejkv
	$trade_group->LoadTradeGroup("individual");
	$list .= $trade_group->DisplayTradeGroup();
	
	$p->DisplayPage($list);

?>
