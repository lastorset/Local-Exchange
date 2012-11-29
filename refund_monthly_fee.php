<?php

/**
 * Returns monthly fees for a certain batch.  First it asks to select which
 * batch of fee is to be returned.  Then it asks for a confirmation.  And
 * lastly, it does the transfer and provides a feedback.
 *
 * GET arguments:
 *     TID = transfer id.
 *     CID = confirmation id.
 *     - these are mainly used for preventing double refunds in case of page
 *       refresh and backbutton presses.
 */

include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;
$p->page_title = _("Refund monthly fee");


// *** Starts main() ***

$cUser->MustBeLevel(2);

if (isset($_GET["TID"]) && is_numeric($_GET["TID"]))
{
    $page = transfer_fee($_GET["TID"], $_GET["trade_time"]);
}
else if (isset($_GET["CID"]) && is_numeric($_GET["CID"]))
{
    $page = confirmation($_GET["CID"], $_GET["selected_time"]);
}
else
{
    $page = select_time();
}

$p->DisplayPage($page);

// *** Ends main() ***



function select_time()
{
    global $cDB;
    $system_account_id = SYSTEM_ACCOUNT_ID;
    $ts = time();
    $year = strftime("%Y", $ts);
    $month = strftime("%m", $ts);
    $trade_type = TRADE_MONTHLY_FEE;
    $refunded = TRADE_MONTHLY_FEE_REVERSAL;

    // We'll show only the monthly fees taken during the last 12 months.
    $date_year_ago = ($year - 1) . "-$month-01";
    $sql = "select distinct(trade_date) from trades where
                 trade_date > '$date_year_ago' and type='$trade_type' and
                 member_id_to = '$system_account_id' and status != '$refunded'";
   //echo $sql;
    $result = $cDB->Query($sql);

    $selection_list = "";
    while ($row = mysql_fetch_object($result))
    {
        $selection_list .=
            "<option value=\"$row->trade_date\">$row->trade_date</option>";
    }

    if (empty($selection_list))
    {
        return _("No monthly transfers found.");
    }

	global $_;
    $html = <<<ENDHTML
        <form method="GET" action="">
          <input type="hidden" name="CID" value="$ts" />

          <select name="selected_time">
            $selection_list
          </select>

          <input type="submit" value="{$_("Refund")}" />
        </form>
ENDHTML;

    return $html;
}


/*
 * Displays a confirmation dialogue.  Also shows a warning msg if monthly
 * fee has been already taken this month.
 */
function confirmation($cid, $selected_time)
{
    if( !defined("TAKE_MONTHLY_FEE"))
    {
        return _("This system isn't setup to process monthly fees.");
    }

    if(isset($_SESSION["LAST_CID"]) && $cid <= $_SESSION["LAST_CID"])
    {
        return _("Action already executed.  Start from the administration page for a new refund.");
    }
    else
    {
        $_SESSION["LAST_CID"] = $cid;
    }

    $ts = time();
	global $_;
    $html = <<<ENDHTML
		{$_("You are about to refund the monthly fee taken on")}
        <em>$selected_time</em>.

        <form method="GET" action="">
          <input type="hidden" name="TID" value="$ts" />
          <input type="hidden" name="trade_time" value="$selected_time">
          <input type="submit" value={$_("Refund now")} />
        </form>

        <p><strong>{$_("Or")}</strong></p>

        <form method="GET" action="admin_menu.php">
          <input type="submit" value={$_("Cancel")} />
        </form>
ENDHTML;

    return $html;
}



/*
 * Does the actual fee transfer from member accounts to the system account.
 */
function transfer_fee($tid, $trade_time)
{
    // Make sure this transaction has not been done before.
    if(isset($_SESSION["LAST_TID"]) && $tid <= $_SESSION["LAST_TID"])
    {
        return _("Already transfered.  Start from the administration page for a new transfer.");
    }
    else
    {
        // Store the current transaction id for later checks.
        $_SESSION["LAST_TID"] = $tid;
    }

    global $cDB, $monthly_fee_exempt_list;
    $monthly_fee = MONTHLY_FEE;
    $system_account_id = SYSTEM_ACCOUNT_ID;
    $member_table = DATABASE_MEMBERS;
    $trade_table = DATABASE_TRADES;
    $trade_type_monthly = TRADE_MONTHLY_FEE;
    $trade_type = TRADE_MONTHLY_FEE_REVERSAL;
    $desc = _("Refund for monthly fee taken on")." $trade_time";
    
    // Transaction starts.
    $cDB->Query("BEGIN");

    $trade_time = $cDB->EscTxt($trade_time);

    // We don't want to charge inactive accounts.
    $query0 = "select trade_id, member_id_from from " . DATABASE_TRADES .
                  " where trade_date = $trade_time 
                      and type='$trade_type_monthly'
                      and member_id_to = '$system_account_id'";
    $result0 = $cDB->Query($query0);

    // This single timestamp will be applied to every transfer done in
    // this transaction.  This is for the ease of identification of this
    // batch of transfer later.
    $ts = time();

    while ($row = mysql_fetch_object($result0))
    {
    		
    		if ( !in_array($row->member_id, $monthly_fee_exempt_list)) {
    	
	        // Category 12 is "Miscellaneous".
	        $query1 = "insert into $trade_table set member_id_to='".$row->member_id_from."',
                              member_id_from='$system_account_id', amount=$monthly_fee, category=12,
                                  description='$desc', type='$trade_type', trade_date=$trade_time";
                                  
	        $result1 = $cDB->Query($query1);
	
	        $query2 = "update $member_table set balance = balance + $monthly_fee
	                             where member_id = '".$row->member_id_from."'";
	        $result2 = $cDB->Query($query2);
	// echo $query2."<br>";
	        $query3 = "update $member_table set balance = balance - $monthly_fee
	                             where member_id = '$system_account_id'";
	        $result3 = $cDB->Query($query3);
	
	        $query4 = "update $trade_table set status = '$trade_type',
	                       trade_date = $trade_time where
	                           trade_id = ".$row->trade_id."";
	        $result4 = $cDB->Query($query4);
	
	        if ( !( $result2 && $result3 && $result4))
	        {
	            $cDB->Query("rollback");
	
	            return _("Couldn't transfer.");
	        }
      }
    }

    $cDB->Query("COMMIT");

    return _("Done");
}



