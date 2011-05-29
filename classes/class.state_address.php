<?php
/* 
	class.state_address.php <ejkleinv@kleinvelderman.net>
	
	this module was added by ejkv to handle state_address_code in the same way as categories are handled
	added for NL to be used for district or neighbourhood: wijk/buurt
	to be used in UK for county (e.g. Sussex), and in the USA for state (e.g. Florida)
	
	The options for the 'State Code' (County / Wijk) dropdown list are temporarily saved in Array: $stateArr
	This Array should be filled by DATABASE Table STATES

 */

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

class cState
{
	var $id;
	var $description;
	
	function cState($description=null) {
		if($description) {
			$this->description = $description;
		}
	}	
	
	function SaveNewState() {
		global $cDB, $cErr;

		$query = $cDB->Query("SELECT description FROM ".DATABASE_STATES." WHERE description=". $cDB->EscTxt($this->description) .";");

		if($row = mysql_fetch_array($query)) {		
			$cErr->Error(_("state_already_exists" /* orphaned string */).": '".$this->description."'.");
			return false;
		} else {
			$insert = $cDB->Query("INSERT INTO ". DATABASE_STATES ."(description) VALUES (". $cDB->EscTxt($this->description) .");");
		}			

		if(mysql_affected_rows() == 1) {
			$this->id = mysql_insert_id();
			return true;
		} else {
			return false;
		}
	}
	
	function SaveState() {
		global $cDB, $cErr;

		$query = $cDB->Query("SELECT description FROM ".DATABASE_STATES." WHERE description=". $cDB->EscTxt($this->description) .";");

		if($row = mysql_fetch_array($query)) {		
			$cErr->Error(_("state_already_exists" /* orphaned string */).": '".$this->description."'.");
			return false;
		} else {
			$update = $cDB->Query("UPDATE ". DATABASE_STATES ." SET description=". $cDB->EscTxt($this->description) ." WHERE state_id=". $cDB->EscTxt($this->id) .";");
		}			
		
		return $update;
	}
	
	function LoadState($id) {
		global $cDB, $cErr;
	
		// select description for this code
		$query = $cDB->Query("SELECT description FROM ".DATABASE_STATES." WHERE state_id=". $cDB->EscTxt($id) .";");
		
		if($row = mysql_fetch_array($query)) {		
			$this->id = $id;
			$this->description = $row[0];
		} else {
			$cErr->Error(_("error_access_state_code" /* orphaned string */)." '".$id."'.  "._("Please try again later").".");
			include("redirect.php");
		}			
	}

// DeleteState not used, state only to be changed, else there should be checked if the state is still used - ejkv
	function DeleteState() {
		global $cDB, $cErr;

		$cErr->Error(_("error_delete_state_code" /* orphaned string */)." '".$id."'. FUNCTION NOT USED."); // function not to be used
		return false; // function not to be used
	
		$delete = $cDB->Query("DELETE FROM ".DATABASE_STATES." WHERE state_id=". $cDB->EscTxt($this->id));
		
		if(mysql_affected_rows() == 1) {
			unset($this);	
			return true;
		} else {
			$cErr->Error(_("error_delete_state_code" /* orphaned string */)." '".$id."'. "._("Please try again later").".");
			include("redirect.php");
		}
	}

	function ShowState() {
		$output = $this->id .", ". $this->description . "<BR>";
		
		return $output;		
	}
	
} // cState_address

class cStateList {
	var $state;	//Will be an array of object class cState_address

	function LoadStateList($redirect=false) {	
		global $cDB, $cErr;
		
		$query = $cDB->Query("SELECT state_id, description FROM ".DATABASE_STATES." ORDER BY description;");

		$i = 0;
		while($row = mysql_fetch_array($query))
		{
			$this->state[$i] = new cState;
			$this->state[$i]->LoadState($row[0]);
			$i += 1;
		}

		if($i == 0) {
			if ($redirect) {
				$cErr->Error(_("error_access_state_record" /* orphaned string */).".  "._("Please try again later").".");
				include("redirect.php");			
			} else {
				return false;
			}
		}	
		return true;	
	}
	
	function MakeStateArray() {	
		$array["0"] = "---";
		
		if($this->LoadStateList()) {
			foreach($this->state as $state) {
				$array[$state->id] = $state->description;
			}
		}
		
		return $array;
	}

}

?>
