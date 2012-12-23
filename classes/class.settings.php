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
		
		global $cDB;
		
		$this->retrieve();
		
		$settings = $this->theSettings;
		
		$sql_data = array();
		
		foreach($settings as $setting) {
			
			$sql_data[''.$setting->name.''] = ''.$_REQUEST["".$setting->name.""].'';
		}
		
		foreach ($sql_data as $setting => $value) {
			
			$result = $cDB->Query("update settings set current_value=".$cDB->EscTxt($value)." where name=".$cDB->EscTxt($setting)."");
			
			if (!$result)
				return "<font color=red>"._("Update failed!")."</font>".mysql_error();
		}
		
		$this->getCurrent(); // Refresh settings in current memory with new updated settings

		$this->saveLanguageSettings();

		return "<font color=green>"._("Settings updated successfully.")."</font>";
	}

	/** Gets the UNITS setting in a form suitable for UI. This makes sure "Hours" gets translated if necessary.
		Strictly speaking this doesn't belong in this class, not being stored in the database. */
	public function getUnitString() {
		if (UNITS != "Hours")
			return UNITS;
		else
			return _("Hours");
	}

	/** Saves language availability to the database. If the database table was empty,
		fills the table with supported languages. */
	function saveLanguageSettings() {
		global $cDB;

		if (is_array($_REQUEST['available_languages'])) {
			$selected = $_REQUEST['available_languages'];
			// Ensure all supported languages are in database while updating availability
			$query = "INSERT INTO `languages` (langcode, available) VALUES ";
			$first = true;
			foreach (cTranslationSupport::$supported_languages as $lang) {
				if (!$first)
					$query .= ",";
				$first = false;

				// Escaping not needed for values; not provided by user
				if (in_array($lang, $selected))
					$query .= " ('$lang', true) ";
				else
					$query .= " ('$lang', false) ";
			}
			$query .= "ON DUPLICATE KEY UPDATE available=VALUES(available)";
			$cDB->Query($query);
		}
		else {
			// Nothing was selected, make all languages unavailable
			$cDB->Query("UPDATE `languages` SET available = false");
		}

		if (isset($_REQUEST['DEFAULT_LANGUAGE']))
		{
			$new_default = $_REQUEST['DEFAULT_LANGUAGE'];
			if (in_array($new_default, cTranslationSupport::$supported_languages))
				$cDB->Query("UPDATE SETTINGS SET current_value='$new_default' WHERE name='DEFAULT_LANGUAGE')");
		}
	}
}

$site_settings = new cSettings();


?>
