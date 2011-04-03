<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

require_once ("class.listing.php");
require_once ("File/PDF.php");
//require_once ("File_PDF/PDF.php");

class cDirectory {
	var $member_list;
	var $offer_list;
	var $want_list;
	var $pdf;
	var $font;
	var $font_size;
	var $font_spacing;
	var $margin;
	var $column;
	

	function cDirectory () {
		$this->member_list = new cMemberGroup();
		$this->member_list->LoadMemberGroup();
		$this->offer_list = new cListingGroup(OFFER_LISTING);
		$this->offer_list->LoadListingGroup("%");
		$this->want_list = new cListingGroup(WANT_LISTING);
		$this->want_list->LoadListingGroup("%");
		$this->column = 1;	
		$this->margin = 15;
		$this->font = "Times";
		$this->font_size = 12;
		$this->font_spacing = 5;
		$this->pdf = &File_PDF::factory("P", "mm", "A4");
		$this->pdf->open();
		$this->pdf->addPage("P");
		$this->pdf->setFont($this->font,"",$this->font_size);
		$this->pdf->setMargins($this->margin, $this->margin, "105");
		$this->pdf->setAutoPageBreak(true,"2");
		$this->pdf->setXY($this->margin,$this->margin);
		$this->pdf->SetDisplayMode("real","single");
	}
	
	function DownloadDirectory () {	
		$this->PrintFirstPage();
	
		$this->PrintSectionHeader($lng_member_information,FIRST);
		$this->PrintMembers();
	
		$this->PrintSectionHeader($lng_offered_listings);
		$this->PrintListings(OFFER_LISTING);
		
		$this->PrintSectionHeader($lng_wanted_listings);
		$this->PrintListings(WANT_LISTING);
		
		$this->pdf->Output($lng_pdf_file_name,true);
	}
	
	function PrintMembers() {	
		foreach($this->member_list->members as $member) {
			if ($member->account_type == "F")
				continue;	// Skip fund accounts
		
			$this->PrintLine("");
			$this->PrintTitle($member->PrimaryName());
			$this->PrintLine(" (". $member->member_id .")");
			if($member->person[0]->email)
				$this->PrintLine($member->person[0]->email);
			if($member->person[0]->phone1_number) {
				$this->PrintText($member->person[0]->DisplayPhone(1));
				if($member->person[0]->phone2_number)
					$this->PrintText(", ". $member->person[0]->DisplayPhone(2));
				$this->PrintLine("");				
			}
		}
	}
	
	function PrintListings($type) {
		$curr_category = "";
		if($type == OFFER_LISTING)
			$listings =& $this->offer_list->listing;
		else 
			$listings =& $this->want_list->listing;
			
		foreach ($listings as $listing) {
			if($listing->status != ACTIVE)
				continue;
				
			if($listing->category->id != $curr_category) {
				$this->PrintCategoryHeader($listing->category->description);
				$curr_category = $listing->category->id;
			}
			
			$this->PrintTitle($listing->title);
			$this->PrintDescription($listing->description);
			$this->PrintMember($listing->member->PrimaryName());
		}	
	}
	
	function PrintSectionHeader($header, $first_page=false) {
		if(!$first_page)
			$this->NewPage();
		else
			$header = "\n". $header;
			
		$this->pdf->setFont($this->font,"B", $this->font_size + 6);
		$this->pdf->Write($this->font_spacing + 2, $header . "\n");
		$this->pdf->setFont($this->font,"", $this->font_size);	
	}
	
	function PrintCategoryHeader($category) {
		$this->DoPageBreaks();
		$this->pdf->setFont($this->font,"B", $this->font_size + 2);
		$this->pdf->Write($this->font_spacing + 1, "\n" . $category . "\n");
		$this->pdf->setFont($this->font,"", $this->font_size);
	} 
	
	function PrintFirstPage() {
		$this->pdf->setFont($this->font,"BI",26);
		$this->pdf->Write(8,SITE_LONG_TITLE ." - ");
		$this->pdf->Write(8,$lng_members_directory."\n");
		$this->pdf->setFont($this->font,"",$this->font_size);
	}
	
	function PrintTitle($title) {
		$this->pdf->setFont($this->font,"BI",$this->font_size);
		$this->pdf->Write($this->font_spacing, $title);
		$this->pdf->setFont($this->font,"",$this->font_size);
	}
	
	function PrintDescription($desc) {
		if ($desc) {
			$this->pdf->setFont($this->font,"BI",$this->font_size);
			$this->pdf->Write($this->font_spacing, ": ");
			$this->pdf->setFont($this->font,"",$this->font_size);
			
			if(strlen($desc) < 40) {
				$this->pdf->Write($this->font_spacing,$desc);
			} else {
				// Need to print long descriptions word-by-word so my 
				// simple column pagebreak system will work.  
				// TODO: Should extend File_PDF class instead...			
				$words = split(" ",$desc);
				foreach($words as $word) {
					$this->DoPageBreaks();
					$this->pdf->Write($this->font_spacing,$word . " ");
				}
			}
		} 
	}
	
	function PrintMember($name) {
		$this->PrintLine(" (". $name .")");
	}
	
	function PrintText ($text) {
		$this->pdf->Write($this->font_spacing, $text);
	}
	
	function PrintLine($line) {
		$this->DoPageBreaks();
		$this->pdf->Write($this->font_spacing, $line . "\n");
	}

	function DoPageBreaks() {
		if($this->pdf->getY() >= 270) {
			if($this->column == 2) {
				$this->NewPage();
			} else { // New Column
				$this->NewColumn();
			} 
		}	
	}
	
	function NewPage() {
		$this->pdf->addPage("P");
		$this->pdf->setXY($this->margin,$this->margin);
		$this->pdf->setMargins($this->margin,$this->margin,"105");
		$this->pdf->setFont($this->font,"", $this->font_size);
		$this->column = 1;	
	}
	
	function NewColumn() {
		$this->pdf->setMargins("115",$this->margin,$this->margin);
		$this->pdf->setXY("115",$this->margin);
		$this->column = 2;	
	}
}

?>
