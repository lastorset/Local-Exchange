<?php

include_once("includes/inc.global.php");
include_once("classes/class.info.php");

if ($_GET["destroySess"]==1) {
	
	if ($_GET["confirm"]==1) {
		session_destroy();
		echo _("Session Destroyed").". <a href=index.php>"._("Continue")."</a>";
	}
	else {
		echo _("Really Destroy Session?")." <a href=pages.php?destroySess=1&confirm=1>"._("Yes")."</a> | <a href=javascript:history.back(1)>"._("No")." "._("(go back)")."</a>";
	}
	
	exit;
}

$pg = cInfo::LoadOne($_REQUEST["id"]);

if ($pg["permission"]==3) // Admin
	$cUser->MustBeLevel(2);
else if ($pg["permission"]==2) // Committe
	$cUser->MustBeLevel(1);
else if ($pg["permission"]==1) // Members
	$cUser->MustBeLoggedOn();

$p->site_section = SECTION_INFO;

if (!$pg)
	$p->page_title = _("Oops, you have requested a non-existant page.");
else {
	$p->page_title = stripslashes($pg["title"]);
}

if ($_REQUEST["printer_view"]!=1 || !$cUser->IsLoggedOn()) { 
	print $p->MakePageHeader();
	print $p->MakePageMenu();
}
else {
	print '<head><link rel="stylesheet" href="http://'. HTTP_BASE .'/print.css" type="text/css"></link></head>';
}

if ($cUser->member_role>0)
	$p->page_title .= ' <br><font size=1><a href=do_info_edit.php?id='.$_REQUEST["id"].'>[Edit]</a></font>';

print $p->MakePageTitle();

if ($pg) {
	print stripslashes($pg["body"]);
}

if ($_REQUEST["printer_view"]!=1 || $cUser->member_role<1) { 
	print $p->MakePageFooter();
}
?>
