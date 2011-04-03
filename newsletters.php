<?php

include_once("includes/inc.global.php");
include("classes/class.uploads.php");
$p->site_section = EVENTS;
$p->page_title = $lng_newsletters;

$output = "<P><BR>";

$newsletters = new cUploadGroup("N");
$newsletters->LoadUploadGroup();

$i=0;

foreach($newsletters->uploads as $newsletter) {
	if($i == 0) {
		$i = 1;
		$output .= "<B>".$lng_latest_newsletter.":</B> ". $newsletter->DisplayURL();
	} else {
		if($i == 1) {
			 $output .= "<P><BR><B>".$lng_archives.":</B><BR><UL>";
			 $i = 2;
		}
		$output .= '<LI>'. $newsletter->DisplayURL() .'</LI>';
	}
}

if ($i == 0)
	$output .= $lng_no_newsletters_yet_posted;
else
	$output .= "</UL>";

$p->DisplayPage($output);

?>
