<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

class cLoginHistory {  
	var $member_id;
	var $total_failed;
	var $consecutive_failures;
	var $last_failed_date;
	var $last_success_date;
	
	// NEED TO MODIFY MEMBER CLASS TO SET STATUS FIELD TO LOCKED AND CHECK DURING LOGIN
	
	function LoadLoginHistory ($member_id) {
		global $cDB, $cErr;
		
		$query = $cDB->Query("SELECT total_failed, consecutive_failures,
                     last_failed_date, last_success_date FROM ".DATABASE_LOGINS.
                     " WHERE member_id=" . $cDB->EscTxt($member_id) . ";");
		
		if($row = mysql_fetch_array($query)) {	
			$this->member_id = $member_id;	
			$this->total_failed = $row[0];
			$this->consecutive_failures = $row[1];
			$this->last_failed_date = $row[2];
			$this->last_success_date = $row[3];	
			return true;
		} else {
			return false;
		}			
	}
	
	function SaveLoginHistory () {
		global $cDB, $cErr;				

		$update = $cDB->Query("UPDATE ".DATABASE_LOGINS." SET total_failed=". $this->total_failed .", consecutive_failures=". $this->consecutive_failures .", last_failed_date=". $cDB->EscTxt($this->last_failed_date) .", last_success_date=". $cDB->EscTxt($this->last_success_date) ." WHERE member_id=". $cDB->EscTxt($this->member_id) .";");

		if(!$update) {
			$cErr->Error("Could not save changes to login history '". $this->member_id ."'. Please try again later.");	
			include("redirect.php");
		} else {
			return true;
		}
	}
	
	function SaveNewLoginHistory () {
		global $cDB, $cErr;
		
		$insert = $cDB->Query("INSERT INTO ". DATABASE_LOGINS ." (member_id, total_failed, consecutive_failures, last_failed_date, last_success_date) VALUES (". $cDB->EscTxt($this->member_id) .", ". $this->total_failed .", ". $this->consecutive_failures .", ". $cDB->EscTxt($this->last_failed_date) .", ". $cDB->EscTxt($this->last_success_date) .");");

		if(mysql_affected_rows() == 1) {	
			return true;
		} else {
			return false;  // Don't display error because it may just be the userid was wrong
		}	
	}	
	
	function RecordLoginSuccess ($member_id) {
		if($this->LoadLoginHistory($member_id)) {
			$this->last_success_date = $this->CurrentTimestamp();
			$this->consecutive_failures = 0;
			return $this->SaveLoginHistory();
		} else {
			$this->member_id = $member_id;
			$this->total_failed = 0;
			$this->consecutive_failures = 0;
			$this->last_success_date = $this->CurrentTimestamp();
			$this->last_failed_date = "00000000000000"; // MySQL won't allow a timestamp to be NULL
			return $this->SaveNewLoginHistory();
		}
	}
	
	function RecordLoginFailure ($member_id) {
		global $cDB;
		
		if($this->LoadLoginHistory($member_id)) {
			$this->last_failed_date = $this->CurrentTimestamp();
			$this->consecutive_failures += 1;
			$this->total_failed += 1;
			if($this->consecutive_failures > FAILED_LOGIN_LIMIT) {
				$member = new cMember;
				$member->LoadMember($member_id);
				$member->status = LOCKED;
				$member->SaveMember();
			}
			return $this->SaveLoginHistory();
		} else {
			$query = $cDB->Query("SELECT NULL FROM ". DATABASE_MEMBERS." WHERE member_id=". $cDB->EscTxt($member_id) .";");
			if (!$row = mysql_fetch_array($query))
				return false;	// Userid must have been misspelled or didn't exist.
			
			$this->member_id = $member_id;
			$this->total_failed = 1;
			$this->consecutive_failures = 1;
			$this->last_failed_date = $this->CurrentTimestamp();
			$this->last_success_date = "00000000000000"; // MySQL won't allow a timestamp to be NULL
			return $this->SaveNewLoginHistory();
		}
	}
	
	function CurrentTimestamp () { // TODO: Move to a new class (maybe "class.time_date.php")...
											 // Also probably shouldn't depend on the default string format.
		$date = getdate();
		$now = $date["year"] . str_pad($date["mon"],2,"0","STR_PAD_LEFT") . str_pad($date["mday"],2,"0","STR_PAD_LEFT") . str_pad($date["hours"],2,"0","STR_PAD_LEFT") . str_pad($date["minutes"],2,"0","STR_PAD_LEFT") . str_pad($date["seconds"],2,"0","STR_PAD_LEFT");
		return $now;		
	}
	
}

?>
