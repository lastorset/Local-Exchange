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
		global $cUser, $SIDEBAR;
		
		$this->keywords = SITE_KEYWORDS;
		$this->page_header = PAGE_HEADER_CONTENT;
		$this->page_footer = PAGE_FOOTER_CONTENT;
		
		foreach ($SIDEBAR as $button) {
			$this->AddSidebarButton($button[0], $button[1]);
		}
		
		if ($cUser->member_role > 0)
			$this->AddSidebarButton(_("Administration"), "admin_menu.php");	
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

		$c = get_defined_constants();
		
		$output = <<<HTML
<HTML>
	<HEAD>
		<link rel="stylesheet" href="http://{$c['HTTP_BASE']}/{$c['SITE_STYLESHEET']}" type="text/css"></link>
		<link rel="shortcut icon" href="http://{$c['IMAGES_PATH']}{$c['FAVICON']}" />
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
		<meta name="description" content="{$this->page_title}">
		<meta name="keywords" content="{$this->keywords}">
		<title>{$c['PAGE_TITLE_HEADER']}$title</title>
	</HEAD>
	<BODY>
HTML;
		
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
		
		global $cUser;
		
		if ($cUser->IsLoggedOn()) {
		$tmp .= "</td></tr><tr><td id=\"footer\" colspan=2><p align=center>
			<a href=".$_SERVER["PHP_SELF"]."?printer_view=1&".$_SERVER["QUERY_STRING"]." target=_blank><img src=http://".IMAGES_PATH ."print.gif border=0><br><font size=1>"._("Printer Friendly View")."</font></a>";
		}
		
		$tmp .= "</TD></TR>". $this->page_footer ."";
	
		$tmp .= "</BODY></HTML>";
		
		return $tmp;
	}	
			
	function DisplayPage($content = "") {
		global $cErr, $cUser;
		if ($content=="")
			$cErr->Error(_("DisplayPage() was called with no content included!  Was a blank page intended?"),ERROR_SEVERITY_HIGH,__FILE__,__LINE__);
		
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
