<?php
/* 
	* class.settings.php <chris@cdmweb.co.uk>
	*
	* This class was added to handle site settings stored in MySQL.
	*
	* The MySQL method of storing settings was introduced in Version 1.0, prior to this the inc.config.php file stored all settings
	*
	* The file inc.config.php still handles some basic settings, but settings that the Administrator may wish to tinker with are now stored in MySQL and are accessible via admin.php. Doing this also negates the need for the webmaster to copy across so many settings from inc.config.php when upgrading to a new version 
*/
	
if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

class cSettings {
	
	var $theSettings = Array(); // Current site settings are stored here
	var $current = Array();
	
	// Constructor - we want to get current site settings
	function cSettings() {
		
		$this->getCurrent();
	}
	
	// Get and store current site settings
	function getCurrent() {
		
		$this->retrieve();
		
		//$this->current = Array();
		
		// Store current settings in easily accessible constants
		
		$stngs = $this->theSettings;
		
		$sql_data = array();
		
			
		foreach($stngs as $s => $ss) {
				
				if ($ss->typ=='bool') {
					
					if (strtolower($ss->current_value)=='false') {
						$ss->current_value = "";
						
					}
					else
						$ss->current_value = 1;
			
					define("".$ss->name."",((boolean) $ss->current_value));	
				}
				else if ($ss->typ=='int')
					define("".$ss->name."",((int) $ss->current_value));
				else
					define("".$ss->name."","".$ss->current_value."");
		}
		
	}
	
	// Retrieve current settings
	function retrieve() {
	
		global $cDB;
		
		$this->theSettings = Array();
		
		$q = "select * from settings";
		
		$result = $cDB->Query($q);
		
		if (!$result)
			return false;
		
		$num_results = mysql_num_rows($result);
		
		if ($num_results>0) {
			
			for ($i=0;$i<$num_results;$i++) {
				$row = mysql_fetch_object($result);
				
				if (!$row->current_value || strlen("".$row->current_value."")<1)
					$row->current_value = $row->default_value;
					
				$this->theSettings[] = $row;
			}
		
		}
		
	}
	
	function split_options($wh) {
		
		$options = explode(",",$wh);
		
		return $options;
	}
	
	// Save new settings
	function update() {
		
		global $cDB, $lng_update_failed, $lng_settings_updated;
		
		$this->retrieve();
		
		$settings = $this->theSettings;
		
		$sql_data = array();
		
		foreach($settings as $setting) {
			
			$sql_data[''.$setting->name.''] = ''.$_REQUEST["".$setting->name.""].'';
		}
		
		foreach ($sql_data as $setting => $value) {
			// Special treatment for certain settings
			if ($setting == "GENERAL_ALLOWANCE")
				$result = cAllowanceLender::UpdateAllowance($setting);
			else
				$result = $cDB->Query("update settings set current_value=".$cDB->EscTxt($value)." where name=".$cDB->EscTxt($setting)."");
			
			if (!$result)
				return "<font color=red>".$lng_update_failed."</font>".mysql_error();
		}
		
		$this->getCurrent(); // Refresh settings in current memory with new updated settings
		
		return "<font color=green>".$lng_settings_updated."</font>";
	}
}

$site_settings = new cSettings();


?>
