<?
require_once("includes/inc.global.php");
require_once("includes/inc.forms.php");

$p->site_section = EVENTS;
$cUser->MustBeLoggedOn();

if (ALLOW_INCOME_SHARES!=true) // Provision for allowing income ties has been turned off, return to homepage
	header("location:http://".HTTP_BASE."/index.php");
	
$ties = new cIncomeTies;

$p->page_title = $lng_income_sharing;

$output = $lng_income_sharing_message01." (".UNITS.") ".$lng_income_sharing_message02." ".UNITS." ".$lng_income_sharing_message03."<p>";

if ($_REQUEST["process"]==1) {
	
	$amount = trim($_REQUEST["amount"]);
	$tie_id = trim(htmlspecialchars($_REQUEST["member_to"]));
	$tie_id = substr($tie_id,0, strpos($tie_id,"?")); // TODO:
	
	if ($tie_id==$cUser->member_id) {
		
		$output = $lng_income_sharing_message04." :)";
		$p->DisplayPage($output);
	
		exit;
	}
	if (!$amount || !$tie_id || !is_numeric($amount) || $amount>99) {
		
		if (!$amount || !$tie_id)
			$output = $lng_not_enough_data;
		else if (!is_numeric($amount))
			$output = $lng_percentage_must_be_numeric;
		else if ($amount>99)
			$output = $lng_sorry_no_more_than_ninetynine_percent." :o).";
		
		$p->DisplayPage($output);
	
		exit;
	}
	
	$output = cIncomeTies::saveTie(array("member_id"=>$cUser->member_id, "amount"=>$amount, "tie_id"=>$tie_id))."<p>";
	//$p->DisplayPage($output);
	
	//exit;
}

if ($_REQUEST["remove"]==1) {
	
	$output = cIncomeTies::deleteTie($cUser->member_id)."<p>";
	
//	$p->DisplayPage($output);
	
	//exit;
}

$myTie = $ties->getTie($cUser->member_id);

if (!$myTie) { // No Income Tie found
	
	$output .= "<font color=red><b>".$lng_income_share_inactive.":</b></font><p>"; 
	
	$output .= "<b>".$lng_create_an_income_share."</b><p>";
	
	$name_list = new cMemberGroup;
	$name_list->LoadMemberGroup();
	
	$output .= "<form method=GET><input type=hidden name=process value=1>";
	
	$output .= $lng_i_would_like_to_share." <input type=text size=1 maxlength=2 name=amount value=10>% ".$lng_off_any." ".UNITS." ".$lng_i_receive."...<p>";
	$output .= "... ".$lng_with_this_account.": ".$name_list->DoNamePicker();
	
	$output .= "<p><input type=submit value='".$lng_create_income_share."'></form>";
}
else {
	
	$output .= "<font color=green><b>".$lng_income_share_active.":</b></font><p>";
	
	$output .= $lng_your_currently_sharing." <b>".$myTie->percent."%</b> ".$lng_off_your_income_with." <b>'".$myTie->tie_id."'</b>.<p><a href=income_ties.php?remove=1>".$lng_remove_income_share."</a><p>".$lng_if_amend_remove_first;
}

$p->DisplayPage($output);

?>