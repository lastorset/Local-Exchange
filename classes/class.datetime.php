<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

class cDateTime {
	var $year;					
	var $month;
	var $day;
	var $hour;
	var $minute;
	var $second;
	
	function cDateTime ($date_str, $redirect=true) { // TODO: There is a problem with timestamp()
		global $cErr;											 // for dates much earlier than 1970.	
		
		if(!$date_str)
			return;
		
		if(is_numeric($date_str)) {  // Probably came direct from the database -- try to format
			$date_str = substr($date_str,0,4)."-".substr($date_str,4,2)."-".substr($date_str,6,2)." ".substr($date_str,8,2).":".substr($date_str,10,2).":".substr($date_str,12,2);
		}
		
		if(($timestamp = strtotime($date_str)) == -1) {
			if(!$redirect)
				return false;
				
			$cErr->Error("Date format invalid in cDateTime.");
			include("redirect.php");
		}
		
		$this->year = date("Y", $timestamp);
		$this->month = date("m", $timestamp);
		$this->day = date("d", $timestamp);			
		$this->hour = date("H", $timestamp);
		$this->minute = date("i", $timestamp);
		$this->second = date("s", $timestamp);
		return true;
	}
	
	function Set($datestr) {
		return $this->cDateTime($datestr);
	}
	
	function MySQLTime () {
		return $this->year . $this->month . $this->day . $this->hour . $this->minute . $this->second;
	}
	
	function MySQLDate () {
		return $this->year . $this->month . $this->day;
	}
	
	function StandardDate () {
		return $this->year ."/". $this->month ."/". $this->day;
	}
	
	function ShortDate () {
		if (MONTH_FIRST)
			return sprintf("%d/%d/%s", $this->month, $this->day, substr($this->year, 2, 2));
		else
			return sprintf("%d/%d/%s", $this->day, $this->month, substr($this->year, 2, 2));
	}
	
	function Timestamp () {
		return strtotime($this->year ."/". $this->month ."/". $this->day ." ". $this->hour .":". $this->minute .":". $this->second);
	}
	
	function DateArray() {
		return array ('d'=>$this->day,'F'=>$this->month,'Y'=>$this->year);
	}
	
	function MinutesAgo () {
		return floor((strtotime("now") - $this->Timestamp())/60);
	}	
	
	function DaysAgo () {
		return floor((strtotime("now") - $this->Timestamp())/86400);
	}
}

?>
