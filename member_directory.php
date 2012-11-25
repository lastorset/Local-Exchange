<?php

include_once("includes/inc.global.php");
$p->site_section = SECTION_DIRECTORY;
$p->page_title = _("Member Directory");

$cUser->MustBeLoggedOn();

//include_once("classes/class.listing.php");

//[chris] Search function
if (SEARCHABLE_MEMBERS_LIST==true) {
	
	$output = "<form action=member_directory.php method=get>";
	$output .= _("Member ID").": <input type=text name=uID size=4 value='".$_REQUEST["uID"]."'>
		<br>"._("Name (all or part)").": <input type=text name=uName value='".$_REQUEST["uName"]."'>
		<br>"._("Location (e.g.")." ".DEFAULT_CITY."): <input type=text name=uLoc value='".$_REQUEST["uLoc"]."'>";
	
	$orderBySel = array();
	$orderBySel["".$_REQUEST["orderBy"].""]='selected';
	
// swapped option value 'idA', 'fl', and 'lf' by ejkv
// added 'addr2' for address_street2 - by ejkv
	$output .= "<br>"._("Order by").": <select name='orderBy'>
		<option value='idA' ".$orderBySel["idA"].">"._("Membership No.")."</option>
		<option value='fl' ".$orderBySel["fl"].">"._("First Name")."</option>
		<option value='lf' ".$orderBySel["lf"].">"._("Last Name")."</option>
		<option value='nh' ".$orderBySel["nh"].">"._("Neighbourhood")."</option>
		<option value='loc' ".$orderBySel["loc"].">"._("Town")."</option>
		<option value='pc' ".$orderBySel["pc"].">"._("Postal code")."</option>
		<option value='addr2' ".$orderBySel["pc"].">"._("Address Line 2")."</option>
		</select>";
	$output .= "<p><input type=submit value="._("Search")."></form>"; 
}

// added _("State") column by ejkv
$output .= "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=\"100%\">
  <TR>
    <TH>"._("Member")."</TH>
    ". (GAME_MECHANICS ? "<TH>" . _("Karma") . "</TH>" : "") ."
    <TH>"._("Phone")."</TH>
    <TH>" . _("Address Line 2") . "</TH>
    <TH>" . _("City") . "</TH>
    <TH>" . _("State") . "</TH>
    <TH>" . _("Zip Code") . "</TH>
";

if (MEM_LIST_DISPLAY_BALANCE==true || $cUser->member_role >= 1)  {   
	$output .= "<TH ALIGN=RIGHT>"._("Balance")."</TH>"; // added ALIGN=RIGHT by ejkv

}
$output .= "</TR>";

//Phones (comma separated with first name in parentheses for non-primary phones)
//Emails (comma separated with first name in parentheses for non-primary emails)

$member_list = new cMemberGroup();
//$member_list->LoadMemberGroup();

// How should results be ordered?
switch($_REQUEST["orderBy"]) {
	
	case("addr2"): // added address_street2 - by ejkv
		$orderBy = 'ORDER BY address_street2 asc';
	break;
	
	case("pc"):
		$orderBy = 'ORDER BY address_post_code asc';
	break;
	
	case("nh"):
		$orderBy = 'ORDER BY address_state_code asc'; // changed address_street2 into address_state_code - by ejkv
	break;
	
	case("loc"):
		$orderBy = 'ORDER BY address_city asc';
	break;
	
	case("fl"):
		$orderBy = 'ORDER BY first_name, last_name';
	break;
	
	case("idA"):
		$orderBy = 'ORDER BY member_id asc'; // changed idD into idA and Order ascending by ejkv
	break;
	
	case("lf"):
		$orderBy = 'ORDER BY last_name, first_name';
	break;
	
	default:
		$orderBy = 'ORDER BY member_id asc';
	break;
}

// SQL condition string
$condition = '';

function buildCondition(&$condition,$wh) { // Add a clause to the SQL condition string
	
//	if (strlen($condition)>0)
		$condition .= " AND ";
	
	$condition .= " ".$wh. " ";	
}

if ($_REQUEST["uID"]) // We' re searching for a specific member ID in the SQL
	buildCondition($condition,"member.member_id='".trim($_REQUEST["uID"])."'");

if ($_REQUEST["uName"]) { // We're searching for a specific username in the SQL
	
	$uName = trim($_REQUEST["uName"]);

	// Does it look like we've been provided with a first AND last name?
	$uName = explode(" ",$uName);
	
	$nameSrch = "person.first_name like '%".trim($uName[0])."%'";
	
	if ($uName[1]) { // surname provided
		
		$nameSrch .= " OR person.last_name like '%".trim($uName[1])."%'";
		
	}
	else // No surname, but term entered may be surname so apply to that too
		$nameSrch .= " OR person.last_name like '%".trim($uName[0])."%'";
	
	
	buildCondition($condition,"(".$nameSrch.")");
}

if ($_REQUEST["uLoc"]) // We're searching for a specific Location in the SQL
	buildCondition($condition,"(person.address_post_code like '%".trim($_REQUEST["uLoc"])."%' OR person.address_state_code like '%".trim($_REQUEST["uLoc"])."%' OR person.address_city like '%".trim($_REQUEST["uLoc"])."%' OR person.address_country like '%".trim($_REQUEST["uLoc"])."%')"); // changed address_street2 into address_state_code - by ejkv
	
// DEBUG: 
//ECHO "SELECT ".DATABASE_MEMBERS.".member_id FROM ". DATABASE_MEMBERS .",". DATABASE_PERSONS." WHERE ". DATABASE_MEMBERS .".member_id=". DATABASE_PERSONS.".member_id AND primary_member='Y' ".$condition." $orderBy";

// Do search in SQL
$c = get_defined_constants();
$query = $cDB->Query("
	SELECT {$c['DATABASE_MEMBERS']}.member_id
	FROM {$c['DATABASE_MEMBERS']} NATURAL JOIN {$c['DATABASE_PERSONS']}
	WHERE primary_member='Y'
		$condition
		$orderBy;
");

		
$i=0;

while($row = mysql_fetch_array($query)) // Each of our SQL results
{
	$member_list->members[$i] = new cMember;			
	$member_list->members[$i]->LoadMember($row[0]);
	$i += 1;
}
		
$i=0;
$state = new cStateList; // added by ejkv
$state_list = $state->MakeStateArray(); // added by ejkv
$state_list[0]="---"; // added by ejkv

if($member_list->members) {
	$karma_total = 0;

	foreach($member_list->members as $member) {
		// RF next condition is a hack to disable display of inactive members
		if($member->status != "I" || SHOW_INACTIVE_MEMBERS==true)  { // force display of inactive members off, unless specified otherwise in config file
		
			if($member->account_type != "F") {  // Don't display fund accounts
				
				if($i % 2)
					$bgcolor = "#e4e9ea";
				else
					$bgcolor = "#FFFFFF";

				$karma = $member->GetKarma();

				if ($karma == 0)
					// Don't fill in the column at all if it's zero. This way active users stand out.
					$karma = null;

				$output .= // added $state_list[$member->person[0]->address_state_code] by ejkv
					"<TR VALIGN=TOP BGCOLOR=". $bgcolor .">
					   <TD><FONT SIZE=2>". $member->AllNames()." (". $member->MemberLink() .")
					       </FONT></TD>
					   ". (GAME_MECHANICS ? "<TD><FONT SIZE=2>". $karma ."</FONT></TD>" : "") ."
					   <TD><FONT SIZE=2>". $member->AllPhones() ."</FONT></TD>
					   <TD><FONT SIZE=2>". $member->person[0]->address_street2 ."</FONT></TD>
					   <TD><FONT SIZE=2>". $member->person[0]->address_city . "</FONT></TD>
					   <TD><FONT SIZE=2>". $state_list[$member->person[0]->address_state_code] . "</FONT></TD>
					   <TD><FONT SIZE=2>". $member->person[0]->address_post_code ."</FONT></TD>";
				
				if (MEM_LIST_DISPLAY_BALANCE==true || $cUser->member_role >= 1)
					$output .= "<TD ALIGN=RIGHT><FONT SIZE=2>". $member->balance ."</FONT></TD>"; // added ALIGN=RIGHT by ejkv
					
				$output .= "</TR>";
				$i+=1;
				if ($karma) {
					$karma_total += $karma;
					$karma_n++;
				}
			}
		} // end loop to force display of inactive members off
	}
} 

// $output .= "</TABLE>";
// RF display active accounts 
$output .= "<TR><TD colspan=5><br><br>".
	(GAME_MECHANICS
		// Translation hint: All placeholders are numbers.
		? sprintf(_('Total of %1$d active accounts. %2$d accounts have a total of %3$d karma.'), $i, $karma_n, $karma_total)
		: sprintf(_('Total of %1$d active accounts.'), $i))
	."</TD></TR></TABLE>";

$p->DisplayPage($output); 

include("includes/inc.events.php");
?>
