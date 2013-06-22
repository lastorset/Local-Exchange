<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

include_once("class.category.php");
include_once("class.feedback.php");
include_once("class.state_address.php"); // added by ejkv

class cListing
{
	var $listing_id;
	var $member; // this will be an object of class cMember
	var $title;
	var $description;
	var $category; // this will be an object of class cCategory
	var $rate;
	var $status;
	var $posting_date; // the date a listing was created or last modified
	var $expire_date;
	var $reactivate_date;
	var $type;


	function cListing($member=null, $values=null) {
		if($member) {
			$this->member = $member;
			$this->title = $values['title'];
			$this->description = $values['description'];
			$this->rate = $values['rate'];
			$this->expire_date = $values['expire_date'];
			$this->type = $values['type'];
			$this->reactivate_date = null;
			$this->status = 'A';
			$this->category = new cCategory();
			$this->category->LoadCategory($values['category']);
		}
	}

	function TypeCode() {
		if($this->type == OFFER_LISTING)
			return OFFER_LISTING_CODE;
		else
			return WANT_LISTING_CODE;
	}

	function TypeDesc($type_code) {
		if($type_code == OFFER_LISTING_CODE)
			return OFFER_LISTING;
		else
			return WANT_LISTING;
	}

	function SaveNewListing() {
		global $cDB, $cErr;

		$insert = $cDB->Query("INSERT INTO ".DATABASE_LISTINGS." (title, description, category_code, member_id, rate, status, expire_date, reactivate_date, type) VALUES (". $cDB->EscTxt($this->title) .",". $cDB->EscTxt($this->description) .",". $cDB->EscTxt($this->category->id) .",". $cDB->EscTxt($this->member->member_id) .",". $cDB->EscTxt($this->rate) .",". $cDB->EscTxt($this->status) .",". $cDB->EscTxt($this->expire_date) .",". $cDB->EscTxt($this->reactivate_date) .",". $cDB->EscTxt($this->TypeCode()) .");");
		$this->listing_id = mysql_insert_id();

		return $insert;
	}

	function SaveListing($update_posting_date=true) {
		global $cDB, $cErr;

		if($update_posting_date) // changed posting date if update=true, due to the date a listing was modified - by ejkv
			$posting_date = ", posting_date='".date("Y-m-d h:i:s")."'"; // changed by ejkv
		else
			$posting_date = "";

		$update = $cDB->Query("UPDATE ".DATABASE_LISTINGS." SET title=". $cDB->EscTxt($this->title) .", description=". $cDB->EscTxt($this->description) .", category_code=". $cDB->EscTxt($this->category->id) .", rate=". $cDB->EscTxt($this->rate) .", status=". $cDB->EscTxt($this->status) .", expire_date=". $cDB->EscTxt($this->expire_date) .", reactivate_date=". $cDB->EscTxt($this->reactivate_date) . $posting_date ." WHERE listing_id=". $this->listing_id .";");

		return $update;
	}

	function DeleteListing($id) {
		global $cDB, $cErr;

		$query = $cDB->Query("DELETE FROM ". DATABASE_LISTINGS ." WHERE listing_id=". $cDB->EscTxt($id) .";");

		return mysql_affected_rows();
	}

	function LoadListing($id)
	{
		global $cDB, $cErr;

		// select all offer data and populate the variables
		$query = $cDB->Query("SELECT listing_id, title, description, category_code, member_id, rate, status, posting_date, expire_date, reactivate_date, type FROM ".DATABASE_LISTINGS." WHERE listing_id=".$cDB->EscTxt($id));

		if($row = mysql_fetch_array($query))
		{
			$this->listing_id=$row['listing_id'];
			$this->title=$row['title'];
			$this->description=$cDB->UnEscTxt($row['description']);
			$this->rate=$cDB->UnEscTxt($row['rate']);
			$this->status=$row['status'];
			$this->posting_date=$row['posting_date'];
			$this->expire_date=$row['expire_date'];
			$this->reactivate_date=$row['reactivate_date'];
			$this->type=$this->TypeDesc($row['type']);
			$this->category = new cCategory();
			$this->category->LoadCategory($row['category_code']);

			// load member associated with member_id
			$member_id=$row['member_id'];
			$this->member = new cMember;
			$this->member->LoadMember($member_id);
		}
		else
		{
			$cErr->Error(sprintf(_("There was an error accessing the listing with id %s. Please try again later."), $id));
			include("redirect.php");
		}

		$this->DeactivateReactivate();
	}

	/** For compatibility with existing links, allows loading a listing using the old primary key,
	 * (title, member ID, type). For simplicity, this just finds the listing ID and passes it to the
	 * regular LoadListing function, even though that means an additional DB query. */
	function LoadListingOldPK($title, $member_id, $type_code) {
		global $cDB, $cErr;

		$result = $cDB->Query("SELECT listing_id FROM ".DATABASE_LISTINGS." WHERE title=".$cDB->EscTxt($title)." AND member_id=" . $cDB->EscTxt($member_id) . " AND type=". $cDB->EscTxt($type_code));

		if($row = mysql_fetch_array($result))
			$this->LoadListing($row['listing_id']);
		else
		{
			$cErr->Error(_("There was an error accessing the")." ".$cDB->EscTxt($title)." "._("listing for")." ".$member_id.".  "._("Please try again later").".");
			include("redirect.php");
		}
	}

	static function HasDuplicateTitle($listing, $new_title) {
		global $cDB;

		$result = $cDB->Query("
			SELECT listing_id
			FROM ".DATABASE_LISTINGS."
			WHERE title=".$cDB->EscTxt($new_title)."
				AND member_id=" . $cDB->EscTxt($listing->member->member_id) . "
				AND type=". $cDB->EscTxt($listing->TypeCode()));

		if (($row = mysql_fetch_array($result))
			&& $row['listing_id'] != $listing->listing_id)
			return true;

		return false;
	}

	function DeactivateReactivate() {
		if($this->reactivate_date) {
			$reactivate_date = new cDateTime($this->reactivate_date);
			if ($this->status == INACTIVE and $reactivate_date->Timestamp() <= strtotime("now")) {
				$this->status = ACTIVE;
				$this->reactivate_date = null;
				$this->SaveListing();
			}
		}
		if($this->expire_date) {
			$expire_date = new cDateTime($this->expire_date);
			if ($this->status <> EXPIRED and $expire_date->Timestamp() <= strtotime("now")) {
				$this->status = EXPIRED;
				$this->SaveListing();
			}
		}
	}

	function ShowListing()
	{
		$output = $this->type . "ed Data:<BR>";
		$output .= $this->title . ", " . $this->description . ", " . $this->category->id . ", " . $this->member->member_id . ", " . $this->rate . ", " . $this->status . ", " . $this->posting_date . ", " . $this->expire_date . ", " . $this->reactivate_date . "<BR><BR>";
		$output .= $this->member->ShowMember();

		return $output;
	}

	function DisplayListing()
	{
		$output = "";
		if($this->description != "")
			$output .= "<STRONG>"._("Description").":</STRONG> ". $this->description ."<BR>";
		if($this->rate != "" && SHOW_RATE_ON_LISTINGS == true)
			$output .= "<STRONG>"._("Rate").":</STRONG> ". $this->rate ."<BR>";
		return $output;
	}

	function GetURL()
	{
		return "http://".HTTP_BASE."/listing_detail.php?id=". $this->listing_id;
	}
}

class cListingGroup
{
	var $title;
	var $listing;  // this will be an array of objects of type cListing
	var $num_listings;  // number of active offers
	var $type;
	var $type_code;

	function cListingGroup($type) {
		$this->type = $type;
		if($type == OFFER_LISTING)
			$this->type_code = OFFER_LISTING_CODE;
		else
			$this->type_code = WANT_LISTING_CODE;
	}

	function InactivateAll($reactivate_date) {
		global $cErr;

		if (!isset($this->listing))
			return true;

		foreach($this->listing as $listing)	{
			$current_reactivate = new cDateTime($listing->reactivate_date, false);
			if(($listing->reactivate_date == null or $current_reactivate->Timestamp() < $reactivate_date->Timestamp()) and $listing->status != EXPIRED) {
				$listing->reactivate_date = $reactivate_date->MySQLDate();
				$listing->status = INACTIVE;
				$success = $listing->SaveListing();

				if(!$success)
					$cErr->Error(_("Could not inactivate listing").": '".$listing->title."'");
			}
		}
		return true;
	}

	function ExpireAll($expire_date) {
		global $cErr;

		if (!isset($this->listing))
			return true;

		foreach($this->listing as $listing)	{
			$listing->expire_date = $expire_date->MySQLDate();
			$success = $listing->SaveListing(false);

			if(!$success)
				$cErr->Error(_("Could not expire listing").": '".$listing->title."'");
		}
		return true;
	}

	function LoadNearbyListingsQuery($lat, $lon, $radius)
	{
		// Thanks to http://www.movable-type.co.uk/scripts/latlong-db.html (licensed under CC-BY)
		global $cDB, $cErr;

		// Radius of Earth
		$R = 6371;

		// first-cut bounding box (in degrees)
		$maxLat = $lat + rad2deg($radius/$R);
		$minLat = $lat - rad2deg($radius/$R);
		// compensate for degrees longitude getting smaller with increasing latitude
		$maxLon = $lon + rad2deg($radius/$R/cos(deg2rad($lat)));
		$minLon = $lon - rad2deg($radius/$R/cos(deg2rad($lat)));

		$latitude = deg2rad($lat);
		$longitude = deg2rad($lon);

		$c = get_defined_constants();
		$query = <<<SQL
			SELECT
				listing_id, distance
			FROM (
				Select listing_id, latitude, longitude,
					acos(sin($latitude)*sin(radians(latitude)) + cos($latitude)*cos(radians(latitude))*cos(radians(longitude)-$longitude)) * $R AS distance
				FROM {$c['DATABASE_PERSONS']} p
					JOIN {$c['DATABASE_MEMBERS']} m ON p.member_id = m.member_id
					JOIN {$c['DATABASE_LISTINGS']} l ON m.member_id = l.member_id
				WHERE
					latitude BETWEEN $minLat AND $maxLat
					AND longitude BETWEEN $minLon AND $maxLon
				) AS FirstCut
			WHERE distance < $radius
			ORDER BY distance
SQL;
		return $query;
	}

	/** When origin and radius are given, a radial search is performed.
	 *
	 * @param origin array(latitude, longitude) to search from.
	 * @param radius how far to search (in km).
	 */
	function LoadListingGroup($title=null, $category=null, $member_id=null, $since=null, $include_expired=true, $origin=null, $radius=null, $include_mine=true)
	{
		global $cDB, $cErr, $cUser;

		if($title == null)
			$this->title = "%";
		else
			$this->title = $title;

		if($category == null)
			$category = "%";

		if($member_id == null)
			$member_id = "%";

		if($since == null)
			$since = "19990101000000";

		if($include_expired)
			$expired = "";
		else
			$expired = " AND expire_date is null";

		if(!$include_mine && $cUser->IsLoggedOn())
			$exclude_mine = "AND member_id NOT LIKE ". $cDB->EscTxt($cUser->member_id);
		else
			$exclude_mine = "";

		if(is_array($origin) && $radius > 0) {
			$nearby_clause = "JOIN ("
				. $this->LoadNearbyListingsQuery($origin[0], $origin[1], $radius)
				. ") AS nearby ON nearby.listing_id = l.listing_id";
			$distance_field = ", distance";
		}
		else {
			$nearby_clause = "";
			$order_by_distance = "";
		}

		//select all the member_ids for this $title
		$query = $cDB->Query(
			"SELECT l.listing_id $distance_field
			FROM ".DATABASE_LISTINGS." l
				JOIN ".DATABASE_CATEGORIES." c ON l.category_code = c.category_id
				$nearby_clause
			WHERE title LIKE ". $cDB->EscTxt($this->title) ."
				AND c.category_id LIKE ". $cDB->EscTxt($category) ."
				AND type=". $cDB->EscTxt($this->type_code) ."
				AND member_id LIKE ". $cDB->EscTxt($member_id) ."
				$exclude_mine
				AND posting_date >= '". $since ."'". $expired ."
			ORDER BY c.description $distance_field, title, member_id;");

		return $this->InstantiateListings($query);
	}

	private function InstantiateListings($cursor)
	{
		// instantiate new cOffer objects and load them
		$i = 0;
		$this->num_listings = 0;

		while($row = mysql_fetch_array($cursor))
		{
			$this->listing[$i] = new cListing;
			$this->listing[$i]->LoadListing($row['listing_id']);
			if($this->listing[$i]->status == 'A')
			{
				$this->num_listings += 1;
			}
			if(isset($row['distance']))
				// Only used for 'nearby' queries
				$this->listing[$i]->distance = sprintf("%.1f", $row['distance']);
			$i += 1;
		}

		if($i == 0) {
			return false;
		}

		return true;
	}

	function DisplayListingGroup($show_ids=false, $active_only=true, $self=true)
	{
		/*[chris]*/ // made some changes to way listings displayed, for better or for worse...

		global $cUser,$cDB,$site_settings;

		$output = "";
		$current_cat = "";

		if(isset($this->listing)) {
			foreach($this->listing as $listing) {

				if($active_only and $listing->status != ACTIVE)
					continue; // Skip inactive items

				if($current_cat != $listing->category->id) {
					$output .= "<P><STRONG>" . $listing->category->description . "</STRONG><P>";
				}
				else
					$output .= "<br>";

				if ($listing->description != "")
					$details = " ".  $listing->description; // RF - simple space is fine
				else
					//$details = "<em>Not supplied</em>"; // Better than leaving a blank space?
					$details = " --- "; // if no details, fill with --- changed by ejkv

				$query = $cDB->Query("SELECT * FROM person WHERE member_id  = ". $cDB->EscTxt($listing->member->member_id) . " limit 0,1;");

				$row = mysql_fetch_array($query);

				// a small change to the way member info is displayed i.e. (joe bloggs - 212)
				$memInfo = " (<em>".stripslashes($row["first_name"])." ".stripslashes($row["mid_name"])." ".stripslashes($row["last_name"])."</em> - <a href=http://". HTTP_BASE ."/member_summary.php?member_id=".$listing->member->member_id.">". $listing->member->member_id ."</a>)"; // added mid_name, moved ")", removed </center> - by ejkv

				$output .= "<A HREF=http://".HTTP_BASE."/listing_detail.php?id=". $listing->listing_id .">" . $listing->title ."</A>";
				if (isset($listing->distance))
					$output .= " <SPAN class=distance>(". $listing->distance ." km)</SPAN>";
				$output .= "<br>". $details;

				// Rate
				if (SHOW_RATE_ON_LISTINGS==true && $listing->rate)
					$output .= " (".$listing->rate." ".strtolower($site_settings->getUnitString()).")<br>"; // line-break added by ejkv
				else // added by ejkv
					$output .= "<br>"; // line-break added by ejkv

				if ($show_ids)
					$output .= "$memInfo"."<br>"; // removed <FONT SIZE=2> .. </FONT>, line-break added by ejkv

				// Do we want to display the PostCode alongside the listing?
				if (SHOW_POSTCODE_ON_LISTINGS==true && $cUser->IsLoggedOn()) { // Only show postcode to logged i members

					$pcode = stripslashes($row["address_post_code"]);
					$pcode = str_replace(array("\n", "\r", "\t", " ", "\o", "\xOB"), '', $pcode); // Remove any white spaces as these will screw up our character count below

					$short_pcode = '';

					// Only display X number of characters from the postcode
					for ($i=0;$i<(NUM_CHARS_POSTCODE_SHOW_ON_LISTINGS+1);$i++) {

						$short_pcode .= $pcode{$i};

					}

				$states = new cStateList; // added by ejkv
				$state_list = $states->MakeStateArray(); // added by ejkv
				$state_list[0]="---"; // added by ejkv

				$output .= " ".$short_pcode." - ".$state_list[$row["address_state_code"]]."<br>"; // added address_state_code and line-break by ejkv
				}

				$current_cat = $listing->category->id;
			}
		}

		if($output == "")
			$output = _("No listings found.");


		return $output;
	}

}

class cTitleList  // This class circumvents the cListing class for performance reasons
{
	var $type;
	var $type_code;  // TODO: 'type' needs to be its own class which would include 'type_code'
	var $items_per_page;  // Not using yet...
	var $current_page;   // Not using yet...

	function cTitleList($type) {
		$this->type = $type;
		if($type == OFFER_LISTING)
			$this->type_code = OFFER_LISTING_CODE;
		else
			$this->type_code = WANT_LISTING_CODE;
	}

	/// Returns an associative array between listing IDs and titles.
	function MakeTitleArray($member_id="%") {
		global $cDB, $cErr;

		$query = $cDB->Query("SELECT DISTINCT listing_id, title FROM ".DATABASE_LISTINGS." WHERE member_id LIKE ". $cDB->EscTxt($member_id) . " AND type=". $cDB->EscTxt($this->type_code) .";");

		$titles = array();

		while($row = mysql_fetch_array($query))
			$titles[$row['listing_id']]= $cDB->UnEscTxt($row['title']);

		return $titles;
	}

	function DisplayMemberListings($member) {
		global $cDB;

		$query = $cDB->Query("SELECT listing_id, title FROM ".DATABASE_LISTINGS." WHERE member_id=". $cDB->EscTxt($member->member_id) ." AND type=". $cDB->EscTxt($this->type_code) ." ORDER BY title;");

		$output = "";
		while($row = mysql_fetch_array($query)) {
			$output .= "<A HREF=listing_edit.php?id=" . $row['listing_id'] ."&mode=" . $_REQUEST["mode"] ."><FONT SIZE=2>". $cDB->UnEscTxt($row['title']) ."</FONT></A><BR>";
		}

		return $output;
	}
}
?>
