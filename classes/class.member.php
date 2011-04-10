<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

include_once("class.person.php");
include_once("Text/Password.php");

class cMember
{
	var $person;  // this will be an array of cPerson class objects
	var $member_id;
	var $password;
	var $member_role;
	var $security_q;
	var $security_a;
	var $status;
	var $member_note;
	var $admin_note;
	var $join_date;
	var $expire_date;
	var $away_date;
	var $account_type;
	var $email_updates;
	var $balance;
	var $restriction;

	function cMember($values=null) {
		if ($values) {
			$this->member_id = $values['member_id'];
			$this->password = $values['password'];
			$this->member_role = $values['member_role'];
			$this->security_q = $values['security_q'];
			$this->security_a = $values['security_a'];
			$this->status = $values['status'];
			$this->member_note = $values['member_note'];
			$this->admin_note = $values['admin_note'];
			$this->join_date = $values['join_date'];
			$this->expire_date = $values['expire_date'];
			$this->away_date = $values['away_date'];
			$this->account_type = $values['account_type'];
			$this->email_updates = $values['email_updates'];
			$this->balance = $values['balance'];	
		}
	}

	function SaveNewMember() {
		global $cDB, $cErr;	
		
		/* [chris] adjusted to store 'confirm_payments' preference */
		return $cDB->Query("INSERT INTO ".DATABASE_MEMBERS." (member_id, password, member_role, security_q, security_a, status, member_note, admin_note, join_date, expire_date, away_date, account_type, email_updates, confirm_payments, balance) VALUES (". $cDB->EscTxt($this->member_id) .",sha(". $cDB->EscTxt($this->password) ."),". $cDB->EscTxt($this->member_role) .",". $cDB->EscTxt($this->security_q) .",". $cDB->EscTxt($this->security_a) .",". $cDB->EscTxt($this->status) .",". $cDB->EscTxt($this->member_note) .",". $cDB->EscTxt($this->admin_note) .",". $cDB->EscTxt($this->join_date) .",". $cDB->EscTxt($this->expire_date) .",". $cDB->EscTxt($this->away_date) .",". $cDB->EscTxt($this->account_type) .",". $cDB->EscTxt($this->email_updates) .",". $cDB->EscTxt($this->confirm_payments) .",". $cDB->EscTxt($this->balance) .");");
	}

	function RegisterWebUser()
	{	
//		if (isset($_SESSION["user_login"]) and $_SESSION["user_login"] != LOGGED_OUT) {
		if (isset($_SESSION["user_login"])) {
			$this->member_id = $_SESSION["user_login"];
			$this->LoadMember($_SESSION["user_login"]);

            // Session regeneration added to boost server-side security.
            session_regenerate_id();
		}
        // Then next block has been inactivated due to security concerns.
		else {
			//$this->LoginFromCookie();
		}		
	}
	
	function LoginFromCookie()
	{
/*
		if (isset($_COOKIE["login"]) && isset($_COOKIE["pass"]))
		{
			$this->Login($_COOKIE["login"], $_COOKIE["pass"], true);
		}
*/
        return false;
	}

	function IsLoggedOn()
	{
//		if (isset($_SESSION["user_login"]) and $_SESSION["user_login"] != LOGGED_OUT)
		if (isset($_SESSION["user_login"]))
			return true;
		else
			return false;
	}

	function Login($user, $pass, $from_cookie=false) {
		global $cDB,$cErr, $lng_account_locked_too_many_login_attempts, $lng_pwd_or_member_id_incorrect, $lng_here, $lng_to_have_pwd_reset; // added $lng_pwd_or_member_id_incorrect by ejkv
		
		$login_history = new cLoginHistory();
//echo "SELECT member_id, password, member_role FROM ".DATABASE_USERS." WHERE member_id = " . $cDB->EscTxt($user) . " AND (password=sha(". $cDB->EscTxt($pass) .") OR password=". $cDB->EscTxt($pass) .") and status = 'A';";
		$query = $cDB->Query("SELECT member_id, password, member_role FROM ".DATABASE_USERS." WHERE member_id = " . $cDB->EscTxt($user) . " AND (password=sha(". $cDB->EscTxt($pass) .") OR password=". $cDB->EscTxt($pass) .") and status = 'A';");			
		if($row = mysql_fetch_array($query)) {
			$login_history->RecordLoginSuccess($user);
			$this->DoLoginStuff($user, $row["password"]);	// using pass from db since it's encrypted, and $pass isn't, if it was entered in the browser.
			return true;
		} elseif (!$from_cookie) {
			$query = $cDB->Query("SELECT NULL FROM ".DATABASE_USERS." WHERE status = 'L' and member_id=". $cDB->EscTxt($user) .";");
			if($row = mysql_fetch_array($query)) {
				$cErr->Error($lng_account_locked_too_many_login_attempts);
			} else {
				$cErr->Error($lng_pwd_or_member_id_incorrect." <A HREF=password_reset.php>".$lng_here."</A> ".$lng_to_have_pwd_reset.".", ERROR_SEVERITY_INFO);
			}
			$login_history->RecordLoginFailure($user);
			return false;
		}	
		return false;
	}
	
	function ValidatePassword($pass) {
		global $cDB;

		$query = $cDB->Query("SELECT member_id, password, member_role FROM ".DATABASE_USERS." WHERE member_id = ". $cDB->EscTxt($this->member_id) ." AND (password=sha(". $cDB->EscTxt($pass) .") OR password=". $cDB->EscTxt($pass) .");");	
		
		if($row = mysql_fetch_array($query))
			return true;
		else
			return false;
	}
	function UnlockAccount() {
		$history = new cLoginHistory;
		$has_logged_on = $history->LoadLoginHistory($this->member_id);
		if($has_logged_on) {
			$consecutive_failures = $history->consecutive_failures;
			$history->consecutive_failures = 0;  // Set count back to zero whether locked or not
			$history->SaveLoginHistory();	
		} 
		
		if($this->status == LOCKED) {
			$this->status = ACTIVE;
			if($this->SaveMember()) {
				return $consecutive_failures;
			}			
		}
		return false;
	}
	
	function DeactivateMember() {
		if($this->status == ACTIVE) {
			$this->status = INACTIVE;
			return $this->SaveMember();
		} else {
			return false;	
		}
	}
	
	function ReactivateMember() {
		if($this->status != ACTIVE) {
			$this->status = ACTIVE;
			return $this->SaveMember();
		} else {
			return false;	
		}
	}
	
	function ChangePassword($pass) { // TODO: Should use SaveMember and should reset $this->password
		global $cDB, $cErr, $lng_error_updating_pwd, $lng_try_again_later;
		
		$update = $cDB->Query("UPDATE ". DATABASE_MEMBERS ." SET password=sha(". $cDB->EscTxt($pass) .") WHERE member_id=". $cDB->EscTxt($this->member_id) .";");
		
		if($update) {
			return true;
		} else {
			$cErr->Error($lng_error_updating_pwd." ".$lng_try_again_later);
			include("redirect.php");
		}
	}
	
	function GeneratePassword() {  
		return Text_Password::create(6) . chr(rand(50,57));
	}

	function DoLoginStuff($user, $pass)
	{
		global $cDB;
		
		//setcookie("login",$user,time()+60*60*24*1,"/");
		//setcookie("pass",$pass,time()+60*60*24*1,"/");

		$this->LoadMember($user);
		$_SESSION["user_login"] = $user;
	}

	function UserLoginPage() // A free-standing login page
	{   global $lng_member_id, $lng_pwd, $lng_login, $lng_if_you_dont_have_account;
		$output = "<DIV STYLE='width=60%; padding: 5px;'><FORM ACTION=".SERVER_PATH_URL."/login.php METHOD=POST>
					<INPUT TYPE=HIDDEN NAME=action VALUE=login>
					<INPUT TYPE=HIDDEN NAME=location VALUE='".$_SERVER["REQUEST_URI"]."'>
					<TABLE class=NoBorder><TR><TD ALIGN=LEFT>".$lng_member_id.":</TD><TD ALIGN=LEFT><INPUT TYPE=TEXT SIZE=12 NAME=user></TD></TR>
					<TR><TD ALIGN=LEFT>".$lng_pwd." :</TD><TD ALIGN=LEFT><INPUT TYPE=PASSWORD SIZE=12 NAME=pass></TD></TR></TABLE>
					<DIV align=LEFT><INPUT TYPE=SUBMIT VALUE=".$lng_login."></DIV>
					</FORM></DIV>
					<BR>
					".$lng_if_you_dont_have_account."
					<BR>";	
		return $output;
	}

	function UserLoginLogout() {
		global $lng_logout, $lng_login;
		if ($this->IsLoggedOn())
		{
			//$output = "<FONT SIZE=1><A HREF='".SERVER_PATH_URL."/member_logout.php'>Logout</A>&nbsp;&nbsp;&nbsp;";
			$output = "<A HREF='".SERVER_PATH_URL."/member_logout.php'>".$lng_logout."</A>&nbsp;&nbsp;&nbsp;";
		} else {
			//$output = "<FONT SIZE=1><A HREF='".SERVER_PATH_URL."/member_login.php'>Login</A>&nbsp;&nbsp;&nbsp;";
			$output = "<A HREF='".SERVER_PATH_URL."/member_login.php'>".$lng_login."</A>&nbsp;&nbsp;&nbsp;";
		}

		return $output;		
	}

	function MustBeLoggedOn()
	{
		global $p, $cErr;
		
		if ($this->IsLoggedOn())
			return true;
		
		// user isn't logged on, but is in a section of the site where they should be logged on.
		$_SESSION['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
		$cErr->SaveErrors();
		header("location:http://".HTTP_BASE."/login_redirect.php");
				
		exit;
	}


	function Logout() {
        setcookie(session_name(), session_id(), time() - 42000, '/');
		$_SESSION = array();
        session_destroy();
	}

	function MustBeLevel($level) {
		global $p, $lng_access_denied_no_permission, $lng_would_like_access_level_increased, $lng_eml_admin, $lng_and_ask;
		$this->MustBeLoggedOn(); // seems prudent to check first.

		if ($this->member_role<$level)
		{
			$page = "<DIV Class='AccessDenied'>".$lng_access_denied_no_permission.".<BR><BR>".$lng_would_like_access_level_increased." <a href='mailto:".EMAIL_ADMIN."'>".$lng_eml_admin."</a>  ".$lng_and_ask.".</DIV>";
			$p->DisplayPage($page);
			exit;

		}

	}
	
	function AccountIsRestricted() {
		
		if ($this->restriction==1)
			return true;
		
		return false;
	}
	
	function LoadMember($member, $redirect=true) {
		global $cDB, $cErr, $lng_error_access_member, $lng_please_try_again_later, $lng_error_access_person_record;

		//
		// select all Member data and populate the properties
		//
		/*[chris] adjusted to retrieve 'confirm_payments' */
		$query = $cDB->Query("SELECT member_id, password, member_role, security_q, security_a, status, member_note, admin_note, join_date, expire_date, away_date, account_type, email_updates, balance, confirm_payments, restriction FROM ".DATABASE_MEMBERS." WHERE member_id=". $cDB->EscTxt($member));
		
		if($row = mysql_fetch_array($query))
		{		
			$this->member_id=$row[0];
			$this->password=$row[1];
			$this->member_role=$row[2];
			$this->security_q=$cDB->UnEscTxt($row[3]);
			$this->security_a=$cDB->UnEscTxt($row[4]);
			$this->status=$row[5];
			$this->member_note=$cDB->UnEscTxt($row[6]);
			$this->admin_note=$cDB->UnEscTxt($row[7]);
			$this->join_date=$row[8];
			$this->expire_date=$row[9];
			$this->away_date=$row[10];
			$this->account_type=$row[11];
			$this->email_updates=$row[12];
			$this->balance=$row[13];
			
			// [chris]		
			$this->confirm_payments=$row[14];
			$this->restriction=$row[15];
		
		}
		else
		{
			if ($redirect) {
				$cErr->Error($lng_error_access_member." (".$member."). ".$lng_please_try_again_later.".");
				include("redirect.php");
			}
			return false;
		}	
						
		//
		// Select associated person records and load into person object array
		//

		$query = $cDB->Query("SELECT person_id FROM ".DATABASE_PERSONS." WHERE member_id=". $cDB->EscTxt($member) ." ORDER BY primary_member DESC, last_name, mid_name, first_name"); // added mid_name by ejkv
		$i = 0;
		
		while($row = mysql_fetch_array($query))
		{
			$this->person[$i] = new cPerson;			// instantiate new cPerson objects and load them
			$this->person[$i]->LoadPerson($row[0]);
			$i += 1;
		}

		if($i == 0)
		{
			if ($redirect) {
				$cErr->Error($lng_error_access_person_record." (".$member.").  ".$lng_please_try_again_later.".");
				include("redirect.php");			
			}
			return false;
		}
		return true;
	}
	
	function ShowMember()
	{   global $lng_member_data, $lng_person_data;
		$output = $lng_member_data.":<BR>";
		$output .= $this->member_id . ", " . $this->password . ", " . $this->member_role . ", " . $this->security_q . ", " . $this->security_a . ", " . $this->status . ", " . $this->member_note . ", " . $this->admin_note . ", " . $this->join_date . ", " . $this->expire_date . ", " . $this->away_date . ", " . $this->account_type . ", " . $this->email_updates . ", " . $this->balance . "<BR><BR>";
		
		$output .= $lng_person_data.":<BR>";
		
		foreach($this->person as $person)
		{
			$output .= $person->ShowPerson();
			$output .= "<BR><BR>";
		}			
						
		return $output;
	}		
	
	function UpdateBalance($amount) {
		$this->balance += $amount;
		return $this->SaveMember();
	}
	
	function SaveMember() {
		global $cDB, $cErr, $lng_could_not_save_changes_member, $lng_please_try_again_later;				
		
		// [chris] included 'confirm_payments' preference
		$update = $cDB->Query("UPDATE ".DATABASE_MEMBERS." SET password=". $cDB->EscTxt($this->password) .", member_role=". $cDB->EscTxt($this->member_role) .", security_q=". $cDB->EscTxt($this->security_q) .", security_a=". $cDB->EscTxt($this->security_a) .", status=". $cDB->EscTxt($this->status) .", member_note=". $cDB->EscTxt($this->member_note) .", admin_note=". $cDB->EscTxt($this->admin_note) .", join_date=". $cDB->EscTxt($this->join_date) .", expire_date=". $cDB->EscTxt($this->expire_date) .", away_date=". $cDB->EscTxt($this->away_date) .", account_type=". $cDB->EscTxt($this->account_type) .", email_updates=". $cDB->EscTxt($this->email_updates) .", confirm_payments=".$cDB->EscTxt($this->confirm_payments).", balance=". $cDB->EscTxt($this->balance) ." WHERE member_id=". $cDB->EscTxt($this->member_id) .";");	

		if(!$update)
			$cErr->Error($lng_could_not_save_changes_member." '". $this->member_id ."'. ".$lng_please_try_again_later.".");

		foreach($this->person as $person) {
			$person->SavePerson();
		}
				
		return $update;	
	}
	
	function PrimaryName () {
		return $this->person[0]->first_name . " " . $this->person[0]->mid_name . " " . $this->person[0]->last_name; // added mid_name by ejkv
	}
	
	function VerifyPersonInAccount($person_id) { // Make sure hacker didn't manually change URL
		global $cErr, $lng_which_joint_member, $lng_invalid_person_id_url;

		if ($person_id == "") { // Make sure a joint member was selected or account has no joint members - added by ejkv
			$cErr->Error($lng_which_joint_member,ERROR_SEVERITY_LOW); // added by ejkv
			include("redirect.php"); // added by ejkv
		} // added by ejkv

		foreach($this->person as $person) {
			if($person->person_id == $person_id)
				return true;
		}
		$cErr->Error($lng_invalid_person_id_url,ERROR_SEVERITY_HIGH);
		include("redirect.php");
	}
	
	function PrimaryAddress () {
		if($this->person[0]->address_street1 != "") {
			$address = $this->person[0]->address_street1 . ", ";
			if($this->person[0]->address_street2 != "")
				$address .= $this->person[0]->address_street2 . ", ";
		} else {
			$address = "";
		}
		
		return $address . $this->person[0]->address_city;
	}
	
	function AllNames () {
		foreach($this->person as $person) {
			if($person->primary_member == "Y") {
				$names = $person->first_name ." ". $person->mid_name ." ". $person->last_name; // added mid_name by ejkv
			} else {
				$names .= ", ". $person->first_name ." ". $person->mid_name ." ". $person->last_name; // added mid_name by ejkv
			}	
		}
		return $names;
	}
	
	function AllPhones () {
		global $lng_fax, $lng_s_fax;
		$phones = "";
		$reg_phones[]="";
		$fax_phones[]="";
		foreach($this->person as $person) {
			if($person->primary_member == "Y") {
				if($person->phone1_number != "") {
					$phones .= $person->DisplayPhone(1);
					$reg_phones[] = $person->DisplayPhone(1);
				}
				if($person->phone2_number != "") {
					$phones .= "<br>". $person->DisplayPhone(2); // replaced ", " by "<br>" - by ejkv
					$reg_phones[] = $person->DisplayPhone(2);
				}
				if($person->fax_number != "") {
					$phones .= "<br>". $person->DisplayPhone("fax"). " (".$lng_fax.")"; // replaced ", " by "<br>" - by ejkv
					$fax_phones[] = $person->DisplayPhone("fax");
				}
			} else {
				if($person->phone1_number != "" and array_search($person->DisplayPhone(1), $reg_phones) === false){ 
					$phones .= "<br>". $person->DisplayPhone(1). " (". $person->first_name .")"; // replaced ", " by "<br>" - by ejkv
					$reg_phones[] = $person->DisplayPhone(1);
				}
				if($person->phone2_number != "" and array_search($person->DisplayPhone(2), $reg_phones) === false) {
					$phones .= "<br>". $person->DisplayPhone(2). " (". $person->first_name .")"; // replaced ", " by "<br>" - by ejkv
					$reg_phones[] = $person->DisplayPhone(2);
				}
				if($person->fax_number != "" and array_search($person->DisplayPhone("fax"), $fax_phones) === false) {
					$phones .= "<br>". $person->DisplayPhone("fax"). " (". $person->first_name .$lng_s_fax.")"; // replaced ", " by "<br>" - by ejkv
					$fax_phones[] = $person->DisplayPhone("fax");
				}
			}	
		}
		return $phones;		
	}
	
	function AllEmails () {
		foreach($this->person as $person) {
			if($person->primary_member == "Y") {
				$emails = '<A HREF=email.php?email_to='. $person->email .'&member_to='. $this->member_id .'>'. $person->email .'</A>';
			} else {
				if($person->email != "" and strpos($emails, $person->email) === false)
					$emails .= '<br><A HREF=email.php?email_to='. $person->email .'&member_to='. $this->member_id .'>'. $person->email .'</A> ('. $person->first_name .')'; // replaced ", " by "<br>" - by ejkv
			}	
		}
		return $emails;	
	}
	
	function VerifyMemberExists($member_id) {
		global $cDB;
	
		$query = $cDB->Query("SELECT NULL FROM ".DATABASE_MEMBERS." WHERE member_id=". $cDB->EscTxt($member_id));
		
		if($row = mysql_fetch_array($query))
			return true;
		else
			return false;
	}
	
	function MemberLink () {
		return "<A HREF=member_summary.php?member_id=". $this->member_id .">". $this->member_id ."</A>";
	}
	
	/*[chris] this function looks up the image for member ($mID) and places it in a HTML img tag */
	function DisplayMemberImg($mID,$typ=false) {
		
		if (ALLOW_IMAGES!=true) // Images are turned off in config
			return " ";
			
		global $cDB;
		
		// note: the 'typ' param has been deprecated since new method introduced for resizing imgs
		/*
		if ($typ=='thumb') {
			$pH = MEMBER_PHOTO_HEIGHT_THUMB;
			$pW = MEMBER_PHOTO_WIDTH_THUMB;
		}
		else {
			
			$pH = MEMBER_PHOTO_HEIGHT;
			$pW = MEMBER_PHOTO_WIDTH;
		}
		*/
		$query = $cDB->Query("SELECT filename FROM ".DATABASE_UPLOADS." WHERE title=".$cDB->EscTxt("mphoto_".$mID)." limit 0,1;");
		
		$num_results = mysql_num_rows($query);
		
		if ($num_results>0) {
			
			$row = mysql_fetch_array($query);
			$imgLoc = 'uploads/'. stripslashes($row["filename"]);
	
			return 	"<img src='".$imgLoc."'><BR>";	
		}
		else
			return 	"<img src='".DEFAULT_PHOTO."'><BR>"; // in case no member-photo uploaded, use default - added by ejkv
	}
	
	function DisplayMember () {
		
		/*[CDM] Added in image, placed all this in 2 column table, looks tidier */
		
		global $cDB,$agesArr,$sexArr, $lng_member, $lng_activity,$lng_no_exchanges_yet, $lng_exchanges_total, $lng_sum_of, $lng_last_on, $lng_feedback_cap, $lng_positive, $lng_total, $lng_negative_lc, $lng_neutral_lc, $lng_joined, $lng_email, $lng_primary_phone, $lng_secondary_phone, $lng_fax, $lng_joint_member, $lng_email, $lng_phone, $lng_secondary_phone, $lng_personal_information, $lng_unspecified, $lng_no_description_supplied, $lng_age, $lng_sex, $lng_about_me;
		
		$output .= "<table width=100%><tr valign=top><td width=50%>";
		
		$output .= "<STRONG>".$lng_member.":</STRONG> ". $this->PrimaryName() . " (". $this->MemberLink().")"."<BR>";
		$stats = new cTradeStats($this->member_id);
		$output .= "<STRONG>".$lng_activity.":</STRONG> ";
		if ($stats->most_recent == "")
			$output .= $lng_no_exchanges_yet."<BR>";
		else		
			$output .= '<A HREF="trade_history.php?mode=other&member_id='. $this->member_id .'">'. $stats->total_trades ." ".$lng_exchanges_total."</A> ".$lng_sum_of." ". $stats->total_units . " ". strtolower(UNITS) . ", ".$lng_last_on." ". $stats->most_recent->ShortDate() ."<BR>";
		$feedbackgrp = new cFeedbackGroup;
		$feedbackgrp->LoadFeedbackGroup($this->member_id);
		if(isset($feedbackgrp->feedback)) {
			$output .= "<b>".$lng_feedback_cap.":</b> <A HREF=feedback_all.php?mode=other&member_id=". $this->member_id . ">" . $feedbackgrp->PercentPositive() . "% ".$lng_positive."</A> (" . $feedbackgrp->TotalFeedback() . " ".$lng_total.", " . $feedbackgrp->num_negative ." ".$lng_negative_lc." & " . $feedbackgrp->num_neutral . " ".$lng_neutral_lc.")<BR>";		
		}

		$joined = new cDateTime($this->join_date);
		$output .= "<STRONG>".$lng_joined.":</STRONG> ". $joined->ShortDate() ."<BR>";

		if($this->person[0]->email != "")
			$output .= "<STRONG>".$lng_email.":</STRONG> ". "<A HREF=email.php?email_to=". $this->person[0]->email ."&member_to=". $this->member_id .">". $this->person[0]->email ."</A><BR>";	
		if($this->person[0]->phone1_number != "")
			$output .= "<STRONG>".$lng_primary_phone.":</STRONG> ". $this->person[0]->DisplayPhone("1") ."<BR>";
		if($this->person[0]->phone2_number != "")
			$output .= "<STRONG>".$lng_secondary_phone.":</STRONG> ". $this->person[0]->DisplayPhone("2") ."<BR>";						
		if($this->person[0]->fax_number != "")
			$output .= "<STRONG>".$lng_fax.":</STRONG> ". $this->person[0]->DisplayPhone("fax") ."<BR>";	
		if($this->person[0]->address_street2 != "") {
            $output .= "<STRONG>" . ADDRESS_LINE_2 . ": </STRONG>" .
                           $this->person[0]->address_street2 . "<BR>";
        }
		if($this->person[0]->address_city != "") {
            $output .= "<STRONG>" . ADDRESS_LINE_3 . ": </STRONG>" .
                           $this->person[0]->address_city . "<BR>";
        }
		if($this->person[0]->address_post_code != "") {
            $output .= "<STRONG>" . ZIP_TEXT . ": </STRONG>" .
                           $this->person[0]->address_post_code . "<BR>";
        }
		if($this->person[0]->address_state_code != "") { // added by ejkv
			$states = new cStateList; // added by ejkv
			$state_list = $states->MakeStateArray(); // added by ejkv
			$state_list[0]="---"; // added by ejkv
	
            $output .= "<STRONG>" . STATE_TEXT . ": </STRONG>" .
                           $state_list[$this->person[0]->address_state_code] . "<BR>";
        } // added address state code by ejkv

		foreach($this->person as $person) {
			if($person->primary_member == "Y")
				continue;	// Skip the primary member, since we already displayed above
		
			if($person->directory_list == "Y") {
				$output .= "<BR><STRONG>".$lng_joint_member.":</STRONG> ". $person->first_name ." ". $person->mid_name ." ". $person->last_name ."<BR>"; // added mid_name by ejkv
				if($person->email != "")
					$output .= "<STRONG>". $person->first_name ."'s ".$lng_email.":</STRONG> ". "<A HREF=email.php?email_to=". $person->email ."&member_to=". $this->member_id .">". $person->email ."</A><BR>";				
				if($person->phone1_number != "")
					$output .= "<STRONG>". $person->first_name ."'s ".$lng_phone.":</STRONG> ". $person->DisplayPhone("1") ."<BR>";
				if($person->phone2_number != "")
					$output .= "<STRONG>". $person->first_name ."'s ".$lng_secondary_phone.":</STRONG> ". $person->DisplayPhone("2") ."<BR>";						
				if($person->fax_number != "")
				$output .= "<STRONG>". $person->first_name ."'s ".$lng_fax.":</STRONG> ". $person->DisplayPhone("fax") ."<BR>";				
			}
		}		
	
	$output .= "</td><td width=50% align=center>";
		
	$output .= cMember::DisplayMemberImg($this->member_id);	
		
	$output .= "</td></tr></table>";
	
	if (SOC_NETWORK_FIELDS==true) {
	
		$output .= "<p><STRONG><I>".$lng_personal_information."</I></STRONG><P>";
		
		$pAge = (strlen($this->person[0]->age)<1) ? $lng_unspecified : $agesArr[$this->person[0]->age];
		$pSex = (!$this->person[0]->sex) ? $lng_unspecified : $sexArr[$this->person[0]->sex];
		$pAbout = (!stripslashes($this->person[0]->about_me)) ? '<em>'.$lng_no_description_supplied.'.</em>' : stripslashes($this->person[0]->about_me);
		
		$output .= "<STRONG>".$lng_age.":</STRONG> ".$pAge."<br>";
		
		$output .= "<STRONG>".$lng_sex.":</STRONG> ".$pSex."<p>";
		
		$output .= "<STRONG>".$lng_about_me.":</STRONG><p> ".$pAbout."<br>";
	}

	return $output;	
	}
	
	function MakeJointMemberArray() {
		global $cDB;
		
		$names = array();
		foreach ($this->person as $person) {
			if($person->primary_member != 'Y') {
				$names[$person->person_id] = $person->first_name ." ". $person->mid_name ." ". $person->last_name; // added mid_name by ejkv
				}
		}
		
		return $names;	
	}		
	
	function DaysSinceLastTrade() {
		global $cDB;
	
		$query = $cDB->Query("SELECT max(trade_date) FROM ". DATABASE_TRADES ." WHERE member_id_to=". $cDB->EscTxt($this->member_id) ." OR member_id_from=". $cDB->EscTxt($this->member_id) .";");
		
		$row = mysql_fetch_array($query);
		
		if($row[0] != "")
			$last_trade = new cDateTime($row[0]);
		else
			$last_trade = new cDateTime($this->join_date);

		return $last_trade->DaysAgo();
	}
	
	function DaysSinceUpdatedListing() {
		global $cDB;
	
		$query = $cDB->Query("SELECT max(posting_date) FROM ". DATABASE_LISTINGS ." WHERE member_id=". $cDB->EscTxt($this->member_id) .";");
		
		$row = mysql_fetch_array($query);
		
		if($row[0] != "")
			$last_update = new cDateTime($row[0]);
		else
			$last_update = new cDateTime($this->join_date);

		return $last_update->DaysAgo();
	}	
}

class cMemberGroup {
	var $members;
	
	function LoadMemberGroup ($active_only=TRUE, $non_members=FALSE) {
		global $cDB;
				
		if($active_only)
			$exclusions = " AND status in ('A','L')";
		else
			$exclusions = null;
			
		if(!$non_members)
			$exclusions .= " AND member_role != '9'";
		
		$query = $cDB->Query("SELECT ".DATABASE_MEMBERS.".member_id FROM ". DATABASE_MEMBERS .",". DATABASE_PERSONS." WHERE ". DATABASE_MEMBERS .".member_id=". DATABASE_PERSONS.".member_id". $exclusions. " AND primary_member='Y' ORDER BY first_name, mid_name, last_name;"); // added mid_name by ejkv
		
		$i=0;
		while($row = mysql_fetch_array($query))
		{
			$this->members[$i] = new cMember;			
			$this->members[$i]->LoadMember($row[0]);
			$i += 1;
		}
		
		if($i == 0)
			return false;
		else
			return true;		
	}	

// function LoadMemberIdGroup added for use of MemberGroup sorted on meber_id - by ejkv
	function LoadMemberIdGroup ($active_only=TRUE, $non_members=FALSE) {
		global $cDB;
				
		if($active_only)
			$exclusions = " AND status in ('A','L')";
		else
			$exclusions = null;
			
		if(!$non_members)
			$exclusions .= " AND member_role != '9'";
		
		$query = $cDB->Query("SELECT ".DATABASE_MEMBERS.".member_id FROM ". DATABASE_MEMBERS .",". DATABASE_PERSONS." WHERE ". DATABASE_MEMBERS .".member_id=". DATABASE_PERSONS.".member_id". $exclusions. " AND primary_member='Y' ORDER BY member_id;");
		
		$i=0;
		while($row = mysql_fetch_array($query))
		{
			$this->members[$i] = new cMember;			
			$this->members[$i]->LoadMember($row[0]);
			$i += 1;
		}
		
		if($i == 0)
			return false;
		else
			return true;		
	}	
	
	function MakeIDArray() {
		global $cDB, $cErr;
		
		$ids="";		
		if($this->members) {
			foreach($this->members as $member) {
					$ids[$member->member_id] = $member->PrimaryName() ." (". $member->member_id .")";
			}		
		}
		
		return $ids;	
	}	
	
	function MakeNameArray() {
		global $cDB, $cErr;
		
		$names["0"] = "";
		
		if($this->members) {
			foreach($this->members as $member) {
				foreach ($member->person as $person) {			
					$names[$member->member_id ."?". $person->person_id] = $person->first_name ." ".$person->mid_name ." ". $person->last_name ." (". $member->member_id .")"; // added mid_name by ejkv
				}
			}	
		
			array_multisort($names);// sort purely by person name (instead of member, person)
		}
		
		return $names;		
	}	
	
	function DoNamePicker() {
		global $lng_matching_members, $lng_member_search;
		$tmp = '<script src=includes/autocomplete.js></script>';
		
		$mems = $this->MakeNameArray();
		
		$tmp .= "<select name=member_to>
			<option id=0 value=0>".count($mems)." ".$lng_matching_members."...</option>";
		
		foreach($mems as $key=>$value) {
			
			$tmp .= "<option id='".$key."' value='".$key."'>".$value."</option>";
		}
		
		$tmp .= "</select>";
//		$form->addElement("select", "member_to", "...", $name_list->MakeNameArray());
		$tmp .= '<input type=text size=20 name=picker value='.$lng_member_search.' onKeyUp="autoComplete(this,document.all.member_to,\'text\')"
			onFocus="this.value=\'\'">
			<!--<input type=button value="Update Dropdown List">-->';
		return $tmp;
	}
	
	// Use of this function requires the inclusion of class.listing.php
	function EmailListingUpdates($interval) {
		global $lng_days, $lng_no_listings_found, $lng_wanted_listings, $lng_offered_listings, $lng_day, $lng_week, $lng_month, $lng_new_updated_listings_during_last; // removed $lng_from - by ejkv
		if(!isset($this->members)) {
			if(!$this->LoadMemberGroup())
				return false;
		}

		$listings = new cListingGroup(OFFER_LISTING);
		$since = new cDateTime("-". $interval ." days"); // $since = new cDateTime("-". $interval ." ".$lng_days);
		$listings->LoadListingGroup(null,null,null,$since->MySQLTime());
		$offered_text = $listings->DisplayListingGroup(true);
		$listings = new cListingGroup(WANT_LISTING);
		$listings->LoadListingGroup(null,null,null,$since->MySQLTime());
		$wanted_text = $listings->DisplayListingGroup(true);
		
		$email_text = "";
		if($offered_text != $lng_no_listings_found)
			$email_text .= "<h2>".$lng_offered_listings."</h2><br>". $offered_text ."<p><br>";
		if($wanted_text != $lng_no_listings_found)
			$email_text .= "<h2>".$lng_wanted_listings."</h2><br>". $wanted_text;
		if(!$email_text)
			return; // If no new listings, don't email
		
		$email_text = "<html><body>". LISTING_UPDATES_MESSAGE ."<p><br>".$email_text. "</body></html>";
			
		if ($interval == '1')
			$period = $lng_day;
		elseif ($interval == '7')
			$period = $lng_week;
		else
			$period = $lng_month;			
		
		foreach($this->members as $member) {						
			if($member->email_updates == $interval and $member->person[0]->email) {
				mail($member->person[0]->email, SITE_SHORT_TITLE .": ".$lng_new_updated_listings_during_last." ". $period, wordwrap($email_text, 64), "From:". EMAIL_ADMIN ."\nMIME-Version: 1.0\n" . "Content-type: text/html; charset=iso-8859-1"); // replaced $lng_from.":" by "From:" - by ejkv
			}
		
		}
	
	}
	
	// Use of this function requires the inclusion of class.listing.php
	function ExpireListings4InactiveMembers() {
		global $lng_days, $lng_important_information_about,$lng_account_lc, $lng_from_colon, $lng_member_has_no_email, $lng_listing_expired_for, $lng_all_listings_auto_espired; // removed $lng_from_colon, - by ejkv
		if(!isset($this->members)) {
			if(!$this->LoadMemberGroup())
				return false;
		}
		
		foreach($this->members as $member) {
			if($member->DaysSinceLastTrade() >= MAX_DAYS_INACTIVE
			and $member->DaysSinceUpdatedListing() >= MAX_DAYS_INACTIVE) {
				$offer_listings = new cListingGroup(OFFER_LISTING);
				$want_listings = new cListingGroup(WANT_LISTING);
				
				$offered_exist = $offer_listings->LoadListingGroup(null, null, $member->member_id, null, false);
				$wanted_exist = $want_listings->LoadListingGroup(null, null, $member->member_id, null, false);
				
				if($offered_exist or $wanted_exist)	{
					$expire_date = new cDateTime("+". EXPIRATION_WINDOW ." days"); // $expire_date = new cDateTime("+". EXPIRATION_WINDOW ." ".$lng_days);
					if($offered_exist)
						$offer_listings->ExpireAll($expire_date);
					if($wanted_exist)
						$want_listings->ExpireAll($expire_date);
				
					if($member->person[0]->email != null) {
						mail($member->person[0]->email, $lng_important_information_about." ". SITE_SHORT_TITLE ." ".$lng_account_lc, wordwrap(EXPIRED_LISTINGS_MESSAGE, 64), "From:". EMAIL_ADMIN); // replaced $lng_from_colon by "From:" - by ejkv
						$note = "";
						$subject_note = "";
					} else {
						$note = "\n\n".$lng_member_has_no_email;
						$subject_note = " (member has no email)";
					}
					
					mail(EMAIL_ADMIN, SITE_SHORT_TITLE ." ".$lng_listing_expired_for." ". $member->member_id. $subject_note, wordwrap($lng_all_listings_auto_espired. $note, 64) , "From:". EMAIL_ADMIN); // replaced $lng_from_colon by "From:" - by ejkv
				}
			}
		}
	}
}

class cMemberGroupMenu extends cMemberGroup {		
	var $id;
	var $name;
	var $person_id;

	function MakeMenuArrays() {
		global $cDB, $cErr;
		
		$i = 0;
		$j = 0;	
		foreach($this->members as $member) {
			foreach ($member->person as $person) {
				$this->id[$i] = $member->member_id;
				$this->name[$i][$j] = $person->first_name." ".$person->mid_name." ".$person->last_name; // added mid_name by ejkv
				$this->person_id[$i][$j] = $person->person_id;						
				$j += 1;
			}
			$i += 1;
		}
		
		if($i <> 0)
			return true;
		else 
			return false;
	}
}

class cBalancesTotal {
	var $balance;
	
	function Balanced() {
		global $cDB, $cErr, $lng_could_not_query_dbase_for_balance, $lng_try_again_later;
		
		$query = $cDB->Query("SELECT sum(balance) from ". DATABASE_MEMBERS .";");
		
		if($row = mysql_fetch_array($query)) {
			$this->balance = $row[0];
			
			if($row[0] == 0)
				return true;
			else
				return false;
		} else {
			$cErr->Error($lng_could_not_query_dbase_for_balance." ".$lng_try_again_later);
			return false;
		}		
	}
}

class cIncomeTies extends cMember {
	
	function getTie($member_id) {
		
		global $cDB;
		
		$q = "select * from income_ties where member_id=".$cDB->EscTxt($member_id)." limit 0,1";
		$result = $cDB->query($q);
		
		if (!$result)
			return false;
		
		$row = mysql_fetch_object($result);
		
		return $row;
	}
	
	function saveTie($data) {
		
		global $cDB, $lng_error_saving_income_share, $lng_income_share_saved;
		
		if (!cIncomeTies::getTie($data["member_id"])) { // has no tie, INSERT row
			
			$q = "insert into income_ties set member_id=".$cDB->EscTxt($data["member_id"]).",
				 tie_id=".$cDB->EscTxt($data["tie_id"]).", percent=".$cDB->EscTxt($data["amount"])."";
				
		}
		else { // has a tie, UPDATE row
			
				$q = "update income_ties set tie_id=".$cDB->EscTxt($data["tie_id"]).", percent=".$cDB->EscTxt($data["amount"])." where member_id=".$cDB->EscTxt($data["member_id"])."";
		}
		
		$result = $cDB->Query($q);
		
		if (!$result)
			return $lng_error_saving_income_share;
			
		return $lng_income_share_saved;
	}
	
	function deleteTie($member_id) {
		
		global $cDB, $lng_no_income_share_to_delete, $lng_error_deleting_income_share, $lng_income_share_deleted;
		
			if (!cIncomeTies::getTie($member_id)) { // has no tie to delete!
			
				return $lng_no_income_share_to_delete."!";
		}
		
		$q = "delete from income_ties where member_id=".$cDB->EscTxt($member_id)."";
		
		$result = $cDB->Query($q);
		
		if (!$result)
			return $lng_error_deleting_income_share;
		
		return $lng_income_share_deleted;
	}
	
}

$cUser = new cMember();
$cUser->RegisterWebUser();

?>
