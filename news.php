<?php
include_once("includes/inc.global.php");
include_once("classes/class.news.php");
include_once("classes/class.uploads.php");

$p->site_section = EVENTS;
$p->page_title = _("News and Events");

$output = "<P><BR>";

$news = new cNewsGroup();
$news->LoadNewsGroup();
$newstext = $news->DisplayNewsGroup();
if($newstext != "")
	$output .= $newstext;
else
	$output .= _("There are no current news items").".<P>";

$newsletters = new cUploadGroup("N");

if($newsletters->LoadUploadGroup()) {
	$output .= "<I>"._("To read the latest")." ". SITE_SHORT_TITLE . " "._("newsletter, go")." <A HREF=newsletters.php>"._("here")."</A>.</I>";
}

$p->DisplayPage($output);


?>
