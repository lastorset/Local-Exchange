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
$p->page_title = _("Exchanges Pending");

$cUser->MustBeLoggedOn();
$member = $cUser;
$member_logged_in = $member->member_id;

$pending = new cTradesPending($member_logged_in);

$list = "<em>"._("NOTE that only transactions currently pending approval from one member or the other are displayed here.")." "._("To view your complete Exchange History please click")." <a href=trade_history.php?mode=self>"._("here")."</a>.</em><p><A HREF=trades_pending.php><FONT SIZE=2>"._("Summary")."</FONT></A> | <A HREF=trades_pending.php?action=incoming><FONT SIZE=2>"._("Payments to Confirm")." (".$pending->numToConfirm.")</FONT></A>";


if (MEMBERS_CAN_INVOICE==true) // No point displaying invoice stats if invoicing has been disabled
	$list .= " | <A HREF=trades_pending.php?action=outgoing><FONT SIZE=2>"._("Invoices to Pay")." (".$pending->numToPay.")</FONT></A>";

$list .= " | <A HREF=trades_pending.php?action=payments_sent><FONT SIZE=2>"._("Sent Payments")." (".$pending->numToHaveConfirmed.")</FONT></A>";

if (MEMBERS_CAN_INVOICE==true) // ditto
	$list .= "| <A HREF=trades_pending.php?action=invoices_sent><FONT SIZE=2>"._("Sent Invoices")." (".$pending->numToBePayed.")</FONT></A><p>";


function initTradeTable() {
	global $site_settings;
	$output = "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=\"100%\"><TR BGCOLOR=\"#d8dbea\"><TD><FONT SIZE=2><B>"._("Date")."</B></FONT></TD><TD><FONT SIZE=2><B>"._("From")."</B></FONT></TD><TD><FONT SIZE=2><B>"._("To")."</B></FONT></TD><TD ALIGN=RIGHT><FONT SIZE=2><B>". $site_settings->getUnitString() ."&nbsp;</B></FONT></TD><TD><FONT SIZE=2><B>&nbsp;"._("Description")."</B></FONT></TD><td><font size=2><b>"._("Action")."</td></font></td></TR>";
	
	return $output;
}

function closeTradeTable() {
	
	return "</table>";
}

function displayTrade($t,$typ) {
		
		global $cDB;
		
		$fcolor = "#554f4f";
		//$fcolor = '#FFFFFF';
		
		if ($t["status"]=='O') {
		
			$bgcolor = "white";
			
			if ($typ=='P')
				//$actionTxt = '';
				$actionTxt = "<a href=trades_pending.php?action=confirm&tid=".$t["id"].">"._("Accept Payment")."</a> | <a href=trades_pending.php?action=reject&tid=".$t["id"].">"._("Reject")."</a>";
			else if ($typ=='I')
//				$actionTxt = '';
				$actionTxt = "<a href=trades_pending.php?action=confirm&tid=".$t["id"].">"._("Pay Invoice")."</a> | 
					<a href=trades_pending.php?action=reject&tid=".$t["id"].">"._("Reject")."</a>";
			else if ($typ=='TBC') {
				
				if ($t["member_to_decision"]==3) {
					$bgcolor = 'red';
					$actionTxt = "<font size=2 color=\"".$fcolor."\">".$t["member_id_to"]." "._("has rejected this transaction").". <br>
					 <a href=trades_pending.php?action=resend&tid=".$t["id"].">[ "._("Resend Payment")." ]</a> | 
					<a href=trades_pending.php?action=accept_rejection&tid=".$t["id"].">[ "._("Remove this Notice")." ]</a></font>";
				}
				else
					$actionTxt = "<font size=2 color=\"".$fcolor."\">"._("Awaiting Confirmation by")." ".$t["member_id_to"]."...</font>";
			}
			else if ($typ=='TBP') {
				
				if ($t["member_to_decision"]==3) {
					$bgcolor = 'red';
					$actionTxt = "<font size=2 color=\"".$fcolor."\">".$t["member_id_to"]." "._("has rejected this transaction").". <br>
					<a href=trades_pending.php?action=resend&tid=".$t["id"].">[ "._("Resend Invoice")." ]</a> | 
					<a href=trades_pending.php?action=accept_rejection&tid=".$t["id"].">[ "._("Remove this Notice")." ]</a></font>";
				}
				else
					$actionTxt = "<font size=2 color=\"".$fcolor."\">"._("Awaiting Payment from")." ".$t["member_id_to"]."...</font>";
			}
		}
		else {
			$bgcolor = "green";
			$fcolor = "#ffffff";
			
			if ($typ=='P')
				$actionTxt = "<font size=2 color=\"".$fcolor."\">"._("Payment Accepted")."!</font>";
			else if ($typ=='I')
				$actionTxt = "<font size=2 color=\"".$fcolor."\">"._("Invoice Paid")."!</font>";
			else if ($typ=='TBC')
				$actionTxt = "<font size=2 color=\"".$fcolor."\">".$t["member_id_to"]." "._("has confirmed")."!</font>";
			else if ($typ=='TBP')
				$actionTxt = "<font size=2 color=\"".$fcolor."\">".$t["member_id_to"]." "._("has paid this invoice")."!</font>";
			
			$actionTxt .= " <font color=\"".$fcolor."\">--</font> <a href=trades_pending.php?action=remove&tid=".$t["id"]."><font size=2 color=\"".$fcolor."\">[ "._("Remove this Notice")." ]</font></a>";
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
	global $member_logged_in;
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
	
				$trade = new cTrade($member_to, $charity_to, htmlspecialchars($theAmount), htmlspecialchars(12), htmlspecialchars(_("Donation from").$member_to->member_id.""), 'T');
		
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
				
				$list .= "<em>"._("This trade does not exist or you do not have permission to act on it.")."</em>";
				
				break;
			}
			
			// Check this is not a 'still Open' trade
			if ($row["status"]!='O') {
				
				$list .= "<em>"._("Sorry, only Open trades can be rejected or resent.")."</em>";
				break;
			}
			
			if ($row["member_to_decision"]!=3) {
				
				$list .= "<em>"._("This member hasn't rejected this transaction")."!</em>";
				break;
			}
			
			$q = "UPDATE trades_pending set member_to_decision=1 where id=".$cDB->EscTxt($row["id"])."";
			
			if ($cDB->Query($q))
				$list .= _("Transaction re-submitted successfully.");
			else
				$list .= "<em>"._("Error updating the database").".</em>";
		}
		else
			$list .= "<em>"._("This trade does not exist or you do not have permission to act on it.")."</em>";
		
	break;
		
	case("accept_rejection"): 
	
		$q = "SELECT * FROM trades_pending where id=".$cDB->EscTxt($_GET["tid"])." limit 0,1";
	
		$result = $cDB->Query($q);
		
		if ($result && mysql_num_rows($result)>0) { // Trade Exists
			
			$row = mysql_fetch_array($result);
			
			// Do we have permission to act on this trade?
			if ($row["member_id_from"]!=$member_logged_in) {
				
				$list .= "<em>"._("This trade does not exist or you do not have permission to act on it.")."</em>";
				
				break;
			}
			
			// Check this is not a 'still Open' trade
			if ($row["status"]!='O') {
				
				$list .= "<em>"._("Sorry, only Open trades can be rejected.")."</em>";
				break;
			}
			
			if ($row["member_to_decision"]!=3) {
				
				$list .= "<em>"._("This member hasn't rejected this transaction")>"!</em>";
				break;
			}
			
			$q = "UPDATE trades_pending set member_from_decision=4 where id=".$cDB->EscTxt($row["id"])."";
			
			if ($cDB->Query($q))
				$list .= _("Transaction removed successfully.");
			else
				$list .= "<em>"._("Error updating the database").".</em>";
		}
		else
			$list .= "<em>"._("This trade does not exist or you do not have permission to act on it.")."</em>";
		
	break;
	
	case("reject"): 
	
		$q = "SELECT * FROM trades_pending where id=".$cDB->EscTxt($_GET["tid"])." limit 0,1";
	
		$result = $cDB->Query($q);
		
		if ($result && mysql_num_rows($result)>0) { // Trade Exists
			
			$row = mysql_fetch_array($result);
			
			// Do we have permission to act on this trade?
			if ($row["member_id_to"]!=$member_logged_in) {
				
				$list .= "<em>"._("This trade does not exist or you do not have permission to act on it.")."</em>";
				
				break;
			}
			
			// Check this is not a 'still Open' trade
			if ($row["status"]!='O') {
				
				$list .= "<em>"._("This trade is no longer Open and therefore cannot be rejected.")."</em>";
				break;
			}
			
			if ($row["typ"]=='T' && $row["member_id_to"]==$member_logged_in) { // We want to reject the payment!
				
				$q = "UPDATE trades_pending set member_to_decision=3 where id=".$cDB->EscTxt($row["id"])."";
			}
	
			else if ($row["typ"]=='I' && $row["member_id_to"]==$member_logged_in) { // We don't want to pay this invoice!
				$q = "UPDATE trades_pending set member_to_decision=3 where id=".$cDB->EscTxt($row["id"])."";
			}
			
			if ($cDB->Query($q))
				$list .= "Member ".$row["member_id_from"]." "._("has been informed that you have rejected this transaction").".";
			else
				$list .= "<em>"._("Error updating the database").".</em>";
		}
		else
			$list .= "<em>"._("This trade does not exist or you do not have permission to act on it.")."</em>";
		
	break;
	
	case("remove"): 
	
		$q = "SELECT * FROM trades_pending where id=".$cDB->EscTxt($_GET["tid"])." limit 0,1";
	
		$result = $cDB->Query($q);
		
		if ($result && mysql_num_rows($result)>0) { // Trade Exists
			
			$row = mysql_fetch_array($result);
			
			// Do we have permission to act on this trade?
			if ($row["member_id_from"]!=$member_logged_in && $row["member_id_to"]!=$member_logged_in) {
				
				$list .= "<em>"._("This trade does not exist or you do not have permission to act on it.")."</em>";
				
				break;
			}
			
			// Check this is not a 'still Open' trade
			if ($row["status"]=='O') {
				
				$list .= "<em>"._("This trade is currently marked as Open and thus cannot be removed until the required action has been taken.")."</em>";
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
				$list .= _("Transaction removed successfully.");
			else
				$list .= "<em>"._("Error updating the database").".</em>";
		}
		else
			$list .= "<em>"._("This trade does not exist or you do not have permission to act on it.")."</em>";
		
	break;
	
	case("confirm"):
	
		$q = "SELECT * FROM trades_pending where id=".$cDB->EscTxt($_GET["tid"])." limit 0,1";
	
		$result = $cDB->Query($q);
		
		if ($result && mysql_num_rows($result)>0) { // Trade Exists
			
			$row = mysql_fetch_array($result);
			
			if ($row["status"]!='O') {
				
				$list .= "<em>"._("This trade has already been confirmed and is now closed.")."</em>";
				break;
			}
			
			/* What is the nature of the trade - Payment or Invoice? */
		
				if ($row["typ"]=='T') { // Payment - we are confirming receipt of incoming
					
					// Check we are the intended recipient
					if ($row["member_id_to"]!=$member_logged_in)
						
						$list .= "<em>"._("You do not have permission to confirm this trade.")."</em>";
					else { // Action the trade
						
							if (!doTrade($row))
								$list .= "<font color=red>Error confirming payment. Please contact your administrator.</font>";
							else {
								
								$cDB->Query("UPDATE trades_pending set status=".$cDB->EscTxt('F')." where id=".$cDB->EscTxt($_GET["tid"])."");
								$list .= "<em>"._("You have accepted a payment of")." ".$row["amount"]." ".strtolower($site_settings->getUnitString())." from ".$row["member_id_from"]."</em>";
						}
					}
				}
				
				else if ($row["typ"]=='I') { // Invoice - we are sending a payment
				
						// Check we are the intended recipient of the invoice
					if ($row["member_id_to"]!=$member_logged_in)
						
						$list .= "<em>"._("You do not have permission to confirm this trade.")."</em>";
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
									$list .= "<font color=red>"._("Error sending payment. Please contact your administrator.").".</font>";
							}
							else {
								
								$cDB->Query("UPDATE trades_pending set status=".$cDB->EscTxt('F')." where id=".$cDB->EscTxt($_GET["tid"])."");
								$list .= "<em>"._("You have sent a payment of")." ".$row["amount"]." ".strtolower($site_settings->getUnitString())." to ".$row["member_id_from"]."</em>";
						}
					}
				}
			}
			
		
			else // This trade doesn't exist in the database!
				$list .= "<em>"._("You have elected to confirm a non-existent trade")."!</em>";
	
	
	break;
	
	case("incoming"):
	
		$list .= "<b>"._("The following Incoming Payments require your confirmation")."...</b><p>";
		
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
			$list .= "<em>"._("None found")."!</em>";
	
	break;
	
	case("outgoing"):
	
		$list .= "<b>"._("The following Invoices need paying")."..</b><p>";
		
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
			$list .= "<em>"._("None found")."!</em>";
	
	
	break;
	
	
	case("payments_sent"):
	
		$list .= "<b>"._("You are awaiting confirmation of the following Payments")."...</b><p>";
		
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
			$list .= "<em>"._("None found")."!</em>";
	
	break;
	
	
	case("invoices_sent"):
	
		$list .= "<b>"._("You are awaiting payment for the following Invoices")."...</b><p>";
		
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
			$list .= "<em>"._("None found")."!</em>";
	
	break;
	
	default:
		
		if (MEMBERS_CAN_INVOICE==true)
			$list .= _("I need to pay")." ".$pending->numToPay." "._("Invoices")."<br>";
		
		$list .= _("I need to confirm")." ".$pending->numToConfirm." "._("Incoming Payments")."<p>";
		
		if (MEMBERS_CAN_INVOICE==true)
			$list .= _("I am awaiting payment for")." ".$pending->numToBePayed." "._("Invoices")."<br>";
	
		$list .= _("I am awaiting confirmation of")." ".$pending->numToHaveConfirmed." "._("Outgoing Payments")."<p>";
	
break;
}

$p->DisplayPage($list);
?>
