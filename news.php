<?php
include_once("includes/inc.global.php");
include("classes/class.news.php");
include("classes/class.uploads.php");

$p->site_section = EVENTS;
$p->page_title = $lng_news_and_events;

$output = "<P><BR>";

$news = new cNewsGroup();
$news->LoadNewsGroup();
$newstext = $news->DisplayNewsGroup();
if($newstext != "")
	$output .= $newstext;
else
	$output .= $lng_no_news_items.".<P>";

$newsletters = new cUploadGroup("N");

if($newsletters->LoadUploadGroup()) {
	$output .= "<I>".$lng_to_read_latest." ". SITE_SHORT_TITLE . " ".$lng_newsletter_go." <A HREF=newsletters.php>".$lng_here."</A>.</I>";
}

$p->DisplayPage($output);


?>
