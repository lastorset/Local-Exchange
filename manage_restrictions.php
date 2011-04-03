<?php

include_once("includes/inc.global.php");
include("classes/class.info.php");
include("includes/inc.forms.php");

$cUser->MustBeLevel(2);

$p->site_section = EVENTS;

$p->page_title = $lng_manage_account_restrictions;

$output = $lng_restrictions_can_be_placed.".<p>";

$query = $cDB->Query("SELECT * FROM ". DATABASE_MEMBERS .",". DATABASE_PERSONS." WHERE ". DATABASE_MEMBERS .".member_id=". DATABASE_PERSONS.".member_id". $exclusions. " AND primary_member='Y' ORDER BY first_name, last_name;");

$members  = array();
		
$i=0;

while($row = mysql_fetch_array($query))
{
			$members[$i] =$row;
			
			$i += 1;
}

$restrictedM = Array();
$okM = Array();

foreach($members as $m) {
	
	if ($m["restriction"]==1)
		$restrictedM[] = $m;
	else
		$okM[] = $m;
}

if ($_REQUEST["process"]) {
	
	$typ = '';
	
	if ($_REQUEST["doRestrict"])
		$typ = 'restrict';
	else if ($_REQUEST["liftRestrict"])
		$typ = 'lift';
		

	switch($typ) {
		
		case("restrict"):
		
		if (!$_REQUEST["ok"]) {
				
				$output .= $lng_error_no_member_id;
				
				break;
			}
			
			$member = new cMember;
			$member->LoadMember($_REQUEST["ok"]);
	
			$query = $cDB->Query("UPDATE ". DATABASE_MEMBERS ." set restriction=1 WHERE member_id=".$cDB->EscTxt($_REQUEST["ok"])."");
			
			if (!$query)
				$output .= $lng_error_could_not_impose_restrictions.".<p>".$lng_mysql_said.": ".mysql_error();
			else {
				$output .= $lng_restrictions_imposed." '".$_REQUEST["ok"]."'";
				
				$mailed = mail($member->person[0]->email, $lng_acces_restricted_on." ".SITE_LONG_TITLE."", LEECH_EMAIL_URLOCKED , "From:".EMAIL_FROM); // added "From:" - by ejkv
			
			}
			
		break;
		
		case("lift"):
			
			if (!$_REQUEST["restricted"]) {
				
				$output .= $lng_error_no_member_id;
				
				break;
			}
			
			$member = new cMember;
			$member->LoadMember($_REQUEST["restricted"]);
	
			$query = $cDB->Query("UPDATE ". DATABASE_MEMBERS ." set restriction=0 WHERE member_id=".$cDB->EscTxt($_REQUEST["restricted"])."");
			
			if (!$query)
				$output .= $lng_error_could_not_lift_restrictions.".<p>".$lng_mysql_said.": ".mysql_error();
			else {
				$output .= $lng_restrictions_lifted." '".$_REQUEST["restricted"]."'";
				
				$mailed = mail($member->person[0]->email, $lng_account_restrictions_lifted_on." ".SITE_LONG_TITLE."", LEECH_EMAIL_URUNLOCKED , "From:".EMAIL_FROM); // Added "From:" - by ejkv
			}
			
		break;
	}
	$p->DisplayPage($output);
	
	exit;
}

$output .= "<form method=POST><input type=hidden name=process value='actionOnMember'>";

$output .= "<font color=green>".$lng_non_restricted."</font> ".$lng_members."<br>";
$output .= "<select name=ok>";

foreach($okM as $key => $m) {

	$output .= "<option value='".$m["member_id"]."'>".$m["first_name"]." ".$m["mid_name"]." ".$m["last_name"]."</option>"; // added .$m["mid_name"]." " by ejkv
}

$output .= "</select>";
$output .= "<input name='doRestrict' type=submit value=".$lng_impose_restriction.">";

$output .= "<p><font color=red>".$lng_restricted."</font> ".$lng_members."<br>";
$output .= "<select name=restricted>";

foreach($restrictedM as $key => $m) {

	$output .= "<option value='".$m["member_id"]."'>".$m["first_name"]." ".$m["mid_name"]." ".$m["last_name"]."</option>"; // added .$m["mid_name"]." " by ejkv
}

$output .= "</select>";
$output .= "<input name='liftRestrict' type=submit value=".$lng_lift_restrictions.">";


$output .= "</form>";

$p->DisplayPage($output);


?>