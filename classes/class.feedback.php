<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

require_once("class.trade.php");

class cFeedback {	
	var $feedback_id;
	var $feedback_date;
	var $status;
	var $member_author;  // will be an object of class cMember
	var $member_about;	// will be an object of class cMember
	var $trade_id;
	var $rating;
	var $comment;
	var $rebuttals;		// will be an object of class cRebuttalGroup, if rebuttals exist
	var $context;			// indicates whether the author of this feedback was the BUYER or SELLER
	var $category;			// category of the associated trade
		
	function cFeedback ($member_id_author=null, $member_id_about=null, $context=null, $category=null, $trade_id=null, $rating=null, $comment=null) { // TODO: derive context & category
		if($member_id_author) {												// rather than passing them
			$this->status = ACTIVE;
			$this->member_author = new cMember();
			$this->member_author->LoadMember($member_id_author);
			$this->member_about = new cMember();
			$this->member_about->LoadMember($member_id_about);
			$this->trade_id = $trade_id;
			$this->rating = $rating;
			$this->comment = $comment;
			$this->context = $context;
			$this->category = new cCategory();
			$this->category->LoadCategory($category);
		}
	}
	
/*	function VerifyTradeMembers() { // Prevent accidental or malicious entry of feedback in which
		global $cErr;					  // seller and buyer do not match up with the recorded trade.
		
		if ($this->member_about->member_id == $this->trade->member_from->member_id) {
			if ($this->member_author->member_id == $this->trade->member_to->member_id)
				return true;
		} elseif ($this->member_about->member_id == $this->trade->member_to->member_id) {
			if ($this->member_author->member_id == $this->trade->member_from->member_id)
				return true;
		} 
		
		$cErr->Error("Members do not match the trade selected.");
		include("redirect.php");	
	} */
	
	function SaveFeedback () {
		global $cDB, $cErr;
		
//		$this->VerifyTradeMembers();
		if($this->FindTradeFeedback($this->trade_id, $this->member_author->member_id)) {
			$cErr->Error(_("Cannot create duplicate feedback."));
			return false;
		}
		
		$insert = $cDB->Query("INSERT INTO ". DATABASE_FEEDBACK ."(feedback_date, status, member_id_author, member_id_about, trade_id, rating, comment) VALUES (now(), ". $cDB->EscTxt($this->status) .", ". $cDB->EscTxt($this->member_author->member_id) .", ". $cDB->EscTxt($this->member_about->member_id) .", ". $cDB->EscTxt($this->trade_id) .", ". $cDB->EscTxt($this->rating) .", ". $cDB->EscTxt($this->comment) .");");

		if(mysql_affected_rows() == 1) {
			$this->feedback_id = mysql_insert_id();	
			$query = $cDB->Query("SELECT feedback_date from ". DATABASE_FEEDBACK ." WHERE feedback_id=". $this->feedback_id .";");
			$row = mysql_fetch_array($query);
			$this->feedback_date = $row[0];	
			return true;
		} else {
			return false;
		}	
	}
	
	function LoadFeedback ($feedback_id) {
		global $cDB, $cErr;
		
		$query = $cDB->Query("SELECT feedback_date, ".DATABASE_FEEDBACK.".status, member_id_author, member_id_about, ".DATABASE_FEEDBACK.".trade_id, rating, comment, member_id_from, category FROM ".DATABASE_FEEDBACK.",". DATABASE_TRADES ." WHERE ".DATABASE_FEEDBACK.".trade_id=". DATABASE_TRADES .".trade_id AND feedback_id=". $cDB->EscTxt($feedback_id) .";");
		
		if($row = mysql_fetch_array($query)) {		
			$this->feedback_id = $feedback_id;		
			$this->feedback_date = new cDateTime($row[0]);
			$this->status = $row[1];
			$this->member_author = new cMember; 
			$this->member_author->LoadMember($row[2]);
			$this->member_about = new cMember;
			$this->member_about->LoadMember($row[3]);
			$this->trade_id = $row[4];
			$this->rating = $row[5];
			$this->comment = $cDB->UnEscTxt($row[6]);
			if($row[7] == $row[3])
				$this->context = BUYER;
			else
				$this->context = SELLER;
				
			$this->category = new cCategory();
			$this->category->LoadCategory($row[8]);	
			$rebuttal_group = new cFeedbackRebuttalGroup();
			if($rebuttal_group->LoadRebuttalGroup($feedback_id))
				$this->rebuttals = $rebuttal_group;
			return true;
		} else {
			$cErr->Error(_("There was an error accessing the feedback table").".  "._("Please try again later").".");
			include("redirect.php");
		}		
	}

	function FindTradeFeedback ($trade_id, $member_id) {
		global $cDB;
		$query = $cDB->Query("SELECT feedback_id FROM ". DATABASE_FEEDBACK ." WHERE trade_id=". $cDB->EscTxt($trade_id) ." AND member_id_author=". $cDB->EscTxt($member_id) .";");
		
		if($row = mysql_fetch_array($query))
			return $row[0];
		else
			return false;
	}
	
	function DisplayFeedback () {
		return $this->RatingText() . "<BR>" . $this->feedback_date->StandardDate(). "<BR>". $this->Context() . "<BR>". $this->member_author->PrimaryName() ." (" . $this->member_author->member_id . ")" . "<BR>" . $this->category->description . "<BR>" . $this->comment;
	}
	
	function RatingText () {
		if ($this->rating == POSITIVE)
			return _("Positive"); // changed by ejkv
		elseif ($this->rating == NEGATIVE)
			return _("Negative"); // changed by ejkv
		else
			return _("Neutral"); // changed by ejkv
	}	
	
	function Context () {
		if ($this->context == SELLER)
			return _("Seller"); // changed by ejkv
		else
			return _("Buyer"); // changed by ejkv
	}
}
	
class cFeedbackGroup {
	var $feedback;		// will be an array of cFeedback objects
	var $member_id;
	var $context;		// Buyer or Seller or Both
	var $since_date;
	var $num_positive=0;
	var $num_negative=0;
	var $num_neutral=0;
	
	function LoadFeedbackGroup ($member_id, $context=null, $since_date=LONG_LONG_AGO) {
		global $cDB, $cErr;
				
		$this->member_id = $member_id;
		$this->since_date = new cDateTime($since_date);
				
		$query = "SELECT feedback_id FROM ".DATABASE_FEEDBACK;
		
		if($context == BUYER)
			$query .= ", ". DATABASE_TRADES ." WHERE member_id_to=member_id_about AND";
		elseif ($context == SELLER) 
			$query .= ", ". DATABASE_TRADES ." WHERE member_id_from=member_id_about AND";
		else
			$query .= " WHERE";
		
		$query .= " feedback_date >= '". $this->since_date->MySQLTime() ."' AND member_id_about='".$this->member_id."' AND status='A' ORDER BY feedback_date DESC;";
		//echo $query;
		
		$query = $cDB->Query($query);
		
		$i=0;
		while($row = mysql_fetch_array($query))
		{
			$this->feedback[$i] = new cFeedback;			
			$this->feedback[$i]->LoadFeedback($row[0]);
			if($this->feedback[$i]->rating == POSITIVE)
				$this->num_positive += 1;
			elseif($this->feedback[$i]->rating == NEGATIVE)
				$this->num_negative += 1;
			else
				$this->num_neutral += 1;
			$i += 1;
		}
		
		if($i == 0)
			return false;
		else
			return true;
	}
	
	function PercentPositive() {
		return number_format(($this->num_positive / ($this->num_positive + $this->num_negative + $this->num_neutral)) * 100, 0); 
	}
	
	function TotalFeedback() {
		return $this->num_positive + $this->num_negative + $this->num_neutral;
	}
	
	function DisplayFeedbackTable($member_viewing) {		
		$output = "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING=3 WIDTH=\"100%\"><TR BGCOLOR=\"#d8dbea\"><TD><FONT SIZE=2><B>"._("Type")."</B></FONT></TD><TD><FONT SIZE=2><B>"._("Date")."</B></FONT></TD><TD><FONT SIZE=2><B>"._("Context")."</B></FONT></TD><TD><FONT SIZE=2><B>"._("From")."</B></FONT></TD><TD><FONT SIZE=2><B>"._("Comment")."</B></FONT></TD></TR>";
		
		if(!$this->feedback)
			return $output. "</TABLE>";   // No feedback yet, presumably
		
		$i=0;
		foreach($this->feedback as $feedback) {
			if($feedback->rating == NEGATIVE)
				$fcolor = "red";
			elseif ($feedback->rating == POSITIVE)
				$fcolor = "#4a5fa4";
			else
				$fcolor = "#554f4f";
				
			if($i % 2)
				$bgcolor = "#e4e9ea";
			else
				$bgcolor = "#FFFFFF";
				
			$output .= "<TR VALIGN=TOP BGCOLOR=". $bgcolor ."><TD><FONT SIZE=2 COLOR=".$fcolor.">". $feedback->RatingText()."</FONT></TD><TD><FONT SIZE=2 COLOR=".$fcolor.">". $feedback->feedback_date->ShortDate() ."</FONT></TD><TD><FONT SIZE=2 COLOR=".$fcolor.">". $feedback->Context() .": " . $feedback->category->description ."</FONT></TD><TD><FONT SIZE=2 COLOR=".$fcolor.">". $feedback->member_author->member_id ."</FONT></TD><TD><FONT SIZE=2 COLOR=".$fcolor.">". $feedback->comment;
			if(isset($feedback->rebuttals))
				$output .= $feedback->rebuttals->DisplayRebuttalGroup($feedback->member_about->member_id); // TODO: Shouldn't have to pass this value, should incorporate into cFeedbackRebuttal
			
			if($feedback->rating != POSITIVE) {
				if ($member_viewing == $feedback->member_about->member_id)
					$output .= "<BR><A HREF=feedback_reply.php?feedback_id=". $feedback->feedback_id ."&mode=self&author=". $member_viewing ."&about=".$feedback->member_author->member_id .">Reply</A> "; 
				elseif ($member_viewing == $feedback->member_author->member_id)
					$output .= "<BR><A HREF=feedback_reply.php?feedback_id=". $feedback->feedback_id ."&mode=self&author=". $member_viewing ."&about=".$feedback->member_about->member_id .">Follow Up</A> ";
			}
			
			$output .= "</FONT></TD></TR>";
			$i+=1;
		}	
		return $output ."</TABLE>";
	}
	
}

class cFeedbackRebuttal {
	var $rebuttal_id;
	var $rebuttal_date;
	var $feedback_id;
	var $member_author;
	var $comment;

	function cFeedbackRebuttal ($feedback_id=null, $member_id=null, $comment=null) {
		if($feedback_id) {
			$this->feedback_id = $feedback_id;
			$this->member_author = new cMember;
			$this->member_author->LoadMember($member_id);
			$this->comment = $comment;
		}
	}
	
	function SaveRebuttal () {
		global $cDB, $cErr;
		
		$insert = $cDB->Query("INSERT INTO ". DATABASE_REBUTTAL ."(rebuttal_date, member_id, feedback_id, comment) VALUES (now(), ". $cDB->EscTxt($this->member_author->member_id) .", ". $cDB->EscTxt($this->feedback_id) .", ". $cDB->EscTxt($this->comment) .");");

		if(mysql_affected_rows() == 1) {
			$this->rebuttal_id = mysql_insert_id();	
			$query = $cDB->Query("SELECT rebuttal_date from ". DATABASE_REBUTTAL ." WHERE rebuttal_id=". $cDB->EscTxt($this->rebuttal_id) .";");
			$row = mysql_fetch_array($query);
			$this->rebuttal_date = $row[0];	
			return true;
		} else {
			return false;
		}	
	}
	
	function LoadRebuttal ($rebuttal_id) {
		global $cDB, $cErr;
		
		$query = $cDB->Query("SELECT rebuttal_date, feedback_id, member_id, comment FROM ".DATABASE_REBUTTAL." WHERE rebuttal_id=". $cDB->EscTxt($rebuttal_id) .";");
		
		if($row = mysql_fetch_array($query)) {		
			$this->rebuttal_id = $rebuttal_id;		
			$this->rebuttal_date = new cDateTime($row[0]);
			$this->feedback_id = $row[1];
			$this->member_author = new cMember; 
			$this->member_author->LoadMember($row[2]);
			$this->comment = $cDB->UnEscTxt($row[3]);

			return true;
		} else {
			$cErr->Error(_("There was an error accessing the rebuttal table").".  "._("Please try again later").".");
			include("redirect.php");
		}		
	}
}	

class cFeedbackRebuttalGroup {
	var $rebuttals;		// will be an array of cFeedbackRebuttal objects
	var $feedback_id;
	
	function LoadRebuttalGroup($feedback_id) {
		global $cDB, $cErr;
		
		$this->feedback_id = $feedback_id;
		$query = $cDB->Query("SELECT rebuttal_id FROM ".DATABASE_REBUTTAL." WHERE feedback_id=". $cDB->EscTxt($feedback_id) ." ORDER by rebuttal_date;");		
	
		$i=0;
		while($row = mysql_fetch_array($query))
		{
			$this->rebuttals[$i] = new cFeedbackRebuttal;			
			$this->rebuttals[$i]->LoadRebuttal($row[0]);
			$i += 1;
		}
		
		if($i == 0)
			return false;
		else
			return true;
	}
	
	function DisplayRebuttalGroup($member_about) {
		$output = "";
		foreach($this->rebuttals as $rebuttal) {
			if($member_about == $rebuttal->member_author->member_id)
				$output .= "<BR><B>"._("Reply").": </B>";
			else
				$output .= "<BR><B>"._("Follow Up").": </B>";
				
			$output .= $rebuttal->comment;
		}		
		return $output;
	}
}
	
?>
