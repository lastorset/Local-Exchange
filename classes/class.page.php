<?php

if (!isset($global))
{
	die(__FILE__." was included without inc.global.php being included first.  Include() that file first, then you can include ".__FILE__);
}

class cPage {
	var $page_title;
	var $page_title_image; // Filename, no path
	var $page_header;	// HTML
	var $page_footer;	// HTML
	var $keywords;		
	var $site_section;
	var $sidebar_buttons; 	// An array of cMenuItem objects
	var $top_buttons;			// An array of cMenuItem objects    TODO: Implement top buttons...

	function cPage() {
		global $cUser, $SIDEBAR, $lng_administration;
		
		$this->keywords = SITE_KEYWORDS;
		$this->page_header = PAGE_HEADER_CONTENT;
		$this->page_footer = PAGE_FOOTER_CONTENT;
		
		foreach ($SIDEBAR as $button) {
			$this->AddSidebarButton($button[0], $button[1]);
		}
		
		if ($cUser->member_role > 0)
			$this->AddSidebarButton($lng_administration, "admin_menu.php");	
	}		
									
	function AddSidebarButton ($button_text, $url) {
		$this->sidebar_buttons[] = new cMenuItem($button_text, $url);
	}
	
	function AddTopButton ($button_text, $url) { // Top buttons aren't integrated into header yet...
		$this->top_buttons[] = new cMenuItem($button_text, $url);
	}

	function MakePageHeader() {
		global $cUser;
		
		if(isset($this->page_title)) 
			$title = " - ". $this->page_title;
		else
			$title = "";
		
		$output = '<HTML><HEAD><link rel="stylesheet" href="http://'. HTTP_BASE .'/'. SITE_STYLESHEET .'" type="text/css"></link><META HTTP-EQUIV="Content-Type" CONTENT="text/html;CHARSET=iso-8859-1"><meta name="description" content="'.$this->page_title.'"><meta NAME="keywords" content="'. $this->keywords .'"><TITLE>'. PAGE_TITLE_HEADER . $title .'</TITLE></HEAD><BODY>';
		
		//$output .= "<HTML><BODY>";
		//$output .= $this->page_header.$cUser->UserLoginLogout()."</h1></td></tr>";
		$output .= $this->page_header ;
	
		return $output;
	}

	function MakePageMenu() {
		global $cUser, $cSite, $cErr;
	
		$output = "<tr><td valign=top id=\"sidebar\"><ul>";
	
		foreach ($this->sidebar_buttons as $menu_item) {
			$output .= $menu_item->DisplayButton();
		}
	
        $output .= "<li>" . $cUser->UserLoginLogout() . "</li>";
		$output .= "</ul><p>&nbsp;</p></td>";
		$output .= "<TD id=\"maincontent\" valign=top>".$cErr->ErrorBox();
	
		return $output;
	}

	function MakePageTitle() {
		global $SECTIONS;
		
		if (!isset($this->page_title) or !isset($this->site_section)) {
			return "";
		} else {
			if (!isset($this->page_title_image))
				$this->page_title_image = $SECTIONS[$this->site_section][2];
				
			return '<H2><IMG SRC="http://'. IMAGES_PATH . $this->page_title_image .'" align=middle>'. $this->page_title .'</H2><P>';
		}		
	}
									
	function MakePageFooter() {
		
		global $cUser, $lng_printer_friendly_view;
		
		if ($cUser->IsLoggedOn()) {
		$tmp .= "</td></tr><tr><td id=\"footer\" colspan=2><p align=center>
			<a href=".$_SERVER["PHP_SELF"]."?printer_view=1&".$_SERVER["QUERY_STRING"]." target=_blank><img src=http://".IMAGES_PATH ."print.gif border=0><br><font size=1>".$lng_printer_friendly_view."</font></a>";
		}
		
		$tmp .= "</TD></TR>". $this->page_footer ."";
	
		$tmp .= "</BODY></HTML>";
		
		return $tmp;
	}	
			
	function DisplayPage($content = "") {
		global $cErr, $cUser, $lng_displaypage_with_no_content;
		if ($content=="")
			$cErr->Error($lng_displaypage_with_no_content,ERROR_SEVERITY_HIGH,__FILE__,__LINE__);
		
		if ($_REQUEST["printer_view"]!=1 || !$cUser->IsLoggedOn()) { 
			print $this->MakePageHeader();
			print $this->MakePageMenu();	
		}
		else {
	
			print '<head><link rel="stylesheet" href="http://'. HTTP_BASE .'/print.css" 				type="text/css"></link></head>';
		}
		
		print $this->MakePageTitle();
		
		print $content;
		
		if ($_REQUEST["printer_view"]!=1 || !$cUser->IsLoggedOn()) { 
			print $this->MakePageFooter();
		}
	}	
	
	
}

class cMenuItem {
	var $button_text;
	var $url;
	
	function cMenuItem ($button_text, $url) {
		$this->button_text = $button_text;
		$this->url = $url;
	}
	
	function DisplayButton() {
		return "<li><div align=left><a href=\"http://". HTTP_BASE ."/". $this->url ."\">". $this->button_text ."</a></div></li>";

        // The following is for url-based sessions.
//		return "<li><div align=left><a href=\"" . $this->url ."\">". $this->button_text ."</a></div></li>";
	}
}

$p = new cPage;

?>
