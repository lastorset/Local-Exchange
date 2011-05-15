<?
include_once("includes/inc.global.php");
include_once("classes/class.datetime.php");
include("classes/class.trade.php");

/*
 An explanation of different member_decisions statuses in the trades_pending database...
 
 1 = Member hasn't made a decision regarding this trade - either it is Open or it has been Fulfilled (see 'status' column)
 2 = Member has removed this trade from his own records
 3 = Member has rejected this trade
 4 = Member has accepted that this trade has been rejected
 
*/

$p->site_section = EXCHANGES;
$p->page_title = $lng_exchanges_pending;

$cUser->MustBeLoggedOn();
$member = $cUser;
$member_logged_in = $member->member_id;

$pending = new cTradesPending($member_logged_in);

$list = "<em>".$lng_only_transactions_pending_from_one_member." ".$lng_to_view_complete_history_click." <a href=trade_history.php?mode=self>".$lng_here."</a>.</em><p><A HREF=trades_pending.php><FONT SIZE=2>".$lng_summary."</FONT></A> | <A HREF=trades_pending.php?action=incoming><FONT SIZE=2>".$lng_payments_to_confirm." (".$pending->numToConfirm.")</FONT></A>";


if (MEMBERS_CAN_INVOICE==true) // No point displaying invoice stats if invoicing has been disabled
	$list .= " | <A HREF=trades_pending.php?action=outgoing><FONT SIZE=2>".$lng_invoices_to_pay." (".$pending->numToPay.")</FONT></A>";

$list .= " | <A HREF=trades_pending.php?action=payments_sent><FONT SIZE=2>".$lng_sent_payments." (".$pending->numToHaveConfirmed.")</FONT></A>";

if (MEMBERS_CAN_INVOICE==true) // ditto
	$list .= "| <A HREF=trades_pending.php?action=invoices_sent><FONT SIZE=2>".$lng_sent_invoices." (".$pending->numToBePayed.")</FONT></A><p>";


function initTradeTable() {
	global $lng_date, $lng_from, $lng_to, $lng_description, $lng_action;
	$output = "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=\"100%\"><TR BGCOLOR=\"#d8dbea\"><TD><FONT SIZE=2><B>".$lng_date."</B></FONT></TD><TD><FONT SIZE=2><B>".$lng_from."</B></FONT></TD><TD><FONT SIZE=2><B>".$lng_to."</B></FONT></TD><TD ALIGN=RIGHT><FONT SIZE=2><B>". UNITS ."&nbsp;</B></FONT></TD><TD><FONT SIZE=2><B>&nbsp;".$lng_description."</B></FONT></TD><td><font size=2><b>".$lng_action."</td></font></td></TR>";
	
	return $output;
}

function closeTradeTable() {
	
	return "</table>";
}

function displayTrade($t,$typ) {
		
		global $cDB, $lng_accept_payment, $lng_reject, $lng_pay_invoice, $lng_has_rejected_transaction, $lng_resend_payment, $lng_remove_notice, $lng_awaiting_confirmation, $lng_resend_invoice, $lng_awaiting_payment_from, $lng_payment_accepted, $lng_invoice_paid,$lng_has_confirmed, $lng_has_paid_invoice;
		
		$fcolor = "#554f4f";
		//$fcolor = '#FFFFFF';
		
		if ($t["status"]=='O') {
		
			$bgcolor = "white";
			
			if ($typ=='P')
				//$actionTxt = '';
				$actionTxt = "<a href=trades_pending.php?action=confirm&tid=".$t["id"].">".$lng_accept_payment."</a> | <a href=trades_pending.php?action=reject&tid=".$t["id"].">".$lng_reject."</a>";
			else if ($typ=='I')
//				$actionTxt = '';
				$actionTxt = "<a href=trades_pending.php?action=confirm&tid=".$t["id"].">".$lng_pay_invoice."</a> | 
					<a href=trades_pending.php?action=reject&tid=".$t["id"].">".$lng_reject."</a>";
			else if ($typ=='TBC') {
				
				if ($t["member_to_decision"]==3) {
					$bgcolor = 'red';
					$actionTxt = "<font size=2 color=\"".$fcolor."\">".$t["member_id_to"]." ".$lng_has_rejected_transaction.". <br>
					 <a href=trades_pending.php?action=resend&tid=".$t["id"].">[ ".$lng_resend_payment." ]</a> | 
					<a href=trades_pending.php?action=accept_rejection&tid=".$t["id"].">[ ".$lng_remove_notice." ]</a></font>";
				}
				else
					$actionTxt = "<font size=2 color=\"".$fcolor."\">".$lng_awaiting_confirmation." ".$t["member_id_to"]."...</font>";
			}
			else if ($typ=='TBP') {
				
				if ($t["member_to_decision"]==3) {
					$bgcolor = 'red';
					$actionTxt = "<font size=2 color=\"".$fcolor."\">".$t["member_id_to"]." ".$lng_has_rejected_transaction.". <br>
					<a href=trades_pending.php?action=resend&tid=".$t["id"].">[ ".$lng_resend_invoice." ]</a> | 
					<a href=trades_pending.php?action=accept_rejection&tid=".$t["id"].">[ ".$lng_remove_notice." ]</a></font>";
				}
				else
					$actionTxt = "<font size=2 color=\"".$fcolor."\">".$lng_awaiting_payment_from." ".$t["member_id_to"]."...</font>";
			}
		}
		else {
			$bgcolor = "green";
			$fcolor = "#ffffff";
			
			if ($typ=='P')
				$actionTxt = "<font size=2 color=\"".$fcolor."\">".$lng_payment_accepted."!</font>";
			else if ($typ=='I')
				$actionTxt = "<font size=2 color=\"".$fcolor."\">".$lng_invoice_paid."!</font>";
			else if ($typ=='TBC')
				$actionTxt = "<font size=2 color=\"".$fcolor."\">".$t["member_id_to"]." ".$lng_has_confirmed."!</font>";
			else if ($typ=='TBP')
				$actionTxt = "<font size=2 color=\"".$fcolor."\">".$t["member_id_to"]." ".$lng_has_paid_invoice."!</font>";
			
			$actionTxt .= " <font color=\"".$fcolor."\">--</font> <a href=trades_pending.php?action=remove&tid=".$t["id"]."><font size=2 color=\"".$fcolor."\">[ ".$lng_remove_notice." ]</font></a>";
		}
			
		if ($typ=='P')
			$output .= "<TR VALIGN=TOP BGCOLOR=". $bgcolor ."><TD><FONT SIZE=2 COLOR=".$fcolor.">". $t["trade_date"]."</FONT></TD><TD><FONT SIZE=2 		COLOR=".$fcolor.">". $t["member_id_from"] ."</FONT></TD><TD><FONT SIZE=2 COLOR=".$fcolor.">".$t["member_id_to"]."</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=2 COLOR=".$fcolor.">". $t["amount"] ."&nbsp;</FONT></TD><TD><FONT SIZE=2 COLOR=".$fcolor.">". $cDB->UnEscTxt($t["description"]) ."</FONT></TD>
				<td>$actionTxt</td>
				</TR>";
		else
				$output .= "<TR VALIGN=TOP BGCOLOR=". $bgcolor ."><TD><FONT SIZE=2 COLOR=".$fcolor.">". $t["trade_date"]."</FONT></TD><TD><FONT SIZE=2 		COLOR=".$fcolor.">". $t["member_id_from"] ."</FONT></TD><TD><FONT SIZE=2 COLOR=".$fcolor.">".$t["member_id_to"]."</FONT></TD><TD ALIGN=RIGHT><FONT SIZE=2 COLOR=".$fcolor.">". $t["amount"] ."&nbsp;</FONT></TD><TD><FONT SIZE=2 COLOR=".$fcolor.">". $cDB->UnEscTxt($t["description"]) ."</FONT></TD>
				<td>$actionTxt</td>
				</TR>";
				
		return $output;
}

function doTrade($t) {
	global $lng_donation_from, $lng_trade_not_exist_or_no_permission, $lng_only_open_trades_reject_or_resent, $lng_member_not_rejected_transaction, $lng_transaction_resubmitted, $lng_error_updating_database, $lng_only_open_trades_reject, $lng_transaction_removed, $lng_trade_no_longer_open, $lng_has_been_informed_rejected_transaction, $lng_trade_marked_open, $lng_trade_confirmed_and_closed, $lng_no_permission_to_confirm_trade, $lng_accepted_payment_of, $lng_error_sending_payment, $lng_you_sent_payment_of, $lng_elected_non_existant_trade, $lng_payments_require_confirmation, $lng_none_found, $lng_invoices_need_paying, $lng_awaiting_confirmation_payments, $lng_awaiting_payment_invoices, $lng_i_need_to_pay, $lng_invoices, $lng_i_need_to_confirm, $lng_incoming_payments, $lng_awaiting_payment_for, $lng_awaiting_confirmation_of, $lng_outgoing_payments;
	$member_to = new cMember;
	
	if ($t["typ"]=='T')
		$member_to->LoadMember($member_logged_in);
	else
		$member_to->LoadMember($t["member_id_from"]);
		
	$member = new cMember;
	
	if ($t["typ"]=='T')
		$member->LoadMember($t["member_id_from"]);
	else
		$member->LoadMember($member_logged_in);
		
	$trade = new cTrade($member, $member_to, htmlspecialchars($t['amount']), htmlspecialchars($t['category']), 		htmlspecialchars($t['description']), 
		"T");
	
	$status = $trade->MakeTrade();
	
	if(!$status)
		return false;
	else {
		
			// Has the recipient got an income tie set-up? If so, we need to transfer a percentage of this elsewhere...
		
			$recipTie = cIncomeTies::getTie($member_to->member_id);
			
			if ($recipTie) {
				
				$theAmount = round(($t['amount']*$recipTie->percent)/100);
				
				$charity_to = new cMember;
				$charity_to->LoadMember($recipTie->tie_id);
	
				$trade = new cTrade($member_to, $charity_to, htmlspecialchars($theAmount), htmlspecialchars(12), htmlspecialchars($lng_donation_from.$member_to->member_id.""), 'T');
		
				$status = $trade->MakeTrade();
			}
			
		return true;
	}
}

switch($_REQUEST["action"]) {
	
	case("resend"): 
	
		$q = "SELECT * FROM trades_pending where id=".$cDB->EscTxt($_GET["tid"])." limit 0,1";
	
		$result = $cDB->Query($q);
		
		if ($result && mysql_num_rows($result)>0) { // Trade Exists
			
			$row = mysql_fetch_array($result);
			
			// Do we have permission to act on this trade?
			if ($row["member_id_from"]!=$member_logged_in) {
				
				$list .= "<em>".$lng_trade_not_exist_or_no_permission."</em>";
				
				break;
			}
			
			// Check this is not a 'still Open' trade
			if ($row["status"]!='O') {
				
				$list .= "<em>".$lng_only_open_trades_reject_or_resent."</em>";
				break;
			}
			
			if ($row["member_to_decision"]!=3) {
				
				$list .= "<em>".$lng_member_not_rejected_transaction."!</em>";
				break;
			}
			
			$q = "UPDATE trades_pending set member_to_decision=1 where id=".$cDB->EscTxt($row["id"])."";
			
			if ($cDB->Query($q))
				$list .= $lng_transaction_resubmitted;
			else
				$list .= "<em>".$lng_error_updating_database.".</em>";
		}
		else
			$list .= "<em>".$lng_trade_not_exist_or_no_permission."</em>";
		
	break;
		
	case("accept_rejection"): 
	
		$q = "SELECT * FROM trades_pending where id=".$cDB->EscTxt($_GET["tid"])." limit 0,1";
	
		$result = $cDB->Query($q);
		
		if ($result && mysql_num_rows($result)>0) { // Trade Exists
			
			$row = mysql_fetch_array($result);
			
			// Do we have permission to act on this trade?
			if ($row["member_id_from"]!=$member_logged_in) {
				
				$list .= "<em>".$lng_trade_not_exist_or_no_permission."</em>";
				
				break;
			}
			
			// Check this is not a 'still Open' trade
			if ($row["status"]!='O') {
				
				$list .= "<em>".$lng_only_open_trades_reject."</em>";
				break;
			}
			
			if ($row["member_to_decision"]!=3) {
				
				$list .= "<em>".$lng_member_not_rejected_transaction>"!</em>";
				break;
			}
			
			$q = "UPDATE trades_pending set member_from_decision=4 where id=".$cDB->EscTxt($row["id"])."";
			
			if ($cDB->Query($q))
				$list .= $lng_transaction_removed;
			else
				$list .= "<em>".$lng_error_updating_database.".</em>";
		}
		else
			$list .= "<em>".$lng_trade_not_exist_or_no_permission."</em>";
		
	break;
	
	case("reject"): 
	
		$q = "SELECT * FROM trades_pending where id=".$cDB->EscTxt($_GET["tid"])." limit 0,1";
	
		$result = $cDB->Query($q);
		
		if ($result && mysql_num_rows($result)>0) { // Trade Exists
			
			$row = mysql_fetch_array($result);
			
			// Do we have permission to act on this trade?
			if ($row["member_id_to"]!=$member_logged_in) {
				
				$list .= "<em>".$lng_trade_not_exist_or_no_permission."</em>";
				
				break;
			}
			
			// Check this is not a 'still Open' trade
			if ($row["status"]!='O') {
				
				$list .= "<em>".$lng_trade_no_longer_open."</em>";
				break;
			}
			
			if ($row["typ"]=='T' && $row["member_id_to"]==$member_logged_in) { // We want to reject the payment!
				
				$q = "UPDATE trades_pending set member_to_decision=3 where id=".$cDB->EscTxt($row["id"])."";
			}
	
			else if ($row["typ"]=='I' && $row["member_id_to"]==$member_logged_in) { // We don't want to pay this invoice!
				$q = "UPDATE trades_pending set member_to_decision=3 where id=".$cDB->EscTxt($row["id"])."";
			}
			
			if ($cDB->Query($q))
				$list .= "Member ".$row["member_id_from"]." ".$lng_has_been_informed_rejected_transaction.".";
			else
				$list .= "<em>".$lng_error_updating_database.".</em>";
		}
		else
			$list .= "<em>".$lng_trade_not_exist_or_no_permission."</em>";
		
	break;
	
	case("remove"): 
	
		$q = "SELECT * FROM trades_pending where id=".$cDB->EscTxt($_GET["tid"])." limit 0,1";
	
		$result = $cDB->Query($q);
		
		if ($result && mysql_num_rows($result)>0) { // Trade Exists
			
			$row = mysql_fetch_array($result);
			
			// Do we have permission to act on this trade?
			if ($row["member_id_from"]!=$member_logged_in && $row["member_id_to"]!=$member_logged_in) {
				
				$list .= "<em>".$lng_trade_not_exist_or_no_permission."</em>";
				
				break;
			}
			
			// Check this is not a 'still Open' trade
			if ($row["status"]=='O') {
				
				$list .= "<em>".$lng_trade_marked_open."</em>";
				break;
			}
			
			if ($row["typ"]=='T' && $row["member_id_from"]==$member_logged_in) { // Our sent payment has finally been confirmed!
				$q = "UPDATE trades_pending set member_from_decision=2 where id=".$cDB->EscTxt($row["id"])."";
			}
			else if ($row["typ"]=='T' && $row["member_id_to"]==$member_logged_in) { // We have confirmed receipt of a payment!
				$q = "UPDATE trades_pending set member_to_decision=2 where id=".$cDB->EscTxt($row["id"])."";
			}
			else if ($row["typ"]=='I' && $row["member_id_from"]==$member_logged_in) { // Our invoice has finally been paid!
				$q = "UPDATE trades_pending set member_from_decision=2 where id=".$cDB->EscTxt($row["id"])."";
			}		
			else if ($row["typ"]=='I' && $row["member_id_to"]==$member_logged_in) { // We have now paid this invoice!
				$q = "UPDATE trades_pending set member_to_decision=2 where id=".$cDB->EscTxt($row["id"])."";
			}
			
			if ($cDB->Query($q))
				$list .= $lng_transaction_removed;
			else
				$list .= "<em>".$lng_error_updating_database.".</em>";
		}
		else
			$list .= "<em>".$lng_trade_not_exist_or_no_permission."</em>";
		
	break;
	
	case("confirm"):
	
		$q = "SELECT * FROM trades_pending where id=".$cDB->EscTxt($_GET["tid"])." limit 0,1";
	
		$result = $cDB->Query($q);
		
		if ($result && mysql_num_rows($result)>0) { // Trade Exists
			
			$row = mysql_fetch_array($result);
			
			if ($row["status"]!='O') {
				
				$list .= "<em>".$lng_trade_confirmed_and_closed."</em>";
				break;
			}
			
			/* What is the nature of the trade - Payment or Invoice? */
		
				if ($row["typ"]=='T') { // Payment - we are confirming receipt of incoming
					
					// Check we are the intended recipient
					if ($row["member_id_to"]!=$member_logged_in)
						
						$list .= "<em>".$lng_no_permission_to_confirm_trade."</em>";
					else { // Action the trade
						
							if (!doTrade($row))
								$list .= "<font color=red>Error confirming payment.</font>";
							else {
								
								$cDB->Query("UPDATE trades_pending set status=".$cDB->EscTxt('F')." where id=".$cDB->EscTxt($_GET["tid"])."");
								$list .= "<em>".$lng_accepted_payment_of." ".$row["amount"]." ".UNITS." from ".$row["member_id_from"]."</em>";
						}
					}
				}
				
				else if ($row["typ"]=='I') { // Invoice - we are sending a payment
				
						// Check we are the intended recipient of the invoice
					if ($row["member_id_to"]!=$member_logged_in)
						
						$list .= "<em>".$lng_no_permission_to_confirm_trade."</em>";
					else { // Action the trade
							/*
							$goingFrom = $_SESSION["user_login"];
							$goingTo = $row["member_id_from"];
							
							$row["member_id_to"] = $goingTo;
							$row["member_id_from"] = $goingFrom;
							*/
							if (!doTrade($row)) {
								
								$member = new cMember;
								$member->LoadMember($member_logged_in);
								if ($member->restriction==1) {
									$list .= LEECH_NOTICE;
								}
								else
									$list .= "<font color=red>".$lng_error_sending_payment.".</font>";
							}
							else {
								
								$cDB->Query("UPDATE trades_pending set status=".$cDB->EscTxt('F')." where id=".$cDB->EscTxt($_GET["tid"])."");
								$list .= "<em>".$lng_you_sent_payment_of." ".$row["amount"]." ".UNITS." to ".$row["member_id_from"]."</em>";
						}
					}
				}
			}
			
		
			else // This trade doesn't exist in the database!
				$list .= "<em>".$lng_elected_non_existant_trade."!</em>";
	
	
	break;
	
	case("incoming"):
	
		$list .= "<b>".$lng_payments_require_confirmation."...</b><p>";
		
		/*
		$cDB->Query("INSERT INTO trades_pending (trade_date, member_id_from, member_id_to, amount, category, description, typ) VALUES (now(), ". 	$cDB->EscTxt($member->member_id) .", ". $cDB->EscTxt($member_to_id) .", ". $cDB->EscTxt($values["units"]) .", ". $cDB->EscTxt($values["category"]) .", ". 	$cDB->EscTxt($values["description"]) .", \"T\");");
		*/
		
		$q = "SELECT * FROM trades_pending where member_id_to=".$cDB->EscTxt($member_logged_in)." and typ='T' and member_to_decision = 1";
	
		$result = $cDB->Query($q);
	
		if ($result) {
			
			$list .= initTradeTable();
			
			for($i=0;$i<mysql_num_rows($result);$i++) {
				
				$row = mysql_fetch_array($result);
				$list .= displayTrade($row,'P');
			}
			
			$list .= closeTradeTable();
		}
		else
			$list .= "<em>".$lng_none_found."!</em>";
	
	break;
	
	case("outgoing"):
	
		$list .= "<b>".$lng_invoices_need_paying."..</b><p>";
		
		$q = "SELECT * FROM trades_pending where member_id_to=".$cDB->EscTxt($member_logged_in)." and typ='I' and member_to_decision = 1";
	
		$result = $cDB->Query($q);
	
		if ($result) {
			
			$list .= initTradeTable();
			
			for($i=0;$i<mysql_num_rows($result);$i++) {
				
				$row = mysql_fetch_array($result);
				$list .= displayTrade($row,'I');
			}
			
			$list .= closeTradeTable();
		}
		else
			$list .= "<em>".$lng_none_found."!</em>";
	
	
	break;
	
	
	case("payments_sent"):
	
		$list .= "<b>".$lng_awaiting_confirmation_payments."...</b><p>";
		
		/*
		$cDB->Query("INSERT INTO trades_pending (trade_date, member_id_from, member_id_to, amount, category, description, typ) VALUES (now(), ". 	$cDB->EscTxt($member->member_id) .", ". $cDB->EscTxt($member_to_id) .", ". $cDB->EscTxt($values["units"]) .", ". $cDB->EscTxt($values["category"]) .", ". 	$cDB->EscTxt($values["description"]) .", \"T\");");
		*/
		
		$q = "SELECT * FROM trades_pending where member_id_from=".$cDB->EscTxt($member_logged_in)." and typ='T' and member_from_decision = 1";
	
		$result = $cDB->Query($q);
	
		if ($result) {
			
			$list .= initTradeTable();
			
			for($i=0;$i<mysql_num_rows($result);$i++) {
				
				$row = mysql_fetch_array($result);
				
				$list .= displayTrade($row,'TBC');
			}
			
			$list .= closeTradeTable();
		}
		else
			$list .= "<em>".$lng_none_found."!</em>";
	
	break;
	
	
	case("invoices_sent"):
	
		$list .= "<b>".$lng_awaiting_payment_invoices."...</b><p>";
		
		/*
		$cDB->Query("INSERT INTO trades_pending (trade_date, member_id_from, member_id_to, amount, category, description, typ) VALUES (now(), ". 	$cDB->EscTxt($member->member_id) .", ". $cDB->EscTxt($member_to_id) .", ". $cDB->EscTxt($values["units"]) .", ". $cDB->EscTxt($values["category"]) .", ". 	$cDB->EscTxt($values["description"]) .", \"T\");");
		*/
		
		$q = "SELECT * FROM trades_pending where member_id_from=".$cDB->EscTxt($member_logged_in)." and typ='I' and member_from_decision = 1";
	
		$result = $cDB->Query($q);
	
		if ($result) {
			
			$list .= initTradeTable();
			
			for($i=0;$i<mysql_num_rows($result);$i++) {
				
				$row = mysql_fetch_array($result);
				
				$list .= displayTrade($row,'TBP');
			}
			
			$list .= closeTradeTable();
		}
		else
			$list .= "<em>".$lng_none_found."!</em>";
	
	break;
	
	default:
		
		if (MEMBERS_CAN_INVOICE==true)
			$list .= $lng_i_need_to_pay." ".$pending->numToPay." ".$lng_invoices."<br>";
		
		$list .= $lng_i_need_to_confirm." ".$pending->numToConfirm." ".$lng_incoming_payments."<p>";
		
		if (MEMBERS_CAN_INVOICE==true)
			$list .= $lng_awaiting_payment_for." ".$pending->numToBePayed." ".$lng_invoices."<br>";
	
		$list .= $lng_awaiting_confirmation_of." ".$pending->numToHaveConfirmed." ".$lng_outgoing_payments."<p>";
	
break;
}

$p->DisplayPage($list);
?>
