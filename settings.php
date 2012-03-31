<?php
include_once("includes/inc.global.php");
$p->site_section = ADMINISTRATION;
$p->page_title = _("Site Settings");

$cUser->MustBeLevel(3); // changed level 2 to level 3 - by ejkv

global $cDB, $site_settings;

$output = "";

// PROCESS Save Settings

if ($_REQUEST["process"]==true) {
	
	$output .= $site_settings->update();
	
	$output .= "<p>";
}

// DISPLAY Settings form

$output .= "<form id=settings method=POST><input type=hidden name=process value=true>";

$output .= "<table width=100%>";

// Sort settings into sections

$sections = array(1 => array(),2 => array(),7 => array(), 3 => array(),4 => array(),6 => array());
$section_names = array(1 => _("General Settings"), 2 => _("Site Features"), 7 => _("Display Options"), 3 => _("Account Restrictions"), 4=>_("Social Networking"),6=>_("Admin Settings"));

foreach($site_settings->theSettings as $key) {
	
	if (!$key->section)
		$key->section = 1; // default to section 1 if no section specified

	$sections[$key->section][] = $key;
}

$aSectionDone = false;

$output .= _("<a name='top'></a><font color=red>PLEASE NOTE: The following are <em>General</em> Settings intended for use by the LETS Administrator. <p>More <em>Advanced</em> configuration settings for the Webmaster are located in the file 'includes/inc.config.php'.</font><p>");

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
		$output .= "<a href=#top>"._("Back to top")."</a><hr>";
	else
		$aSectionDone = true;
		
	$output .= "<a name='sec".$a."'></a><table width=100%><tr valign=top><td><H3>".$section_names[$a]."</H3></td></tr></table>
				<p><table width=100%>";
	
	foreach($b as $key) {
		
		$output .= "<tr valign=top>";
		$output .= "<td width=70%>".stripslashes($key->display_name)."</td>";
		$output .= "<td width=30%>";
		
		// What type of form element?
		// smalltext, longtext, multiple, radio, bool, hidden
	
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
				 
				$output .= "<option value='TRUE' $selectedT>"._("Yes")."</option>";
			
				$output .= "<option value='FALSE' $selectedF>"._("No")."</option>";
			
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
				<p class=description>$key->descrip</p>
				<p><table width=100%>";
		}
	}
}

// Handle language settings separately

if (extension_loaded(intl)) // Required for Locale::getDisplayLanguage
{
	$output .= "</table>";
	$output .= "<h3>". _("Language settings") ."</h3>"
			.  "<input type=checkbox checked id=ENABLE_TRANSLATION name=ENABLE_TRANSLATION /><label for=ENABLE_TRANSLATION>". _("Enable translation of user interface") ."</label>"
			.  "<p class=description>". _("Enable translation of user-interface text. This only translates text that is built into Local Exchange; news, listings etc. are not automatically translated.") ."</p>"
			.  "<table class='language-selector'><tr>"
			// Translation hint: Default language
			.  "<th title='". _("If the user does not select a language, they will see this one") ."'>". _("Default") ."</th>"
			// Translation hint: Available language
			.  "<th title='". _("Allow users to select this language from the dropdown") ."'>". _("Available") ."</th>"
			.  "<th>". _("Language") ."</th>"
			.  "</tr>";
	foreach (cTranslationSupport::$supported_languages as $lang) {
		$output .= "<tr>"
				.  "<td class=widget><input type=radio name='DEFAULT_LANGUAGE[]' value='$lang'></td>"
				.  "<td class=widget><input type=checkbox name='available_languages[]' value='$lang'></td>"
				.  "<td>". ucfirst(Locale::getDisplayLanguage($lang, $translation->current_language)) ."</td>"
				.  "</tr>";
	}
	$output .= "</table>";
	$output .= "<p class=description>". _("Select the default language using the radio button, and using the checkboxes, select the available languages in the drop-down menu. Selecting one or no languages will disable the drop-down menu. Web-browser preferences may still select languages you have not made available.") ."</p>";
	$output .= "<table width=100%>";
}

// Epilogue

$output .= "</table><p><input type=submit value="._("Save Settings")."></form>";

$p->DisplayPage($output);
