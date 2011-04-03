<?php

/**
 * Executes a two step process of confirmation and transfer of monthly fees from
 * each member account to the system account.
 *
 * GET arguments:
 *   TID = transfer id.
 *   CID = confirmation id.
 *
 */

include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;
$p->page_title = $lng_take_monthly_fee;


// *** Starts main() ***

$cUser->MustBeLevel(2);

if (isset($_GET["TID"]) && is_numeric($_GET["TID"]))
{
    $page = transfer_fee($_GET["TID"]);
}
else if (isset($_GET["CID"]) && is_numeric($_GET["CID"]))
{
    $page = confirmation($_GET["CID"]);
}
else
{
    $page = "Bad request.";
}

$p->DisplayPage($page);

// *** Ends main() ***



/*
 * Displays a confirmation dialogue.  Also shows a warning msg if monthly
 * fee has been already taken this month.
 */
function confirmation($cid)
{   global $lng_not_setup_charge_monthly_fees, $lng_already_confirmed, $lng_sure_transfer_fee_for_month_of, $lng_monthly_fee_for, $lng_monthly_fee, $lng_has_already_been_taken, $lng_transfer_now, $lng_cancel, $lng_or; // added $lng_monthly_fee by ejkv
    if( !defined("TAKE_MONTHLY_FEE"))
    {
        return $lng_not_setup_charge_monthly_fees;
    }

    if(isset($_SESSION["LAST_CID"]) && $cid <= $_SESSION["LAST_CID"])
    {
        return $lng_already_confirmed;
    }
    else
    {
        $_SESSION["LAST_CID"] = $cid;
    }

    // If fee has been already taken for this month, then setup the $warning
    // variable.
    if(already_fee_takenp())
    {
        $month = strftime("%B", time());
        $warning = "<p>".$lng_sure_transfer_fee_for_month_of." $month?  ".$lng_monthly_fee_for." $month ".$lng_has_already_been_taken.".</p>";

    }
    else
    {
        $warning = "";
    }

    $ts = time();
    $html = <<<ENDHTML
        $warning        

        <form method="GET" action="">
          <input type="hidden" name="TID" value="$ts" />
          <input type="submit" value=$lng_transfer_now />
        </form>

        <p><strong>$lng_or</strong></p>

        <form method="GET" action="admin_menu.php">
          <input type="submit" value=$lng_cancel />
        </form>
ENDHTML;

    return $html;
}


/**
 * @return - bool - true if monthly fee has been already taken this month.
 */
function already_fee_takenp()
{
    global $cDB;
    $system_account_id = SYSTEM_ACCOUNT_ID;

    $first_day_of_month = strftime("%Y-%m-01", time());
    $result = $cDB->Query("select count(trade_id) from " . DATABASE_TRADES .
                            " where trade_date > '$first_day_of_month' and
                                member_id_to = '$system_account_id'");

    // How many times a monthly fee has been taken this month?
    if ($result)
	    $trade_count = mysql_result($result, 0, 0);

    if($trade_count)
    {
        return true;
    }
    else
    {
        return false;
    }
}


/*
 * Does the actual fee transfer from member accounts to the system account.
 */
function transfer_fee($tid)
{   global $cDB, $monthly_fee_exempt_list, $lng_already_transfered, $lng_error_during_transfer, $lng_done;
    // Make sure this transaction has not been done before.
    if(isset($_SESSION["LAST_TID"]) && $tid <= $_SESSION["LAST_TID"])
    {
        return $lng_already_transfered;

    }
    else
    {
        // Store the current transaction id for later checks.
        $_SESSION["LAST_TID"] = $tid;
    }

    global $cDB, $monthly_fee_exempt_list, $lng_monthly_fee; // , $lng_monthly_fee added by ejkv
    $monthly_fee = MONTHLY_FEE;
    $system_account_id = SYSTEM_ACCOUNT_ID;
    $member_table = DATABASE_MEMBERS;
    $trade_table = DATABASE_TRADES;
    $trade_type = TRADE_MONTHLY_FEE;
    
    // Transaction starts.
    $cDB->Query("BEGIN");

    // We don't want to charge inactive accounts.
    $query0 = "select member_id from $member_table where status='A'";
    $result0 = $cDB->Query($query0);

    // This single timestamp will be applied to every transfer done in
    // this transaction.  This is for the ease of identification of this
    // batch of transfer later.
    
    // This causes probs, results in trade_date = 0000000; because trade_date is set to auto in SQL?
    $ts = time();
		//$ts = 'trade_date'; // Auto - probably gonna foul up the Refund Monthly Fee bit
		
    while ($row = mysql_fetch_object($result0))
    {
        if ( !in_array($row->member_id, $monthly_fee_exempt_list))
        {
            // Category 12 is "Miscellaneous".
            // Logs Trade
            $query1 = "insert into $trade_table set trade_date=from_unixtime(".$ts."),   
            	 status='V', member_id_from='".$row->member_id."',
                              member_id_to='$system_account_id', amount=$monthly_fee, category=12,
                                  description='".$lng_monthly_fee."', type='$trade_type'"; // changed 'Monthly fee' into $lng_monthly_fee by ejkv
   
            $result1 = $cDB->Query($query1);
	
						// Take fee from member
            $query2 = "update $member_table set balance = balance - $monthly_fee
                             where member_id = '".$row->member_id."'";
         //  echo $query2."<p>";
            $result2 = $cDB->Query($query2);
 						
 						// Deposit fee in system account
            $query3 = "update $member_table set balance = balance + $monthly_fee
                             where member_id = '$system_account_id'";
           
            $result3 = $cDB->Query($query3);

            if ( !$result2 || !$result3 )
            {
                $cDB->Query("rollback");

                return $lng_error_during_transfer;
            }
            
        }
    }

    $cDB->Query("COMMIT");

    return $lng_done;
}



