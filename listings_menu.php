<?php
include_once("includes/inc.global.php");
$p->site_section = LISTINGS;
$p->page_title = $lng_update_listings;

$cUser->MustBeLoggedOn();

$list = "<STRONG>".$lng_offered_listings."</STRONG><P>";
$list .= "<A HREF=listing_create.php?type=Offer&mode=self><FONT SIZE=2>".$lng_create_new_offer_listing."</FONT></A><BR>";
$list .= "<A HREF=listing_to_edit.php?type=Offer&mode=self><FONT SIZE=2>".$lng_edit_offered_listings."</FONT></A><BR>";
$list .= "<A HREF=listing_delete.php?type=Offer&mode=self><FONT SIZE=2>".$lng_delete_offered_listings."</FONT></A><P>";

$list .= "<STRONG>".$lng_wanted_listings."</STRONG><P>";
$list .= "<A HREF=listing_create.php?type=Want&mode=self><FONT SIZE=2>".$lng_create_new_want_listing."</FONT></A><BR>";
$list .= "<A HREF=listing_to_edit.php?type=Want&mode=self><FONT SIZE=2>".$lng_edit_wanted_listings."</FONT></A><BR>";
$list .= "<A HREF=listing_delete.php?type=Want&mode=self><FONT SIZE=2>".$lng_delete_wanted_listings."</FONT></A><P>";

$list .= "<STRONG>".$lng_miscellaneous."</STRONG><P>";
$list .= "<A HREF=holiday.php?mode=self><FONT SIZE=2>".$lng_going_on_holiday."</FONT></A><BR>";

$p->DisplayPage($list);

?>
