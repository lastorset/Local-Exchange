<?php

include_once("includes/inc.global.php");
include_once("classes/class.info.php");
include("includes/inc.forms.php");

$cUser->MustBeLevel(2);

$p->site_section = EVENTS;

$p->page_title = _("Manage Account Restrictions");

$output = _("Restrictions can be placed on accounts if you feel they are over-using the services of others and not offering enough back in return").".<p>";

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
				
				$output .= _("Error: No username specified.");
				
				break;
			}
			
			$member = new cMember;
			$member->LoadMember($_REQUEST["ok"]);
	
			$query = $cDB->Query("UPDATE ". DATABASE_MEMBERS ." set restriction=1 WHERE member_id=".$cDB->EscTxt($_REQUEST["ok"])."");
			
			if (!$query)
				$output .= _("Error: could not impose restrictions on this account").".<p>"._("MySQL Said").": ".mysql_error();
			else {
				$output .= _("Restrictions have been imposed on username")." '".$_REQUEST["ok"]."'";
				
				$mailed = mail($member->person[0]->email, _("Access Restricted on")." ".SITE_LONG_TITLE."", LEECH_EMAIL_URLOCKED , "From:".EMAIL_FROM ."\nContent-type: text/plain; charset=UTF-8"); // added "From:" - by ejkv
			
			}
			
		break;
		
		case("lift"):
			
			if (!$_REQUEST["restricted"]) {
				
				$output .= _("Error: No username specified.");
				
				break;
			}
			
			$member = new cMember;
			$member->LoadMember($_REQUEST["restricted"]);
	
			$query = $cDB->Query("UPDATE ". DATABASE_MEMBERS ." set restriction=0 WHERE member_id=".$cDB->EscTxt($_REQUEST["restricted"])."");
			
			if (!$query)
				$output .= _("Error: could not lift restrictions on this account").".<p>"._("MySQL Said").": ".mysql_error();
			else {
				$output .= _("Restrictions have been lifted on username")." '".$_REQUEST["restricted"]."'";
				
				$mailed = mail($member->person[0]->email, _("Account Restrictions lifted on")." ".SITE_LONG_TITLE."", LEECH_EMAIL_URUNLOCKED , "From:".EMAIL_FROM ."\nContent-type: text/plain; charset=UTF-8"); // Added "From:" - by ejkv
			}
			
		break;
	}
	$p->DisplayPage($output);
	
	exit;
}

$output .= "<form method=POST><input type=hidden name=process value='actionOnMember'>";

$output .= "<font color=green>"._("Non-Restricted")."</font> "._("Members")."<br>";
$output .= "<select name=ok>";

foreach($okM as $key => $m) {

	$output .= "<option value='".$m["member_id"]."'>".$m["first_name"]." ".$m["mid_name"]." ".$m["last_name"]."</option>"; // added .$m["mid_name"]." " by ejkv
}

$output .= "</select>";
$output .= "<input name='doRestrict' type=submit value="._("Impose Restriction").">";

$output .= "<p><font color=red>"._("Restricted")."</font> "._("Members")."<br>";
$output .= "<select name=restricted>";

foreach($restrictedM as $key => $m) {

	$output .= "<option value='".$m["member_id"]."'>".$m["first_name"]." ".$m["mid_name"]." ".$m["last_name"]."</option>"; // added .$m["mid_name"]." " by ejkv
}

$output .= "</select>";
$output .= "<input name='liftRestrict' type=submit value="._("Lift Restriction").">";


$output .= "</form>";

$p->DisplayPage($output);


?>
