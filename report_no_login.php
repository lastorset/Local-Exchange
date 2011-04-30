<?php
	include_once("includes/inc.global.php");
	
	include("classes/class.table.php");
	
	$p->site_section = ADMINISTRATION;
	$p->page_title = $lng_members_never_logged_in;
	
	$cUser->MustBeLevel(1);
	
	$output = "";
	
	$table = new cTable;
	$table->AddSimpleHeader(array($lng_member, $lng_join_date, $lng_phone_numbers, $lng_emls));
	// $table->SetFieldAlignRight(4);  // row 4 is numeric and should align to the right
	
	$allmembers = new cMemberGroup;
	$allmembers->LoadMemberGroup();
	
	foreach($allmembers->members as $member) {
		if($member->account_type == "F"      // Skip fund accounts
			|| $member->account_type == "O") // Skip system accounts
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
		$output = $lng_all_members_logged_in;
	
	$p->DisplayPage($output);
	
?>
