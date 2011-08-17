<?php
include_once("includes/inc.global.php");
$p->site_section = LISTINGS;
$p->page_title = _("Create Listings");

$cUser->MustBeLoggedOn();

$list = "<STRONG>"._("Offered Listings")."</STRONG><P>";
$list .= "<A HREF=listing_create.php?type=Offer><FONT SIZE=2>"._("Create New Offer Listing")."</FONT></A><BR>";
$list .= "<A HREF=listing_to_edit.php?type=Offer><FONT SIZE=2>"._("Edit Offered Listings")."</FONT></A><BR>";
$list .= "<A HREF=listing_delete.php?type=Offer><FONT SIZE=2>"._("Delete Offered Listings")."</FONT></A><P>";

$list .= "<STRONG>"._("Wanted Listings")."</STRONG><P>";
$list .= "<A HREF=listing_create.php?type=Want><FONT SIZE=2>"._("Create New Want Listing")."</FONT></A><BR>";
$list .= "<A HREF=listing_to_edit.php?type=Want><FONT SIZE=2>"._("Edit Wanted Listings")."</FONT></A><BR>";
$list .= "<A HREF=listing_delete.php?type=Want><FONT SIZE=2>"._("Delete Wanted Listings")."</FONT></A><P>";

$p->DisplayPage($list);

?>
