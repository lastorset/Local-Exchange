<?php

class cPerson
{
	var $person_id;			
	var $member_id;
	var $primary_member;
	var $directory_list;
	var $first_name;
	var $last_name;
	var $mid_name;
	var $dob;
	var $mother_mn;
	var $email;
	var $phone1_area;
	var $phone1_number;
	var $phone1_ext;
	var $phone2_area;
	var $phone2_number;
	var $phone2_ext;
	var $fax_area;
	var $fax_number;
	var $fax_ext;
	var $address_street1;
	var $address_street2;
	var $address_city;
	var $address_state_code;
	var $address_post_code;
	var $address_country;

	function cPerson($values=null) {
		if($values) {
			$this->member_id = $values['member_id'];
			$this->primary_member = $values['primary_member'];
			$this->directory_list = $values['directory_list'];
			$this->first_name = $values['first_name'];
			$this->last_name = $values['last_name'];
			$this->mid_name = $values['mid_name'];
			$this->dob = $values['dob'];
			$this->mother_mn = $values['mother_mn'];
			$this->email = $values['email'];
			$this->phone1_area = $values['phone1_area'];
			$this->phone1_number = $values['phone1_number'];
			$this->phone1_ext = $values['phone1_ext'];
			$this->phone2_area = $values['phone2_area'];
			$this->phone2_number = $values['phone2_number'];
			$this->phone2_ext = $values['phone2_ext'];
			$this->fax_area = $values['fax_area'];
			$this->fax_number = $values['fax_number'];
			$this->fax_ext = $values['fax_ext'];
			$this->address_street1 = $values['address_street1'];
			$this->address_street2 = $values['address_street2'];
			$this->address_city = $values['address_city'];
			$this->address_state_code = $values['address_state_code'];
			$this->address_post_code = $values['address_post_code'];
			$this->address_country = $values['address_country'];			
			
			/*[chris] store the social networking vars */
				$this->age = $values['age'];
				$this->sex = $values['sex'];
				$this->about_me = $values['about_me'];
		
		}
	}

	function SaveNewPerson() {
		global $cDB, $cErr, $lng_could_not_save_new_person_same_name;

		$duplicate_exists = $cDB->Query("SELECT NULL FROM ".DATABASE_PERSONS." WHERE member_id=". $cDB->EscTxt($this->member_id) ." AND first_name". $cDB->EscTxt2($this->first_name) ." AND last_name". $cDB->EscTxt2($this->last_name) ." AND mother_mn". $cDB->EscTxt2($this->mother_mn) ." AND mid_name". $cDB->EscTxt2($this->mid_name) ." AND dob". $cDB->EscTxt2($this->dob) .";");
		
		if($row = mysql_fetch_array($duplicate_exists)) {
			$cErr->Error($lng_could_not_save_new_person_same_name);
			include("redirect.php");
		}
	
		$insert = $cDB->Query("INSERT INTO ".DATABASE_PERSONS." (member_id, primary_member, directory_list, first_name, last_name, mid_name, dob, mother_mn, email, phone1_area, phone1_number, phone1_ext, phone2_area, phone2_number, phone2_ext, fax_area, fax_number, fax_ext, address_street1, address_street2, address_city, address_state_code, address_post_code, address_country) VALUES (". $cDB->EscTxt($this->member_id) .",". $cDB->EscTxt($this->primary_member) .",". $cDB->EscTxt($this->directory_list) .",". $cDB->EscTxt($this->first_name) .",". $cDB->EscTxt($this->last_name) .",". $cDB->EscTxt($this->mid_name) .",". $cDB->EscTxt($this->dob) .",". $cDB->EscTxt($this->mother_mn) .",". $cDB->EscTxt($this->email) .",". $cDB->EscTxt($this->phone1_area) .",". $cDB->EscTxt($this->phone1_number) .",". $cDB->EscTxt($this->phone1_ext) .",". $cDB->EscTxt($this->phone2_area) .",". $cDB->EscTxt($this->phone2_number) .",". $cDB->EscTxt($this->phone2_ext) .",". $cDB->EscTxt($this->fax_area) .",". $cDB->EscTxt($this->fax_number) .",". $cDB->EscTxt($this->fax_ext) .",". $cDB->EscTxt($this->address_street1) .",". $cDB->EscTxt($this->address_street2) .",". $cDB->EscTxt($this->address_city) .",". $cDB->EscTxt($this->address_state_code) .",". $cDB->EscTxt($this->address_post_code) .",". $cDB->EscTxt($this->address_country).");");
		
		return $insert;
	}
			
	function SavePerson() {
		global $cDB, $cErr, $lng_could_not_save_changes, $lng_please_try_again_later;
		
		/*[chris]*/ // Added store personal profile data
		$update = $cDB->Query("UPDATE ". DATABASE_PERSONS ." SET member_id=". $cDB->EscTxt($this->member_id) .", primary_member=". $cDB->EscTxt($this->primary_member) .", directory_list=". $cDB->EscTxt($this->directory_list) .", first_name=". $cDB->EscTxt($this->first_name) .", last_name=". $cDB->EscTxt($this->last_name) .", mid_name=". $cDB->EscTxt($this->mid_name) .", dob=". $cDB->EscTxt($this->dob) .", mother_mn=". $cDB->EscTxt($this->mother_mn) .", email=". $cDB->EscTxt($this->email) .", phone1_area=". $cDB->EscTxt($this->phone1_area) .", phone1_number=". $cDB->EscTxt($this->phone1_number) .", phone1_ext=". $cDB->EscTxt($this->phone1_ext) .", phone2_area=". $cDB->EscTxt($this->phone2_area) .", phone2_number=". $cDB->EscTxt($this->phone2_number) .", phone2_ext=". $cDB->EscTxt($this->phone2_ext) .", fax_area=". $cDB->EscTxt($this->fax_area) .", fax_number=". $cDB->EscTxt($this->fax_number) .", fax_ext=". $cDB->EscTxt($this->fax_ext) .", address_street1=". $cDB->EscTxt($this->address_street1) .", address_street2=". $cDB->EscTxt($this->address_street2) .", address_city=". $cDB->EscTxt($this->address_city) .", address_state_code=". $cDB->EscTxt($this->address_state_code) .", address_post_code=". $cDB->EscTxt($this->address_post_code) .", address_country=". $cDB->EscTxt($this->address_country).", about_me=". $cDB->EscTxt($this->about_me) .","."age=".  $cDB->EscTxt($this->age) .",". "sex=". $cDB->EscTxt($this->sex) . " WHERE person_id=". $cDB->EscTxt($this->person_id) .";");

		if(!$update)
			$cErr->Error($lng_could_not_save_changes." '". $this->first_name ." ". $this->mid_name ." ". $this->last_name ."'. ".$lng_please_try_again_later."."); // added mid_name by ejkv	
			
		return $update;
	}

	function LoadPerson($who)
	{
		global $cDB, $cErr, $lng_error_access_person, $lng_please_try_again_later;
		
		/*[chris]*/ // Added fetch personal profile data
		$query = $cDB->Query("SELECT member_id, primary_member, directory_list, first_name, last_name, mid_name, dob, mother_mn, email, phone1_area, phone1_number, phone1_ext, phone2_area, phone2_number, phone2_ext, fax_area, fax_number, fax_ext, address_street1, address_street2, address_city, address_state_code, address_post_code, address_country, about_me, age, sex FROM ".DATABASE_PERSONS." WHERE person_id=". $cDB->EscTxt($who));
		
		if($row = mysql_fetch_array($query))
		{
			$this->person_id=$who;	
			$this->member_id=$row[0];
			$this->primary_member=$row[1];
			$this->directory_list=$row[2];
			$this->first_name=$cDB->UnEscTxt($row[3]);
			$this->last_name=$cDB->UnEscTxt($row[4]);
			$this->mid_name=$cDB->UnEscTxt($row[5]);
			$this->dob=$row[6];
			$this->mother_mn=$cDB->UnEscTxt($row[7]);
			$this->email=$row[8];
			$this->phone1_area=$row[9];
			$this->phone1_number=$row[10];
			$this->phone1_ext=$row[11];
			$this->phone2_area=$row[12];
			$this->phone2_number=$row[13];
			$this->phone2_ext=$row[14];
			$this->fax_area=$row[15];
			$this->fax_number=$row[16];
			$this->fax_ext=$row[17];
			$this->address_street1=$cDB->UnEscTxt($row[18]);
			$this->address_street2=$cDB->UnEscTxt($row[19]);
			$this->address_city=$row[20];
			$this->address_state_code=$row[21];
			$this->address_post_code=$row[22];
			$this->address_country=$row[23];		
			
			/*[chris]*/
			
			$this->about_me=$row[24];
			$this->age=$row[25];
			$this->sex=$row[26];		
		}
		else 
		{
			$cErr->Error($lng_error_access_person." (".$who.").  ".$lng_please_try_again_later.".");
			include("redirect.php");
		}		
	}		
	
	function DeletePerson() {
		global $cDB, $cErr, $lng_cannot_delete_primary_member, $lng_error_deleting_joint_member, $lng_please_try_again_later;
		
		if($this->primary_member == 'Y') {
			$cErr->Error($lng_cannot_delete_primary_member."!");	
			return false;
		} 
		
		$delete = $cDB->Query("DELETE FROM ".DATABASE_PERSONS." WHERE person_id=". $cDB->EscTxt($this->person_id));
		
		unset($this->person_id);
		
		if (mysql_affected_rows() == 1) {
			return true;
		} else {
			$cErr->Error($lng_error_deleting_joint_member, " ".$lng_please_try_again_later."." );
		}
		
	}
							
	function ShowPerson()
	{
		$output = $this->person_id . ", " . $this->member_id . ", " . $this->primary_member . ", " . $this->directory_list . ", " . $this->first_name . ", " . $this->mid_name . ", " . $this->last_name . ", " . $this->dob . ", " . $this->mother_mn . ", " . $this->email . ", " . $this->phone1_area . ", " . $this->phone1_number . ", " . $this->phone1_ext . ", " . $this->phone2_area . ", " . $this->phone2_number . ", " . $this->phone2_ext . ", " . $this->fax_area . ", " . $this->fax_number . ", " . $this->fax_ext . ", " . $this->address_street1 . ", " . $this->address_street2 . ", " . $this->address_city . ", " . $this->address_state_code . ", " . $this->address_post_code . ", " . $this->address_country; // swapped mid_name and last_name by ejkv
		
		return $output;
	}

	function Name() {
		return $this->first_name . " " .$this->mid_name . " " .$this->last_name; // added mid_name by ejkv
	}
			
	function DisplayPhone($type)
	{
		global $cErr, $lng_phone_type_not_exist;

		switch ($type)
		{
			case "1":
				$phone_area = $this->phone1_area;
				$phone_number = $this->phone1_number;
				$phone_ext = $this->phone1_ext;
				break;
			case "2":
				$phone_area = $this->phone2_area;
				$phone_number = $this->phone2_number;
				$phone_ext = $this->phone2_ext;
				break;
			case "fax":
				$phone_area = $this->fax_area;
				$phone_number = $this->fax_number;
				$phone_ext = $this->fax_ext;
				break;								
			default:
				$cErr->Error($lng_phone_type_not_exist);
				return "ERROR";
		}
/*		
		if($phone_number != "") {
			if($phone_area != "" and $phone_area != DEFAULT_PHONE_AREA)
				$phone = "(". $phone_area .") ";
			else
				$phone = "";
				
			$phone .= substr($phone_number,0,3) ."-". substr($phone_number,3,4);
			if($phone_ext !="")
				$phone .= " Ext. ". $phone_ext;
		} else {
			$phone = "";
		}
*/
        $phone = $phone_number;
		
		return $phone;
	}
}

// TODO: cPerson should use this class instead of a text field
class cPhone {
	var $area;
	var $prefix;
	var $suffix;
	var $ext;
	
	function cPhone($phone_str=null) { // this constructor attempts to break down free-form phone #s
		if($phone_str) {						// TODO: Use reg expressions to shorten this thing
			$ext = "";
			$phone_str = strtolower($phone_str);
			if ($loc = strpos($phone_str, "x")) {
				$ext = substr($phone_str, $loc+1, 10);
				$phone_str = substr($phone_str, 0, $loc); // strip extension off the main string
				$ext = ereg_replace("t","",$ext);
				$ext = ereg_replace("\.","",$ext);
				$ext = ereg_replace(" ","",$ext);
				if(!is_numeric($ext))
					$ext = "";
			}
			$phone_str = ereg_replace("\(","",$phone_str);
			$phone_str = ereg_replace("\)","",$phone_str);
			$phone_str = ereg_replace("-","",$phone_str);
			$phone_str = ereg_replace("\.","",$phone_str);
			$phone_str = ereg_replace(" ","",$phone_str);
			$phone_str = ereg_replace("e","",$phone_str);


			if(strlen($phone_str) == 7) {
				$this->area = DEFAULT_PHONE_AREA;
				$this->prefix = substr($phone_str,0,3);
				$this->suffix = substr($phone_str,3,4);
				$this->ext = $ext;
			} elseif (strlen($phone_str) == 10) {
				$this->area = substr($phone_str,0,3);
				$this->prefix = substr($phone_str,3,3);
				$this->suffix = substr($phone_str,6,4);
				$this->ext = $ext;				
			} else {
				return false;			
			}
		}
	}
	
	function TenDigits() {
		return $this->area . $this->prefix . $this->suffix;
	}
	
	function SevenDigits() {
		return $this->prefix . $this->suffix;
	}
	
}


/**
 * Temporary phone class for UK.  This is to be used in place of all instances
 * of "cPhone".
 */
class cPhone_uk {
	var $area;
	var $prefix;
	var $suffix;
	var $ext;
    var $number;

	// this constructor attempts to break down free-form phone #s
	function cPhone_uk($phone_str=null) { 
        // TODO: Use reg expressions to shorten this thing
		if( !empty($phone_str)) {						
            $tmp = preg_replace("/[^\d]/", "", $phone_str);

            // Most UK phone numbers when written without the areacode are 8
            // digits.
            if(strlen($tmp) >= 8) { 
                $this->number = $phone_str;
                $this->ext = "";
                $this->area = DEFAULT_PHONE_AREA;

                // We are not using them.  But they are checked in
                // verify_phone_number()
                $this->prefix = $this->suffix = true;
            }
            else {
                return false;
            }
        }
	}
	
	function TenDigits() {
		return $this->number;
	}
	
	function SevenDigits() {
		return $this->number;
	}
}

?>
