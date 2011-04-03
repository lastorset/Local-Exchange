<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

class cLogEntry {
	var $log_id;
	var $log_date;
	var $admin_id; // usually a member_id, but not always
	var $category; // See inc.global.php for constants used in this field
	var $action;	// See inc.global.php for constants used in this field
	var $ref_id; // usually refences a trade_id, feedback_id, or similar
	var $note;
	
	function cLogEntry ($category, $action, $ref_id, $note=null) {
		global $cUser;
	
		$this->category = $category;
		$this->action = $action;
		$this->ref_id = $ref_id;
		$this->note = $note;
		$this->admin_id = $cUser->member_id;
	}
	
	function SaveLogEntry () {
		global $cDB, $cErr;
		
		$insert = $cDB->Query("INSERT INTO ". DATABASE_LOGGING ." (admin_id, category, action, ref_id, log_date, note) VALUES (". $cDB->EscTxt($this->admin_id) .", ". $cDB->EscTxt($this->category) .", ". $cDB->EscTxt($this->action) .", ". $cDB->EscTxt($this->ref_id) .", now(), ". $cDB->EscTxt($this->note) .");");

		if(mysql_affected_rows() == 1) {
			$this->log_id = mysql_insert_id();	
			$query = $cDB->Query("SELECT log_date from ". DATABASE_LOGGING ." WHERE log_id=". $this->log_id .";");
			$row = mysql_fetch_array($query);
			$this->log_date = $row[0];	
			return true;
		} else {
			return false;
		}		
	}
}

class cLogStatistics {
	function MostRecentLog ($category, $action=null) {
		global $cDB;
	
		if($action != null)
			$exclusions = " AND action='". $action ."'";
		else
			$exclusions = null;
	
		$query = $cDB->Query("SELECT max(log_date) FROM ". DATABASE_LOGGING ." WHERE category=". $cDB->EscTxt($category) . $exclusions .";");
		
		if($row = mysql_fetch_array($query))	
			return new cDateTime($row[0]);
		else
			return false;
	}
}


// System events are processes which only need to run periodically,
// and so are run at intervals rather than weighing the system
// down by running them each time a particlular page is loaded.

class cSystemEvent {
	var $event_type; // See inc.global.php for constants used in this field
	var $event_interval; // See inc.config.php for interval settings
	
	function cSystemEvent ($event_type, $event_interval=null) {
		global $SYSTEM_EVENTS;
		$this->event_type = $event_type;
		
		if($event_interval)
			$this->event_interval = $event_interval; // use explicit interval
		else
			$this->event_interval = $SYSTEM_EVENTS[$event_type]; // use defined interval
	}
	
	function TimeForEvent () {
		$logs = new cLogStatistics;
		$last_event = $logs->MostRecentLog($this->event_type);
		if($last_event->MinutesAgo() >= $this->event_interval)
			return true;
		elseif ($last_event == "") // Never run before, so now's as good a time as any
			return true;
		else
			return false;
	}
	
	function LogEvent() {
		$e = new cLogEntry($this->event_type, $this->event_type, $this->event_type);
		$e->admin_id = "EVENT_SYSTEM";
		$e->SaveLogEntry();
	}

}

?>
