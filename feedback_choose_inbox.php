<?php

include_once("includes/inc.global.php");

$p->site_section = FEEDBACK;
$p->page_title = _("Leave Feedback");

include_once("classes/class.feedback.php");

$member = new cMember;

if($_REQUEST["mode"] == "admin") {
	$cUser->MustBeLevel(2);
	$member->LoadMember($_REQUEST["member_id"]);
} else {
	$cUser->MustBeLoggedOn();
	$member = $cUser;
}

$since_date = new cDateTime("-". DAYS_REQUEST_FEEDBACK ." days"); // $since_date = new cDateTime("-". DAYS_REQUEST_FEEDBACK ." "._("days"));
$tradegrp = new cTradeGroup($member->member_id, $since_date->MySQLDate()); 
$tradegrp->LoadTradeGroup();

$output = "<B>"._("For which Exchange?")."</B><BR>";
$output .= "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=\"100%\">";

$i=0;

foreach($tradegrp->trade as $trade) {	
	if($trade->type == TRADE_REVERSAL or $trade->status == TRADE_REVERSAL)
		continue;	// No reason to list reversed trades, so let's skip 'em

	if($i % 2)
		$bgcolor = "#EEEEEE";
	else
		$bgcolor = "#FFFFFF";
	
	// Ok this page is ugly, my sincere apologies...
	//
	// What we want here is to provide a sort of inbox for feedback.
	//
	// If the person hasn't left feedback, we want to give them the opportunity.
	// If they have, then we would still want to provide a link under certain circumstances:
	//	1) The other member left negative or nuetral feedback.
	// 2) They left negative/neutral feeback and the other member has responded.
	//
	// In both cases, the most recent posting should be shown.
			
			
	// Was this member the buyer or seller?
	if ($trade->member_from->member_id == $member->member_id) {
		$member_id_other = $trade->member_to->member_id;
		$feedback_member =& $trade->feedback_buyer;
		$feedback_other =& $trade->feedback_seller;
	} else {
		$member_id_other = $trade->member_from->member_id;
		$feedback_other =& $trade->feedback_buyer;
		$feedback_member =& $trade->feedback_seller;
	}
		
	$context = $feedback_member->context;
	
	// Let's set some defaults for the logic to come...
	$show = false;
	$description = $cDB->UnEscTxt($trade->description);
	$link = "feedback.php";
	
	if(!$feedback_member) {	// Member hasn't left feedback yet, lets show a simple link
		$show = true;
	} elseif (isset($feedback_member->rebuttal) or isset($feedback_other->rebuttal)) {
		if(isset($feedback_member->rebuttal) and isset($feedback_other->rebuttal)) {
		
		} elseif (isset($feedback_member->rebuttal)) {
				
		} else {	// Member left negative/neutral feedback earlier, give a chance to follow up
			$show = true;
			if(
			$description .= "<BR><B>". $text .": </B>". $comment;
			$link = "feedback_reply.php";
		}
		
	} elseif ($feedback_other->rating == NEGATIVE or $feedback_other->rating == NEUTRAL) {
		$show = true;
		$description .= "<BR><B>". $feedback_other->RatingText() ." Feedback: </B>". $feedback_other->comment;
		$link = "feedback_reply.php";
	}
	
	if($show) {
		$date = new cDateTime($trade->trade_date);	
		$trade_date = $date->ShortDate();
				
		$output .= "<TR VALIGN=TOP BGCOLOR=". $bgcolor ."><TD><FONT SIZE=2><A HREF=". $link ."?author=". $member->member_id ."&about=". $member_id_other ."&trade_id=". $trade->trade_id ."&mode=".$_REQUEST["mode"] .">". $trade_date ."</A></FONT></TD><TD><FONT SIZE=2>". $member_id_other ."</FONT></TD><TD><FONT SIZE=2>". $trade->category->description ."</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=2>". $trade->amount ."&nbsp;</FONT></TD><TD><FONT SIZE=2>". $description ."</FONT></TD></TR>";	
	} 	
	
	$i+=1;
}

$output .= "</TABLE>";

if($i == 0)
	$output .= _("There are no exchanges to leave feedback for.  You have already left feedback for all your recent exchanges.");

$p->DisplayPage($output);

?>
