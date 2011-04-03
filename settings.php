<?php
include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;
$p->page_title = $lng_site_settings;

$cUser->MustBeLevel(3); // changed level 2 to level 3 - by ejkv

global $cDB, $site_settings;

$output = "";

// PROCESS Save Settings

if ($_REQUEST["process"]==true) {
	
	$output .= $site_settings->update();
	
	$output .= "<p>";
}

// DISPLAY Settings form

$output .= "<form method=POST><input type=hidden name=process value=true>";

$output .= "<table width=100%>";

// Sort settings into sections

$sections = array(1 => array(),2 => array(),7 => array(), 3 => array(),4 => array(),6 => array());
$section_names = array(1 => $lng_general_settings, 2 => $lng_site_features, 7 => $lng_display_options, 3 => $lng_account_restrictions, 4=>$lng_social_networking,6=>$lng_admin_settings);

foreach($site_settings->theSettings as $key) {
	
	if (!$key->section)
		$key->section = 1; // default to section 1 if no section specified

	$sections[$key->section][] = $key;
}

$aSectionDone = false;

$output .= $lng_following_are_admin_settings;

$output .= "<p>";

$aSecNameDone = false;
foreach($section_names as $id => $name) {
	
	if ($aSecNameDone==true)
		$output .= " | ";
	else
		$aSecNameDone = true;
		
	$output .= "<a href=#sec".$id.">".$name."</a>";
}
$output .= "<p>";

foreach($sections as $a => $b) {
	
	$output .= "</table>";
	
	if ($aSectionDone==true)
		$output .= "<a href=#top>".$lng_back_to_top."</a><hr>";
	else
		$aSectionDone = true;
		
	$output .= "<a name='sec".$a."'></a><table width=100%><tr valign=top><td><STRONG>".$section_names[$a]."</STRONG></td></tr></table>
				<p><table width=100%>";
	
	foreach($b as $key) {
		
		$output .= "<tr valign=top>";
		$output .= "<td width=70%>".stripslashes($key->display_name)."</td>";
		$output .= "<td width=30%>";
		
		// What type of form element?
		// smalltext, longtext, multiple, radio, bool
	
		switch($key->typ) {
			
			case("bool"):
			
				$output .= "<select name='".$key->name."'>";
				
				$selectedT = '';
				$selectedF = '';
				//echo $key->name." = ".$key->current_value."<br>";
				if ($key->current_value==1 || $key->current_value=='TRUE')
					$selectedT = 'selected';
				else
					$selectedF = 'selected';
				 
				$output .= "<option value='TRUE' $selectedT>".$lng_yes."</option>";
			
				$output .= "<option value='FALSE' $selectedF>".$lng_no."</option>";
			
				$output .= "</select>";
				
			break;
				
			case("radio"):
				
				$options = cSettings::split_options($key->options);
				
				foreach($options as $o) {
					
					$selected = "";
					
					if ($o==$key->current_value)
						$selected = "checked";
						
					$output .= "<input type=radio name=".$key->name." value='".$o."' $selected> ".stripslashes(ucfirst($o))." ";
				}
						
			break;
			
			case("multiple"):
			
				$output .= "<select name='".$key->name."'>";
				
				$options = cSettings::split_options($key->options);
				
				foreach ($options as $o) {
					
					$selected = "";
					
					if ($o==$key->current_value)
						$selected = " selected";
						
					$output .= "<option name='".$o."' value='".$o."' $selected>".stripslashes(ucfirst(strtolower($o)))."</option>";
				}
				
				$output .= "</select>";
				
			break;
			
			case("longtext"):
			
				$output .= "<textarea rows=5 cols=30 name='".$key->name."'>".stripslashes($key->current_value)."</textarea>";
				
			break;
			
			case("int"):
			
				$output .= "<input type=text size=5 value='".stripslashes($key->current_value)."' name='".$key->name."'>";
				
			break;
			
			default: // Assume smalltext
			
				$output .= "<input type=text maxlength='".$key->max_length."' value='".stripslashes($key->current_value)."' name='".$key->name."'>";
				
			break;
		}
		
		$output .= "</td>";
		$output .= "</tr>";
		
		if ($key->descrip) {
			
			$output .= "</table>
				<table width=100%><tr valign=top><td><font color=green>".$key->descrip."</font></td></tr></table>
				<p><table width=100%>";
		}
	}
}

$output .= "</table><p><input type=submit value=".$lng_save_settings."></form>";

$p->DisplayPage($output);
