<?php

include_once("includes/inc.global.php");
include("classes/class.info.php");
include("includes/inc.forms.php");

$cUser->MustBeLevel(2); // Wouldn't make sense to allow anyone below the top Admin level to edit page permissions 

$p->site_section = EVENTS;

$p->page_title = $lng_edit_info_page_permissions;

$output = $lng_permission_level_message;

$pgs = cInfo::LoadPages();

if ($_REQUEST["process"]==true) {
	
	foreach($pgs as $pg) {
	
		$q = 'UPDATE cdm_pages set permission='.$_REQUEST["p".$pg["id"]].' where id='.$cDB->EscTxt($pg["id"]).'';
		
		$cDB->Query($q);
	}
	
	$output = $lng_info_page_permissions_updated;
	
	$p->DisplayPage($output);

	exit;
}

if ($pgs) {
	
	$output .= "<form method=POST><input type=hidden name=process value=true>";
	
	$output .= "<table width=70%>";
		
	foreach($pgs as $pg) {
		
		$output .= "<tr><td>ID#".$pg["id"]."</td><td>".stripslashes($pg["title"])."</td><td>".doPermissionsSelect($pg)."</td></tr>";
	}
	
	$output .= "</table><p>";
	$output .= "<input type=submit value=".$lng_update_permissions."></form>";
}
else
	$output .= $lng_no_info_pages;
	
$p->DisplayPage($output);

function doPermissionsSelect($p) {
	global $lng_guests, $lng_members, $lng_committee, $lng_admin;
	$pTexts = Array($lng_guests,$lng_members,$lng_committee,$lng_admin);
		
	$tmp = "<select name=p".$p["id"].">";
	
	foreach($pTexts as $id => $value) {
		
		$tmp .= "<option value=".$id." ";
		
		if ($p["permission"]==$id)
			$tmp .= "selected";
			
		$tmp .= ">".$value."</option>";
	}
	
	$tmp .= "</select>";
	
	return $tmp;
	
}

function permission2text($p) {
	global $lng_guests, $lng_members, $lng_committee, $lng_admin;
	if (!$p)
		$p = 0;
		
	$pTexts = Array($lng_guests,$lng_members,$lng_committee,$lng_admin);
	
	return $pTexts[$p];
}
