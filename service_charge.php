<?php

include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;
$p->page_title = $lng_service_charge;


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
{    global $lng_not_setup_for_charge_service_fees, $lng_already_confirmed, $lng_transfer_now, $lng_cancel, $lng_amount, $lng_fee_description, $lng_or, $lng_service_fee;
    if( !defined("TAKE_SERVICE_FEE"))
    {
        return $lng_not_setup_for_charge_service_fees;
    }

    if(isset($_SESSION["LAST_CID"]) && $cid <= $_SESSION["LAST_CID"])
    {
        return $lng_already_confirmed;
    }
    else
    {
        $_SESSION["LAST_CID"] = $cid;
    }

    $warning = "";

    $ts = time();
 
    $html = <<<ENDHTML
        $warning        

        <form method="GET" action="">
          <input type="hidden" name="TID" value="$ts" />
          <table><tr><td>
          <b>$lng_amount</b></td><td> <input type="text" name="amount" size=4 maxlength=5></tr>
          <tr><td> 
          <b>$lng_fee_description</b></td><td> <textarea name="desc" rows=2 cols=40 maxlength=255>$lng_service_fee</textarea></tr></table>
        
<p><input type="submit" value=$lng_transfer_now />
        </form><p><strong>$lng_or</strong></p>

        <form method="GET" action="admin_menu.php">
          <input type="submit" value=$lng_cancel />
        </form>
ENDHTML;

    return $html;
}

/*
 * Does the actual fee transfer from member accounts to the system account.
 */
function transfer_fee($tid)
{
    global $cDB, $monthly_fee_exempt_list, $lng_already_transfered, $lng_fee_must_be_numeric, $lng_service_charge, $lng_error_during_transfer, $lng_done; // The monthly fee exempt list will be applied to service charges also
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

    
    $fee = trim($_REQUEST["amount"]);
    
    if (!is_numeric($fee))
    	return $lng_fee_must_be_numeric;
    
    $description = trim($_REQUEST["desc"]);
    
    if (!$description)
    	$description = $lng_service_charge; // A sensible default if admin hasn't bothered specifying a description
    
    $system_account_id = SYSTEM_ACCOUNT_ID;
    $member_table = DATABASE_MEMBERS;
    $trade_table = DATABASE_TRADES;
    $trade_type = 'S';
    
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
	
    while ($row = mysql_fetch_object($result0))
    {
        if ( !in_array($row->member_id, $monthly_fee_exempt_list))
        {
            // Category 12 is "Miscellaneous".
            // Logs Trade
            $query1 = "insert into $trade_table set trade_date=from_unixtime(".$ts."),   
            	 status='V', member_id_from='".$row->member_id."',
                              member_id_to='$system_account_id', amount=$fee, category=12,
                                  description='". $description ."', type='$trade_type'";
   
            $result1 = $cDB->Query($query1);
	
						// Take fee from member
            $query2 = "update $member_table set balance = balance - $fee
                             where member_id = '".$row->member_id."'";
         //  echo $query2."<p>";
            $result2 = $cDB->Query($query2);
 						
 						// Deposit fee in system account
            $query3 = "update $member_table set balance = balance + $fee
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



