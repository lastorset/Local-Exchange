<?
require_once("includes/inc.global.php");
require_once("includes/inc.forms.php");

global $site_settings;

$p->site_section = EVENTS;
$cUser->MustBeLoggedOn();

if (ALLOW_INCOME_SHARES!=true) // Provision for allowing income ties has been turned off, return to homepage
	header("location:http://".HTTP_BASE."/index.php");
	
$ties = new cIncomeTies;

$p->page_title = _("Income Sharing");

$output = _("You have the option of contributing a percentage of any income")." (".$site_settings->getUnitString().") "._("you receive to another account. If you specify an Income Share, every time you receive")." ".$site_settings->getUnitString()." "._("a specified percentage (of your choosing) will automatically be paid to the account of your choice. You can change this arrangement at any time, but you are only allowed to share your income with one other account at a time.")."<p>";

if ($_REQUEST["process"]==1) {
	
	$amount = trim($_REQUEST["amount"]);
	$tie_id = trim(htmlspecialchars($_REQUEST["member_to"]));
	$tie_id = substr($tie_id,0, strpos($tie_id,"?")); // TODO:
	
	if ($tie_id==$cUser->member_id) {
		
		$output = _("Sorry you can't share income with yourself.")." :)";
		$p->DisplayPage($output);
	
		exit;
	}
	if (!$amount || !$tie_id || !is_numeric($amount) || $amount>99) {
		
		if (!$amount || !$tie_id)
			$output = _("Not enough data to proceed.");
		else if (!is_numeric($amount))
			$output = _("The percentage must be numeric and must not contain any other characters (e.g. '10' = good input, '10%' = bad input)");
		else if ($amount>99)
			$output = _("Sorry, you can't contribute more than 99% of your income to another account - but it's the thought that counts")." :o).";
		
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
	
	$output .= "<font color=red><b>"._("Income Share Inactive").":</b></font><p>"; 
	
	$output .= "<b>"._("Create an Income Share")."</b><p>";
	
	$name_list = new cMemberGroup;
	$name_list->LoadMemberGroup();
	
	$output .= "<form method=GET><input type=hidden name=process value=1>";
	
	$output .= _("I would like to share")." <input type=text size=1 maxlength=2 name=amount value=10>% "._("of any")." ".$site_settings->getUnitString()." "._("I receive")."...<p>";
	$output .= "... "._("with this account").": ".$name_list->DoNamePicker();
	
	$output .= "<p><input type=submit value='"._("Create Income Share")."'></form>";
}
else {
	
	$output .= "<font color=green><b>"._("Income Share Active").":</b></font><p>";
	
	$output .= _("You are currently sharing")." <b>".$myTie->percent."%</b> "._("of your income with account")." <b>'".$myTie->tie_id."'</b>.<p><a href=income_ties.php?remove=1>"._("Remove Income Share")."</a><p>"._("If you wish to amend this Income Share you will first need to remove it and then create a new one.");
}

$p->DisplayPage($output);

?>
