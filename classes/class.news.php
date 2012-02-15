<?php

class cNews {
	var $news_id;
	var $title;
	var $description;
	var $expire_date;
	var $sequence;

	function cNews ($title=null, $description=null, $expire_date=null, $sequence=null) {
		if($title) {
			$this->title = $title;
			$this->description = $description;
			$this->expire_date = new cDateTime($expire_date);
			$this->sequence = $sequence;
		}
	}
	
	function SaveNewNews () {
		global $cDB, $cErr;
		
		$insert = $cDB->Query("INSERT INTO ". DATABASE_NEWS ." (title, description, expire_date, sequence) VALUES (".$cDB->EscTxt($this->title) .", ". $cDB->EscTxt($this->description) .", '". $this->expire_date->MySQLDate() ."', ". $this->sequence .");");

		if(mysql_affected_rows() == 1) {
			$this->news_id = mysql_insert_id();		
			return true;
		} else {
			$cErr->Error(_("Could not save news item."));
			return false;
		}		
	}
	
	function SaveNews () {
		global $cDB, $cErr;			
		
		$update = $cDB->Query("UPDATE ".DATABASE_NEWS." SET title=". $cDB->EscTxt($this->title) .", description=". $cDB->EscTxt($this->description) .", expire_date='". $this->expire_date->MySQLDate(). "', sequence=". $this->sequence ." WHERE news_id=". $cDB->EscTxt($this->news_id) .";");

		return $update;	
	}
	
	function LoadNews ($news_id) {
		global $cDB, $cErr;
		
//		$this->ExpireNews();
				
		$query = $cDB->Query("SELECT title, description, expire_date, sequence FROM ".DATABASE_NEWS." WHERE  news_id=". $cDB->EscTxt($news_id) .";");
		
		if($row = mysql_fetch_array($query)) {		
			$this->news_id = $news_id;
			$this->title = $cDB->UnEscTxt($row[0]);
			$this->description = $cDB->UnEscTxt($row[1]);		
			$this->expire_date = new cDateTime($row[2]);
			$this->sequence = $row[3];
			return true;
		} else {
			$cErr->Error(_("There was an error accessing the news table.  Please try again later."));
			include("redirect.php");
		}
		
	}

	function DisplayNews () {
		$output = "<H2>". $this->title ."</H2><P>";
		$output .= $this->description ."<P>";
		return $output;
	}
}

class cNewsGroup {
	var $newslist;  // will be an array of cNews objects
	var $max_seq;
	
	function LoadNewsGroup () {
		global $cDB, $cErr;
		
		$this->DeleteOldNews();
	
		$query = $cDB->Query("SELECT news_id FROM ".DATABASE_NEWS." ORDER BY sequence DESC;");
		
		$i = 0;				
		while($row = mysql_fetch_array($query)) {
			$this->newslist[$i] = new cNews;			
			$this->newslist[$i]->LoadNews($row[0]);
			$i += 1;
		}

		if($i == 0)
			return false;
		else
			$this->max_seq = $this->newslist[0]->sequence;
			return true;
	}
	
	function DisplayNewsGroup () {
		$output = "";
		if(!isset($this->newslist))
			return $output;
		
		foreach($this->newslist as $news) {
			if($news->expire_date->Timestamp() > strtotime("yesterday"))
				$output .= $news->DisplayNews() . "<BR>";
		}
		return $output;
	}
	
	function MakeNewsArray() {
		if (!isset($this->newslist))
			return false;
			
		foreach($this->newslist as $news) {
			$list[$news->news_id] = $news->title;
		}
		return $list;
	}

	function MakeNewsSeqArray($current_seq=null) { // TODO: OK, this is just ugly...	   // Should use 1,2,3,4... and reorder
		$prior_seq = 0;			              	   // all each time.
		$prior_title = _("At top of list");
		$lead_txt = "";
		$follow_txt = "";
		
		if (!isset($this->newslist))
			return array("100"=>$prior_title);
		
		foreach($this->newslist as $news) {
			if ($current_seq == $news->sequence) {
				$list[$this->CutZero($current_seq)] = $lead_txt. $prior_title . $follow_txt;
			} elseif ($prior_seq != $current_seq or $current_seq == null) {
				if ($prior_seq == 0)
					$seq = $this->GetNewSeqNum();
				else
					$seq = $this->GetSeqNumAfter($prior_seq);
					
				$list[$seq] = $lead_txt. $prior_title .$follow_txt;
			}
			
			$prior_seq = $news->sequence;
			$saved_title = $prior_title;
			$prior_title = $news->title;
			$lead_txt = _("After")." '";
			$follow_txt = "'";
		}
		
		if ($current_seq != $news->sequence) {
			if ($prior_seq == 0)
				$seq = $this->GetNewSeqNum();
			else
				$seq = $this->GetSeqNumAfter($prior_seq);
		
			$list[$seq] = $lead_txt . $prior_title . $follow_txt;
		}
		
		return $list;	
	}	
	
	function CutZero($value) {
   	return preg_replace("/(\.\d+?)0+$/", "$1", $value)*1;
	}

	function DeleteOldNews () {
		global $cDB;
		
		$future_date = new cDateTime("-14 days");
		
		$delete = $cDB->Query("DELETE FROM ".DATABASE_NEWS." WHERE expire_date < '". $future_date->MySQLDate() ."';");
		return $delete;
	}
	
	function GetSeqNumAfter ($high) {
		$low = 0;
		foreach($this->newslist as $news) {
			if ($news->sequence < $high) {
				$low = $news->sequence;
				break;
			} 
		}
		
		return $low + (($high - $low) / 2);
	}
	
	function GetNewSeqNum () {
		return round($this->max_seq + 100, -2);
	}
}

?>
