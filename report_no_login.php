<?php
	include_once("includes/inc.global.php");
	
	include_once("classes/class.table.php");
	
	$p->site_section = ADMINISTRATION;
	$p->page_title = _("Members Who Have Never Logged In");
	
	$cUser->MustBeLevel(1);
	
	$output = "";
	
	$table = new cTable;
	$table->AddSimpleHeader(array(_("Member"), _("Join Date"), _("Phone Number(s)"), _("Email(s)")));
	// $table->SetFieldAlignRight(4);  // row 4 is numeric and should align to the right
	
	$allmembers = new cMemberGroup;
	$allmembers->LoadMemberGroup();
	
	foreach($allmembers->members as $member) {
		if($member->account_type == "F")  // Skip fund accounts
			continue;
			
		$history = new cLoginHistory;
		if(! $history->LoadLoginHistory($member->member_id)) { // Have they logged in?
			$join_date = new cDateTime($member->join_date);
			$data = array($member->PrimaryName(), $join_date->ShortDate(), $member->AllPhones(), $member->AllEmails());
			$table->AddSimpleRow($data);
		}
	}
	
	$output = $table->DisplayTable();
	
	if($output == "")
		$output = _("All members in the system have logged in at least once.");
	
	$p->DisplayPage($output);
	
?>
